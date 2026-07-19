import { Zap } from 'lucide-react';

export function Footer() {
    return (
        <footer className="border-t border-neutral-200 bg-white py-8">
            <div className="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-6 sm:flex-row lg:px-8">
                <div className="flex items-center gap-2">
                    <span className="flex h-6 w-6 items-center justify-center rounded-md bg-[#EE4D2D]/10 text-[#EE4D2D]">
                        <Zap className="h-3.5 w-3.5" />
                    </span>
                    <span className="text-sm font-medium text-neutral-600">NF Facilitator</span>
                </div>
                <p className="text-xs text-neutral-400">
                    &copy; {new Date().getFullYear()} NF Facilitator. Todos os direitos reservados.
                </p>
            </div>
        </footer>
    );
}
