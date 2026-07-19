import { cn } from '@/lib/cn';

export function ProgressBar({ value, max = 100, className }) {
    const percentage = max > 0 ? Math.min(100, Math.round((value / max) * 100)) : 0;

    return (
        <div className={cn('h-2 w-full overflow-hidden rounded-full bg-secondary', className)}>
            <div className="h-full bg-primary transition-all" style={{ width: `${percentage}%` }} />
        </div>
    );
}
