/**
 * AppSidebar Component
 * 
 * This component provides role-based navigation for the application:
 * 
 * - Admin Users: Access to admin dashboard, designer management, orders, and categories
 * - Designer Users: Access to designer dashboard, rachmat management, and subscription
 * - Client Users: Access to basic dashboard
 * 
 * Navigation items are dynamically rendered based on the user's user_type field.
 * All navigation items include Arabic labels and appropriate icons.
 */

import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem, SidebarRail } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
    BookOpen,
    Folder,
    LayoutGrid,
    Users,
    ShoppingCart,
    FolderOpen,
    Package,
    CreditCard,
    PlusCircle,
    Settings,
    DollarSign,
    Wallet,
    BarChart3,
    FileText,
    Store,
    Share2,
    Globe,
    Shield,
    ClipboardList
} from 'lucide-react';
import AppLogo from './app-logo';

// Admin navigation items
const adminNavItems: NavItem[] = [
    {
        title: 'لوحة الإدارة',
        href: '/admin/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'إدارة المصممين',
        href: '/admin/designers',
        icon: Users,
    },
    {
        title: 'إدارة الطلبات',
        href: '/admin/orders',
        icon: ShoppingCart,
    },
    {
        title: 'إدارة الفئات',
        href: '/admin/categories',
        icon: FolderOpen,
    },
    {
        title: 'اقتراحات أجزاء الرشمات',
        href: '/admin/parts-suggestions',
        icon: Globe,
    },
    {
        title: 'إدارة الرشمات',
        href: '/admin/rachmat',
        icon: Package,
    },
    {
        title: 'إدارة خطط الأسعار',
        href: '/admin/pricing-plans',
        icon: DollarSign,
    },
    {
        title: 'معلومات الدفع',
        href: '/admin/payment-info',
        icon: Wallet,
    },
    {
        title: 'طلبات الاشتراك',
        href: '/admin/subscription-requests',
        icon: FileText,
    },
    {
        title: 'سياسة الخصوصية',
        href: '/admin/privacy-policy',
        icon: Shield,
    },

];

// Designer navigation items
const designerNavItems: NavItem[] = [
    {
        title: 'لوحة المصمم',
        href: '/designer/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'رشماتي',
        href: '/designer/rachmat',
        icon: Package,
    },
    {
        title: 'إضافة رشمة',
        href: '/designer/rachmat/create',
        icon: PlusCircle,
    },
    {
        title: 'طلباتي',
        href: '/designer/orders',
        icon: ClipboardList,
    },
    {
        title: 'إدارة المتجر',
        href: '/designer/store',
        icon: Store,
    },
    {
        title: 'التحليلات والتقارير',
        href: '/designer/analytics',
        icon: BarChart3,
    },
    {
        title: 'طلبات الاشتراك',
        href: '/designer/subscription-requests',
        icon: CreditCard,
    },

];



// Default navigation items for regular users
const defaultNavItems: NavItem[] = [
    {
        title: 'لوحة التحكم',
        href: '/dashboard',
        icon: LayoutGrid,
    },
];

const footerNavItems: NavItem[] = [
    
];

export function AppSidebar() {
    const { auth } = usePage<SharedData>().props;
    
    // Determine navigation items based on user type
    const getNavigationItems = (): NavItem[] => {
        if (!auth.user) return defaultNavItems;
        
        // Check user type
        const userType = auth.user?.user_type;
        
        switch (userType) {
            case 'admin':
                return adminNavItems;
            case 'designer':
                return designerNavItems;
            default:
                return defaultNavItems;
        }
    };

    const mainNavItems = getNavigationItems();

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>

            <SidebarRail />
        </Sidebar>
    );
}
