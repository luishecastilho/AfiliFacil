import { Link, usePage } from '@inertiajs/react';

export function NfUsageMeter() {
    const { nf_usage: usage } = usePage().props;

    if (!usage) {
        return null;
    }

    const { used, limit, plan } = usage;
    const isUnlimited = limit === null;
    const percentage = isUnlimited ? 0 : Math.min(100, Math.round((used / limit) * 100));
    const atLimit = !isUnlimited && used >= limit;
    const isWarning = !isUnlimited && !atLimit && percentage > 80;

    const barColor = atLimit ? 'bg-red-500' : isWarning ? 'bg-amber-500' : 'bg-[#EE4D2D]';

    return (
        <div className="rounded-md px-2 py-1.5 group-data-[collapsible=icon]:hidden">
            <div className="flex items-center justify-between text-xs">
                <span className="font-medium text-sidebar-foreground/80">NF-e este mês</span>
                <span className="tabular-nums text-muted-foreground">
                    {isUnlimited ? used : `${used}/${limit}`}
                </span>
            </div>

            {!isUnlimited && (
                <div className="mt-1.5 h-1 w-full overflow-hidden rounded-full bg-sidebar-border">
                    <div
                        className={`h-full rounded-full transition-all ${barColor}`}
                        style={{ width: `${percentage}%` }}
                    />
                </div>
            )}

            {atLimit ? (
                <Link
                    href={route('billing.index')}
                    className="mt-1.5 block text-xs font-medium text-red-500 hover:underline"
                >
                    Limite atingido — fazer upgrade
                </Link>
            ) : (
                plan !== 'advanced' && (
                    <Link
                        href={route('billing.index')}
                        className="mt-1.5 block text-xs font-medium text-[#EE4D2D] hover:underline"
                    >
                        Fazer upgrade
                    </Link>
                )
            )}
        </div>
    );
}
