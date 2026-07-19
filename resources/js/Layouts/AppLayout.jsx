import { AppSidebar } from '@/Components/App/AppSidebar';
import { Separator } from '@/Components/ui/Separator';
import {
    SidebarInset,
    SidebarProvider,
    SidebarTrigger,
} from '@/Components/ui/Sidebar';

export default function AppLayout({ header, children }) {
    return (
        <SidebarProvider>
            <AppSidebar />
            <SidebarInset>
                <header className="flex h-16 shrink-0 items-center gap-2 border-b transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12">
                    <div className="flex items-center gap-2 px-4">
                        <SidebarTrigger className="-ml-1" />
                        <Separator orientation="vertical" className="mr-2 h-4" />
                        {header}
                    </div>
                </header>

                <main className="flex flex-1 flex-col gap-4 p-4 md:p-6">{children}</main>
            </SidebarInset>
        </SidebarProvider>
    );
}
