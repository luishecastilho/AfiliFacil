import { Link } from '@inertiajs/react';
import { useState } from 'react';
import { Sparkles } from 'lucide-react';
import { Alert } from '@/Components/ui/Alert';
import { Button } from '@/Components/ui/Button';

/**
 * Reusable inline "upgrade to a paid plan" nudge used by the free-tier
 * conversion hooks. Thin wrapper over Alert + a CTA that routes to the billing
 * page, so every prompt across the panel looks and behaves consistently.
 *
 * Pass `dismissKey` to make the prompt dismissible for the current browser
 * session (used by the always-on global banner so it never nags).
 */
export function UpgradePrompt({
    variant = 'info',
    title,
    children,
    cta = 'Fazer upgrade',
    dismissKey,
    className,
}) {
    const storageKey = dismissKey ? `upgrade-prompt-dismissed:${dismissKey}` : null;

    const [dismissed, setDismissed] = useState(() => {
        if (!storageKey || typeof window === 'undefined') return false;
        return window.sessionStorage.getItem(storageKey) === '1';
    });

    if (dismissed) return null;

    function dismiss() {
        if (storageKey) window.sessionStorage.setItem(storageKey, '1');
        setDismissed(true);
    }

    return (
        <Alert
            variant={variant}
            title={title}
            icon={Sparkles}
            className={className}
            action={
                <div className="flex flex-wrap items-center gap-2">
                    <Button asChild size="sm">
                        <Link href={route('billing.index')}>{cta}</Link>
                    </Button>
                    {storageKey && (
                        <Button type="button" size="sm" variant="ghost" onClick={dismiss}>
                            Agora não
                        </Button>
                    )}
                </div>
            }
        >
            {children}
        </Alert>
    );
}
