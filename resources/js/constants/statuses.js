export const IMPORT_STATUS_LABELS = {
    pending: 'Pending',
    uploading: 'Uploading',
    parsing: 'Parsing',
    parsed: 'Parsed',
    validating: 'Validating',
    validated: 'Validated',
    done: 'Done',
    failed: 'Failed',
    cancelled: 'Cancelled',
};

export const IMPORT_ROW_STATUS_LABELS = {
    pending: 'Pending',
    valid: 'Valid',
    invalid: 'Invalid',
    duplicate: 'Duplicate',
    queued: 'Queued',
    invoiced: 'Invoiced',
    failed: 'Failed',
};

export const INVOICE_STATUS_LABELS = {
    queued: 'Queued',
    processing: 'Processing',
    generated: 'Generated',
    failed: 'Failed',
    cancelled: 'Cancelled',
    retrying: 'Retrying',
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
