import { Logo } from '@/Components/Logo';

const FOOTER_LINKS = [
    { href: '#como-funciona', label: 'Como funciona' },
    { href: '#funcionalidades', label: 'Funcionalidades' },
    { href: '#planos', label: 'Planos' },
    { href: '#duvidas', label: 'Dúvidas' },
];

export function Footer() {
    return (
        <footer className="border-t border-neutral-200 bg-neutral-50 py-10">
            <div className="mx-auto flex max-w-7xl flex-col items-center gap-6 px-6 sm:flex-row sm:justify-between lg:px-8">
                <div className="flex flex-col items-center gap-2 sm:items-start">
                    <div className="flex items-center gap-2">
                        <Logo size={24} plate={false} />
                        <span className="font-display text-sm font-bold text-neutral-700">AfiliFacil</span>
                    </div>
                    <p className="font-serif text-sm italic text-neutral-400">
                        Emissão automática de NFS-e para afiliados Shopee.
                    </p>
                </div>

                <nav className="flex flex-wrap items-center justify-center gap-x-6 gap-y-2">
                    {FOOTER_LINKS.map((link) => (
                        <a key={link.href} href={link.href} className="text-xs text-neutral-500 transition hover:text-neutral-900">
                            {link.label}
                        </a>
                    ))}
                </nav>

                <p className="text-xs text-neutral-400">
                    &copy; {new Date().getFullYear()} AfiliFacil. Todos os direitos reservados.
                </p>
            </div>
        </footer>
    );
}
