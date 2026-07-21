import { Logo } from '@/Components/Logo';

export function Footer() {
    return (
        <footer className="border-t border-neutral-200 bg-white py-8">
            <div className="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-6 sm:flex-row lg:px-8">
                <div className="flex items-center gap-2">
                    <Logo size={24} plate={false} />
                    <span className="text-sm font-medium text-neutral-600">AfiliFacil</span>
                </div>
                <p className="text-xs text-neutral-400">
                    &copy; {new Date().getFullYear()} AfiliFacil. Todos os direitos reservados.
                </p>
            </div>
        </footer>
    );
}
