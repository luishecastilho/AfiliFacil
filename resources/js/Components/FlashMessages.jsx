import { usePage } from '@inertiajs/react';
import { X } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Alert } from '@/Components/ui/Alert';

/**
 * Renders the shared `flash` prop ({status, warning}) as dismissible alerts.
 * Success messages auto-dismiss; warnings stay until dismissed or navigated away.
 */
export function FlashMessages() {
    const flash = usePage().props.flash;
    const [shown, setShown] = useState({ status: true, warning: true });

    useEffect(() => {
        setShown({ status: true, warning: true });
    }, [flash?.status, flash?.warning]);

    useEffect(() => {
        if (!flash?.status || !shown.status) return;
        const timer = setTimeout(() => setShown((s) => ({ ...s, status: false })), 6000);
        return () => clearTimeout(timer);
    }, [flash?.status, shown.status]);

    if (!flash) return null;

    const dismiss = (key) => (
        <button type="button" onClick={() => setShown((s) => ({ ...s, [key]: false }))} aria-label="Fechar">
            <X className="size-4 opacity-60 hover:opacity-100" />
        </button>
    );

    return (
        <div className="space-y-3">
            {flash.status && shown.status && (
                <Alert variant="success" action={dismiss('status')}>
                    {flash.status}
                </Alert>
            )}
            {flash.warning && shown.warning && (
                <Alert variant="warning" action={dismiss('warning')}>
                    {flash.warning}
                </Alert>
            )}
        </div>
    );
}
