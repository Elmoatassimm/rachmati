<?php

namespace Database\Factories;

use App\Models\DesignerSocialMedia;
use App\Models\Designer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DesignerSocialMedia>
 */
class DesignerSocialMediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $platforms = ['facebook', 'instagram', 'twitter', 'telegram', 'whatsapp', 'youtube', 'website'];
        $platform = fake()->randomElement($platforms);
        
        $urls = [
            'facebook' => 'https://facebook.com/' . fake()->userName(),
            'instagram' => 'https://instagram.com/' . fake()->userName(),
            'twitter' => 'https://twitter.com/' . fake()->userName(),
            'telegram' => 'https://t.me/' . fake()->userName(),
            'whatsapp' => 'https://wa.me/' . fake()->phoneNumber(),
            'youtube' => 'https://youtube.com/@' . fake()->userName(),
            'website' => 'https://' . fake()->domainName(),
        ];

        return [
            'designer_id' => Designer::factory(),
            'platform' => $platform,
            'url' => $urls[$platform],
            'is_active' => fake()->boolean(80), // 80% chance of being active
        ];
    }

    /**
     * Indicate that the social media link is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the social media link is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set specific platform.
     */
    public function platform(string $platform): static
    {
        $urls = [
            'facebook' => 'https://facebook.com/' . fake()->userName(),
            'instagram' => 'https://instagram.com/' . fake()->userName(),
            'twitter' => 'https://twitter.com/' . fake()->userName(),
            'telegram' => 'https://t.me/' . fake()->userName(),
            'whatsapp' => 'https://wa.me/' . fake()->phoneNumber(),
            'youtube' => 'https://youtube.com/@' . fake()->userName(),
            'website' => 'https://' . fake()->domainName(),
        ];

        return $this->state(fn (array $attributes) => [
            'platform' => $platform,
            'url' => $urls[$platform] ?? 'https://' . fake()->domainName(),
        ]);
    }
} 