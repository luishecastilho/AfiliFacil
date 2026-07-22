import { usePage } from '@inertiajs/react';

/**
 * Centralizes the free-tier conversion-hook logic so every upgrade nudge across
 * the panel shares one source of truth (mirrors the thresholds used by
 * NfUsageMeter). Reads the globally shared `nf_usage` prop.
 *
 * @returns {{
 *   isFree: boolean,
 *   used: number,
 *   limit: (number|null),
 *   remaining: number,
 *   percentage: number,
 *   nearLimit: boolean,
 *   atLimit: boolean,
 *   showNudge: boolean,
 * }}
 */
export function useFreeTierStatus() {
    const { nf_usage: usage } = usePage().props;

    const safeDefault = {
        isFree: false,
        used: 0,
        limit: null,
        remaining: 0,
        percentage: 0,
        nearLimit: false,
        atLimit: false,
        showNudge: false,
    };

    // No usage prop (unauthenticated) or unlimited plan → nothing to nudge.
    if (!usage || usage.plan !== 'free' || usage.limit === null) {
        return safeDefault;
    }

    const { used, limit } = usage;
    const remaining = Math.max(0, limit - used);
    const percentage = Math.min(100, Math.round((used / limit) * 100));
    const atLimit = used >= limit;
    const nearLimit = !atLimit && percentage > 80;

    return {
        isFree: true,
        used,
        limit,
        remaining,
        percentage,
        nearLimit,
        atLimit,
        showNudge: nearLimit || atLimit,
    };
}
