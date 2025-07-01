import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { LucideIcon } from 'lucide-react';
import { cn } from '@/lib/utils';

interface ModernStatsCardProps {
  title: string;
  value: string | number;
  subtitle?: string;
  icon?: LucideIcon;
  colorScheme?: 'blue' | 'emerald' | 'purple' | 'amber' | 'green' | 'yellow' | 'orange' | 'pink' | 'indigo';
  className?: string;
}

const colorSchemes = {
  blue: {
    background: 'from-blue-500/10 via-transparent to-blue-500/5',
    icon: 'from-blue-500 to-blue-600',
    value: 'from-blue-600 to-blue-500',
    bar: 'from-blue-500 to-blue-600'
  },
  emerald: {
    background: 'from-emerald-500/10 via-transparent to-emerald-500/5',
    icon: 'from-emerald-500 to-emerald-600',
    value: 'from-emerald-600 to-emerald-500',
    bar: 'from-emerald-500 to-emerald-600'
  },
  purple: {
    background: 'from-purple-500/10 via-transparent to-purple-500/5',
    icon: 'from-purple-500 to-purple-600',
    value: 'from-purple-600 to-purple-500',
    bar: 'from-purple-500 to-purple-600'
  },
  amber: {
    background: 'from-amber-500/10 via-transparent to-amber-500/5',
    icon: 'from-amber-500 to-amber-600',
    value: 'from-amber-600 to-amber-500',
    bar: 'from-amber-500 to-amber-600'
  },
  green: {
    background: 'from-green-500/10 via-transparent to-green-500/5',
    icon: 'from-green-500 to-green-600',
    value: 'from-green-600 to-green-500',
    bar: 'from-green-500 to-green-600'
  },
  yellow: {
    background: 'from-yellow-500/10 via-transparent to-yellow-500/5',
    icon: 'from-yellow-500 to-yellow-600',
    value: 'from-yellow-600 to-yellow-500',
    bar: 'from-yellow-500 to-yellow-600'
  },
  orange: {
    background: 'from-orange-500/10 via-transparent to-orange-500/5',
    icon: 'from-orange-500 to-orange-600',
    value: 'from-orange-600 to-orange-500',
    bar: 'from-orange-500 to-orange-600'
  },
  pink: {
    background: 'from-pink-500/10 via-transparent to-pink-500/5',
    icon: 'from-pink-500 to-pink-600',
    value: 'from-pink-600 to-pink-500',
    bar: 'from-pink-500 to-pink-600'
  },
  indigo: {
    background: 'from-indigo-500/10 via-transparent to-indigo-500/5',
    icon: 'from-indigo-500 to-indigo-600',
    value: 'from-indigo-600 to-indigo-500',
    bar: 'from-indigo-500 to-indigo-600'
  }
};

export function ModernStatsCard({
  title,
  value,
  subtitle,
  icon: Icon,
  colorScheme = 'blue',
  className,
}: ModernStatsCardProps) {
  const colors = colorSchemes[colorScheme];

  return (
    <Card className={cn(
      'group relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-2',
      className
    )}>
      <div className={`absolute inset-0 bg-gradient-to-br ${colors.background}`}></div>
      <CardHeader className="relative pb-4">
        <div className="flex items-center justify-between">
          <div className="space-y-2">
            <CardTitle className="text-sm font-semibold text-muted-foreground uppercase tracking-wider">
              {title}
            </CardTitle>
            <div className={`text-4xl font-black bg-gradient-to-r ${colors.value} bg-clip-text text-transparent`}>
              {typeof value === 'number' ? value.toLocaleString() : value}
            </div>
          </div>
          {Icon && (
            <div className={`w-14 h-14 bg-gradient-to-br ${colors.icon} rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300`}>
              <Icon className="w-7 h-7 text-white" />
            </div>
          )}
        </div>
      </CardHeader>
      <CardContent className="relative pt-0">
        {subtitle && (
          <p className="text-sm text-muted-foreground">{subtitle}</p>
        )}
        <div className={`mt-3 h-1 bg-gradient-to-r ${colors.bar} rounded-full`}></div>
      </CardContent>
    </Card>
  );
}

export default ModernStatsCard;
