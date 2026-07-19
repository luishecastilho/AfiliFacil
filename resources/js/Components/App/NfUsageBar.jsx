import { Link, usePage } from '@inertiajs/react';

export function NfUsageBar() {
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
        <div className="border-b border-gray-100 bg-white px-4 py-3 sm:px-6 lg:px-8">
            <div className="mx-auto flex max-w-7xl flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex-1">
                    <div className="flex items-center justify-between text-xs text-gray-500">
                        <span>{isUnlimited ? `${used} NF-e emitidas este mês` : `${used} de ${limit} NF-e usadas este mês`}</span>
                        {!isUnlimited && <span>{percentage}%</span>}
                    </div>

                    {!isUnlimited && (
                        <div className="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-gray-200">
                            <div
                                className={`h-full rounded-full transition-all ${barColor}`}
                                style={{ width: `${percentage}%` }}
                            />
                        </div>
                    )}

                    {atLimit && (
                        <p className="mt-1.5 text-xs font-medium text-red-600">
                            Você atingiu o limite do seu plano.
                        </p>
                    )}
                </div>

                {plan !== 'advanced' && (
                    <Link
                        href={route('billing.index')}
                        className="shrink-0 text-xs font-semibold text-[#EE4D2D] hover:text-[#D94426]"
                    >
                        Fazer upgrade
                    </Link>
                )}
            </div>
        </div>
    );
}
