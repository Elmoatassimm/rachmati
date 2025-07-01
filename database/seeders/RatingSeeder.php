<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rating;
use App\Models\User;
use App\Models\Rachma;

class RatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = User::where('user_type', 'client')->get();
        $rachmat = Rachma::all();

        // Create ratings for all rachmat - increased for pagination testing
        foreach ($rachmat as $rachma) {
            // Each rachma gets 2-8 ratings instead of 1-3
            $numRatings = rand(2, 8);
            
            for ($i = 0; $i < $numRatings; $i++) {
                $client = $clients->random();
                
                // Check if this user already rated this rachma
                $existingRating = Rating::where([
                    'user_id' => $client->id,
                    'target_id' => $rachma->id,
                    'target_type' => 'rachma'
                ])->first();
                
                if ($existingRating) {
                    continue; // Skip if already rated
                }
                
                // Rating distribution: 60% positive (4-5), 30% average (3), 10% negative (1-2)
                $rand = rand(1, 100);
                if ($rand <= 60) {
                    $rating = rand(4, 5);
                } elseif ($rand <= 90) {
                    $rating = 3;
                } else {
                    $rating = rand(1, 2);
                }

                Rating::create([
                    'user_id' => $client->id,
                    'target_id' => $rachma->id,
                    'target_type' => 'rachma',
                    'rating' => $rating,
                    'created_at' => now()->subDays(rand(0, 60)),
                    'updated_at' => now()->subDays(rand(0, 60)),
                ]);
            }
        }

        $this->command->info('Ratings created successfully with realistic distribution!');
        $this->command->info('Total Ratings: ' . Rating::count());
    }
} 