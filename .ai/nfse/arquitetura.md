# Arquitetura — Motor de Emissão NFS-e In-House

**Pré-requisito de leitura:** [`pesquisa.md`](pesquisa.md) (conceitos, APIs, prazos, complexidades).

**Escopo:** desenho da solução sobre o codebase existente. Nenhum código aqui — estrutura, contratos conceituais, deltas de dados e justificativas. Requisitos: multi-município, multi-provedor, multi-certificado, extensível para NF-e e CT-e.

---

## 1. Visão geral e princípios

**Estilo: Ports & Adapters (hexagonal) pragmático sobre o Laravel existente.**

O app já pratica o padrão em miniatura: `MarketplaceImporterInterface`/`InvoiceProviderInterface` são ports; `ShopeeImporter`/`NullInvoiceProvider` são adapters. A arquitetura NFS-e **generaliza esse padrão**, sem introduzir DDD purista (sem agregados/repos abstratos que o Eloquent já resolve).

Princípios:
1. **O pipeline atual permanece o orquestrador.** `GenerateInvoicesJob → IssueInvoiceJob → UploadInvoiceFilesJob → GenerateZipJob` não muda de forma — muda o que acontece dentro de `IssueInvoiceAction`.
2. **Fiscal é um módulo, não o app.** Tudo que é "Brasil fiscal" (XML, assinatura, transporte, certificados) vive em `app/Fiscal/`, isolado do domínio de importação/billing.
3. **O documento fiscal é a unidade de extensão.** NFS-e hoje; NF-e e CT-e depois — compartilham certificado, assinatura, storage e eventos; diferem em leiaute, transporte e autorizador.
4. **Cada dependência externa atrás de um port.** Governo muda leiaute/URL sem aviso; a troca deve ser local ao adapter.

## 2. Estrutura de pastas proposta

```
app/
├── Fiscal/                              # NOVO — módulo fiscal
│   ├── Domain/                          # tipos puros, sem I/O
│   │   ├── ValueObjects/                #   CnpjCpf, ChaveAcesso, InscricaoMunicipal,
│   │   │                                #   Competencia, ItemLc116, Aliquota, DpsId
│   │   ├── Enums/                       #   RegimeTributario, AmbienteEmissao (producao|
│   │   │                                #   producao_restrita), TipoDocumentoFiscal (nfse|nfe|cte),
│   │   │                                #   MotivoRejeicao (categorias)
│   │   └── Exceptions/                  #   RejeicaoTerminalException, TransmissaoException,
│   │                                    #   CertificadoInvalidoException
│   ├── Application/                     # casos de uso fiscais (chamados pelas Actions/Jobs)
│   │   ├── EmitirNfse/                  #   monta payload → numera → gera XML → assina →
│   │   │                                #   transmite → interpreta retorno
│   │   ├── CancelarNfse/
│   │   ├── SubstituirNfse/
│   │   └── SincronizarEventosAdn/      #   polling NSU (eventos externos)
│   ├── Ports/                           # interfaces (contratos)
│   │   ├── FiscalDriverInterface        #   emitir/consultar/cancelar/baixarDanfse (ver §5)
│   │   ├── XmlSignerInterface
│   │   ├── CertificateVaultInterface
│   │   ├── DpsNumberAllocatorInterface
│   │   └── MunicipalityResolverInterface
│   └── Infrastructure/                  # adapters (I/O real)
│       ├── Drivers/
│       │   ├── NacionalNfse/            #   MVP: DpsXmlBuilder, SefinClient (mTLS),
│       │   │                            #   AdnClient, DanfseClient, RejectionMapper
│       │   ├── Abrasf/                  #   futuro: base SOAP + variantes (Ginfes, Betha…)
│       │   └── NullDriver/              #   substitui NullInvoiceProvider (dev/test)
│       ├── Signing/                     #   XmlSecLibsSigner (robrichards/xmlseclibs)
│       ├── Certificates/                #   EncryptedS3CertificateVault, Pkcs12Reader
│       └── Routing/                     #   DatabaseMunicipalityResolver
├── Actions/ Jobs/ Models/ …             # existentes — ajustes pontuais (ver §7)
```

**Por quê assim:** espelha a convenção que o repo já tem (`Marketplace/` e `InvoiceProvider/` são módulos por contexto); `Domain/Application/Ports/Infrastructure` dá fronteiras claras sem quebrar o restante do app, que continua "Laravel normal" (Actions, Jobs, Models). `app/InvoiceProvider/` é **absorvido** por `app/Fiscal/` (o `NullInvoiceProvider` vira `NullDriver`).

## 3. Fronteiras de serviço

| Serviço (lógico) | Responsabilidade | Não faz |
|---|---|---|
| **Emissão** | Orquestrar DPS→XML→assinatura→transmissão→retorno | Não conhece HTTP/SOAP (delega ao driver) |
| **Certificados** | Guardar/validar/entregar material criptográfico; expiração | Não conhece leiautes fiscais |
| **Roteamento** | Município (IBGE) → driver + config | Não conhece XML |
| **Documentos** | Persistir DPS/XML/DANFSE em S3; retenção 5 anos | Não gera conteúdo |
| **Eventos fiscais** | Cancelamento, substituição, sync ADN | Não decide regra de negócio do app |

São fronteiras **lógicas** (namespaces + interfaces), não microserviços — monólito Laravel continua. **Por quê:** o volume (dezenas de emissões/min no pior caso) não justifica infra distribuída; fronteiras lógicas dão o mesmo desacoplamento com custo zero de operação, e permitem extrair um "signing service" isolado no futuro se o requisito de segurança apertar (§11).

## 4. Camada de domínio

**Novas entidades (Eloquent, em `app/Models/` — seguem o padrão existente):**
- **`Issuer`** — perfil fiscal do emitente, 1:1 com `User` (dados: ver §8). Separado de `User` porque: (a) `users` já acumula auth+billing+Cashier; (b) prepara multi-organização futura (N issuers por conta); (c) o gate de onboarding é uma pergunta sobre `Issuer`, não sobre `User`.
- **`Certificate`** — N:1 com `Issuer` (histórico de renovações; um ativo por vez).
- **`DpsSequence`** — numeração por (issuer, série, ambiente) com lock pessimista.
- **`MunicipalityProvider`** — roteamento IBGE→driver.

**Value objects e enums (em `app/Fiscal/Domain/`):** `CnpjCpf` (com validação módulo-11 — **resolve também o TODO existente** em `ValidateImportRowAction::isValidTaxDocument`), `ChaveAcesso` (50 díg., parse/validação), `DpsId` (concatenação DPS+cMun+tpInsc+doc+série+número), `Competencia`, `ItemLc116`, `Aliquota`, `RegimeTributario` (MEI/Simples/Normal), `AmbienteEmissao`.

**Por quê:** os VOs concentram as regras de formato que causam a maioria das rejeições (E0004 etc.) em código puro e unit-testável, sem tocar em banco/rede — a base de testes de domínio que hoje não existe.

## 5. Interfaces (ports)

**`FiscalDriverInterface`** — substitui o atual `InvoiceProviderInterface` (2 métodos) por um contrato de ciclo de vida completo. Operações (conceituais):
- `slug()` — identificador persistido em `invoices.provider` (mantém compat).
- `emitir(payload) → ResultadoEmissao` — síncrono ou "pendente+protocolo" (para ABRASF assíncrono), retornando chave, número, XML autorizado.
- `consultar(chave|protocolo) → SituacaoDocumento` — resolve pendências (polling ABRASF, idempotência pós-timeout).
- `cancelar(chave, motivo) → ResultadoEvento`
- `baixarXml(chave)` / `baixarDanfse(chave)` — bytes, obtidos com a autenticação do driver.
- `ambiente()` — produção/produção restrita.

Demais ports: **`XmlSignerInterface`** (assinar elemento por Id com material do certificado), **`CertificateVaultInterface`** (armazenar PFX cifrado, validar senha/validade/cadeia, entregar material decifrado só em memória), **`DpsNumberAllocatorInterface`** (próximo número por issuer+série, transacional), **`MunicipalityResolverInterface`** (IBGE→driver configurado).

**Por quê o contrato expandido:** o atual `issue(): array` não expressa cancelamento, consulta, idempotência nem download autenticado — tudo obrigatório num motor real (pesquisa §12–15). O retorno tipado (DTO) substitui o array shape frouxo. `slug()` preservado para não migrar dados existentes.

## 6. Adapters (infraestrutura)

- **`NacionalNfseDriver` (MVP):** `DpsXmlBuilder` (Anexo I v1.01 + validação XSD local), `SefinClient` (Guzzle/cURL com mTLS por certificado do emitente, gzip+base64, tratamento 429/`Retry-After`, timeouts), `DanfseClient`, `AdnClient` (NSU), `RejectionMapper` (código→categoria terminal/retryável→mensagem pt-BR).
- **`NullDriver`:** comportamento do `NullInvoiceProvider` atual — mantém `composer test`/dev sem certificado. Continua **bound por padrão em ambiente de teste**.
- **`Abrasf/` (futuro):** classe base SOAP (lote, polling, assinatura dupla) + subclasses por fornecedor (Ginfes, Betha, WebIss…), cada uma parametrizada por município via `MunicipalityProvider.config` (JSON: URLs, versão, quirks). Espelha o que `marketplaces.importer_class + config` já faz para importadores.
- **Descoberta:** `DatabaseMunicipalityResolver` consulta `municipality_providers` pelo IBGE do **emitente**; seed inicial: todos os municípios aderentes ao padrão nacional → `nacional`. Bind do driver por invoice no container (o driver é escolhido **por emitente**, não globalmente — muda o `AppServiceProvider` de bind fixo para factory/manager, no padrão dos Managers do Laravel, ex. `FiscalManager::driver('nacional')`).

**Por quê:** o registro em banco (e não só config) permite ativar município/driver sem deploy e registra a decisão por usuário; o padrão Manager é idiomático Laravel e o time já o conhece via filesystem/queue.

## 7. Camada de aplicação (o que muda no pipeline atual)

| Peça atual | Destino |
|---|---|
| `GenerateInvoicesJob` / `GroupRowsForInvoicingAction` | **Inalterados** (agrupamento por seller+mês continua). Ganham pré-check do gate fiscal (§9). |
| `IssueInvoiceAction` | Passa a delegar ao caso de uso `EmitirNfse`: resolver driver (issuer) → alocar número DPS → montar/assinar XML → transmitir → persistir retorno. Mantém a responsabilidade por status/eventos do `Invoice`. |
| `IssueInvoiceJob` | Mantém throttle/retries; throttle passa a ser **por issuer** (`invoice-provider:{issuer_id}`, pesquisa §20); em timeout, chama `consultar()` antes de retry (idempotência). |
| `UploadInvoiceFilesJob` | Deixa de baixar `pdf_url`/`xml_url` públicos; recebe bytes via `baixarXml`/`baixarDanfse` do driver. Persistência S3/`invoice_files` inalterada. |
| `RetryInvoiceAction` | Só re-enfileira rejeições **retryáveis**; terminais exigem correção cadastral. |
| Novos jobs | `SincronizarEventosAdnJob` (agendado, NSU); `NotifyCertificateExpiryJob` (agendado, alertas 30/15/7 dias). |

**Por quê:** preserva tudo que já funciona (batching, eventos, notificações, polling do frontend) e concentra a mudança num único ponto (`IssueInvoiceAction` → `EmitirNfse`), reduzindo risco de regressão.

## 8. Modelo de dados — deltas (especificação, sem migrations)

**`issuers`** (1:1 user, `BelongsToUserScope`): `user_id`, `tax_document` + `document_type`, `legal_name`, `trade_name`, `municipal_registration`, endereço completo + `address_ibge_code`, `tax_regime` (mei/simples/normal), `special_regime`, `cnae`, `service_code_lc116`, `municipal_service_code`, `iss_rate` (nullable — pode vir da parametrização), `iss_withheld` (bool), `environment` (producao/producao_restrita), `fiscal_profile_completed_at` (nullable — materializa o gate §9), timestamps/softDeletes.

**`certificates`** (N:1 issuer): `issuer_id`, `storage_path` (S3 privado, PFX **cifrado pela aplicação**), `password_encrypted` (cast encrypted), `subject_cn`, `subject_document` (CNPJ do cert — validar == issuer), `serial_number`, `valid_from`, `valid_until`, `chain_ok` (bool), `active` (um por issuer), `last_used_at`, timestamps/softDeletes. **Nunca** expor download.

**`dps_sequences`**: `issuer_id`, `series` (5), `environment`, `last_number` (bigint) — incremento com `lockForUpdate` dentro da transação de emissão.

**`municipality_providers`**: `ibge_code` (7, unique), `driver` (nacional/ginfes/…), `config` (json), `active`.

**`invoices` — colunas novas:** `issuer_id` (FK), `dps_series`, `dps_number`, `service_code`, `iss_rate`, `iss_amount`, `environment`, `substituted_by_id` (nullable self-FK), `cancellation_reason` (nullable). `access_key` (50) e `provider_payload` já servem. Novos valores em `InvoiceEventType`: `substituted`, `cancellation_requested`.

**`sellers` — colunas novas (opcionais):** `municipal_registration`, `is_iss_withholder` (bool) — tomador que retém ISS.

**`invoice_files`:** enum `type` ganha `dps_xml` (guardar a DPS enviada além da NFS-e retornada — pesquisa §16).

**Por quê:** issuer separado (justificado em §4); sequência em tabela própria porque numeração é o único ponto do sistema que exige lock pessimista — isolá-la evita contenção na tabela `invoices`; `environment` em issuer *e* invoice permite homologação por usuário e auditoria do ambiente em que cada nota nasceu.

## 9. Gate de onboarding fiscal

**Regra de produto:** o usuário só importa/processa após perfil fiscal completo.

- **Definição de "completo"** (método canônico `Issuer::isFiscalProfileComplete()`, materializado em `fiscal_profile_completed_at`): todos os campos obrigatórios do §8 preenchidos **+** documento validado (módulo-11) **+** município roteável (`MunicipalityResolver` encontra driver ativo) **+** **certificado ativo, senha válida e não expirado** (sem exceção MEI: a emissão via API exige certificado — pesquisa §6).
- **Enforcement em 3 camadas:**
  1. **Rotas** — middleware `EnsureFiscalProfileComplete` nas rotas de `imports.*` (create/store) e `invoices.generate`; redireciona para o onboarding.
  2. **Domínio** — `GenerateInvoicesJob`/`EmitirNfse` re-verificam (defesa em profundidade: certificado pode expirar entre upload e emissão) → falha com evento claro, nunca chega ao provedor.
  3. **UX** — página `Settings/Fiscal` (Inertia) com checklist de progresso (dados fiscais → endereço → serviço/alíquota → certificado); banner persistente no `AppLayout` e botões de upload desabilitados com CTA enquanto incompleto. Alertas de expiração de certificado reaproveitam o sistema de notificações existente.
- **Sequenciamento no funil:** cadastro → e-mail verificado → **perfil fiscal** → importar → emitir. O plano free (5 NF) continua controlando volume, não acesso.

**Por quê 3 camadas:** o middleware dá UX imediata; a re-verificação no job cobre corrida (certificado expira, perfil editado com import em fila); a UI evita frustração de descobrir o bloqueio só no clique.

## 10. Extensibilidade futura (NF-e, CT-e)

| Reutilizado como está | Específico por documento |
|---|---|
| `CertificateVault` (mesmo e-CNPJ assina NF-e/CT-e) | Builder de XML (leiautes distintos) |
| `XmlSigner` (XMLDSig idêntico) | Transporte (NF-e/CT-e = SOAP SEFAZ **estadual**, autorizador por UF) |
| Storage/retenção (`invoice_files`, S3) | Chave de acesso (44 díg. vs 50) → VO por tipo |
| Esqueleto de eventos/cancelamento | Contingência (SVC/EPEC — só NF-e/CT-e) |
| Gate de onboarding, auditoria, throttle por emitente | DANFE/DACTE próprios (não há API de PDF) |

`TipoDocumentoFiscal` no domínio e `FiscalDriverInterface` neutro (fala em "documento", não "NFS-e") garantem que NF-e entra como novo conjunto de drivers (`Drivers/NfeSefaz/`), sem tocar nos ports. **Decisão consciente:** não sobre-generalizar agora — nenhuma abstração especulativa para SEFAZ estadual até existir demanda; apenas não bloquear (nomes neutros, VOs por tipo).

## 11. Segurança e multi-tenancy

- `Issuer`, `Certificate` com `#[ScopedBy([BelongsToUserScope::class])]` (padrão do repo) + policies (`IssuerPolicy`, `CertificatePolicy`).
- Certificado: PFX cifrado pela aplicação **antes** do S3 (envelope: chave de dados por certificado, cifrada pela `APP_KEY`; upgrade path para AWS KMS); senha em cast `encrypted`; decifração apenas em memória no momento de assinar/conectar; PEM temporário em disco efêmero `0600` com unlink em `finally` (limitação do cURL — pesquisa §17); proibido logar material do certificado.
- `AuditObserver` (já existente) estendido a `Issuer`/`Certificate`; evento de auditoria a cada uso de certificado.
- Horizon: emissão continua na fila `default`; segregar workers por fila já é suportado — nenhum requisito novo.

## 12. Justificativa das decisões (resumo)

| Decisão | Por quê |
|---|---|
| Hexagonal leve, monólito | Repo já usa ports/adapters em miniatura; volume não justifica microserviços; fronteiras lógicas dão o desacoplamento necessário |
| `app/Fiscal/` absorve `app/InvoiceProvider/` | Um módulo por contexto (padrão do repo); "provider" vira caso particular de "driver fiscal" |
| Driver **nacional primeiro** | Decisão do usuário + obrigatoriedade Simples Nacional em 09/2026 cobre o público-alvo com 1 integração REST |
| `Issuer` separado de `User` | `users` já é auth+billing; gate fiscal é atributo do emitente; prepara multi-org |
| Numeração em `dps_sequences` com lock | Rejeição E0004/duplicidade é o erro mais comum; serialização por emitente é requisito, não otimização |
| Contrato de driver com ciclo completo | `issue()` de 2 métodos não expressa cancelar/consultar/idempotência/download autenticado |
| Roteamento em banco (IBGE→driver) | Ativar municípios sem deploy; espelha `marketplaces.importer_class` |
| DANFSE oficial (não gerar PDF) no nacional | Menos código e layout sempre conforme; PDF próprio só na trilha ABRASF |
| Gate em 3 camadas | Middleware = UX; job = corrida/expiração; UI = clareza |
| `NullDriver` mantido | Testes e dev sem certificado continuam funcionando |
| VOs para regras de formato | Rejeições nascem de formato; código puro unit-testável inicia a suíte de testes de domínio |

## 13. Roadmap por fases

| Fase | Entrega | Itens da pesquisa (§21) | Critério de aceite |
|---|---|---|---|
| **F1** | `Issuer` + `Certificate` (cofre) + gate de onboarding + página `Settings/Fiscal` | 1, 2 | Usuário sem perfil completo não importa; PFX validado e cifrado; alertas de expiração |
| **F2** | Domínio: VOs (CnpjCpf módulo-11, ChaveAcesso, DpsId), `dps_sequences`, `DpsXmlBuilder` + XSD, `XmlSecLibsSigner` | 3, 4, 5 | DPS gerada e assinada valida contra XSD v1.01 em testes unitários (sem rede) |
| **F3** | `SefinClient` mTLS + `EmitirNfse` fim-a-fim em **produção restrita**; `RejectionMapper`; idempotência pós-timeout | 6, 7 | NFS-e real autorizada em produção restrita a partir de um import Shopee |
| **F4** | XML/DANFSE → S3 (`UploadInvoiceFilesJob` re-plugado), parametrização municipal + cache, ZIP real | 8, 12 | Download de PDF+XML autênticos; ZIP com arquivos reais |
| **F5** | Cancelamento + substituição + UI | 9, 10 | Cancelar/substituir refletidos em status, eventos e ADN |
| **F6** | `SincronizarEventosAdnJob`, roteamento municipal completo, **go-live produção** | 11, 13 | Nota real em produção; eventos externos detectados |
| **F7+** | Adapters ABRASF sob demanda; campos IBS/CBS (RTC) | 14, 15, 17 | Por município/regulação |

Cada fase termina com testes de feature do fluxo (a suíte de domínio hoje inexistente nasce em F2 — resolve também o P1 "Domain Test Suite" do backlog).
