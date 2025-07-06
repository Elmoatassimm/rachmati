<?php

namespace Tests\Feature\Middleware;

use App\Models\Designer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Contracts\Auth\Authenticatable;

class EnsureDesignerSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private User|Authenticatable $user;
    private Designer $designer;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and designer
        $this->user = User::factory()->designer()->create();
        $this->designer = Designer::factory()->create([
            'user_id' => $this->user->id,
            'subscription_status' => 'active',
            'subscription_end_date' => Carbon::now()->addMonth(),
        ]);
    }

    /** @test */
    public function it_allows_access_for_active_subscription()
    {
        $response = $this->actingAs($this->user)
            ->get('/designer/rachmat');

        $response->assertStatus(200);
        $this->assertFalse(session()->has('warning'));
    }

    /** @test */
    public function it_shows_warning_for_subscription_expiring_soon()
    {
        // Set subscription to expire in 5 days
        $this->designer->update([
            'subscription_end_date' => Carbon::now()->addDays(5),
        ]);

        $response = $this->actingAs($this->user)
            ->get('/designer/rachmat');

        $response->assertStatus(200);
        $response->assertSessionHas('warning', 'اشتراكك سينتهي خلال 5 أيام');
    }

    /** @test */
    public function it_redirects_expired_subscription_to_renewal_page()
    {
        // Set subscription as expired
        $this->designer->update([
            'subscription_status' => 'expired',
            'subscription_end_date' => Carbon::now()->subDay(),
        ]);

        $response = $this->actingAs($this->user)
            ->get('/designer/rachmat');

        $response->assertRedirect('/designer/subscription-requests/create');
        $response->assertSessionHas('error', 'انتهت صلاحية اشتراكك. يرجى تجديد الاشتراك للمتابعة');
    }

    /** @test */
    public function it_allows_access_during_grace_period()
    {
        // Set subscription as expired but within grace period
        $this->designer->update([
            'subscription_status' => 'expired',
            'subscription_end_date' => Carbon::now()->subDays(2),
        ]);

        $response = $this->actingAs($this->user)
            ->get('/designer/rachmat?grace_period=7');

        $response->assertStatus(200);
        $response->assertSessionHas('warning', 'انتهت صلاحية اشتراكك. يمكنك الوصول لهذه الصفحة لمدة 5 أيام أخرى');
    }

    /** @test */
    public function it_redirects_pending_subscription_to_pending_page()
    {
        $this->designer->update([
            'subscription_status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/designer/rachmat');

        $response->assertRedirect('/designer/subscription/pending');
        $response->assertSessionHas('info', 'اشتراكك قيد المراجعة. يرجى انتظار موافقة الإدارة');
    }

    /** @test */
    public function it_redirects_non_designers_to_dashboard()
    {
        // Create a regular user without designer profile
        $regularUser = User::factory()->create();

        $response = $this->actingAs($regularUser)
            ->get('/designer/rachmat');

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('error', 'هذه الصفحة مخصصة للمصممين فقط');
    }

    /** @test */
    public function it_redirects_unauthenticated_users_to_login()
    {
        $response = $this->get('/designer/rachmat');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function it_updates_status_when_subscription_expires()
    {
        // Set subscription to expire in the past
        $this->designer->update([
            'subscription_status' => 'active',
            'subscription_end_date' => Carbon::now()->subDay(),
        ]);

        // Access any protected route to trigger the middleware
        $this->actingAs($this->user)
            ->get('/designer/rachmat');

        // Refresh the designer model
        $this->designer->refresh();

        $this->assertEquals('expired', $this->designer->subscription_status);
    }

    /** @test */
    public function it_maintains_active_status_with_warning_when_near_expiry()
    {
        // Set subscription to expire in 6 days
        $this->designer->update([
            'subscription_status' => 'active',
            'subscription_end_date' => Carbon::now()->addDays(6),
        ]);

        // Access any protected route to trigger the middleware
        $response = $this->actingAs($this->user)
            ->get('/designer/rachmat');

        // Refresh the designer model
        $this->designer->refresh();

        $this->assertEquals('active', $this->designer->subscription_status);
        $this->assertTrue(session()->has('warning'));
        $this->assertEquals('اشتراكك سينتهي خلال 6 أيام', session('warning'));
    }

    /** @test */
    public function it_respects_custom_redirect_route()
    {
        $this->designer->update([
            'subscription_status' => 'expired',
            'subscription_end_date' => Carbon::now()->subDay(),
        ]);

        $response = $this->actingAs($this->user)
            ->get('/designer/rachmat?redirect=designer.store.index');

        $response->assertRedirect('/designer/store');
    }
} 