import React, { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

import { Card } from '@/components/ui/card';
import { cn } from '@/lib/utils';
import {
  ChevronsLeft,
  ChevronsRight,
  MoreHorizontal
} from 'lucide-react';

interface CustomPaginationProps {
  currentPage: number;
  totalPages: number;
  totalItems: number;
  itemsPerPage: number;
  onPageChange: (page: number) => void;
  showGoToPage?: boolean;
  showTotalInfo?: boolean;
  className?: string;
  // Additional params to pass with navigation
  additionalParams?: Record<string, any>;
}

export function CustomPagination({
  currentPage,
  totalPages,
  totalItems,
  itemsPerPage,
  onPageChange,
  showGoToPage = true,
  showTotalInfo = true,
  className,
  additionalParams = {}
}: CustomPaginationProps) {
  const [goToPage, setGoToPage] = useState('');

  if (totalPages <= 1) return null;

  const handleGoToPage = () => {
    const page = parseInt(goToPage);
    if (page >= 1 && page <= totalPages) {
      onPageChange(page);
      setGoToPage('');
    }
  };

  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter') {
      handleGoToPage();
    }
  };



  // Calculate visible page numbers
  const getVisiblePages = () => {
    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    
    // Adjust if we're near the end
    if (endPage - startPage < maxVisiblePages - 1) {
      startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }

    return { startPage, endPage };
  };

  const { startPage, endPage } = getVisiblePages();
  const fromItem = (currentPage - 1) * itemsPerPage + 1;
  const toItem = Math.min(currentPage * itemsPerPage, totalItems);

  return (
    <Card className={cn(
      "bg-gradient-to-br from-card via-card to-muted/30 border-0 shadow-lg",
      className
    )}>
      <div className="p-6 space-y-4">
        {/* Top section: Items info and per page selector */}
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          {showTotalInfo && (
            <div className="text-sm text-muted-foreground">
              عرض {fromItem.toLocaleString()} إلى {toItem.toLocaleString()} من {totalItems.toLocaleString()} عنصر
            </div>
          )}
          

        </div>

        {/* Main pagination controls */}
        <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          {/* Navigation buttons */}
          <div className="flex items-center justify-center lg:justify-start gap-1">
            {/* First page */}
            <Button
              variant="outline"
              size="sm"
              onClick={() => onPageChange(1)}
              disabled={currentPage === 1}
              className="h-9 w-9 p-0 bg-background/50 hover:bg-background border-border/50"
              aria-label="الصفحة الأولى"
            >
              <ChevronsRight className="h-4 w-4" />
            </Button>



            {/* Page numbers */}
            <div className="flex items-center gap-1 mx-2">
              {/* First page if not visible */}
              {startPage > 1 && (
                <>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => onPageChange(1)}
                    className="h-9 w-9 p-0 bg-background/50 hover:bg-background border-border/50"
                  >
                    1
                  </Button>
                  {startPage > 2 && (
                    <div className="flex items-center justify-center w-9 h-9">
                      <MoreHorizontal className="h-4 w-4 text-muted-foreground" />
                    </div>
                  )}
                </>
              )}

              {/* Visible page numbers */}
              {Array.from({ length: endPage - startPage + 1 }, (_, i) => {
                const page = startPage + i;
                const isCurrentPage = page === currentPage;
                
                return (
                  <Button
                    key={page}
                    variant={isCurrentPage ? "default" : "outline"}
                    size="sm"
                    onClick={() => onPageChange(page)}
                    className={cn(
                      "h-9 w-9 p-0",
                      isCurrentPage 
                        ? "bg-primary text-primary-foreground shadow-md" 
                        : "bg-background/50 hover:bg-background border-border/50"
                    )}
                    aria-label={`الصفحة ${page}`}
                    aria-current={isCurrentPage ? "page" : undefined}
                  >
                    {page}
                  </Button>
                );
              })}

              {/* Last page if not visible */}
              {endPage < totalPages && (
                <>
                  {endPage < totalPages - 1 && (
                    <div className="flex items-center justify-center w-9 h-9">
                      <MoreHorizontal className="h-4 w-4 text-muted-foreground" />
                    </div>
                  )}
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => onPageChange(totalPages)}
                    className="h-9 w-9 p-0 bg-background/50 hover:bg-background border-border/50"
                  >
                    {totalPages}
                  </Button>
                </>
              )}
            </div>



            {/* Last page */}
            <Button
              variant="outline"
              size="sm"
              onClick={() => onPageChange(totalPages)}
              disabled={currentPage === totalPages}
              className="h-9 w-9 p-0 bg-background/50 hover:bg-background border-border/50"
              aria-label="الصفحة الأخيرة"
            >
              <ChevronsLeft className="h-4 w-4" />
            </Button>
          </div>

          {/* Go to page input */}
          {showGoToPage && (
            <div className="flex items-center gap-2 justify-center lg:justify-end">
              <span className="text-sm text-muted-foreground">الذهاب إلى الصفحة</span>
              <Input
                type="number"
                min="1"
                max={totalPages}
                value={goToPage}
                onChange={(e) => setGoToPage(e.target.value)}
                onKeyDown={handleKeyDown}
                className="w-16 h-8 text-center"
                placeholder={currentPage.toString()}
              />
              <Button
                variant="outline"
                size="sm"
                onClick={handleGoToPage}
                disabled={!goToPage || parseInt(goToPage) < 1 || parseInt(goToPage) > totalPages}
                className="h-8 px-3"
              >
                اذهب
              </Button>
            </div>
          )}
        </div>

        {/* Page info */}
        <div className="text-center text-xs text-muted-foreground">
          الصفحة {currentPage} من {totalPages}
        </div>
      </div>
    </Card>
  );
}

export default CustomPagination;
