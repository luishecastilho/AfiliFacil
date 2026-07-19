import { ArrowDown, ArrowUp, ChevronsUpDown } from 'lucide-react';
import { cn } from '@/lib/cn';
import { Button } from '@/Components/ui/Button';

export function DataTableColumnHeader({ column, title, className }) {
    if (!column.getCanSort()) {
        return <div className={cn(className)}>{title}</div>;
    }

    const sorted = column.getIsSorted();

    return (
        <Button
            variant="ghost"
            size="sm"
            className={cn('-ml-3 h-8', className)}
            onClick={() => column.toggleSorting(sorted === 'asc')}
        >
            <span>{title}</span>
            {sorted === 'desc' ? (
                <ArrowDown className="ml-2 h-4 w-4" />
            ) : sorted === 'asc' ? (
                <ArrowUp className="ml-2 h-4 w-4" />
            ) : (
                <ChevronsUpDown className="ml-2 h-4 w-4" />
            )}
        </Button>
    );
}
