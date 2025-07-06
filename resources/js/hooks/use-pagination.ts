import { useState, useCallback } from 'react';
import { router } from '@inertiajs/react';

interface UsePaginationOptions {
  preserveScroll?: boolean;
  preserveState?: boolean;
  replace?: boolean;
  onSuccess?: () => void;
  onError?: () => void;
}

interface QueryParams {
  [key: string]: string | number | null;
}

export function usePagination(path: string, options: UsePaginationOptions = {}) {
  const [isLoading, setIsLoading] = useState(false);

  const handlePageChange = useCallback((page: number, additionalParams: QueryParams = {}) => {
    setIsLoading(true);

    // Filter out null, undefined, and empty string values
    const filteredParams = Object.fromEntries(
      Object.entries(additionalParams).filter(([_key, value]) =>
        value !== null && value !== undefined && value !== ''
      )
    );

    const queryParams: QueryParams = {
      ...filteredParams,
      page: page.toString(),
    };

    router.visit(path, {
      method: 'get',
      data: queryParams,
      preserveState: options.preserveState ?? true,
      replace: options.replace ?? true,
      preserveScroll: options.preserveScroll ?? true,
      onSuccess: () => {
        setIsLoading(false);
        options.onSuccess?.();
      },
      onError: () => {
        setIsLoading(false);
        options.onError?.();
      },
    });
  }, [path, options]);



  return {
    isLoading,
    handlePageChange
  };
}