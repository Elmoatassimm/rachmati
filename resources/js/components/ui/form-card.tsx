import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

interface FormAction {
  label: string;
  onClick: () => void;
  variant?: 'default' | 'outline' | 'secondary' | 'ghost' | 'link' | 'destructive';
  icon?: React.ReactNode;
  disabled?: boolean;
  loading?: boolean;
}

interface FormCardProps {
  title: string;
  subtitle?: string;
  children: React.ReactNode;
  actions?: FormAction[];
  className?: string;
  gradient?: boolean;
}

export function FormCard({
  title,
  subtitle,
  children,
  actions = [],
  className,
  gradient = false,
}: FormCardProps) {
  return (
    <Card className={cn(
      'border-0 shadow-md',
      gradient && 'bg-gradient-to-r from-blue-50/50 to-indigo-50/50',
      className
    )}>
      <CardHeader className="pb-4">
        <CardTitle className="text-xl font-semibold text-foreground">
          {title}
        </CardTitle>
        {subtitle && (
          <p className="text-sm text-muted-foreground">{subtitle}</p>
        )}
      </CardHeader>
      <CardContent className="space-y-6 p-6">
        {children}
        {actions.length > 0 && (
          <div className="flex gap-3 pt-2">
            {actions.map((action, index) => (
              <Button
                key={index}
                variant={action.variant || 'default'}
                onClick={action.onClick}
                disabled={action.disabled || action.loading}
                className="px-6 h-11"
              >
                {action.loading ? (
                  <div className="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin mr-2" />
                ) : (
                  action.icon && <span className="mr-2">{action.icon}</span>
                )}
                {action.label}
              </Button>
            ))}
          </div>
        )}
      </CardContent>
    </Card>
  );
}

export default FormCard;
