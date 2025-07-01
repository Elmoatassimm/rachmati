import React from 'react';
import { ModernStatsCard } from '@/components/ui/modern-stats-card';
import { Users, UserCheck, Package, Clock, TrendingUp, DollarSign, ShoppingBag } from 'lucide-react';

interface AdminStatsCardsProps {
  stats: {
    totalUsers?: number;
    totalDesigners?: number;
    activeDesigners?: number;
    totalRachmat?: number;
    activeRachmat?: number;
    totalOrders?: number;
    pendingOrders?: number;
    currentMonthOrders?: number;
    lastMonthOrders?: number;
    currentMonthRevenue?: number;
    lastMonthRevenue?: number;
  };
}

export function AdminStatsCards({ stats }: AdminStatsCardsProps) {
  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('ar-DZ').format(amount) + ' دج';
  };

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
      {/* Total Users Card */}
      {stats.totalUsers !== undefined && (
        <ModernStatsCard
          title="إجمالي المستخدمين"
          value={stats.totalUsers}
          subtitle={`Total Users`}
          icon={Users}
          colorScheme="blue"
        />
      )}

      {/* Active Designers Card */}
      {stats.activeDesigners !== undefined && (
        <ModernStatsCard
          title="المصممين النشطين"
          value={stats.activeDesigners}
          subtitle={`Active Designers (${stats.totalDesigners || 0} total)`}
          icon={UserCheck}
          colorScheme="emerald"
        />
      )}

      {/* Active Rachmat Card */}
      {stats.activeRachmat !== undefined && (
        <ModernStatsCard
          title="الرشمات النشطة"
          value={stats.activeRachmat}
          subtitle={`Active Rachmat (${stats.totalRachmat || 0} total)`}
          icon={Package}
          colorScheme="purple"
        />
      )}

      {/* Pending Orders Card */}
      {stats.pendingOrders !== undefined && (
        <ModernStatsCard
          title="الطلبات المعلقة"
          value={stats.pendingOrders}
          subtitle={`Pending Orders (${stats.totalOrders || 0} total)`}
          icon={Clock}
          colorScheme="amber"
        />
      )}
    </div>
  );
}

export default AdminStatsCards;
