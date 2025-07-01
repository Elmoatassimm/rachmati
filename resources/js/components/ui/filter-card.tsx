import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

interface FilterField {
  type: 'text' | 'select' | 'date';
  label: string;
  value: string;
  onChange: (value: string) => void;
  placeholder?: string;
  options?: { value: string; label: string }[];
  className?: string;
}

interface FilterCardProps {
  title?: string;
  fields: FilterField[];
  onSearch: () => void;
  onReset: () => void;
  searchLabel?: string;
  resetLabel?: string;
  className?: string;
}

export function FilterCard({
  title = 'البحث والتصفية',
  fields,
  onSearch,
  onReset,
  searchLabel = 'بحث',
  resetLabel = 'إعادة تعيين',
  className,
}: FilterCardProps) {
  const renderField = (field: FilterField, index: number) => {
    const baseInputClasses = "w-full h-11 px-3 py-2 border border-input bg-background rounded-md focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition-colors";
    
    return (
      <div key={index} className={cn('space-y-2', field.className)}>
        <Label className="block text-sm font-medium text-foreground">
          {field.label}
        </Label>
        {field.type === 'text' && (
          <Input
            type="text"
            value={field.value}
            onChange={(e) => field.onChange(e.target.value)}
            placeholder={field.placeholder}
            className="h-11"
          />
        )}
        {field.type === 'select' && (
          <select
            value={field.value}
            onChange={(e) => field.onChange(e.target.value)}
            className={baseInputClasses}
          >
            {field.options?.map((option) => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </select>
        )}
        {field.type === 'date' && (
          <input
            type="date"
            value={field.value}
            onChange={(e) => field.onChange(e.target.value)}
            className={baseInputClasses}
          />
        )}
      </div>
    );
  };

  return (
    <Card className={cn(
      'transition-all duration-200 hover:shadow-md border-0 shadow-sm',
      className
    )}>
      <CardHeader className="pb-4">
        <CardTitle className="text-xl font-semibold text-foreground">{title}</CardTitle>
      </CardHeader>
      <CardContent>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
          {fields.map((field, index) => renderField(field, index))}
          <div className="flex items-end space-x-2 space-x-reverse">
            <Button onClick={onSearch} className="flex-1 h-11">
              {searchLabel}
            </Button>
            <Button onClick={onReset} variant="outline" className="h-11">
              {resetLabel}
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}

export default FilterCard;
