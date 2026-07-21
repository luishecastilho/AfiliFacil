import { ChevronDown } from 'lucide-react';

// Exported so Landing.jsx can generate the FAQPage JSON-LD from the same source.
export const FAQ_ITEMS = [
    {
        question: 'Afiliado Shopee precisa emitir nota fiscal?',
        answer:
            'Sim. Pela nova regra, toda venda com comissão extra exige uma NFS-e emitida para o vendedor que pagou essa comissão. Na prática, o afiliado emite uma nota por vendedor a cada mês.',
    },
    {
        question: 'O que é NFS-e?',
        answer:
            'É a Nota Fiscal de Serviço eletrônica — o documento que formaliza um serviço prestado, como o de divulgação que o afiliado presta aos vendedores.',
    },
    {
        question: 'Preciso emitir uma nota para cada vendedor?',
        answer:
            'Sim — é exatamente essa a dor que o AfiliFacil resolve: em vez de emitir uma a uma, você importa o relatório e o sistema gera uma NFS-e por vendedor, automaticamente.',
    },
    {
        question: 'Preciso ter CNPJ para usar o AfiliFacil?',
        answer:
            'Sim. Para emitir NFS-e das suas comissões é necessário ter um CNPJ. Com ele em mãos, o AfiliFacil cuida de todo o resto do processo de emissão.',
    },
    {
        question: 'Que arquivo eu envio?',
        answer:
            'O relatório de comissões que a própria Shopee disponibiliza na sua conta de afiliado, em CSV. Você não precisa editar nada.',
    },
    {
        question: 'Quanto custa?',
        answer:
            'O plano gratuito emite até 5 NFS-e por mês. Os planos pagos começam em R$ 39,90/mês (50 NFS-e) e vão até NFS-e ilimitadas por R$ 169,90/mês.',
    },
    {
        question: 'Funciona para outros marketplaces além da Shopee?',
        answer: 'Hoje o AfiliFacil é focado na Shopee. Outros marketplaces estão no nosso radar.',
    },
];

export function Faq() {
    return (
        <section id="duvidas" className="bg-neutral-50 py-24">
            <div className="mx-auto max-w-3xl px-6 lg:px-8">
                <div className="text-center">
                    <h2 className="text-3xl font-bold tracking-tight text-neutral-900 sm:text-4xl">
                        Dúvidas frequentes de afiliados Shopee
                    </h2>
                </div>

                <div className="mt-12 space-y-4">
                    {FAQ_ITEMS.map((item) => (
                        <details
                            key={item.question}
                            className="group rounded-2xl border border-neutral-200 bg-white p-6"
                        >
                            <summary className="flex cursor-pointer list-none items-center justify-between gap-4 [&::-webkit-details-marker]:hidden">
                                <h3 className="text-base font-semibold text-neutral-900">{item.question}</h3>
                                <ChevronDown className="h-5 w-5 shrink-0 text-neutral-400 transition group-open:rotate-180" />
                            </summary>
                            <p className="mt-3 text-sm leading-relaxed text-neutral-500">{item.answer}</p>
                        </details>
                    ))}
                </div>
            </div>
        </section>
    );
}
