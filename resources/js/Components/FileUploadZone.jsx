import { useCallback, useState } from 'react';
import { UploadCloud } from 'lucide-react';
import { cn } from '@/lib/cn';

export function FileUploadZone({ onFileSelect, accept = '.csv,.xlsx,.xls', file }) {
    const [isDragging, setIsDragging] = useState(false);

    const handleDrop = useCallback(
        (event) => {
            event.preventDefault();
            setIsDragging(false);

            const dropped = event.dataTransfer.files?.[0];
            if (dropped) onFileSelect(dropped);
        },
        [onFileSelect],
    );

    return (
        <label
            onDragOver={(event) => {
                event.preventDefault();
                setIsDragging(true);
            }}
            onDragLeave={() => setIsDragging(false)}
            onDrop={handleDrop}
            className={cn(
                'flex cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed p-10 text-center transition-colors',
                isDragging ? 'border-primary bg-secondary/50' : 'border-input',
            )}
        >
            <UploadCloud className="h-8 w-8 text-muted-foreground" />
            <span className="text-sm text-muted-foreground">
                {file ? file.name : 'Drag and drop a file here, or click to browse'}
            </span>
            <span className="text-xs text-muted-foreground">CSV, XLSX, or XLS</span>
            <input
                type="file"
                accept={accept}
                className="hidden"
                onChange={(event) => event.target.files?.[0] && onFileSelect(event.target.files[0])}
            />
        </label>
    );
}
