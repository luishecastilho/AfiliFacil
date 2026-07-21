import { useState } from 'react';
import { Link } from '@inertiajs/react';
import { Menu, X } from 'lucide-react';

import { Logo } from '@/Components/Logo';

const NAV_LINKS = [
    { href: '/#como-funciona', label: 'Como funciona' },
    { href: '/#funcionalidades', label: 'Funcionalidades' },
    { href: '/#planos', label: 'Planos' },
    { href: '/#duvidas', label: 'Dúvidas' },
];

export function Header() {
    const [isMenuOpen, setIsMenuOpen] = useState(false);

    return (
        <header className="fixed inset-x-0 top-4 z-50 px-4">
            <div className="mx-auto w-full max-w-3xl">
                <nav className="flex h-14 items-center justify-between rounded-full border border-white/10 bg-[#0a0a0a]/80 pl-5 pr-2 shadow-lg shadow-black/20 backdrop-blur-md">
                    <Link href="/" className="flex items-center gap-2">
                        <Logo size={28} rounded="rounded-md" />
                        <span className="font-display text-base font-bold tracking-tight text-white">AfiliFacil</span>
                    </Link>

                    <div className="hidden items-center gap-6 md:flex">
                        {NAV_LINKS.map((link) => (
                            <a key={link.href} href={link.href} className="text-sm text-neutral-300 transition hover:text-white">
                                {link.label}
                            </a>
                        ))}
                    </div>

                    <div className="hidden items-center gap-2 md:flex">
                        <Link href={route('login')} className="px-3 text-sm font-medium text-neutral-300 transition hover:text-white">
                            Entrar
                        </Link>
                        <Link
                            href={route('register')}
                            className="rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-neutral-900 transition hover:scale-[1.03] hover:bg-neutral-100"
                        >
                            Começar grátis
                        </Link>
                    </div>

                    <button
                        type="button"
                        onClick={() => setIsMenuOpen((open) => !open)}
                        className="inline-flex items-center justify-center rounded-full p-2.5 text-neutral-300 hover:bg-white/5 hover:text-white md:hidden"
                        aria-label="Abrir menu"
                        aria-expanded={isMenuOpen}
                    >
                        {isMenuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
                    </button>
                </nav>

                {isMenuOpen && (
                    <div className="mt-2 rounded-2xl border border-white/10 bg-[#0a0a0a]/95 px-6 py-5 shadow-lg shadow-black/20 backdrop-blur-md md:hidden">
                        <div className="flex flex-col gap-4">
                            {NAV_LINKS.map((link) => (
                                <a
                                    key={link.href}
                                    href={link.href}
                                    onClick={() => setIsMenuOpen(false)}
                                    className="text-sm text-neutral-300 hover:text-white"
                                >
                                    {link.label}
                                </a>
                            ))}
                            <Link href={route('login')} className="text-sm font-medium text-neutral-300 hover:text-white">
                                Entrar
                            </Link>
                            <Link
                                href={route('register')}
                                className="w-full rounded-full bg-white px-4 py-2.5 text-center text-sm font-semibold text-neutral-900 hover:bg-neutral-100"
                            >
                                Começar grátis
                            </Link>
                        </div>
                    </div>
                )}
            </div>
        </header>
    );
}
