import { Link, usePage } from '@inertiajs/react';
import {
    CreditCard,
    FileText,
    LayoutDashboard,
    LogOut,
    Receipt,
    ShieldCheck,
    Upload,
    User,
    Users,
    ChevronsUpDown,
} from 'lucide-react';

import { NfUsageMeter } from '@/Components/App/NfUsageMeter';
import { Avatar, AvatarFallback } from '@/Components/ui/Avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/DropdownMenu';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/Components/ui/Sidebar';

const NAV_ITEMS = [
    { name: 'Início', route: 'dashboard', icon: LayoutDashboard },
    { name: 'Cadastro Fiscal', route: 'issuer.edit', icon: ShieldCheck },
    { name: 'Importações', route: 'imports.index', icon: Upload },
    { name: 'Notas', route: 'invoices.index', icon: FileText },
    { name: 'Afiliados', route: 'sellers.index', icon: Users },
    { name: 'Assinatura', route: 'billing.index', icon: CreditCard },
];

function initials(name = '') {
    return (
        name
            .split(' ')
            .filter(Boolean)
            .slice(0, 2)
            .map((part) => part[0])
            .join('')
            .toUpperCase() || 'U'
    );
}

export function AppSidebar() {
    const { auth, fiscal } = usePage().props;
    const user = auth.user;
    const fiscalPending = fiscal && !fiscal.complete;
    const { setOpenMobile } = useSidebar();

    return (
        <Sidebar collapsible="icon">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/" onClick={() => setOpenMobile(false)}>
                                <div className="flex aspect-square size-8 items-center justify-center rounded-lg bg-[#EE4D2D] text-white">
                                    <Receipt className="size-4" />
                                </div>
                                <div className="grid flex-1 text-left text-sm leading-tight">
                                    <span className="truncate font-semibold">AfiliFacil</span>
                                    <span className="truncate text-xs text-muted-foreground">Notas de afiliados</span>
                                </div>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <SidebarGroup>
                    <SidebarGroupLabel>Platform</SidebarGroupLabel>
                    <SidebarMenu>
                        {NAV_ITEMS.map((item) => {
                            const active = route().current(`${item.route.split('.')[0]}.*`);
                            return (
                                <SidebarMenuItem key={item.route}>
                                    <SidebarMenuButton asChild isActive={active} tooltip={item.name}>
                                        <Link href={route(item.route)} onClick={() => setOpenMobile(false)}>
                                            <item.icon />
                                            <span>{item.name}</span>
                                            {item.route === 'issuer.edit' && fiscalPending && (
                                                <span
                                                    className="ml-auto size-2 shrink-0 rounded-full bg-amber-500"
                                                    title="Cadastro fiscal pendente"
                                                />
                                            )}
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            );
                        })}
                    </SidebarMenu>
                </SidebarGroup>
            </SidebarContent>

            <SidebarFooter>
                <NfUsageMeter />

                <SidebarMenu>
                    <SidebarMenuItem>
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <SidebarMenuButton
                                    size="lg"
                                    className="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                                >
                                    <Avatar className="size-8 rounded-lg">
                                        <AvatarFallback className="rounded-lg">{initials(user.name)}</AvatarFallback>
                                    </Avatar>
                                    <div className="grid flex-1 text-left text-sm leading-tight">
                                        <span className="truncate font-semibold">{user.name}</span>
                                        <span className="truncate text-xs text-muted-foreground">{user.email}</span>
                                    </div>
                                    <ChevronsUpDown className="ml-auto size-4" />
                                </SidebarMenuButton>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                className="w-[--radix-dropdown-menu-trigger-width] min-w-56 rounded-lg"
                                side="top"
                                align="end"
                                sideOffset={4}
                            >
                                <DropdownMenuLabel className="p-0 font-normal">
                                    <div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                        <Avatar className="size-8 rounded-lg">
                                            <AvatarFallback className="rounded-lg">{initials(user.name)}</AvatarFallback>
                                        </Avatar>
                                        <div className="grid flex-1 text-left text-sm leading-tight">
                                            <span className="truncate font-semibold">{user.name}</span>
                                            <span className="truncate text-xs text-muted-foreground">{user.email}</span>
                                        </div>
                                    </div>
                                </DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem asChild>
                                    <Link href={route('profile.edit')}>
                                        <User className="mr-2 size-4" />
                                        Perfil
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem asChild>
                                    <Link href={route('logout')} method="post" as="button" className="w-full">
                                        <LogOut className="mr-2 size-4" />
                                        Sair
                                    </Link>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarFooter>
        </Sidebar>
    );
}
