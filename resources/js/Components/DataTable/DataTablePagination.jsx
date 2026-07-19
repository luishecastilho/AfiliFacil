import { Button } from '@/Components/ui/Button';

export function DataTablePagination({ currentPage, lastPage, onPageChange }) {
    return (
        <div className="flex items-center justify-end space-x-2 py-4">
            <span className="text-sm text-muted-foreground">
                Page {currentPage} of {lastPage}
            </span>
            <Button
                variant="outline"
                size="sm"
                onClick={() => onPageChange(currentPage - 1)}
                disabled={currentPage <= 1}
            >
                Previous
            </Button>
            <Button
                variant="outline"
                size="sm"
                onClick={() => onPageChange(currentPage + 1)}
                disabled={currentPage >= lastPage}
            >
                Next
            </Button>
        </div>
    );
}
