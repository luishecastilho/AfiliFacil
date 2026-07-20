import { HelpCircle } from 'lucide-react';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/Components/ui/Tooltip';

/**
 * Small help icon with a tooltip, for explaining fiscal jargon in plain language.
 * Requires a TooltipProvider in the tree (mounted globally in AppLayout).
 */
export function HelpTooltip({ children, className }) {
    return (
        <Tooltip>
            <TooltipTrigger asChild>
                <button
                    type="button"
                    className="inline-flex text-muted-foreground transition-colors hover:text-foreground"
                    aria-label="Ajuda"
                >
                    <HelpCircle className={className ?? 'size-4'} />
                </button>
            </TooltipTrigger>
            <TooltipContent className="max-w-xs text-left font-normal leading-snug">{children}</TooltipContent>
        </Tooltip>
    );
}
