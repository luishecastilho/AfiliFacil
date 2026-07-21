import { Link } from '@inertiajs/react';
import { LegalLayout } from '@/Components/Legal/LegalLayout';

export default function Privacy() {
    return (
        <LegalLayout
            title="Política de Privacidade"
            metaDescription="Política de Privacidade do AfiliFacil — como tratamos seus dados pessoais em conformidade com a LGPD."
            lastUpdated="21 de julho de 2026"
        >
            <p>
                Esta Política de Privacidade descreve como a <strong>LHC Technology</strong>, inscrita no CNPJ sob o
                nº 49.265.984/0001-21 (&ldquo;AfiliFacil&rdquo;, &ldquo;nós&rdquo;), coleta, utiliza, compartilha e
                protege os dados pessoais tratados por meio da plataforma AfiliFacil, em conformidade com a Lei Geral de
                Proteção de Dados (Lei nº 13.709/2018 &ndash; &ldquo;LGPD&rdquo;).
            </p>

            <section>
                <h2>1. Controlador dos dados</h2>
                <p>
                    O controlador dos dados tratados na Plataforma é a LHC Technology (CNPJ 49.265.984/0001-21). Para
                    questões relativas a esta Política ou ao tratamento de dados, entre em contato pelo e-mail{' '}
                    <a href="mailto:luis@lhctechnology.com.br">luis@lhctechnology.com.br</a>.
                </p>
            </section>

            <section>
                <h2>2. Dados que coletamos</h2>
                <ul>
                    <li>
                        <strong>Dados de conta:</strong> nome, e-mail e senha (armazenada de forma criptografada).
                    </li>
                    <li>
                        <strong>Dados fiscais do emitente:</strong> CNPJ, razão social, inscrição municipal, endereço e{' '}
                        <strong>certificado digital A1</strong> utilizado para emissão das notas.
                    </li>
                    <li>
                        <strong>Relatórios de comissão:</strong> arquivos enviados por você que contêm dados de{' '}
                        <strong>terceiros (vendedores)</strong>, como nome/razão social, CNPJ e valores de comissão.
                    </li>
                    <li>
                        <strong>Dados de pagamento:</strong> processados pela Stripe. A Plataforma não armazena os dados
                        completos do seu cartão.
                    </li>
                    <li>
                        <strong>Dados de uso:</strong> registros de acesso, endereço IP e logs técnicos necessários ao
                        funcionamento e à segurança da Plataforma.
                    </li>
                </ul>
            </section>

            <section>
                <h2>3. Finalidades e bases legais</h2>
                <p>Tratamos os dados para as seguintes finalidades, com as respectivas bases legais da LGPD:</p>
                <ul>
                    <li>
                        <strong>Prestação do serviço</strong> (criação de conta, emissão de NFS-e, downloads) &mdash;
                        execução de contrato;
                    </li>
                    <li>
                        <strong>Cumprimento de obrigações legais e fiscais</strong> &mdash; obrigação legal ou
                        regulatória;
                    </li>
                    <li>
                        <strong>Cobrança e gestão da assinatura</strong> &mdash; execução de contrato;
                    </li>
                    <li>
                        <strong>Segurança, prevenção a fraudes e melhoria do serviço</strong> &mdash; legítimo interesse.
                    </li>
                </ul>
            </section>

            <section>
                <h2>4. Certificado digital</h2>
                <p>
                    O certificado digital A1 é uma informação sensível. Ele é armazenado de forma segura e criptografada
                    e utilizado <strong>exclusivamente</strong> para assinar e emitir as NFS-e solicitadas por você. Não
                    compartilhamos seu certificado com terceiros para qualquer outra finalidade.
                </p>
            </section>

            <section>
                <h2>5. Dados de terceiros nos relatórios</h2>
                <p>
                    Os relatórios de comissão que você importa contêm dados de vendedores. Em relação a esses dados,{' '}
                    <strong>você atua como Controlador</strong> e a LHC Technology atua como{' '}
                    <strong>Operadora</strong> (LGPD, art. 5º, VII), tratando-os apenas para gerar as notas fiscais
                    conforme suas instruções. Você é responsável por possuir base legal para o tratamento desses dados.
                </p>
            </section>

            <section>
                <h2>6. Compartilhamento e subprocessadores</h2>
                <p>
                    Não vendemos seus dados. Compartilhamos dados apenas com prestadores necessários à operação da
                    Plataforma, que atuam como operadores:
                </p>
                <ul>
                    <li>
                        <strong>Stripe</strong> &mdash; processamento de pagamentos e gestão de assinaturas;
                    </li>
                    <li>
                        <strong>Amazon Web Services (AWS)</strong> &mdash; armazenamento de arquivos (Amazon S3) e
                        infraestrutura de hospedagem.
                    </li>
                </ul>
                <p>
                    Também podemos compartilhar dados quando exigido por lei ou por autoridade competente. Alguns desses
                    prestadores podem tratar dados fora do Brasil; nesses casos, adotam-se salvaguardas adequadas nos
                    termos da LGPD.
                </p>
            </section>

            <section>
                <h2>7. Armazenamento e segurança</h2>
                <p>
                    Adotamos medidas técnicas e organizacionais para proteger os dados contra acesso não autorizado,
                    perda ou alteração, incluindo criptografia, controle de acesso e monitoramento. Nenhum sistema é
                    100% seguro, mas trabalhamos continuamente para reduzir riscos.
                </p>
            </section>

            <section>
                <h2>8. Retenção de dados</h2>
                <p>
                    Mantemos os dados pelo tempo necessário às finalidades desta Política e ao cumprimento de obrigações
                    legais e fiscais. Encerrada a conta, os dados podem ser mantidos pelos prazos legais aplicáveis e,
                    depois, eliminados ou anonimizados.
                </p>
            </section>

            <section>
                <h2>9. Direitos do titular</h2>
                <p>
                    Nos termos do art. 18 da LGPD, você pode solicitar: confirmação da existência de tratamento; acesso
                    aos dados; correção de dados incompletos ou desatualizados; anonimização, bloqueio ou eliminação;
                    portabilidade; informação sobre compartilhamento; e revogação do consentimento. Para exercê-los,
                    escreva para <a href="mailto:luis@lhctechnology.com.br">luis@lhctechnology.com.br</a>.
                </p>
            </section>

            <section>
                <h2>10. Cookies</h2>
                <p>
                    Utilizamos cookies essenciais para autenticação e manutenção da sessão. Eles são necessários ao
                    funcionamento da Plataforma e não são usados para publicidade.
                </p>
            </section>

            <section>
                <h2>11. Dados de menores</h2>
                <p>
                    A Plataforma destina-se a pessoas jurídicas e não se dirige a menores de 18 anos. Não coletamos
                    intencionalmente dados de menores.
                </p>
            </section>

            <section>
                <h2>12. Alterações desta Política</h2>
                <p>
                    Podemos atualizar esta Política periodicamente. A versão vigente estará sempre disponível nesta
                    página, com a data de atualização correspondente.
                </p>
            </section>

            <section>
                <h2>13. Encarregado e contato</h2>
                <p>
                    Para assuntos relacionados à proteção de dados, o contato do encarregado (DPO) é{' '}
                    <a href="mailto:luis@lhctechnology.com.br">luis@lhctechnology.com.br</a>. Consulte também os nossos{' '}
                    <Link href={route('terms')}>Termos de Uso</Link>.
                </p>
            </section>

            <section>
                <h2>14. Legislação aplicável</h2>
                <p>
                    Esta Política é regida pela Lei Geral de Proteção de Dados (Lei nº 13.709/2018) e demais leis
                    aplicáveis da República Federativa do Brasil.
                </p>
            </section>
        </LegalLayout>
    );
}
