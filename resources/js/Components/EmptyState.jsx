import { Card, CardContent } from '@/Components/ui/Card';

/**
 * Friendly empty state: icon + title + description + optional CTA/action node.
 */
export function EmptyState({ icon: Icon, title, description, action }) {
    return (
        <Card className="border-dashed">
            <CardContent className="flex flex-col items-center gap-4 py-12 text-center">
                {Icon && (
                    <div className="flex size-12 items-center justify-center rounded-full bg-muted">
                        <Icon className="size-6 text-muted-foreground" />
                    </div>
                )}
                <div className="space-y-1">
                    <p className="font-medium">{title}</p>
                    {description && <p className="mx-auto max-w-md text-sm text-muted-foreground">{description}</p>}
                </div>
                {action}
            </CardContent>
        </Card>
    );
}
