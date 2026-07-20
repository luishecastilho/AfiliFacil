import { Link, usePage } from '@inertiajs/react';
import { Alert } from '@/Components/ui/Alert';
import { Button } from '@/Components/ui/Button';

/**
 * Global amber banner shown while the fiscal profile is incomplete, so the user
 * always knows the next required step. Hidden on the fiscal settings page itself.
 */
export function FiscalReadinessBanner() {
    const { fiscal } = usePage().props;
    const onFiscalPage = route().current('issuer.*');

    if (!fiscal || fiscal.complete || onFiscalPage) return null;

    return (
        <Alert
            variant="warning"
            title="Seu cadastro fiscal ainda não está completo"
            action={
                <Button asChild size="sm" variant="outline">
                    <Link href={route('issuer.edit')}>Completar cadastro fiscal</Link>
                </Button>
            }
        >
            Você precisa concluir o cadastro fiscal e validar com o portal nacional antes de emitir notas ou assinar um
            plano.
        </Alert>
    );
}
