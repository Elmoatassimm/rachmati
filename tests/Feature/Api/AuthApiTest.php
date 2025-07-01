<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__ . '/Helpers/ApiTestHelpers.php';

uses(RefreshDatabase::class);

// Auth Register Tests
test('register creates a new client user successfully', function () {
    $userData = [
        'name' => 'أحمد محمد',
        'email' => 'ahmed@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '+213555123456',
        'telegram_chat_id' => '123456789'
    ];

    $response = $this->postJson('/api/auth/register', $userData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                    'user_type'
                ],
                'access_token',
                'token_type',
                'expires_in'
            ]
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'ahmed@test.com',
        'user_type' => 'client',
        'name' => 'أحمد محمد'
    ]);

    expect($response->json('data.user.user_type'))->toBe('client');
});

test('register fails with duplicate email', function () {
    User::factory()->create(['email' => 'test@test.com']);

    $userData = [
        'name' => 'Test User',
        'email' => 'test@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ];

    $response = $this->postJson('/api/auth/register', $userData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('register fails with invalid data', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => '',
        'email' => 'invalid-email',
        'password' => '123',
        'password_confirmation' => '456'
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

test('register fails with missing required fields', function () {
    $response = $this->postJson('/api/auth/register', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

// Auth Login Tests
test('login authenticates client user successfully', function () {
    $user = User::factory()->create([
        'email' => 'client@test.com',
        'password' => bcrypt('password123'),
        'user_type' => 'client'
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'client@test.com',
        'password' => 'password123'
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user',
                'access_token',
                'token_type',
                'expires_in'
            ]
        ]);

    expect($response->json('success'))->toBeTrue();
});

test('login fails with wrong credentials', function () {
    User::factory()->create([
        'email' => 'test@test.com',
        'password' => bcrypt('password123'),
        'user_type' => 'client'
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@test.com',
        'password' => 'wrongpassword'
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'بيانات الدخول غير صحيحة'
        ]);
});

test('login fails for non-client users', function () {
    $admin = User::factory()->create([
        'email' => 'admin@test.com',
        'password' => bcrypt('password123'),
        'user_type' => 'admin'
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'admin@test.com',
        'password' => 'password123'
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'غير مصرح لك بالدخول من هذا المكان'
        ]);
});

test('login fails with invalid data', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'invalid-email',
        'password' => ''
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});

// Auth Me Tests
test('me returns authenticated user data', function () {
    $user = User::factory()->create(['user_type' => 'client']);
    $token = getAuthToken($user);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->getJson('/api/auth/me');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'name',
                'email',
                'user_type'
            ]
        ]);

    expect($response->json('data.id'))->toBe($user->id);
});

test('me fails without authentication', function () {
    $response = $this->getJson('/api/auth/me');

    $response->assertStatus(401);
});

test('me fails with invalid token', function () {
    $response = $this->withHeaders(['Authorization' => 'Bearer invalid-token'])
        ->getJson('/api/auth/me');

    $response->assertStatus(401);
});

// Auth Logout Tests
test('logout successfully logs out user', function () {
    $user = User::factory()->create(['user_type' => 'client']);
    $token = getAuthToken($user);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/auth/logout');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح'
        ]);
});

test('logout fails without authentication', function () {
    $response = $this->postJson('/api/auth/logout');

    $response->assertStatus(401);
});

// Auth Refresh Tests
test('refresh returns new token', function () {
    $user = User::factory()->create(['user_type' => 'client']);
    $token = getAuthToken($user);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/auth/refresh');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user',
                'access_token',
                'token_type',
                'expires_in'
            ]
        ]);
});

test('refresh fails without authentication', function () {
    $response = $this->postJson('/api/auth/refresh');

    $response->assertStatus(401);
});

// Auth Profile Update Tests
test('update profile succeeds with valid data', function () {
    $user = User::factory()->create(['user_type' => 'client']);
    $token = getAuthToken($user);

    $updateData = [
        'name' => 'اسم جديد',
        'phone' => '+213555987654',
        'telegram_chat_id' => '987654321'
    ];

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->putJson('/api/auth/profile', $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'تم تحديث الملف الشخصي بنجاح'
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'اسم جديد',
        'phone' => '+213555987654',
        'telegram_chat_id' => '987654321'
    ]);
});

test('update profile fails with invalid data', function () {
    $user = User::factory()->create(['user_type' => 'client']);
    $token = getAuthToken($user);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->putJson('/api/auth/profile', [
            'name' => str_repeat('a', 256), // Too long
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('update profile fails without authentication', function () {
    $response = $this->putJson('/api/auth/profile', [
        'name' => 'New Name'
    ]);

    $response->assertStatus(401);
});

test('update profile allows partial updates', function () {
    $user = User::factory()->create([
        'user_type' => 'client',
        'name' => 'Old Name',
        'phone' => 'Old Phone'
    ]);
    $token = getAuthToken($user);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
        ->putJson('/api/auth/profile', [
            'name' => 'New Name'
            // phone should remain unchanged
        ]);

    $response->assertStatus(200);
    
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'New Name',
        'phone' => 'Old Phone'
    ]);
}); 