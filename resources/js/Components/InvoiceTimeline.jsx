import { INVOICE_EVENT_LABELS } from '@/constants/statuses';
import { formatDate } from '@/lib/formatters';

export function InvoiceTimeline({ events }) {
    if (!events?.length) {
        return <p className="text-sm text-muted-foreground">Nenhum evento ainda.</p>;
    }

    return (
        <ol className="relative space-y-4 border-l border-border pl-4">
            {events.map((event) => (
                <li key={event.id}>
                    <div className="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full bg-primary" />
                    <p className="text-sm font-medium">{INVOICE_EVENT_LABELS[event.event] ?? event.event}</p>
                    <p className="text-xs text-muted-foreground">{formatDate(event.created_at)}</p>
                </li>
            ))}
        </ol>
    );
}
