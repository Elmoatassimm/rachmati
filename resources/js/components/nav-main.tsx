import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';

export function NavMain({ items = [] }: { items: NavItem[] }) {
    const page = usePage();
    return (
        <SidebarGroup className="px-2">
            <SidebarGroupLabel className="px-3 py-2 text-sm font-semibold text-sidebar-foreground/70 tracking-wide">
                القائمة الرئيسية
            </SidebarGroupLabel>
            <SidebarMenu className="space-y-1">
                {items.map((item) => (
                    <SidebarMenuItem key={item.title}>
                        <SidebarMenuButton
                            asChild
                            size="lg"
                            isActive={page.url.startsWith(item.href)}
                            tooltip={{ children: item.title }}
                            className="group relative"
                        >
                            <Link
                                href={item.href}
                                className="text-right flex flex-row-reverse items-center w-full font-medium transition-all duration-300 hover:translate-x-1 rtl:hover:-translate-x-1 group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:hover:translate-x-0"
                                prefetch
                            >
                                <span className="flex-1 text-right leading-relaxed transition-all duration-300 group-data-[collapsible=icon]:hidden">{item.title}</span>
                                {item.icon && (
                                    <item.icon className="transition-all duration-300 group-hover:scale-110 group-data-[collapsible=icon]:scale-110" />
                                )}
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}
