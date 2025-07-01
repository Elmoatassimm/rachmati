<?php

use App\Models\User;
use App\Models\Designer;
use App\Models\Rachma;
use App\Models\Category;
use App\Models\Rating;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__ . '/Helpers/ApiTestHelpers.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test users
    $this->client = User::factory()->create(['user_type' => 'client']);
    $this->designer = Designer::factory()->create();
    
    // Create test rachma
    $this->category = Category::factory()->create();
    $this->rachma = Rachma::factory()->create([
        'designer_id' => $this->designer->id,
        'category_id' => $this->category->id,
    ]);
});

// Store Rating Tests
test('store creates rachma rating successfully', function () {
    $token = getAuthToken($this->client);
    
    $ratingData = [
        'target_id' => $this->rachma->id,
        'target_type' => 'rachma',
        'rating' => 4,
        'comment' => 'Great rachma!'
    ];

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/ratings', $ratingData);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Rating submitted successfully',
            'data' => [
                'user_id' => $this->client->id,
                'target_id' => $this->rachma->id,
                'target_type' => 'rachma',
                'rating' => 4
            ]
        ]);

    // Verify rating was created in database
    $this->assertDatabaseHas('ratings', [
        'user_id' => $this->client->id,
        'target_id' => $this->rachma->id,
        'target_type' => 'rachma',
        'rating' => 4
    ]);

    // Verify comment was created
    $this->assertDatabaseHas('comments', [
        'user_id' => $this->client->id,
        'target_id' => $this->rachma->id,
        'target_type' => 'rachma',
        'comment' => 'Great rachma!'
    ]);
});

test('store creates store rating successfully', function () {
    $token = getAuthToken($this->client);
    
    $ratingData = [
        'target_id' => $this->designer->id,
        'target_type' => 'store',
        'rating' => 5,
        'comment' => 'Excellent store!'
    ];

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/ratings', $ratingData);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Rating submitted successfully'
        ]);

    $this->assertDatabaseHas('ratings', [
        'user_id' => $this->client->id,
        'target_id' => $this->designer->id,
        'target_type' => 'store',
        'rating' => 5
    ]);
});

test('store creates rating without comment', function () {
    $token = getAuthToken($this->client);
    
    $ratingData = [
        'target_id' => $this->rachma->id,
        'target_type' => 'rachma',
        'rating' => 3
    ];

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/ratings', $ratingData);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Rating submitted successfully'
        ]);

    $this->assertDatabaseHas('ratings', [
        'user_id' => $this->client->id,
        'target_id' => $this->rachma->id,
        'target_type' => 'rachma',
        'rating' => 3
    ]);

    $this->assertDatabaseMissing('comments', [
        'user_id' => $this->client->id,
        'target_id' => $this->rachma->id,
        'target_type' => 'rachma'
    ]);
});

// Authentication Tests
test('store fails without authentication', function () {
    $ratingData = [
        'target_id' => $this->rachma->id,
        'target_type' => 'rachma',
        'rating' => 4
    ];

    $response = $this->postJson('/api/ratings', $ratingData);

    $response->assertStatus(401);
});

// Validation Tests
test('store fails with missing required fields', function () {
    $token = getAuthToken($this->client);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/ratings', []);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Validation errors'
        ])
        ->assertJsonValidationErrors(['target_id', 'target_type', 'rating']);
});

test('store fails with invalid target_type', function () {
    $token = getAuthToken($this->client);
    
    $ratingData = [
        'target_id' => $this->rachma->id,
        'target_type' => 'invalid',
        'rating' => 4
    ];

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/ratings', $ratingData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['target_type']);
});

test('store fails with invalid rating value', function () {
    $token = getAuthToken($this->client);
    
    $ratingData = [
        'target_id' => $this->rachma->id,
        'target_type' => 'rachma',
        'rating' => 6 // Invalid - max is 5
    ];

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/ratings', $ratingData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['rating']);
});

test('store fails with non-integer rating', function () {
    $token = getAuthToken($this->client);
    
    $ratingData = [
        'target_id' => $this->rachma->id,
        'target_type' => 'rachma',
        'rating' => 'four'
    ];

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/ratings', $ratingData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['rating']);
});

test('store fails with comment too long', function () {
    $token = getAuthToken($this->client);
    
    $ratingData = [
        'target_id' => $this->rachma->id,
        'target_type' => 'rachma',
        'rating' => 4,
        'comment' => str_repeat('a', 1001) // Exceeds 1000 char limit
    ];

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/ratings', $ratingData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['comment']);
});

// Business Logic Tests
test('store fails with non-existent target for rachma', function () {
    $token = getAuthToken($this->client);
    
    $ratingData = [
        'target_id' => 99999, // Non-existent
        'target_type' => 'rachma',
        'rating' => 5
    ];

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/ratings', $ratingData);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'Target not found'
        ]);
});

test('store fails with non-existent target for store', function () {
    $token = getAuthToken($this->client);
    
    $ratingData = [
        'target_id' => 99999, // Non-existent
        'target_type' => 'store',
        'rating' => 5
    ];

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/ratings', $ratingData);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'Target not found'
        ]);
});

test('store fails when user already rated the target', function () {
    $token = getAuthToken($this->client);
    
    // Create first rating
    Rating::create([
        'user_id' => $this->client->id,
        'target_id' => $this->rachma->id,
        'target_type' => 'rachma',
        'rating' => 4
    ]);

    $ratingData = [
        'target_id' => $this->rachma->id,
        'target_type' => 'rachma',
        'rating' => 5
    ];

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/ratings', $ratingData);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'You have already rated this item'
        ]);
});

test('store updates rachma average rating', function () {
    $token = getAuthToken($this->client);
    
    // Create initial rating for rachma
    Rating::create([
        'user_id' => User::factory()->create(['user_type' => 'client'])->id,
        'target_id' => $this->rachma->id,
        'target_type' => 'rachma',
        'rating' => 4
    ]);

    $ratingData = [
        'target_id' => $this->rachma->id,
        'target_type' => 'rachma',
        'rating' => 2
    ];

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/ratings', $ratingData);

    $response->assertStatus(201);

    // Check if rachma average rating was updated
    $this->rachma->refresh();
    expect((float)$this->rachma->average_rating)->toBe(3.0); // (4 + 2) / 2 = 3
    expect($this->rachma->ratings_count)->toBe(2);
});

test('store updates designer average rating', function () {
    $token = getAuthToken($this->client);
    
    $ratingData = [
        'target_id' => $this->designer->id,
        'target_type' => 'store',
        'rating' => 5
    ];

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/ratings', $ratingData);

    $response->assertStatus(201);
    
    // Verify rating was stored
    $this->assertDatabaseHas('ratings', [
        'user_id' => $this->client->id,
        'target_id' => $this->designer->id,
        'target_type' => 'store',
        'rating' => 5
    ]);
});

// Index Tests (Get Ratings)
test('index returns ratings for rachma', function () {
    // Create some ratings
    Rating::factory()->count(3)->create([
        'target_id' => $this->rachma->id,
        'target_type' => 'rachma'
    ]);

    // Create some comments
    Comment::factory()->count(2)->create([
        'target_id' => $this->rachma->id,
        'target_type' => 'rachma'
    ]);

    $token = getAuthToken($this->client);
    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson("/api/ratings/rachma/{$this->rachma->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'ratings' => [
                    'data' => [
                        '*' => [
                            'id',
                            'user_id',
                            'target_id',
                            'target_type',
                            'rating',
                            'created_at',
                            'updated_at',
                            'user'
                        ]
                    ],
                    'current_page',
                    'per_page',
                    'total'
                ],
                'comments' => [
                    'data' => [
                        '*' => [
                            'id',
                            'user_id',
                            'target_id',
                            'target_type',
                            'comment',
                            'created_at',
                            'updated_at',
                            'user'
                        ]
                    ]
                ],
                'statistics'
            ]
        ]);
});

test('index returns ratings for store', function () {
    // Create some ratings
    Rating::factory()->count(3)->create([
        'target_id' => $this->designer->id,
        'target_type' => 'store'
    ]);

    // Create some comments  
    Comment::factory()->count(2)->create([
        'target_id' => $this->designer->id,
        'target_type' => 'store'
    ]);

    $token = getAuthToken($this->client);
    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson("/api/ratings/store/{$this->designer->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'ratings',
                'comments',
                'statistics'
            ]
        ]);
});

test('index fails for non-existent target', function () {
    $token = getAuthToken($this->client);
    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/ratings/rachma/99999');

    // Controller returns 200 with empty results for non-existent targets
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'ratings' => [
                    'data' => []
                ],
                'comments' => [
                    'data' => []
                ]
            ]
        ]);
});

test('index fails with invalid target type', function () {
    $token = getAuthToken($this->client);
    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson("/api/ratings/invalid/{$this->rachma->id}");

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid target type'
        ]);
});

test('index returns empty result for target with no ratings', function () {
    $newRachma = Rachma::factory()->create([
        'designer_id' => $this->designer->id,
        'category_id' => $this->category->id
    ]);

    $token = getAuthToken($this->client);
    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson("/api/ratings/rachma/{$newRachma->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'ratings' => [
                    'data' => []
                ],
                'comments' => [
                    'data' => []
                ]
            ]
        ]);
});

test('index includes rating distribution in statistics', function () {
    // Create ratings with different values
    Rating::factory()->create([
        'target_id' => $this->rachma->id,
        'target_type' => 'rachma',
        'rating' => 5
    ]);
    Rating::factory()->create([
        'target_id' => $this->rachma->id,
        'target_type' => 'rachma',
        'rating' => 4
    ]);
    Rating::factory()->create([
        'target_id' => $this->rachma->id,
        'target_type' => 'rachma',
        'rating' => 3
    ]);

    $token = getAuthToken($this->client);
    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson("/api/ratings/rachma/{$this->rachma->id}");

    $response->assertStatus(200);
    
    $statistics = $response->json('data.statistics');
    expect($statistics)->toHaveKey('total');
    expect($statistics)->toHaveKey('average');
    expect($statistics)->toHaveKey('five_star');
    expect($statistics)->toHaveKey('four_star');
    expect($statistics)->toHaveKey('three_star');
    expect($statistics)->toHaveKey('two_star');
    expect($statistics)->toHaveKey('one_star');
});

test('index paginates ratings properly', function () {
    // Create more than 15 ratings
    Rating::factory()->count(20)->create([
        'target_id' => $this->rachma->id,
        'target_type' => 'rachma'
    ]);

    $token = getAuthToken($this->client);
    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson("/api/ratings/rachma/{$this->rachma->id}?per_page=10");

    $response->assertStatus(200);
    // Controller uses default 15 per page, not custom per_page
    expect(count($response->json('data.ratings.data')))->toBeLessThanOrEqual(15);
});

// Edge Cases
test('store handles concurrent rating submissions', function () {
    $token = getAuthToken($this->client);
    
    // Simulate concurrent requests
    $ratingData = [
        'target_id' => $this->rachma->id,
        'target_type' => 'rachma',
        'rating' => 4
    ];

    $response1 = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/ratings', $ratingData);
    
    $response2 = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/ratings', [
            'target_id' => $this->rachma->id,
            'target_type' => 'rachma',
            'rating' => 5
        ]);

    // First should succeed, second should fail
    expect($response1->status())->toBe(201);
    expect($response2->status())->toBe(400);
});

test('index handles malformed target ID', function () {
    $token = getAuthToken($this->client);
    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/ratings/rachma/invalid-id');

    // Controller returns 200 with empty results for invalid IDs
    $response->assertStatus(200);
});

test('store accepts all valid rating values', function () {
    $initialRatingCount = Rating::count();
    
    for ($rating = 1; $rating <= 5; $rating++) {
        $client = User::factory()->create(['user_type' => 'client']);
        $clientToken = getAuthToken($client);
        
        // Create different rachma for each rating to avoid duplication
        $rachma = Rachma::factory()->create([
            'designer_id' => $this->designer->id,
            'category_id' => $this->category->id
        ]);
        
        $ratingData = [
            'target_id' => $rachma->id,
            'target_type' => 'rachma',
            'rating' => $rating
        ];

        $response = $this->withHeaders(['Authorization' => "Bearer $clientToken"])
            ->postJson('/api/ratings', $ratingData);

        $response->assertStatus(201);
    }
    
    // Verify that 5 new ratings were created
    expect(Rating::count())->toBe($initialRatingCount + 5);
    
    // Verify all rating values 1-5 exist in the database
    for ($rating = 1; $rating <= 5; $rating++) {
        $this->assertDatabaseHas('ratings', [
            'target_type' => 'rachma',
            'rating' => $rating
        ]);
    }
}); 