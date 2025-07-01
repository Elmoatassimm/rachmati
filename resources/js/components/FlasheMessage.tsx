import { useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import { toast, Toaster } from 'sonner';

export default function FlashedMessages() {
  const { flash } = usePage<PageProps>().props;

  useEffect(() => {
    if (flash?.success) {
      toast.success(flash.success, {
        duration: 4000,
        style: {
          direction: 'rtl',
          textAlign: 'right',
        },
      });
    }
    
    if (flash?.error) {
      toast.error(flash.error, {
        duration: 5000,
        style: {
          direction: 'rtl', 
          textAlign: 'right',
        },
      });
    }

    if (flash?.info) {
      toast.info(flash.info, {
        duration: 4000,
        style: {
          direction: 'rtl',
          textAlign: 'right',
        },
      });
    }

    if (flash?.warning) {
      toast.warning(flash.warning, {
        duration: 4500,
        style: {
          direction: 'rtl',
          textAlign: 'right',
        },
      });
    }
  }, [flash]);

  return (
    <Toaster 
      position="top-center" 
      richColors 
      toastOptions={{
        style: {
          fontFamily: 'Cairo, system-ui, sans-serif',
          fontSize: '14px',
        },
      }}
    />
  );
}