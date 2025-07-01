<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding for pagination testing...');

        // 1. Base data first
        $this->call([
            PricingPlanSeeder::class,
            AdminUserSeeder::class,
            UserSeeder::class,
        ]);

        // 2. Content data
        $this->call([
            CategorySeeder::class,
            DesignerSeeder::class,
            PrivacyPolicySeeder::class,
            AdminPaymentInfoSeeder::class,
        ]);

        // 3. Large datasets for pagination testing
        $this->call([
            RachmatSeeder::class, // Now creates ~114 rachmat (14 + 100)
         //   FatimaRachmatSeeder::class, // Creates 200 rachmat specifically for Fatima
            PartsSuggestionsSeeder::class,
            OrderSeeder::class, // Now creates 200 orders
            CommentSeeder::class,
            RatingSeeder::class,
            SubscriptionRequestSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('✅ Database seeding completed!');
        $this->command->info('📊 Data Summary:');
        $this->command->info('   - Categories: ' . \App\Models\Category::count());
        $this->command->info('   - Rachmat: ' . \App\Models\Rachma::count() . ' (including 200 for Fatima)');
        $this->command->info('   - Orders: ' . \App\Models\Order::count());
        $this->command->info('   - Users: ' . \App\Models\User::count());
        $this->command->info('   - Designers: ' . \App\Models\Designer::count());
        $this->command->info('   - Parts: ' . \App\Models\Part::count());
        $this->command->newLine();
        $this->command->info('🎯 Ready for pagination testing with monterey.png images!');
    }
}
