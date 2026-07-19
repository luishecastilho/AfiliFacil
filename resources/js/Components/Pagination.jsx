import { Link } from '@inertiajs/react';
import { cn } from '@/lib/cn';

/**
 * Renders Laravel paginator links (the `links` array from a paginated Inertia prop).
 */
export function Pagination({ links }) {
    if (!links || links.length <= 3) return null;

    return (
        <nav className="flex items-center justify-center gap-1 py-4">
            {links.map((link, index) => (
                <Link
                    key={index}
                    href={link.url ?? '#'}
                    preserveScroll
                    className={cn(
                        'rounded-md px-3 py-1.5 text-sm',
                        link.active ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-secondary',
                        !link.url && 'pointer-events-none opacity-50',
                    )}
                    dangerouslySetInnerHTML={{ __html: link.label }}
                />
            ))}
        </nav>
    );
}
