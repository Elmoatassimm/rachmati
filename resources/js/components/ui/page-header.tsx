import React from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

interface PageHeaderAction {
  label: string;
  onClick: () => void;
  variant?: 'default' | 'outline' | 'secondary' | 'ghost' | 'link' | 'destructive';
  icon?: React.ReactNode;
  className?: string;
}

interface PageHeaderProps {
  title: string;
  subtitle?: string;
  actions?: PageHeaderAction[];
  searchProps?: {
    value: string;
    onChange: (value: string) => void;
    placeholder?: string;
    className?: string;
  };
  className?: string;
}

export function PageHeader({
  title,
  subtitle,
  actions = [],
  searchProps,
  className,
}: PageHeaderProps) {
  return (
    <div className={cn('space-y-6', className)}>
      <div className="flex flex-col md:flex-row md:justify-between md:items-start gap-6">
        <div className="space-y-2">
          <h1 className="text-4xl font-bold tracking-tight text-foreground">
            {title}
          </h1>
          {subtitle && (
            <p className="text-lg text-muted-foreground">
              {subtitle}
            </p>
          )}
        </div>
        
        {(actions.length > 0 || searchProps) && (
          <div className="flex flex-col sm:flex-row gap-3">
            {searchProps && (
              <Input
                type="text"
                placeholder={searchProps.placeholder || 'بحث...'}
                value={searchProps.value}
                onChange={(e) => searchProps.onChange(e.target.value)}
                className={cn('w-full sm:w-64 h-11', searchProps.className)}
              />
            )}
            {actions.map((action, index) => (
              <Button
                key={index}
                variant={action.variant || 'default'}
                onClick={action.onClick}
                className={cn('flex items-center gap-2 h-11 px-6', action.className)}
              >
                {action.icon}
                {action.label}
              </Button>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}

export default PageHeader;
