import React, { useState } from 'react';
import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { 
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { Order } from '@/types';
import {
  Clock,
  CheckCircle,
  XCircle,
  Edit3,
  Save,
  X,
  AlertTriangle
} from 'lucide-react';

interface Props {
  order: Order;
  onUpdate?: () => void;
}

const statusOptions = [
  { value: 'pending', label: 'معلق', description: 'في انتظار المراجعة والمعالجة', icon: Clock },
  { value: 'completed', label: 'مكتمل', description: 'تم إكمال الطلب وتسليم الملف', icon: CheckCircle },
  { value: 'rejected', label: 'مرفوض', description: 'تم رفض الطلب', icon: XCircle },
];

export default function InlineOrderStatusUpdate({ order, onUpdate }: Props) {
  const [isOpen, setIsOpen] = useState(false);
  const [showRejectionReason, setShowRejectionReason] = useState(false);

  const { data, setData, put, processing, errors, reset, clearErrors } = useForm({
    status: order.status,
    admin_notes: order.admin_notes || '',
    rejection_reason: order.rejection_reason || '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    put(route('admin.orders.update', order.id), {
      preserveScroll: true,
      onSuccess: () => {
        setIsOpen(false);
        reset();
        onUpdate?.();
      },
      onError: (errors) => {
        console.error('Status update failed:', errors);
      },
    });
  };

  const handleStatusChange = (newStatus: string) => {
    setData('status', newStatus);
    setShowRejectionReason(newStatus === 'rejected');
    
    // Clear rejection reason if not rejecting
    if (newStatus !== 'rejected') {
      setData('rejection_reason', '');
    }
    
    clearErrors();
  };

  const handleCancel = () => {
    reset();
    setShowRejectionReason(order.status === 'rejected');
    clearErrors();
    setIsOpen(false);
  };

  const getStatusBadge = (status: string) => {
    const variants = {
      pending: 'bg-yellow-100 text-yellow-800 border-yellow-200',
      completed: 'bg-green-100 text-green-800 border-green-200',
      rejected: 'bg-red-100 text-red-800 border-red-200',
    };
    return variants[status as keyof typeof variants] || 'bg-gray-100 text-gray-800 border-gray-200';
  };

  const getStatusIcon = (status: string) => {
    const option = statusOptions.find(opt => opt.value === status);
    if (!option) return <Clock className="w-3 h-3" />;
    const Icon = option.icon;
    return <Icon className="w-3 h-3" />;
  };

  const currentStatus = statusOptions.find(opt => opt.value === order.status);

  return (
    <Dialog open={isOpen} onOpenChange={setIsOpen}>
      <DialogTrigger asChild>
        <div className="group cursor-pointer">
          <Badge 
            className={`${getStatusBadge(order.status)} hover:shadow-md transition-all duration-200 group-hover:scale-105 flex items-center gap-1.5 px-3 py-1.5 border`}
          >
            {getStatusIcon(order.status)}
            <span className="font-medium">{currentStatus?.label || order.status}</span>
            <Edit3 className="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" />
          </Badge>
        </div>
      </DialogTrigger>
      
      <DialogContent className="sm:max-w-[500px] max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2 text-xl">
            <Edit3 className="w-5 h-5 text-primary" />
            تحديث حالة الطلب #{order.id}
          </DialogTitle>
          <DialogDescription className="text-base">
            قم بتحديث حالة الطلب وإضافة ملاحظات إدارية حسب الحاجة
          </DialogDescription>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Current Order Info */}
          <div className="p-4 bg-gradient-to-r from-muted/30 to-muted/10 rounded-lg border border-border/50">
            <div className="grid grid-cols-2 gap-4 text-sm">
              <div>
                <Label className="text-xs font-semibold text-muted-foreground uppercase">العميل</Label>
                <p className="font-medium">{order.client?.name || 'غير محدد'}</p>
              </div>
              <div>
                <Label className="text-xs font-semibold text-muted-foreground uppercase">المبلغ</Label>
                <p className="font-semibold text-emerald-600">
                  {new Intl.NumberFormat('ar-DZ').format(order.amount)} دج
                </p>
              </div>
            </div>
          </div>

          {/* Status Selection */}
          <div className="space-y-3">
            <Label className="text-base font-semibold">حالة الطلب *</Label>
            <Select value={data.status} onValueChange={handleStatusChange}>
              <SelectTrigger className="h-12 text-base">
                <SelectValue placeholder="اختر حالة الطلب" />
              </SelectTrigger>
              <SelectContent>
                {statusOptions.map((option) => {
                  const Icon = option.icon;
                  return (
                    <SelectItem key={option.value} value={option.value} className="py-3">
                      <div className="flex items-center gap-3">
                        <Icon className="w-4 h-4" />
                        <div>
                          <div className="font-medium">{option.label}</div>
                          <div className="text-xs text-muted-foreground">{option.description}</div>
                        </div>
                      </div>
                    </SelectItem>
                  );
                })}
              </SelectContent>
            </Select>
            {errors.status && (
              <p className="text-sm text-red-600">{errors.status}</p>
            )}
          </div>

          {/* Rejection Reason */}
          {showRejectionReason && (
            <div className="space-y-3 p-4 bg-gradient-to-r from-red-50 to-red-100 rounded-lg border border-red-200">
              <div className="flex items-center gap-2">
                <AlertTriangle className="w-4 h-4 text-red-600" />
                <Label className="text-base font-semibold text-red-800">سبب الرفض *</Label>
              </div>
              <Textarea
                value={data.rejection_reason}
                onChange={(e) => setData('rejection_reason', e.target.value)}
                placeholder="أدخل سبب رفض الطلب..."
                className="min-h-[80px] border-red-300 focus:border-red-500 focus:ring-red-500"
              />
              {errors.rejection_reason && (
                <p className="text-sm text-red-600">{errors.rejection_reason}</p>
              )}
            </div>
          )}

          {/* Admin Notes */}
          <div className="space-y-3">
            <Label className="text-base font-semibold">ملاحظات الإدارة</Label>
            <Textarea
              value={data.admin_notes}
              onChange={(e) => setData('admin_notes', e.target.value)}
              placeholder="أضف ملاحظات إدارية (اختياري)..."
              className="min-h-[100px]"
            />
            {errors.admin_notes && (
              <p className="text-sm text-red-600">{errors.admin_notes}</p>
            )}
            <p className="text-xs text-muted-foreground">
              هذه الملاحظات للاستخدام الداخلي ولن تظهر للعميل
            </p>
          </div>

          <DialogFooter className="gap-2">
            <Button
              type="button"
              variant="outline"
              onClick={handleCancel}
              disabled={processing}
              className="px-6"
            >
              <X className="w-4 h-4 ml-2" />
              إلغاء
            </Button>
            <Button
              type="submit"
              disabled={processing}
              className="px-6 bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70"
            >
              {processing ? (
                <div className="flex items-center gap-2">
                  <div className="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin" />
                  جاري التحديث...
                </div>
              ) : (
                <div className="flex items-center gap-2">
                  <Save className="w-4 h-4" />
                  تحديث الحالة
                </div>
              )}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}
