# Pesquisa Técnica — Emissão de NFS-e In-House no Brasil

**Objetivo:** base de conhecimento para construir um motor próprio de emissão de NFS-e (sem PlugNotas, NFE.io, Focus NF-e), priorizando o **Padrão Nacional NFS-e** (API REST gov.br), com trilha futura para provedores municipais ABRASF.

**Contexto do produto:** cada usuário da plataforma (afiliado, tipicamente MEI/ME) emite NFS-e **em nome próprio** contra os sellers (tomadores) referentes a comissões de marketplace. A plataforma automatiza a emissão em lote a partir dos relatórios importados.

**Data da pesquisa:** 19/07/2026. Versões e prazos citados foram verificados via web nesta data — revalidar antes de implementar (o cronograma da NFS-e nacional e a Reforma Tributária mudam rápido).

---

## 1. Como funciona a NFS-e no Brasil

- A NFS-e documenta **prestação de serviços**, tributada pelo **ISS (ISSQN)** — imposto **municipal** (LC 116/2003 define a lista de serviços e regras). Por isso, historicamente, **cada município** tinha seu próprio sistema, layout e webservice de NFS-e (~5.570 municípios).
- Conceitos-chave:
  - **RPS (Recibo Provisório de Serviços)** — modelo ABRASF: documento provisório gerado pelo contribuinte e convertido em NFS-e pela prefeitura.
  - **DPS (Declaração de Prestação de Serviços)** — modelo nacional: substitui o RPS; é o XML que o contribuinte gera, assina e transmite; a Sefin Nacional o converte em NFS-e.
  - **Competência** — data do fato gerador (prestação do serviço); no nosso caso, o `reference_month` da comissão.
  - **Item da LC 116** — código do serviço (ex.: **10.05** — "Agenciamento, corretagem ou intermediação de bens móveis…" é o candidato natural para comissão de afiliado; confirmar com contador) + **código de tributação municipal** quando exigido.
- **Serviço de intermediação/afiliado:** o afiliado presta serviço de agenciamento ao seller → emite NFS-e tendo o seller como **tomador**. ISS é devido, em regra, no **município do prestador** (exceções do art. 3º da LC 116 não se aplicam a agenciamento comum).

## 2. Arquitetura do Sistema Nacional NFS-e (Padrão Nacional)

Fonte oficial: [Portal NFS-e gov.br — Documentação Técnica](https://www.gov.br/nfse/pt-br/biblioteca/documentacao-tecnica) e [Manual das APIs do Contribuinte (Emissor Público API) v1.2, out/2025](https://www.gov.br/nfse/pt-br/biblioteca/documentacao-tecnica/documentacao-atual/manual-contribuintes-emissor-publico-api-sistema-nacional-nfs-e-v1-2-out2025.pdf).

Componentes:
- **Sefin Nacional** — recepciona a DPS e **gera a NFS-e de forma síncrona** (POST com a DPS assinada → resposta já com a NFS-e autorizada ou rejeição).
- **ADN (Ambiente de Dados Nacional)** — repositório nacional de DF-e; distribui documentos e eventos entre fiscos, prestadores e tomadores (DFe distribution, paginado por NSU).
- **Emissor Público Nacional** — web/app gratuito (login gov.br); referência de UX, não usado por nós.
- **CNC (Cadastro Nacional de Contribuintes)** — cadastro nacional; APIs de consulta.
- **Painel Administrativo Municipal** — onde o município parametriza alíquotas, regimes, retenções (consultáveis via **API de Parametrização**).

**Formato de comunicação:** rotas REST com envelope **JSON**; o documento fiscal em si é **XML assinado (XMLDSig), comprimido GZip e codificado Base64** dentro do JSON.

**URLs base (verificadas 07/2026):**

| Serviço | Produção Restrita (homologação) | Produção |
|---|---|---|
| Sefin Nacional (emissão DPS, eventos) | `https://sefin.producaorestrita.nfse.gov.br/API/SefinNacional/` | `https://sefin.nfse.gov.br/SefinNacional/` |
| ADN Contribuintes (distribuição DF-e) | `https://adn.producaorestrita.nfse.gov.br/contribuintes/` | `https://adn.nfse.gov.br/contribuintes/` |
| DANFSE (PDF) | `https://adn.producaorestrita.nfse.gov.br/danfse/` | `https://adn.nfse.gov.br/danfse/` |
| Parametrização municipal | `https://adn.producaorestrita.nfse.gov.br/parametrizacao/` | `https://adn.nfse.gov.br/parametrizacao/` |
| Swagger contribuintes | [producaorestrita.nfse.gov.br/swagger/contribuintesissqn](https://www.producaorestrita.nfse.gov.br/swagger/contribuintesissqn/) | idem em produção |

**Versões vigentes (04/2026):** Schemas XSD NFS-e **v1.01** (fev/2026); Anexo I — Leiaute SEFIN ADN-DPS **v1.01**; Anexo II — Eventos (PedRegEvt) **v1.01**; Anexo B — lista NBS2 de serviços **v1.01**; Anexo C — IndOp/**IBSCBS v1.01** (Reforma Tributária: campos de IBS/CBS já entrando no leiaute — acompanhar `RTC`).

**Chave de acesso: 50 dígitos.** O ID da DPS é a concatenação `"DPS" + cMunEmi(7) + tpInscFed(1) + CNPJ/CPF(14, CPF com zeros à esquerda) + série(5) + número(15)`. A coluna `invoices.access_key` (50 chars) já comporta a chave da NFS-e.

**Cronograma/obrigatoriedade (verificado 07/2026):**
- **MEI**: obrigado ao padrão nacional desde **set/2023** ([Receita Federal](https://www.gov.br/receitafederal/pt-br/assuntos/noticias/2026/abril/nfs-e-de-padrao-nacional-sera-obrigatoria-para-optantes-do-simples-nacional)).
- **ME/EPP do Simples Nacional**: obrigatoriedade a partir de **01/09/2026** — exatamente o público-alvo da plataforma; o timing do projeto é excelente.
- Municípios >50 mil habitantes devem operar no padrão nacional até o 2º semestre de 2026, sob pena de sanções (perda de transferências voluntárias, restrições no IBS).

## 3. Provedores municipais (trilha futura)

Para municípios não aderentes ao padrão nacional (ou usuários fora dele), o mercado é fragmentado em fornecedores de sistemas que implementam (variações do) padrão ABRASF, cada um com WSDL, URLs e "dialetos" próprios:

| Fornecedor | Exemplos de municípios | Observações |
|---|---|---|
| GINFES | Guarulhos, Santo André… | ABRASF 1.x/2.x, SOAP |
| Betha | Centenas de municípios SC/PR/RS | ABRASF 2.x |
| ISSNet | Cuiabá, Campo Grande… | ABRASF 2.x |
| WebISS | Petrópolis, Uberaba… | ABRASF 2.x |
| GISS Online | Santos, Praia Grande… | dialeto próprio sobre ABRASF |
| **São Paulo (prefeitura própria)** | SP capital | **Padrão próprio** ("Nota do Milhão"/NFTS), não-ABRASF |
| Nota Carioca (Rio) | RJ capital | ABRASF 1.0 adaptado |

Lição da comunidade ([nfephp-org/sped-nfse](https://packagist.org/packages/nfephp-org/sped-nfse)): *"não existe padrão único; municípios trocam layout e provedor sem critério"* → cada provedor é um **adapter** isolado, com tabela de roteamento por município (ver §10).

## 4. Especificações ABRASF

- Versões: **1.0** (2008, ainda usada — ex. Nota Carioca), **2.01–2.04** (atual: [ABRASF v2.04](https://abrasf.org.br/biblioteca/arquivos-publicos/nfs-e/versao-2-04), com [WSDL oficial](https://abrasf.org.br/biblioteca/arquivos-publicos/nfs-e/versao-2-04/wsdl-nfse-v2-04)).
- Operações SOAP típicas:
  - `RecepcionarLoteRps` — **assíncrono**: envia lote (até 50 RPS), recebe **protocolo**; exige polling.
  - `RecepcionarLoteRpsSincrono` — síncrono (2.x).
  - `GerarNfse` — síncrono, 1 RPS (2.x).
  - `ConsultarLoteRps`, `ConsultarSituacaoLoteRps` — polling do lote.
  - `ConsultarNfsePorRps`, `ConsultarNfseFaixa`/`ConsultarNfseServicoPrestado` — consulta.
  - `CancelarNfse`, `SubstituirNfse` — cancelamento/substituição.
- **Assinatura em lote:** cada RPS é assinado individualmente **e** o lote recebe uma assinatura adicional (assinar o lote por último para não invalidar as internas).
- Municípios publicam "manuais complementares" que **alteram** o padrão (campos extras, versões de schema diferentes, SOAPAction distinta) — o adapter por fornecedor precisa de config por município.

## 5. Schemas XML

- **Nacional:** XSDs oficiais v1.01 ([biblioteca](https://www.gov.br/nfse/pt-br/biblioteca/documentacao-tecnica/documentacao-atual)) — `DPS_v1.01.xsd`, `NFSe_v1.01.xsd`, eventos (`pedRegEvento`). Elementos centrais da DPS: `infDPS` (id, `tpAmb`, `dhEmi`, `dCompet`, `prest`, `toma`, `serv` com `cServ`/`cTribNac`, `valores` com `vServ`, ISS, retenções) + `Signature`.
- **ABRASF:** `EnviarLoteRpsEnvio`, `Rps/InfDeclaracaoPrestacaoServico`, etc., por versão.
- **Validação local obrigatória:** validar contra XSD **antes** de transmitir (via `DOMDocument::schemaValidate`) — elimina a maior parte das rejeições e permite mensagens de erro amigáveis.
- Atenção à **Reforma Tributária (RTC)**: Anexo C IndOp/IBSCBS já introduz indicadores IBS/CBS no leiaute; a partir de 2026/2027 os documentos fiscais ganham campos de IBS/CBS. Projetar o gerador de XML para evoluir de versão de schema sem reescrita.

## 6. Certificado digital A1

- **ICP-Brasil**, tipos: **A1** (arquivo `.pfx`/PKCS#12, validade 12 meses, chave privada exportável — o único viável para SaaS server-side) e A3 (token/smartcard físico — inviável para automação em nuvem).
- **e-CNPJ** (da empresa/MEI) ou **e-CPF** (da pessoa física). Para emissão via API em nome do usuário: **e-CNPJ A1 do próprio usuário** (MEI tem CNPJ).
- Conteúdo do PFX: certificado + chave privada + cadeia (AC intermediária/raiz). Extrair com OpenSSL (`openssl_pkcs12_read` em PHP); validar: senha correta, data de validade, cadeia ICP-Brasil, CNPJ do certificado == CNPJ do emitente cadastrado.
- **MEI sem certificado:** o **Emissor Nacional web/app** permite emissão com login gov.br **sem** certificado — mas isso **não vale para API**: a API nacional exige **mTLS com certificado ICP-Brasil** e XML assinado ([Nota Gateway — guia API NFSe Nacional](https://notagateway.com.br/blog/api-nfse-nacional/)). Consequência de produto: **todo usuário que quiser emissão automática precisa de e-CNPJ A1** (~R$ 130–250/ano). Alternativa manual (fora de escopo): gerar a DPS e o usuário emitir à mão no portal.
- Operacional: alertas de expiração (30/15/7 dias), bloqueio de emissão com certificado vencido, renovação anual.

## 7. Assinatura digital XML (XMLDSig)

- Padrão **XMLDSig enveloped signature**:
  - Canonicalização **C14N** (`http://www.w3.org/TR/2001/REC-xml-c14n-20010315`);
  - Transforms: `enveloped-signature` + C14N;
  - Digest/assinatura: **SHA-256** (`rsa-sha256`) no padrão nacional (ABRASF legado às vezes ainda SHA-1);
  - `Reference URI="#<id do infDPS>"` — assina-se o elemento `infDPS` (nacional) ou `InfDeclaracaoPrestacaoServico`/lote (ABRASF);
  - `KeyInfo/X509Data/X509Certificate` com o certificado do emitente.
- **Lib PHP de referência:** `robrichards/xmlseclibs` (usada por todo o ecossistema nfephp). Alternativa: portar o `Certificate`/`Signer` de [nfephp-org/sped-nfse-nacional](https://packagist.org/packages/nfephp-org/sped-nfse-nacional) (API para DPS nacional já existente — avaliar usar como dependência ou como referência de implementação).
- Armadilhas: whitespace/encoding alteram o digest (gerar XML sem formatação, UTF-8, sem BOM); ordem dos elementos conforme XSD; `Id` do `infDPS` deve bater com a `Reference URI`.

## 8. Autenticação

- **API Nacional:** **mTLS** — o certificado A1 do emitente é apresentado no handshake TLS (em PHP/cURL: `CURLOPT_SSLCERT`/`CURLOPT_SSLKEY` com PEM extraído do PFX, ou Guzzle `cert`/`ssl_key`). Não há OAuth/token para contribuinte; a identidade É o certificado. Implicação: **uma conexão por certificado/usuário** — sem pool de conexões compartilhado entre tenants.
- **ABRASF municipal:** varia — maioria usa a assinatura XML como autenticação (transport TLS simples), alguns exigem mTLS, outros usuário/senha no SOAP header (ex.: GISS).
- **gov.br OAuth:** usado apenas no emissor web — não disponível para API de contribuinte.

## 9. SOAP vs REST

| | Nacional (REST) | ABRASF (SOAP) |
|---|---|---|
| Envelope | JSON | SOAP 1.1/1.2 (`SoapClient` ou cURL manual) |
| Documento | XML assinado GZip+Base64 | XML assinado inline |
| Emissão | Síncrona (POST DPS → NFS-e) | Lote assíncrono + polling (ou `GerarNfse` síncrono) |
| Auth | mTLS ICP-Brasil | TLS + assinatura XML (varia) |
| Extensões PHP | ext-curl, ext-openssl, ext-zlib, ext-dom | + ext-soap |
- Muitos webservices municipais têm TLS antigo/cadeias quebradas → cURL manual com opções por município costuma ser mais robusto que `SoapClient`.
- Verificar no deploy: `ext-openssl`, `ext-dom`, `ext-zlib`, `ext-curl` (padrão), `ext-soap` (habilitar quando iniciar trilha ABRASF).

## 10. Mapeamento município → provedor

- Chave de roteamento: **código IBGE (7 dígitos)** do município do emitente (já existe `address_ibge_code` no schema para sellers; faltará para o emitente).
- Estratégia: tabela `municipality_providers` (código IBGE → driver + config: URLs, versão de schema, particularidades). Para o padrão nacional, a lista de municípios aderentes é dinâmica — **consultável via API de parametrização municipal** (`/parametrizacao`, por código de município) e mantida no [portal gov.br/nfse](https://www.gov.br/nfse/pt-br/municipios).
- Fallback: usuário em município não aderente e sem adapter ABRASF → bloquear onboarding com mensagem clara ("seu município ainda não é suportado").
- Com a obrigatoriedade de 09/2026 para Simples Nacional, a cobertura do driver nacional tende a ~totalidade dos usuários-alvo; adapters ABRASF viram cauda longa.

## 11. Dados obrigatórios do contribuinte (gap list vs schema atual)

**Emitente (prestador) — hoje inexistente no schema (User tem só name/email):**

| Campo | Obrigatório | Existe hoje? |
|---|---|---|
| CNPJ (ou CPF) | Sim | ❌ |
| Inscrição Municipal (IM) | Depende do município | ❌ |
| Razão social / nome | Sim | ⚠️ só `users.name` |
| Endereço completo + **código IBGE** | Sim | ❌ |
| Regime tributário (`opSimpNac`: MEI / Simples / Normal, `regEspTrib`) | Sim | ❌ |
| Item LC 116 (`cTribNac`, ex. 10.05) + cód. tributação municipal | Sim | ❌ |
| Alíquota ISS (ou obtida da parametrização municipal) | Sim* | ❌ |
| CNAE | Recomendado | ❌ |
| Certificado A1 (PFX + senha) | Sim (API) | ❌ |
| Série + numeração de DPS (sequência própria por emitente) | Sim | ❌ |

*No padrão nacional, para Simples/MEI a alíquota pode ser derivada da parametrização municipal/regime — o manual detalha quando informar.

**Tomador (seller) — `sellers` já cobre o essencial:**
- ✅ CNPJ/CPF, nome, endereço com código IBGE, e-mail.
- ⚠️ Tomador é opcional em alguns cenários, mas para B2B (comissão) deve ser informado; endereço/IBGE do tomador é recomendado, e e-mail permite o envio automático.

**Invoice — colunas novas necessárias:** número/série da DPS, `cTribNac`/código de serviço, alíquota e valor do ISS, indicador de retenção, ambiente (produção/homologação), digest/hash do XML assinado.

## 12. Ciclo de vida da nota (padrão nacional)

```
Perfil fiscal completo + certificado válido
  → montar DPS (XML) → validar XSD → assinar (XMLDSig)
  → POST /nfse (Sefin Nacional, mTLS, gzip+base64)
      ├─ 201: NFS-e autorizada (retorna chave de acesso 50 díg. + XML da NFS-e)
      └─ 4xx: rejeição (código + motivo) → corrigir ou falhar
  → persistir XML autorizado + chave → baixar DANFSE (PDF)
  → eventos posteriores: cancelamento, substituição, manifestação
```
- **Numeração:** série (5) + número (15) da DPS são controlados **pelo emitente** — sequência monotônica por emitente+série, sem furos idealmente; rejeição **E0004** (identificador inválido) é comum quando a concatenação da chave DPS não bate ([Tecnospeed — rejeição E0004](https://atendimento.tecnospeed.com.br/hc/pt-br/articles/36948285233303)). Exige **lock transacional** na alocação do número (multi-worker!).
- A emissão nacional é **síncrona** — não há polling para emissão em si (diferente do lote ABRASF).

## 13. Cancelamento e substituição

- **Cancelamento:** evento (`pedRegEvento` de cancelamento) enviado à Sefin Nacional, assinado, referenciando a chave de acesso. Municípios impõem prazos (comum: até a competência seguinte, ou análise fiscal após o prazo).
- **Substituição:** enviar nova DPS contendo a **chave da NFS-e substituída** (`chNFSeSubstituida`) → o sistema gera evento de "cancelamento por substituição", cancela a original e vincula a substituta. Prazo típico: **até 6 meses do fato gerador**; depois disso, só via atendimento (SAV).
- Restrições: nota com manifestação do tomador ou com análise fiscal pendente não pode ser substituída.
- No app: mapear para `InvoiceStatus::Cancelled` (já existe) + novo evento em `InvoiceEventType`.

## 14. Consulta de status / polling

- **Nacional:** emissão síncrona ⇒ pouca necessidade de polling. Consultas disponíveis: NFS-e por chave (`GET /nfse/{chave}`), DPS→chave (`GET /dps/{id}`), eventos por chave, e **distribuição ADN por NSU** (caixa de entrada de documentos/eventos do contribuinte — útil para detectar cancelamentos feitos fora da plataforma e notas onde o usuário é tomador).
- **ABRASF:** `RecepcionarLoteRps` retorna protocolo → poll `ConsultarLoteRps` com backoff (ex.: 5s, 15s, 30s…) até autorizado/rejeitado.
- O frontend já tem `useInvoicePoller` — o backend atualiza status via jobs; nada muda conceitualmente.

## 15. DANFSE (PDF)

- **Padrão nacional: não gerar PDF próprio** — a API DANFSE oficial (`/danfse/{chaveAcesso}`) devolve o PDF pronto. Menos código, layout sempre conforme.
- Fallback/ABRASF: gerar DANFSE próprio a partir do XML (dompdf/mpdf + template com chave de acesso, QR de verificação do município) — só na trilha municipal, onde nem todo provedor dá PDF.
- Fit atual: `UploadInvoiceFilesJob` já baixa arquivos por URL e grava em S3 (`invoice_files` já tem tipos `pdf`/`xml`) — trocar a origem de `pdf_url` fake pela chamada DANFSE autenticada (mTLS), que exige request com certificado, não URL pública.

## 16. Armazenamento

- **Guarda fiscal:** XML autorizado (e eventos de cancelamento) por **5 anos** (prazo decadencial/prescricional — CTN art. 173/174). O XML é o documento fiscal; o PDF é mera representação.
- S3 já é o padrão do app (`invoice_files`). Recomendações: prefixo por tenant (`invoices/{user_id}/…`, já usado), **S3 Object Lock/versioning** para imutabilidade, lifecycle para Glacier após ~1 ano, criptografia SSE.
- Guardar também a **DPS enviada** (não só a NFS-e retornada) para auditoria de divergências, e o retorno bruto (já existe `invoices.provider_payload` JSON).

## 17. Segurança (cofre de certificados)

- O PFX + senha do usuário é o ativo mais sensível do sistema (permite emitir documentos fiscais em nome dele).
- Mínimo viável (Laravel): PFX criptografado at-rest (S3 **privado** + criptografia da aplicação antes do upload; senha com `encrypted` cast / `Crypt`), chave da aplicação rotacionável; **nunca** logar senha/conteúdo; descriptografar só em memória no momento de assinar/conectar; arquivos PEM temporários em disco efêmero com unlink imediato (cURL exige arquivo — usar `php://memory` não é possível para `CURLOPT_SSLCERT`; mitigar com tmpfs e permissões 0600).
- Ideal (fase 2): **KMS/HSM** (AWS KMS envelope encryption; AWS CloudHSM/Secrets Manager) e segregação do serviço de assinatura.
- Auditoria: registrar todo uso do certificado (`audit_logs` já existe) — upload, assinatura, conexão, falha de senha.
- Acesso multi-tenant: certificado sempre escopado ao dono (`BelongsToUserScope` + policies), sem endpoint de download do PFX.

## 18. LGPD

- Dados tratados: CPF/CNPJ, nome, endereço, e-mail de **terceiros** (sellers/tomadores) e do usuário; certificado digital (dado de autenticação sensível operacionalmente, embora não "sensível" no art. 5º).
- **Bases legais:** execução de contrato (art. 7º, V) para dados do usuário; **cumprimento de obrigação legal** (art. 7º, II) para dados fiscais dos tomadores nas notas emitidas.
- **Retenção vs eliminação:** o direito de eliminação (art. 18) **não alcança** documentos fiscais dentro do prazo de guarda legal (art. 16, I — cumprimento de obrigação legal). Ao excluir conta: anonimizar/excluir o que não for fiscal; reter XMLs/NFS-e pelos 5 anos com acesso restrito; **excluir o certificado imediatamente**.
- Deveres: minimização (só campos exigidos pelo leiaute), RIPD/registro de operações, contrato de operador (a plataforma atua como **operadora** dos dados dos tomadores em nome do usuário-controlador — refletir nos Termos de Uso), notificação de incidentes (vazamento de certificado = incidente grave).

## 19. Tratamento de erros

- **Categorizar rejeições:**
  - **Terminais (não retentar):** erro de schema/XSD, assinatura inválida, dados cadastrais errados (CNPJ divergente, IM inválida), duplicidade de DPS, regra de negócio (E-codes de validação). Ação: `InvoiceStatus::Failed` + mensagem acionável ao usuário.
  - **Retryáveis:** HTTP 5xx, timeout, 429 (com `Retry-After`), indisponibilidade. Ação: retry/backoff (pipeline atual já tem `tries=5`, backoff `[10,30,60,300,900]` — adequado).
  - **Duplicidade após timeout:** se um POST estourar timeout, **consultar antes de reenviar** (a nota pode ter sido autorizada) — idempotência via consulta da chave DPS (`GET /dps/{id}`), nunca reenvio cego com número novo.
- Persistir código + descrição da rejeição em `invoice_events.metadata` (estrutura já existe) e mapear os códigos mais comuns para mensagens pt-BR amigáveis.

## 20. Rate limiting & performance

- A API nacional aplica rate limit com **HTTP 429 + `Retry-After`** (limites oficiais não publicados de forma estável — dimensionar conservadoramente e reagir aos headers; os números "360 GET/min" que circulam são de gateways terceiros como [Nuvem Fiscal](https://dev.nuvemfiscal.com.br/docs/limites/), **não** da Sefin).
- O throttle atual (`Redis::throttle('invoice-provider')->allow(10)->every(60)`) é um bom ponto de partida **global**; considerar throttle **por emitente/certificado** (o limite do governo tende a ser por certificado) — ex. chave `invoice-provider:{issuer_id}`.
- Custo por emissão: handshake mTLS + assinatura + POST síncrono (~1–3 s típico). Para lotes grandes: paralelizar por emitente (workers da fila `default`), nunca paralelo dentro do mesmo emitente+série (numeração sequencial exige serialização — lock por emitente).
- Cachear parametrização municipal (alíquotas/regimes) com TTL de horas; cache do PEM extraído do PFX por request, nunca em cache compartilhado.

## 21. Classificação por complexidade de implementação

| # | Item | Complexidade | Justificativa | Ordem sugerida |
|---|---|---|---|---|
| 1 | Cadastro fiscal do emitente (issuer) + gate de onboarding | **Baixa** | CRUD + validações + middleware; sem integração externa | 1 |
| 2 | Upload/validação/cofre de certificado A1 | **Média** | openssl_pkcs12, criptografia at-rest, expiração, segurança | 1 |
| 3 | Numeração DPS (série/número com lock) | **Baixa** | Sequência transacional por emitente | 2 |
| 4 | Geração do XML da DPS + validação XSD | **Média** | Leiaute extenso (Anexo I), muitos campos condicionais | 2 |
| 5 | Assinatura XMLDSig | **Média** | xmlseclibs resolve o grosso; armadilhas de C14N/digest | 2 |
| 6 | Cliente REST mTLS (Sefin Nacional) + gzip/base64 | **Média** | cURL com cert por tenant; tratamento 429/timeout/idempotência | 3 |
| 7 | Emissão fim-a-fim em **produção restrita** | **Média** | Integração dos itens 3–6 + mapeamento de rejeições | 3 |
| 8 | Download/armazenamento XML + DANFSE (S3) | **Baixa** | Pipeline `UploadInvoiceFilesJob` já existe; trocar origem | 4 |
| 9 | Cancelamento (evento) | **Média** | XML de evento assinado + regras de prazo | 5 |
| 10 | Substituição de NFS-e | **Média** | DPS com chave substituída + vínculos | 5 |
| 11 | Distribuição ADN (NSU) p/ eventos externos | **Média** | Polling paginado, novo job agendado | 6 |
| 12 | Parametrização municipal (alíquotas) + cache | **Baixa** | GET simples + cache | 4 |
| 13 | Roteamento município→provedor | **Baixa** | Tabela + resolver | 6 |
| 14 | Adapters ABRASF (por fornecedor) | **Alta** | SOAP, dialetos por município, lote assíncrono, N variações | 7+ (sob demanda) |
| 15 | DANFSE próprio (PDF) p/ ABRASF | **Média** | Template + QR por município | 7+ |
| 16 | LGPD (termos, retenção, exclusão) | **Média** | Processos + código de retenção/anonimização | contínuo |
| 17 | Campos IBS/CBS (Reforma Tributária) | **Média→Alta** | Leiautes ainda evoluindo (Anexo C v1.01); acompanhar | 2027 |

**Caminho crítico do MVP (emitir NFS-e real em produção restrita):** 1 → 2 → 3 → 4 → 5 → 6 → 7. Tudo depois disso é incremento.

---

## Fontes principais

- [Portal NFS-e — Documentação Técnica (gov.br)](https://www.gov.br/nfse/pt-br/biblioteca/documentacao-tecnica) · [Documentação Atual (versões/anexos)](https://www.gov.br/nfse/pt-br/biblioteca/documentacao-tecnica/documentacao-atual) · [APIs Prod. Restrita e Produção](https://www.gov.br/nfse/pt-br/biblioteca/documentacao-tecnica/apis-prod-restrita-e-producao)
- [Manual das APIs do Contribuinte v1.2 (out/2025) — PDF](https://www.gov.br/nfse/pt-br/biblioteca/documentacao-tecnica/documentacao-atual/manual-contribuintes-emissor-publico-api-sistema-nacional-nfs-e-v1-2-out2025.pdf)
- [Swagger Contribuintes ISSQN (produção restrita)](https://www.producaorestrita.nfse.gov.br/swagger/contribuintesissqn/)
- [Receita Federal — NFS-e padrão nacional obrigatória para o Simples Nacional (abr/2026)](https://www.gov.br/receitafederal/pt-br/assuntos/noticias/2026/abril/nfs-e-de-padrao-nacional-sera-obrigatoria-para-optantes-do-simples-nacional)
- [ABRASF — Versão 2.04](https://abrasf.org.br/biblioteca/arquivos-publicos/nfs-e/versao-2-04) · [WSDL v2.04](https://abrasf.org.br/biblioteca/arquivos-publicos/nfs-e/versao-2-04/wsdl-nfse-v2-04)
- [nfephp-org/sped-nfse-nacional (Packagist)](https://packagist.org/packages/nfephp-org/sped-nfse-nacional) · [nfephp-org/sped-nfse](https://packagist.org/packages/nfephp-org/sped-nfse)
- [Nota Gateway — API NFSe Nacional na prática](https://notagateway.com.br/blog/api-nfse-nacional/) · [Tecnospeed — Documentação padrão nacional](https://atendimento.tecnospeed.com.br/hc/pt-br/articles/38360053945367-Documenta%C3%A7%C3%A3o-T%C3%A9cnica-Padr%C3%A3o-NFS-e-Nacional) · [Tecnospeed — Rejeição E0004](https://atendimento.tecnospeed.com.br/hc/pt-br/articles/36948285233303)
