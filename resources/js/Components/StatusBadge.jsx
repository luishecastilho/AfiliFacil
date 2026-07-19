import { Badge } from '@/Components/ui/Badge';
import { STATUS_BADGE_VARIANTS } from '@/constants/statuses';

export function StatusBadge({ status, label }) {
    const variant = STATUS_BADGE_VARIANTS[status] ?? 'secondary';

    return <Badge variant={variant}>{label ?? status}</Badge>;
}
