import { useEffect, useState } from 'react';

export function useInvoicePoller(progressUrl, enabled = true, intervalMs = 3000) {
    const [counts, setCounts] = useState({});

    useEffect(() => {
        if (!enabled) return;

        let cancelled = false;

        const poll = async () => {
            const response = await fetch(progressUrl, { headers: { Accept: 'application/json' } });
            const data = await response.json();

            if (!cancelled) {
                setCounts(data);
            }
        };

        poll();
        const interval = setInterval(poll, intervalMs);

        return () => {
            cancelled = true;
            clearInterval(interval);
        };
    }, [progressUrl, enabled, intervalMs]);

    return counts;
}
