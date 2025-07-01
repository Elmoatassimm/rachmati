import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { DataTable } from '@/components/ui/data-table';
import { ColumnDef } from '@tanstack/react-table';
import { cn } from '@/lib/utils';

interface DataCardProps<TData, TValue> {
  title: string;
  subtitle?: string;
  columns: ColumnDef<TData, TValue>[];
  data: TData[];
  searchPlaceholder?: string;
  searchColumn?: string;
  className?: string;
  headerActions?: React.ReactNode;
}

export function DataCard<TData, TValue>({
  title,
  subtitle,
  columns,
  data,
  searchPlaceholder = 'البحث...',
  searchColumn,
  className,
  headerActions,
}: DataCardProps<TData, TValue>) {
  return (
    <Card className={cn(
      'transition-all duration-200 hover:shadow-md border-0 shadow-sm',
      className
    )}>
      <CardHeader className="pb-4">
        <div className="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
          <div className="space-y-1">
            <CardTitle className="text-xl font-semibold text-foreground">
              {title}
            </CardTitle>
            {subtitle && (
              <p className="text-sm text-muted-foreground">{subtitle}</p>
            )}
          </div>
          {headerActions && (
            <div className="flex gap-2">
              {headerActions}
            </div>
          )}
        </div>
      </CardHeader>
      <CardContent>
        <DataTable
          columns={columns}
          data={data}
          searchPlaceholder={searchPlaceholder}
          searchColumn={searchColumn}
        />
      </CardContent>
    </Card>
  );
}

export default DataCard;
