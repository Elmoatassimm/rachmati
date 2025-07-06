import React from 'react';
import { DataTable, DataTableProps } from '@/components/ui/data-table';
import { Pagination } from '@/components/ui/pagination';
import { Loader2 } from 'lucide-react';

interface PaginatedData<T> {
  data: T[];
  current_page: number;
  first_page_url: string;
  from: number;
  last_page: number;
  last_page_url: string;
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
  next_page_url: string | null;
  path: string;
  per_page: number;
  prev_page_url: string | null;
  to: number;
  total: number;
}

interface DataTablePaginationProps<T> extends Omit<DataTableProps<T>, 'data'> {
  paginatedData: PaginatedData<T>;
  isLoading?: boolean;
  onPageChange: (page: number) => void;
  showGoToPage?: boolean;
}

export function DataTablePagination<T>({
  paginatedData,
  isLoading = false,
  onPageChange,
  showGoToPage = true,
  ...props
}: DataTablePaginationProps<T>) {
  return (
    <div className="space-y-6">
      <DataTable
        data={paginatedData.data}
        {...props}
      />

      {/* Loading indicator */}
      {isLoading && (
        <div className="flex items-center justify-center gap-2 text-sm text-muted-foreground py-4">
          <Loader2 className="w-4 h-4 animate-spin" />
          جاري التحديث...
        </div>
      )}

      {/* Pagination */}
      {paginatedData.last_page > 1 && (
        <Pagination
          currentPage={paginatedData.current_page}
          totalPages={paginatedData.last_page}
          totalItems={paginatedData.total}
          itemsPerPage={paginatedData.per_page}
          onPageChange={onPageChange}
          showGoToPage={showGoToPage}
        />
      )}
    </div>
  );
}