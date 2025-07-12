<?php

use App\Models\User;
use App\Models\PasswordResetCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Auth\ApiPasswordResetNotification;
use Carbon\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
});

// Forgot Password Tests
test('forgot password sends verification code for valid client email', function () {
    $user = User::factory()->create([
        'email' => 'client@test.com',
        'user_type' => 'client',
    ]);

    $response = $this->postJson('/api/forgot-password', [
        'email' => 'client@test.com',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'تم إرسال رمز التحقق إلى بريدك الإلكتروني',
        ]);

    // Check that verification code was created
    $this->assertDatabaseHas('password_reset_codes', [
        'email' => 'client@test.com',
    ]);

    // Check that notification was sent
    Notification::assertSentTo($user, ApiPasswordResetNotification::class);
});

test('forgot password fails for non-existent email', function () {
    $response = $this->postJson('/api/forgot-password', [
        'email' => 'nonexistent@test.com',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'أخطاء في التحقق من البيانات',
        ]);

    // Check that no verification code was created
    $this->assertDatabaseMissing('password_reset_codes', [
        'email' => 'nonexistent@test.com',
    ]);
});

test('forgot password fails for invalid email format', function () {
    $response = $this->postJson('/api/forgot-password', [
        'email' => 'invalid-email',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('forgot password fails for designer users', function () {
    $user = User::factory()->create([
        'email' => 'designer@test.com',
        'user_type' => 'designer',
    ]);

    $response = $this->postJson('/api/forgot-password', [
        'email' => 'designer@test.com',
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'غير مصرح لك بإعادة تعيين كلمة المرور من هذا المكان',
        ]);
});

test('forgot password fails for admin users', function () {
    $user = User::factory()->create([
        'email' => 'admin@test.com',
        'user_type' => 'admin',
    ]);

    $response = $this->postJson('/api/forgot-password', [
        'email' => 'admin@test.com',
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'غير مصرح لك بإعادة تعيين كلمة المرور من هذا المكان',
        ]);
});

test('forgot password replaces existing codes for same email', function () {
    $user = User::factory()->create([
        'email' => 'client@test.com',
        'user_type' => 'client',
    ]);

    // Create first code
    $firstCode = PasswordResetCode::createForEmail('client@test.com');
    
    // Request another code
    $response = $this->postJson('/api/forgot-password', [
        'email' => 'client@test.com',
    ]);

    $response->assertStatus(200);

    // Check that only one code exists for this email
    $codes = PasswordResetCode::where('email', 'client@test.com')->get();
    expect($codes)->toHaveCount(1);
    expect($codes->first()->code)->not->toBe($firstCode->code);
});

// Reset Password Tests
test('reset password works with valid code and data', function () {
    $user = User::factory()->create([
        'email' => 'client@test.com',
        'user_type' => 'client',
        'password' => Hash::make('oldpassword'),
    ]);

    $resetCode = PasswordResetCode::createForEmail('client@test.com');

    $response = $this->postJson('/api/reset-password', [
        'email' => 'client@test.com',
        'code' => $resetCode->code,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'تم تغيير كلمة المرور بنجاح',
        ]);

    // Check that password was updated
    $user->refresh();
    expect(Hash::check('newpassword123', $user->password))->toBeTrue();

    // Check that code was marked as used
    $resetCode->refresh();
    expect($resetCode->used_at)->not->toBeNull();
});

test('reset password fails with invalid verification code', function () {
    $user = User::factory()->create([
        'email' => 'client@test.com',
        'user_type' => 'client',
    ]);

    $response = $this->postJson('/api/reset-password', [
        'email' => 'client@test.com',
        'code' => '999999',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية',
        ]);
});

test('reset password fails with expired verification code', function () {
    $user = User::factory()->create([
        'email' => 'client@test.com',
        'user_type' => 'client',
    ]);

    // Create expired code
    $resetCode = PasswordResetCode::create([
        'email' => 'client@test.com',
        'code' => '123456',
        'expires_at' => Carbon::now()->subMinutes(20), // Expired
    ]);

    $response = $this->postJson('/api/reset-password', [
        'email' => 'client@test.com',
        'code' => '123456',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية',
        ]);
});

test('reset password fails with already used verification code', function () {
    $user = User::factory()->create([
        'email' => 'client@test.com',
        'user_type' => 'client',
    ]);

    $resetCode = PasswordResetCode::createForEmail('client@test.com');
    $resetCode->markAsUsed(); // Mark as used

    $response = $this->postJson('/api/reset-password', [
        'email' => 'client@test.com',
        'code' => $resetCode->code,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية',
        ]);
});

test('reset password fails for non-client users', function () {
    $user = User::factory()->create([
        'email' => 'designer@test.com',
        'user_type' => 'designer',
    ]);

    $resetCode = PasswordResetCode::createForEmail('designer@test.com');

    $response = $this->postJson('/api/reset-password', [
        'email' => 'designer@test.com',
        'code' => $resetCode->code,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'غير مصرح لك بإعادة تعيين كلمة المرور من هذا المكان',
        ]);
});

test('reset password validates password confirmation', function () {
    $user = User::factory()->create([
        'email' => 'client@test.com',
        'user_type' => 'client',
    ]);

    $resetCode = PasswordResetCode::createForEmail('client@test.com');

    $response = $this->postJson('/api/reset-password', [
        'email' => 'client@test.com',
        'code' => $resetCode->code,
        'password' => 'newpassword123',
        'password_confirmation' => 'differentpassword',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('reset password validates minimum password length', function () {
    $user = User::factory()->create([
        'email' => 'client@test.com',
        'user_type' => 'client',
    ]);

    $resetCode = PasswordResetCode::createForEmail('client@test.com');

    $response = $this->postJson('/api/reset-password', [
        'email' => 'client@test.com',
        'code' => $resetCode->code,
        'password' => '123',
        'password_confirmation' => '123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('reset password validates verification code format', function () {
    $user = User::factory()->create([
        'email' => 'client@test.com',
        'user_type' => 'client',
    ]);

    $response = $this->postJson('/api/reset-password', [
        'email' => 'client@test.com',
        'code' => '12345', // Too short
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});

// Rate Limiting Tests
test('forgot password is rate limited', function () {
    $user = User::factory()->create([
        'email' => 'client@test.com',
        'user_type' => 'client',
    ]);

    // Make 5 requests (the limit)
    for ($i = 0; $i < 5; $i++) {
        $response = $this->postJson('/api/forgot-password', [
            'email' => 'client@test.com',
        ]);
        $response->assertStatus(200);
    }

    // 6th request should be rate limited
    $response = $this->postJson('/api/forgot-password', [
        'email' => 'client@test.com',
    ]);

    $response->assertStatus(429); // Too Many Requests
});

test('reset password is rate limited', function () {
    $user = User::factory()->create([
        'email' => 'client@test.com',
        'user_type' => 'client',
    ]);

    // Make 5 requests (the limit)
    for ($i = 0; $i < 5; $i++) {
        $response = $this->postJson('/api/reset-password', [
            'email' => 'client@test.com',
            'code' => '123456',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        // These will fail validation but count towards rate limit
    }

    // 6th request should be rate limited
    $response = $this->postJson('/api/reset-password', [
        'email' => 'client@test.com',
        'code' => '123456',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(429); // Too Many Requests
});
