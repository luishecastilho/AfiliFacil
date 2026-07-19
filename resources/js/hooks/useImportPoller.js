import { router } from '@inertiajs/react';
import { useEffect } from 'react';

const TERMINAL_STATUSES = ['done', 'failed', 'cancelled', 'validated'];

export function useImportPoller(importId, status, intervalMs = 3000) {
    useEffect(() => {
        if (TERMINAL_STATUSES.includes(status)) {
            return;
        }

        const interval = setInterval(() => {
            router.reload({ only: ['import'] });
        }, intervalMs);

        return () => clearInterval(interval);
    }, [importId, status, intervalMs]);
}
