import { Input } from '@/Components/ui/Input';

export function DataTableToolbar({ searchValue, onSearchChange, searchPlaceholder = 'Search...', children }) {
    return (
        <div className="flex items-center justify-between py-4">
            <Input
                placeholder={searchPlaceholder}
                value={searchValue}
                onChange={(event) => onSearchChange(event.target.value)}
                className="max-w-sm"
            />
            <div className="flex items-center gap-2">{children}</div>
        </div>
    );
}
