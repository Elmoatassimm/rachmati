<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SubscriptionRequest;
use App\Models\Designer;
use App\Models\PricingPlan;
use App\Models\User;
use Carbon\Carbon;

class SubscriptionRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $designers = Designer::all();
        $pricingPlans = PricingPlan::where('is_active', true)->get();
        $adminUsers = User::where('user_type', 'admin')->get();

        // Create subscription requests with different statuses
        $requestsData = [
            // Pending requests (30%)
            [
                'status' => 'pending',
                'count' => 6,
                'days_ago' => [1, 3, 5, 7, 10, 15],
            ],
            // Approved requests (50%)
            [
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],
            // Rejected requests (20%)
            [
                'status' => 'rejected',
                'count' => 4,
                'days_ago' => [12, 18, 22, 28],
            ],
            [
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],[
                'status' => 'approved',
                'count' => 10,
                'days_ago' => [20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
            ],
        ];

        foreach ($requestsData as $requestType) {
            for ($i = 0; $i < $requestType['count']; $i++) {
                $designer = $designers->random();
                $pricingPlan = $pricingPlans->random();
                $daysAgo = $requestType['days_ago'][$i] ?? rand(1, 70);
                
                $createdAt = Carbon::now()->subDays($daysAgo);
                $requestedStartDate = $createdAt->copy()->addDays(rand(1, 14));
                
                // Select random payment proof image
                $paymentProofs = [
                    'subscription-requests/payment-proofs/ccp_receipt_1.jpg',
                    'subscription-requests/payment-proofs/ccp_receipt_2.jpg',
                    'subscription-requests/payment-proofs/baridi_mob_1.jpg',
                    'subscription-requests/payment-proofs/baridi_mob_2.jpg',
                    'subscription-requests/payment-proofs/dahabiya_receipt_1.jpg',
                    'subscription-requests/payment-proofs/dahabiya_receipt_2.jpg',
                ];

                $selectedProof = $paymentProofs[array_rand($paymentProofs)];
                $proofSize = rand(150000, 800000); // Random file size between 150KB and 800KB

                $requestData = [
                    'designer_id' => $designer->id,
                    'pricing_plan_id' => $pricingPlan->id,
                    'status' => $requestType['status'],
                    'payment_proof_path' => $selectedProof,
                    'payment_proof_original_name' => 'إثبات_دفع_' . ($i + 1) . '.jpg',
                    'payment_proof_size' => $proofSize,
                    'payment_proof_mime_type' => 'image/jpeg',
                    'subscription_price' => $pricingPlan->price,
                    'requested_start_date' => $requestedStartDate->format('Y-m-d'),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];

                // Add review information for approved/rejected requests
                if ($requestType['status'] !== 'pending') {
                    $reviewedAt = $createdAt->copy()->addDays(rand(1, 5));
                    $requestData['reviewed_by'] = $adminUsers->random()->id;
                    $requestData['reviewed_at'] = $reviewedAt;
                    $requestData['updated_at'] = $reviewedAt;
                    
                    if ($requestType['status'] === 'approved') {
                        $requestData['admin_notes'] = $this->getApprovalNote();
                        
                        // Update designer subscription if approved
                        $designer->update([
                            'subscription_status' => 'active',
                            'subscription_start_date' => $requestedStartDate,
                            'subscription_end_date' => $requestedStartDate->copy()->addMonths($pricingPlan->duration_months),
                            'pricing_plan_id' => $pricingPlan->id,
                            'subscription_price' => $pricingPlan->price,
                        ]);
                    } else {
                        $requestData['admin_notes'] = $this->getRejectionNote();
                    }
                }

                SubscriptionRequest::create($requestData);
            }
        }

        $this->command->info('Subscription requests created successfully!');
        $this->command->info('- Pending: 6 requests');
        $this->command->info('- Approved: 10 requests');
        $this->command->info('- Rejected: 4 requests');
    }

    private function getApprovalNote(): string
    {
        $notes = [
            'تم قبول طلب الاشتراك بعد التحقق من صحة الدفع. مرحباً بك في منصة رشمات!',
            'طلب اشتراك مقبول. تم التحقق من إثبات الدفع بنجاح.',
            'تم الموافقة على الاشتراك. يمكنك الآن البدء في رفع رشماتك.',
            'طلب مقبول. نتمنى لك تجربة ممتعة ومربحة على المنصة.',
            'تم قبول الطلب بعد مراجعة شاملة. أهلاً وسهلاً بك.',
        ];

        return $notes[array_rand($notes)];
    }

    private function getRejectionNote(): string
    {
        $notes = [
            'تم رفض الطلب بسبب عدم وضوح إثبات الدفع. يرجى إعادة الإرسال بصورة أوضح.',
            'مبلغ الدفع غير مطابق لسعر الخطة المختارة. يرجى التحقق والمحاولة مرة أخرى.',
            'إثبات الدفع غير صالح أو منتهي الصلاحية.',
            'معلومات الدفع غير مكتملة. يرجى إرفاق جميع المعلومات المطلوبة.',
            'تم رفض الطلب لعدم مطابقة البيانات. يرجى مراجعة المعلومات المدخلة.',
        ];

        return $notes[array_rand($notes)];
    }
}
