import { Link } from '@inertiajs/react';
import { LegalLayout } from '@/Components/Legal/LegalLayout';

export default function Terms() {
    return (
        <LegalLayout
            title="Termos de Uso"
            metaDescription="Termos de Uso da plataforma AfiliFacil — condições de uso do serviço de emissão automática de NFS-e para afiliados."
            lastUpdated="21 de julho de 2026"
        >
            <p>
                Estes Termos de Uso (&ldquo;Termos&rdquo;) regem o acesso e a utilização da plataforma AfiliFacil
                (&ldquo;Plataforma&rdquo;), operada por <strong>LHC Technology</strong>, inscrita no CNPJ sob o
                nº 49.265.984/0001-21 (&ldquo;AfiliFacil&rdquo;, &ldquo;nós&rdquo;). Ao criar uma conta ou utilizar a
                Plataforma, você (&ldquo;Usuário&rdquo;) declara ter lido, compreendido e aceitado integralmente estes
                Termos. Caso não concorde, não utilize a Plataforma.
            </p>

            <section>
                <h2>1. Definições</h2>
                <ul>
                    <li>
                        <strong>Plataforma:</strong> o software e os serviços do AfiliFacil disponibilizados pela web.
                    </li>
                    <li>
                        <strong>Usuário:</strong> pessoa jurídica que se cadastra e utiliza a Plataforma.
                    </li>
                    <li>
                        <strong>NFS-e:</strong> Nota Fiscal de Serviço eletrônica emitida por meio da Plataforma.
                    </li>
                    <li>
                        <strong>Certificado Digital:</strong> certificado do tipo A1 do Usuário, utilizado para assinar
                        e emitir as NFS-e.
                    </li>
                </ul>
            </section>

            <section>
                <h2>2. Descrição do serviço</h2>
                <p>
                    A Plataforma automatiza a emissão de NFS-e a partir de relatórios de comissão de marketplaces (como
                    a Shopee). O Usuário importa o relatório, os dados são validados e agrupados por vendedor e mês de
                    referência, e as notas são emitidas em lote e disponibilizadas para download (PDF, XML e ZIP).
                </p>
                <p>
                    A Plataforma é uma <strong>ferramenta de apoio</strong> à emissão de documentos fiscais. Não somos
                    contadores nem prestamos consultoria contábil, tributária ou jurídica.
                </p>
            </section>

            <section>
                <h2>3. Cadastro e conta</h2>
                <p>
                    Para utilizar a Plataforma é obrigatório possuir um <strong>CNPJ</strong> ativo e regular. O Usuário
                    compromete-se a fornecer informações verdadeiras, completas e atualizadas, e a mantê-las assim.
                </p>
                <p>
                    O Usuário é o único responsável pela guarda e confidencialidade de suas credenciais de acesso e por
                    todas as atividades realizadas em sua conta. Notifique-nos imediatamente em caso de uso não
                    autorizado.
                </p>
            </section>

            <section>
                <h2>4. Certificado digital</h2>
                <p>
                    A emissão das NFS-e depende do envio do Certificado Digital A1 do Usuário. Ao enviá-lo, o Usuário
                    autoriza expressamente a AfiliFacil a utilizá-lo <strong>exclusivamente</strong> para assinar e
                    emitir as notas fiscais solicitadas por ele. O Usuário declara ser o titular legítimo do certificado
                    e responsabiliza-se por sua validade.
                </p>
            </section>

            <section>
                <h2>5. Responsabilidade fiscal</h2>
                <p>
                    As obrigações tributárias e fiscais decorrentes da atividade do Usuário são de sua exclusiva
                    responsabilidade. O Usuário é responsável pela <strong>veracidade e correção</strong> dos dados
                    informados e importados, incluindo valores, CNPJs e dados dos vendedores.
                </p>
                <p>
                    Recomendamos o acompanhamento por profissional de contabilidade. A AfiliFacil não se responsabiliza
                    por notas emitidas com base em dados incorretos fornecidos pelo Usuário, nem por decisões fiscais
                    tomadas por ele.
                </p>
            </section>

            <section>
                <h2>6. Planos, pagamentos e assinatura</h2>
                <p>
                    A Plataforma oferece um plano Gratuito e planos pagos (Básico e Avançado), cada um com um limite de
                    NFS-e por mês, conforme descrito na página de planos. Os pagamentos dos planos pagos são processados
                    pela <strong>Stripe</strong>, e a assinatura é recorrente e renovada automaticamente até que seja
                    cancelada.
                </p>
                <p>
                    O Usuário pode cancelar a assinatura a qualquer momento; o acesso ao plano pago permanece ativo até
                    o fim do período já pago. Valores já cobrados não são reembolsados de forma proporcional, salvo
                    quando exigido por lei.
                </p>
            </section>

            <section>
                <h2>7. Uso aceitável</h2>
                <p>É vedado ao Usuário, entre outras condutas:</p>
                <ul>
                    <li>utilizar a Plataforma para fins ilícitos ou emitir notas fraudulentas;</li>
                    <li>enviar dados de terceiros sem autorização ou base legal;</li>
                    <li>tentar acessar áreas, contas ou dados que não lhe pertencem;</li>
                    <li>comprometer a segurança, a integridade ou a disponibilidade da Plataforma;</li>
                    <li>copiar, revender ou explorar a Plataforma sem autorização.</li>
                </ul>
            </section>

            <section>
                <h2>8. Propriedade intelectual</h2>
                <p>
                    A Plataforma, sua marca, código, design e conteúdos são de titularidade da LHC Technology e
                    protegidos por lei. Estes Termos não transferem ao Usuário qualquer direito de propriedade
                    intelectual, concedendo apenas uma licença limitada, pessoal e intransferível de uso.
                </p>
            </section>

            <section>
                <h2>9. Disponibilidade e alterações do serviço</h2>
                <p>
                    Envidamos esforços para manter a Plataforma disponível, mas não garantimos funcionamento
                    ininterrupto ou livre de erros. Podemos alterar, suspender ou descontinuar funcionalidades,
                    mediante aviso quando razoável.
                </p>
            </section>

            <section>
                <h2>10. Limitação de responsabilidade</h2>
                <p>
                    Na máxima extensão permitida pela lei, a AfiliFacil não se responsabiliza por danos indiretos,
                    lucros cessantes, perda de dados ou prejuízos decorrentes de: dados incorretos fornecidos pelo
                    Usuário; indisponibilidade de serviços de terceiros (como prefeituras, Stripe ou provedores de
                    infraestrutura); ou uso indevido da Plataforma.
                </p>
            </section>

            <section>
                <h2>11. Suspensão e rescisão</h2>
                <p>
                    Podemos suspender ou encerrar o acesso do Usuário que violar estes Termos ou a legislação aplicável.
                    O Usuário pode encerrar sua conta a qualquer momento pelas configurações da Plataforma.
                </p>
            </section>

            <section>
                <h2>12. Proteção de dados</h2>
                <p>
                    O tratamento de dados pessoais é regido pela nossa{' '}
                    <Link href={route('privacy')}>Política de Privacidade</Link>, que integra estes Termos.
                </p>
            </section>

            <section>
                <h2>13. Alterações destes Termos</h2>
                <p>
                    Podemos atualizar estes Termos periodicamente. A versão vigente estará sempre disponível nesta
                    página, com a respectiva data de atualização. O uso continuado da Plataforma após alterações implica
                    concordância com os novos Termos.
                </p>
            </section>

            <section>
                <h2>14. Legislação aplicável</h2>
                <p>
                    Estes Termos são regidos pelas leis da República Federativa do Brasil, incluindo o Código de Defesa
                    do Consumidor, o Código Civil e a Lei Geral de Proteção de Dados (Lei nº 13.709/2018).
                </p>
            </section>

            <section>
                <h2>15. Contato</h2>
                <p>
                    Dúvidas sobre estes Termos podem ser enviadas para a LHC Technology (CNPJ 49.265.984/0001-21) pelo
                    e-mail <a href="mailto:luis@lhctechnology.com.br">luis@lhctechnology.com.br</a>.
                </p>
            </section>
        </LegalLayout>
    );
}
