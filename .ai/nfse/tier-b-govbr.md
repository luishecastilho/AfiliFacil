# Plano — Fase B (Tier B): gov.br + Worklist manual

> **Status: PLANEJADO, não implementado.** O caminho ativo do produto é o **Tier A (certificado A1)**, já em produção no código. Este documento descreve como adicionar, no futuro, um tier assistido/manual sem certificado. Nada aqui está ligado hoje.

## Contexto e restrição

Nem todo usuário tem certificado A1. O Tier B oferece uma alternativa **assistida**: a plataforma agrupa e calcula todas as notas e entrega uma **worklist "pronta para emitir"**; o usuário emite **manualmente** no Emissor Nacional (login gov.br) e confirma na plataforma.

**Restrição técnica confirmada** (ver [`pesquisa.md`](pesquisa.md) §6/§8): a API nacional **só emite com certificado A1**. gov.br OAuth é **apenas identidade** — não autoriza emissão. O Emissor Nacional web **não tem importação em lote**. Logo, o valor do Tier B é limitado: a worklist existe para tornar o processo manual o menos penoso possível (copiar/colar campo a campo, deep-link, e marcação de status).

**Dependência externa:** virar *relying party* do gov.br Login Único exige credenciamento/aprovação do governo (não é imediato). Por isso o **núcleo do Tier B é a worklist**, que funciona sem o login gov.br; o gov.br é uma camada de identidade que pode ser ligada depois.

## O que já existe (base pronta)

- Enum [`EmissionMode`](../../app/Enums/EmissionMode.php) com `Automated` (padrão) e `Manual`.
- Colunas gov.br já na tabela `issuers` (migration `create_issuers_table`): `govbr_sub`, `govbr_cpf`, `govbr_linked_at`; e helper `Issuer::isGovbrLinked()`.
- Gate já é tier-aware: `SubscriptionService::fiscalReady()` já trata `emission_mode = manual` exigindo `govbr_linked_at` (hoje o único item faltante para "completo" no modo manual).
- Página `Settings/Fiscal.jsx` já tem o seletor de modo e um item de checklist "Conta gov.br conectada (modo manual)" (placeholder, sem ação).
- Config `nf-facilitator.nfse.emissor_nacional_url` já definida (deep-link do Emissor Nacional).

## Escopo a implementar (quando ativarmos)

### 1. Status e eventos de emissão manual
- Adicionar `InvoiceStatus::AwaitingManual` (`awaiting_manual`, "pronta p/ emissão manual") — atualizar o enum, o `label()`, e a lista `enum` da migration `create_invoices_table` (nova migration `ALTER`).
- Adicionar `InvoiceEventType::AwaitingManual` e `InvoiceEventType::EmittedManually`.
- Ao confirmar emissão manual: invoice → `Generated`, `provider = 'manual'`, `access_key` = chave colada (nullable), `issued_at = now()`.

### 2. Ramificação da geração por modo
- Em [`GenerateInvoicesJob`](../../app/Jobs/GenerateInvoicesJob.php) / [`GroupRowsForInvoicingAction`](../../app/Actions/Invoice/GroupRowsForInvoicingAction.php): se `issuer->emission_mode === Manual`, criar os invoices com status `AwaitingManual` e **não** despachar `IssueInvoiceJob` (sem API). O Tier A permanece inalterado.

### 3. Worklist manual (UI + ação)
- Nova aba/filtro em `resources/js/Pages/Invoices/` listando invoices `AwaitingManual`.
- Por nota: exibir os campos prontos para copiar (CNPJ do tomador, valor, competência, discriminação, código de serviço) + deep-link para `nfse.emissor_nacional_url` + botão **"Marcar como emitida"**.
- Rota + `MarkInvoiceEmittedAction` (`POST /invoices/{invoice}/mark-emitted`): input opcional de chave de acesso e upload opcional do XML/PDF retornado (grava em `invoice_files`); muda status para `Generated`.
- Opcional: export CSV/PDF-resumo das notas a emitir.

### 4. Login gov.br (camada de identidade)
- `GovBrController` com fluxo OAuth2/OIDC (Login Único): rotas `/auth/govbr/redirect` e `/auth/govbr/callback`, com `state`/PKCE.
- Provider: Socialite custom ou base community (ex.: `dtedesco/govbr-oauth`).
- Config: bloco `services.govbr` (`client_id`, `client_secret`, URLs, scopes). Requer credenciamento no gov.br.
- Callback grava `issuer.govbr_sub/govbr_cpf/govbr_linked_at`. Ao vincular, o gate de assinatura do modo manual passa a "completo".
- Na `Settings/Fiscal.jsx`, trocar o placeholder do checklist manual por um botão real "Conectar gov.br".

### 5. Integração com o gate (já quase pronta)
- `fiscalReady()` já cobre o modo manual (exige `govbr_linked_at`). Só falta o botão de conectar e o fluxo OAuth preencherem esse campo. Sem mudança na lógica do middleware/checkout.

## Riscos e decisões
- **Credenciamento gov.br** pode atrasar — por isso a worklist (itens 1–3) é entregável independente do item 4. Enquanto o gov.br não estiver aprovado, pode-se, opcionalmente, considerar o modo manual "pronto" só com os dados fiscais completos (decisão de produto a rever na ativação).
- **Rastreio de emissão:** como a emissão é fora da plataforma, o status depende da confirmação do usuário (marcar como emitida + colar chave). Não há garantia automática de que a nota foi realmente emitida.
- **Sem lote no portal:** deixar claro na UI que a emissão é nota a nota (limitação do Emissor Nacional).

## Testes (quando implementar)
- Branch manual em `GenerateInvoicesJob`: modo manual cria `AwaitingManual` e não despacha `IssueInvoiceJob`.
- `mark-emitted` muda status e grava chave/arquivo.
- Callback gov.br (com `Http::fake`) grava `govbr_linked_at` e o gate manual passa a "completo".
- Gate: usuário manual sem gov.br é bloqueado no checkout; com gov.br, liberado (já coberto parcialmente por `FiscalGateTest`).

## Ordem sugerida
1 (status/eventos) → 2 (branch de geração) → 3 (worklist + mark-emitted) → 4 (gov.br OAuth) → 5 (botão conectar). Itens 1–3 entregam valor sem depender do credenciamento gov.br.
