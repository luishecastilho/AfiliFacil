export const IMPORT_STATUS_LABELS = {
    pending: 'Pendente',
    uploading: 'Enviando',
    parsing: 'Lendo arquivo',
    parsed: 'Lido',
    validating: 'Validando',
    validated: 'Validado',
    done: 'Concluído',
    failed: 'Falhou',
    cancelled: 'Cancelado',
};

export const IMPORT_ROW_STATUS_LABELS = {
    pending: 'Pendente',
    valid: 'Válida',
    invalid: 'Inválida',
    duplicate: 'Duplicada',
    queued: 'Na fila',
    invoiced: 'Emitida',
    failed: 'Falhou',
};

export const INVOICE_STATUS_LABELS = {
    queued: 'Na fila',
    processing: 'Processando',
    generated: 'Emitida',
    failed: 'Falhou',
    cancelled: 'Cancelada',
    retrying: 'Repetindo',
    awaiting_manual: 'Emitir manualmente',
};

export const INVOICE_EVENT_LABELS = {
    queued: 'Na fila',
    processing: 'Processando',
    generated: 'Emitida',
    failed: 'Falhou',
    retried: 'Nova tentativa',
    downloaded: 'Baixada',
    cancelled: 'Cancelada',
    awaiting_manual: 'Aguardando emissão manual',
    emitted_manually: 'Emitida manualmente',
};

export const STATUS_BADGE_VARIANTS = {
    pending: 'secondary',
    uploading: 'secondary',
    parsing: 'secondary',
    parsed: 'secondary',
    validating: 'secondary',
    validated: 'default',
    queued: 'secondary',
    processing: 'secondary',
    valid: 'default',
    generated: 'default',
    done: 'default',
    invoiced: 'default',
    invalid: 'destructive',
    failed: 'destructive',
    cancelled: 'destructive',
    duplicate: 'outline',
    retrying: 'outline',
};
