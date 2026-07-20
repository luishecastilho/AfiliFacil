import { Label } from '@/Components/ui/Label';
import { HelpTooltip } from '@/Components/HelpTooltip';

/**
 * Label with an optional help tooltip beside it, for form fields.
 */
export function FieldLabel({ htmlFor, children, help }) {
    return (
        <div className="flex items-center gap-1.5">
            <Label htmlFor={htmlFor}>{children}</Label>
            {help && <HelpTooltip>{help}</HelpTooltip>}
        </div>
    );
}

/**
 * Muted hint text shown below a field (e.g. "onde encontrar").
 */
export function FieldHint({ children }) {
    return <p className="text-xs text-muted-foreground">{children}</p>;
}
