import { useState } from 'react';
import { Link } from '@inertiajs/react';
import { Menu, X, Zap } from 'lucide-react';

const NAV_LINKS = [
    { href: '#como-funciona', label: 'Como funciona' },
    { href: '#funcionalidades', label: 'Funcionalidades' },
    { href: '#planos', label: 'Planos' },
];

export function Header() {
    const [isMenuOpen, setIsMenuOpen] = useState(false);

    return (
        <header className="sticky top-0 z-50 border-b border-white/10 bg-[#0a0a0a]/80 backdrop-blur-md">
            <nav className="mx-auto flex h-16 max-w-7xl items-center justify-between px-6 lg:px-8">
                <Link href="/" className="flex items-center gap-2">
                    <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-[#EE4D2D]/10 text-[#EE4D2D]">
                        <Zap className="h-5 w-5" />
                    </span>
                    <span className="text-base font-semibold tracking-tight text-white">AfiliFacil</span>
                </Link>

                <div className="hidden items-center gap-8 md:flex">
                    {NAV_LINKS.map((link) => (
                        <a key={link.href} href={link.href} className="text-sm text-neutral-300 transition hover:text-white">
                            {link.label}
                        </a>
                    ))}
                </div>

                <div className="hidden items-center gap-3 md:flex">
                    <Link href={route('login')} className="text-sm font-medium text-neutral-300 transition hover:text-white">
                        Entrar
                    </Link>
                    <Link
                        href={route('register')}
                        className="rounded-full bg-[#EE4D2D] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#D94426]"
                    >
                        Começar grátis
                    </Link>
                </div>

                <button
                    type="button"
                    onClick={() => setIsMenuOpen((open) => !open)}
                    className="inline-flex items-center justify-center rounded-md p-2 text-neutral-300 hover:bg-white/5 hover:text-white md:hidden"
                    aria-label="Abrir menu"
                    aria-expanded={isMenuOpen}
                >
                    {isMenuOpen ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
                </button>
            </nav>

            {isMenuOpen && (
                <div className="border-t border-white/10 bg-[#0a0a0a] px-6 py-4 md:hidden">
                    <div className="flex flex-col gap-4">
                        {NAV_LINKS.map((link) => (
                            <a key={link.href} href={link.href} className="text-sm text-neutral-300 hover:text-white">
                                {link.label}
                            </a>
                        ))}
                        <Link href={route('login')} className="text-sm font-medium text-neutral-300 hover:text-white">
                            Entrar
                        </Link>
                        <Link
                            href={route('register')}
                            className="w-full rounded-full bg-[#EE4D2D] px-4 py-2 text-center text-sm font-semibold text-white hover:bg-[#D94426]"
                        >
                            Começar grátis
                        </Link>
                    </div>
                </div>
            )}
        </header>
    );
}
