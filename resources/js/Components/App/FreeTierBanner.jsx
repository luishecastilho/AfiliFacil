import { UpgradePrompt } from '@/Components/App/UpgradePrompt';
import { useFreeTierStatus } from '@/hooks/useFreeTierStatus';

/**
 * Always-on conversion hook: a global banner shown on every authenticated page
 * once a free user is near (>80%) or at their monthly NF-e limit. Dismissible
 * per session so it nudges without nagging. Hidden on the billing page itself.
 */
export function FreeTierBanner() {
    const { showNudge, atLimit, used, limit, remaining } = useFreeTierStatus();
    const onBillingPage = route().current('billing.*');

    if (!showNudge || onBillingPage) return null;

    if (atLimit) {
        return (
            <UpgradePrompt
                variant="warning"
                title={`Você atingiu o limite de ${limit} notas do plano Gratuito`}
                dismissKey={`at-limit:${limit}`}
            >
                Novas notas só serão emitidas no próximo mês. Faça upgrade para um plano pago e volte a emitir agora
                mesmo.
            </UpgradePrompt>
        );
    }

    return (
        <UpgradePrompt
            variant="info"
            title={`Você já usou ${used} de ${limit} notas grátis este mês`}
            dismissKey={`near-limit:${used}/${limit}`}
        >
            Restam apenas {remaining} nota(s) no plano Gratuito. Faça upgrade para emitir mais notas sem interrupção.
        </UpgradePrompt>
    );
}
