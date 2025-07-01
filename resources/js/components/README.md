# Reusable Components Guide

This guide explains how to use the modern, reusable header and card components across all admin and designer pages.

## Components Overview

### 1. AdminPageHeader
A modern header component specifically designed for admin pages.

### 2. DesignerPageHeader  
A modern header component specifically designed for designer pages.

### 3. ModernStatsCard
A reusable statistics card component with gradient styling and animations.

### 4. AdminStatsCards
Pre-configured stats cards for common admin metrics.

### 5. DesignerStatsCards
Pre-configured stats cards for common designer metrics.

## Usage Examples

### Admin Pages

```tsx
import { AdminPageHeader } from '@/components/admin/AdminPageHeader';
import { AdminStatsCards } from '@/components/admin/AdminStatsCards';
import { ModernStatsCard } from '@/components/ui/modern-stats-card';

// Basic header
<AdminPageHeader 
  title="إدارة الطلبات"
  subtitle="إدارة ومتابعة جميع الطلبات والمعاملات"
/>

// Header with action buttons
<AdminPageHeader 
  title="إدارة المصممين"
  subtitle="مراجعة وإدارة المصممين المسجلين في المنصة"
>
  <Button>إضافة مصمم جديد</Button>
</AdminPageHeader>

// Pre-configured admin stats
<AdminStatsCards stats={{
  totalUsers: 150,
  activeDesigners: 25,
  totalOrders: 500,
  pendingOrders: 12,
  currentMonthRevenue: 50000
}} />

// Custom stats card
<ModernStatsCard
  title="إجمالي المبيعات"
  value={1250}
  subtitle="Total Sales"
  icon={ShoppingCart}
  colorScheme="purple"
/>
```

### Designer Pages

```tsx
import { DesignerPageHeader } from '@/components/designer/DesignerPageHeader';
import { DesignerStatsCards } from '@/components/designer/DesignerStatsCards';

// Basic header
<DesignerPageHeader 
  title="إدارة الرشمات"
  subtitle="إدارة وتتبع جميع تصاميمك ومبيعاتك"
/>

// Header with custom content
<DesignerPageHeader 
  title={`مرحباً، ${designer.store_name}`}
  subtitle="مرحباً بك في لوحة تحكم المصمم"
>
  <div className="space-y-3 text-right">
    <Badge>نشط</Badge>
    <p>ينتهي في: 2024-12-31</p>
  </div>
</DesignerPageHeader>

// Pre-configured designer stats
<DesignerStatsCards stats={{
  totalRachmat: 45,
  activeRachmat: 38,
  totalSales: 120,
  totalEarnings: 25000,
  unpaidEarnings: 5000,
  averageRating: 4.8
}} />
```

## Color Schemes

The `ModernStatsCard` component supports the following color schemes:

- `blue` - Blue gradient (default)
- `emerald` - Emerald/green gradient
- `purple` - Purple gradient  
- `amber` - Amber/yellow gradient
- `green` - Green gradient
- `yellow` - Yellow gradient
- `orange` - Orange gradient
- `pink` - Pink gradient
- `indigo` - Indigo gradient

## Page Layout Structure

All admin and designer pages should follow this structure:

```tsx
<AppLayout>
  <Head title="Page Title" />
  
  <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
    <div className="p-8 space-y-10">
      {/* Header */}
      <AdminPageHeader title="..." subtitle="..." />
      
      {/* Stats Cards */}
      <AdminStatsCards stats={stats} />
      
      {/* Content Cards */}
      <Card className="relative overflow-hidden border-0 bg-gradient-to-br from-card via-card to-muted/30 shadow-xl">
        <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-primary/10"></div>
        <CardHeader className="relative pb-6">
          <CardTitle className="text-2xl font-bold text-foreground">
            Card Title
          </CardTitle>
        </CardHeader>
        <CardContent className="relative">
          {/* Card content */}
        </CardContent>
      </Card>
    </div>
  </div>
</AppLayout>
```

## Benefits

1. **Consistency** - All pages use the same modern design patterns
2. **Maintainability** - Changes to the design can be made in one place
3. **Reusability** - Components can be easily reused across different pages
4. **Theme Support** - Uses CSS variables and theme-aware classes
5. **Performance** - Optimized with proper animations and transitions
6. **Accessibility** - Built with proper semantic HTML and ARIA attributes

## Migration Guide

To migrate existing pages:

1. Import the appropriate header component
2. Replace the existing header with the new component
3. Replace manual stats cards with the pre-configured components
4. Update the page layout structure to match the recommended pattern
5. Apply the modern card styling to content sections
