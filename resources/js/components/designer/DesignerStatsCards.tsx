import React from 'react';
import { ModernStatsCard } from '@/components/ui/modern-stats-card';
import { Package, TrendingUp, DollarSign, ShoppingCart, Star } from 'lucide-react';

interface DesignerStatsCardsProps {
  stats: {
    totalRachmat?: number;
    activeRachmat?: number;
    totalSales?: number;
    totalEarnings?: number;
    unpaidEarnings?: number;
    averageRating?: number;
  };
}

export function DesignerStatsCards({ stats }: DesignerStatsCardsProps) {
  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('ar-DZ', {
      style: 'currency',
      currency: 'DZD',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
      {/* Total Rachmat Card */}
      {stats.totalRachmat !== undefined && (
        <ModernStatsCard
          title="إجمالي الرشمات"
          value={stats.totalRachmat}
          subtitle="Total Rachmat"
          icon={Package}
          colorScheme="blue"
        />
      )}

      {/* Active Rachmat Card */}
      {stats.activeRachmat !== undefined && (
        <ModernStatsCard
          title="الرشمات النشطة"
          value={stats.activeRachmat}
          subtitle="Active Designs"
          icon={TrendingUp}
          colorScheme="green"
        />
      )}

      {/* Total Sales Card */}
      {stats.totalSales !== undefined && (
        <ModernStatsCard
          title="إجمالي المبيعات"
          value={stats.totalSales}
          subtitle="Total Sales"
          icon={ShoppingCart}
          colorScheme="purple"
        />
      )}

      {/* Total Earnings Card */}
      {stats.totalEarnings !== undefined && (
        <ModernStatsCard
          title="إجمالي الأرباح"
          value={formatCurrency(stats.totalEarnings)}
          subtitle="Total Earnings"
          icon={DollarSign}
          colorScheme="yellow"
        />
      )}

      {/* Unpaid Earnings Card */}
      {stats.unpaidEarnings !== undefined && (
        <ModernStatsCard
          title="الأرباح غير المدفوعة"
          value={formatCurrency(stats.unpaidEarnings)}
          subtitle="Unpaid Earnings"
          icon={DollarSign}
          colorScheme="orange"
        />
      )}

      {/* Average Rating Card */}
      {stats.averageRating !== undefined && (
        <ModernStatsCard
          title="متوسط التقييم"
          value={`${stats.averageRating}/5`}
          subtitle="Average Rating"
          icon={Star}
          colorScheme="pink"
        />
      )}
    </div>
  );
}

export default DesignerStatsCards;
