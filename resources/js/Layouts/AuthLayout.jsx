import { Link } from '@inertiajs/react';
import { Receipt } from 'lucide-react';

export default function AuthLayout({ title, description, children }) {
    return (
        <div className="grid min-h-svh lg:grid-cols-2">
            <div className="flex flex-col gap-4 p-6 md:p-10">
                <div className="flex justify-center gap-2 md:justify-start">
                    <Link href="/" className="flex items-center gap-2 font-medium">
                        <div className="flex size-6 items-center justify-center rounded-md bg-[#EE4D2D] text-white">
                            <Receipt className="size-4" />
                        </div>
                        NF-facilitator
                    </Link>
                </div>

                <div className="flex flex-1 items-center justify-center">
                    <div className="w-full max-w-sm">
                        {(title || description) && (
                            <div className="mb-6 flex flex-col items-center gap-1 text-center">
                                {title && <h1 className="text-2xl font-bold">{title}</h1>}
                                {description && (
                                    <p className="text-balance text-sm text-muted-foreground">{description}</p>
                                )}
                            </div>
                        )}
                        {children}
                    </div>
                </div>
            </div>

            <div className="relative hidden overflow-hidden bg-[#EE4D2D] lg:block">
                <div className="absolute inset-0 bg-gradient-to-br from-[#EE4D2D] via-[#e6431f] to-[#c9381a]" />
                <div className="relative flex h-full flex-col justify-between p-12 text-white">
                    <div className="flex items-center gap-2 text-lg font-semibold">
                        <Receipt className="size-6" />
                        NF-facilitator
                    </div>
                    <div className="space-y-4">
                        <p className="text-3xl font-semibold leading-tight">
                            NF-e automática a partir dos seus relatórios de comissão.
                        </p>
                        <p className="max-w-md text-white/80">
                            Importe o relatório da Shopee, revise e emita todas as notas fiscais em minutos.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}
