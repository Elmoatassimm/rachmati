import React from 'react';
import { BarChart3, LucideIcon } from 'lucide-react';
import { ModernPageHeader } from '@/components/ui/modern-page-header';

interface AdminPageHeaderProps {
  title?: string;
  subtitle?: string;
  icon?: LucideIcon;
  children?: React.ReactNode;
  className?: string;
}

export function AdminPageHeader({
  title = 'لوحة الإدارة',
  subtitle = 'Tableau de bord administrateur',
  icon = BarChart3,
  children,
  className,
}: AdminPageHeaderProps) {
  return (
    <ModernPageHeader
      title={title}
      subtitle={subtitle}
      icon={icon}
      className={className}
    >
      {children}
    </ModernPageHeader>
  );
}

export default AdminPageHeader;
