import React from 'react';
import { Crown, LucideIcon } from 'lucide-react';
import { ModernPageHeader } from '@/components/ui/modern-page-header';

interface DesignerPageHeaderProps {
  title?: string;
  subtitle?: string;
  icon?: LucideIcon;
  children?: React.ReactNode;
  className?: string;
}

export function DesignerPageHeader({
  title = 'لوحة المصمم',
  subtitle = 'مرحباً بك في لوحة تحكم المصمم',
  icon = Crown,
  children,
  className,
}: DesignerPageHeaderProps) {
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

export default DesignerPageHeader;
