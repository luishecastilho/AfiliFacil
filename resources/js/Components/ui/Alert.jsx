import { CheckCircle2, Info, TriangleAlert, XCircle } from 'lucide-react';
import { cn } from '@/lib/cn';

const VARIANTS = {
    info: {
        container: 'border-sky-300 bg-sky-50 text-sky-900 dark:border-sky-900 dark:bg-sky-950/40 dark:text-sky-100',
        icon: Info,
        iconColor: 'text-sky-600',
    },
    warning: {
        container: 'border-amber-300 bg-amber-50 text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-100',
        icon: TriangleAlert,
        iconColor: 'text-amber-600',
    },
    success: {
        container: 'border-emerald-300 bg-emerald-50 text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-100',
        icon: CheckCircle2,
        iconColor: 'text-emerald-600',
    },
    destructive: {
        container: 'border-destructive/40 bg-destructive/10 text-destructive dark:text-red-200',
        icon: XCircle,
        iconColor: 'text-destructive',
    },
};

export function Alert({ variant = 'info', title, children, icon, action, className }) {
    const config = VARIANTS[variant] ?? VARIANTS.info;
    const Icon = icon ?? config.icon;

    return (
        <div className={cn('flex items-start gap-3 rounded-lg border p-4', config.container, className)} role="alert">
            <Icon className={cn('mt-0.5 size-5 shrink-0', config.iconColor)} />
            <div className="min-w-0 flex-1 space-y-1">
                {title && <p className="text-sm font-medium">{title}</p>}
                {children && <div className="text-sm opacity-90">{children}</div>}
                {action && <div className="pt-1">{action}</div>}
            </div>
        </div>
    );
}
