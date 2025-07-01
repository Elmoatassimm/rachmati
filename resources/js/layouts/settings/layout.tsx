import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { User, Lock, Palette } from 'lucide-react';
import { type PropsWithChildren } from 'react';

const sidebarNavItems: NavItem[] = [
    {
        title: 'الملف الشخصي',
        href: '/settings/profile',
        icon: User,
    },
    {
        title: 'كلمة المرور',
        href: '/settings/password',
        icon: Lock,
    },
    {
        title: 'المظهر',
        href: '/settings/appearance',
        icon: Palette,
    },
];

export default function SettingsLayout({ children }: PropsWithChildren) {
    // When server-side rendering, we only render the layout on the client...
    if (typeof window === 'undefined') {
        return null;
    }

    const currentPath = window.location.pathname;

    return (
        <div className="px-4 py-6 space-y-6">
            <Heading title="الإعدادات" description="إدارة ملفك الشخصي وإعدادات الحساب" />

            <div className="flex flex-col gap-6 lg:flex-row">
                <aside className="w-full lg:w-64">
                    <Card>
                        <CardContent className="p-6">
                            <nav className="space-y-2">
                                {sidebarNavItems.map((item, index) => {
                                    const Icon = item.icon;
                                    return (
                                        <Button
                                            key={`${item.href}-${index}`}
                                            size="sm"
                                            variant={currentPath === item.href ? "secondary" : "ghost"}
                                            asChild
                                            className={cn(
                                                'w-full justify-start gap-2',
                                                currentPath === item.href && 'bg-secondary'
                                            )}
                                        >
                                            <Link href={item.href} prefetch>
                                                {Icon && <Icon className="h-4 w-4" />}
                                                {item.title}
                                            </Link>
                                        </Button>
                                    );
                                })}
                            </nav>
                        </CardContent>
                    </Card>
                </aside>

                <Separator className="lg:hidden" />

                <div className="flex-1">
                    <Card>
                        <CardContent className="p-6">
                            {children}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    );
}
