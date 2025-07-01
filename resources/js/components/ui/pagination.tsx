import React from 'react';
import { Button } from '@/components/ui/button';

interface PaginationProps {
  currentPage: number;
  totalPages: number;
  onPageChange: (page: number) => void;
}

export function Pagination({ currentPage, totalPages, onPageChange }: PaginationProps) {
  if (totalPages <= 1) return null;

  const pages = [];
  const maxVisiblePages = 5;
  
  // Calculate start and end pages
  let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
  let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
  
  // Adjust if we're near the end
  if (endPage - startPage < maxVisiblePages - 1) {
    startPage = Math.max(1, endPage - maxVisiblePages + 1);
  }

  // Previous button
  if (currentPage > 1) {
    pages.push(
      <Button
        key="prev"
        variant="outline"
        size="sm"
        onClick={() => onPageChange(currentPage - 1)}
        className="text-sm"
      >
        السابق
      </Button>
    );
  }

  // First page if not visible
  if (startPage > 1) {
    pages.push(
      <Button
        key={1}
        variant="outline"
        size="sm"
        onClick={() => onPageChange(1)}
        className="text-sm"
      >
        1
      </Button>
    );
    
    if (startPage > 2) {
      pages.push(
        <span key="ellipsis1" className="px-2 text-muted-foreground">
          ...
        </span>
      );
    }
  }

  // Page numbers
  for (let i = startPage; i <= endPage; i++) {
    pages.push(
      <Button
        key={i}
        variant={i === currentPage ? "default" : "outline"}
        size="sm"
        onClick={() => onPageChange(i)}
        className="text-sm min-w-[40px]"
      >
        {i}
      </Button>
    );
  }

  // Last page if not visible
  if (endPage < totalPages) {
    if (endPage < totalPages - 1) {
      pages.push(
        <span key="ellipsis2" className="px-2 text-muted-foreground">
          ...
        </span>
      );
    }
    
    pages.push(
      <Button
        key={totalPages}
        variant="outline"
        size="sm"
        onClick={() => onPageChange(totalPages)}
        className="text-sm"
      >
        {totalPages}
      </Button>
    );
  }

  // Next button
  if (currentPage < totalPages) {
    pages.push(
      <Button
        key="next"
        variant="outline"
        size="sm"
        onClick={() => onPageChange(currentPage + 1)}
        className="text-sm"
      >
        التالي
      </Button>
    );
  }

  return (
    <div className="flex items-center justify-center gap-2">
      {pages}
    </div>
  );
} 