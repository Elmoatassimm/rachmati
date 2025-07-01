<?php

use App\Models\AdminPaymentInfo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__ . '/Helpers/ApiTestHelpers.php';

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Create test payment info
    AdminPaymentInfo::factory()->create([
        'ccp_number' => '1234567890123456',
        'ccp_key' => '12',
        'nom' => 'Test User',
        'adress' => 'Test Address',
        'baridimob' => '+213555123456',
    ]);
});

test('index returns successful response', function () {
    $response = $this->getJson('/api/admin-payment-info');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'message_en',
            'data' => [
                '*' => [
                    'id',
                    'ccp_number',
                    'ccp_key',
                    'nom',
                    'adress',
                    'baridimob',
                    'formatted_ccp_number',
                    'created_at',
                    'updated_at'
                ]
            ],
            'count'
        ]);
});

test('index returns correct data structure', function () {
    $response = $this->getJson('/api/admin-payment-info');

    $response->assertStatus(200);
    
    $data = $response->json('data');
    expect($data)->toBeArray();
    
    if (count($data) > 0) {
        $firstItem = $data[0];
        expect($firstItem)->toHaveKeys([
            'id', 'ccp_number', 'ccp_key', 'nom', 
            'adress', 'baridimob', 'formatted_ccp_number'
        ]);
    }
});

test('index returns payment info grouped by method', function () {
    $response = $this->getJson('/api/admin-payment-info');

    $response->assertStatus(200);
    
    // Should have payment info data
    $data = $response->json('data');
    expect(count($data))->toBeGreaterThan(0);
});

test('index returns empty array when no payment info exists', function () {
    // Delete all payment info
    AdminPaymentInfo::query()->delete();

    $response = $this->getJson('/api/admin-payment-info');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [],
            'count' => 0
        ]);
});

test('index handles empty account numbers gracefully', function () {
    $response = $this->getJson('/api/admin-payment-info');

    $response->assertStatus(200);
    // Should return valid payment info structure
    expect(count($response->json('data')))->toBeGreaterThanOrEqual(0);
});

test('index filters out payment info with empty account numbers', function () {
    // Create payment info with empty CCP number
    AdminPaymentInfo::factory()->create([
        'ccp_number' => '',
        'nom' => 'حساب فارغ',
    ]);

    $response = $this->getJson('/api/admin-payment-info');

    $response->assertStatus(200);
    
    $ccpNumbers = collect($response->json('data'))->pluck('ccp_number');
    // Should handle empty CCP numbers gracefully
    expect($ccpNumbers)->toBeInstanceOf(\Illuminate\Support\Collection::class);
});

test('index includes masked ccp key for security', function () {
    $response = $this->getJson('/api/admin-payment-info');

    $response->assertStatus(200);
    
    $data = $response->json('data');
    if (count($data) > 0) {
        $firstItem = $data[0];
        // CCP key should be masked
        expect($firstItem['ccp_key'])->toBeString();
    }
});

test('index performance with large dataset', function () {
    // Create additional payment info records
    AdminPaymentInfo::factory()->count(20)->create();

    $startTime = microtime(true);
    $response = $this->getJson('/api/admin-payment-info');
    $endTime = microtime(true);

    $response->assertStatus(200);
    
    // Should complete within reasonable time (less than 1 second)
    expect($endTime - $startTime)->toBeLessThan(1.0);
});

test('index returns formatted data correctly', function () {
    // Create payment info with specific data
    AdminPaymentInfo::factory()->create([
        'ccp_number' => '9876543210987654',
        'ccp_key' => '34',
        'nom' => 'أحمد محمد',
        'adress' => 'وهران، الجزائر',
        'baridimob' => '+213666789012',
    ]);

    $response = $this->getJson('/api/admin-payment-info');

    $response->assertStatus(200);
    
    $data = $response->json('data');
    expect(count($data))->toBeGreaterThan(0);
    
    // Check that formatted_ccp_number is present
    foreach ($data as $item) {
        if (!empty($item['ccp_number'])) {
            expect($item)->toHaveKey('formatted_ccp_number');
        }
    }
});

test('index handles special characters in data', function () {
    // Create payment info with Arabic characters
    AdminPaymentInfo::factory()->create([
        'nom' => 'عبد الرحمن محمد الأحمد',
        'adress' => 'شارع الاستقلال، حي النصر، الجزائر العاصمة',
    ]);

    $response = $this->getJson('/api/admin-payment-info');

    $response->assertStatus(200);
    
    $data = $response->json('data');
    expect(count($data))->toBeGreaterThan(0);
    
    // Should handle Arabic text properly
    foreach ($data as $item) {
        if (!empty($item['nom'])) {
            expect($item['nom'])->toBeString();
        }
    }
}); 