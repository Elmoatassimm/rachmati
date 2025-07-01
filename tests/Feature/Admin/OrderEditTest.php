<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use App\Models\Rachma;
use App\Models\Designer;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderEditTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $client;
    protected $designer;
    protected $rachma;
    protected $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::factory()->create([
            'user_type' => 'admin',
            'email' => 'admin@test.com'
        ]);

        // Create client user
        $this->client = User::factory()->create([
            'user_type' => 'client',
            'email' => 'client@test.com'
        ]);

        // Create designer user and profile
        $designerUser = User::factory()->create([
            'user_type' => 'designer',
            'email' => 'designer@test.com'
        ]);

        $this->designer = Designer::factory()->create([
            'user_id' => $designerUser->id,
            'subscription_status' => 'active'
        ]);

        // Create category and rachma
        $category = Category::factory()->create();
        $this->rachma = Rachma::factory()->create([
            'designer_id' => $this->designer->id,
            'category_id' => $category->id,
            'price' => 1500.00
        ]);

        // Create test order
        $this->order = Order::factory()->create([
            'client_id' => $this->client->id,
            'rachma_id' => $this->rachma->id,
            'amount' => 1500.00,
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function admin_can_access_order_edit_page()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.orders.edit', $this->order));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Admin/Orders/Edit')
                ->has('order')
                ->has('paymentMethods')
                ->has('statuses')
        );
    }

    /** @test */
    public function non_admin_cannot_access_order_edit_page()
    {
        $response = $this->actingAs($this->client)
            ->get(route('admin.orders.edit', $this->order));

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_update_order_status_only()
    {
        $response = $this->actingAs($this->admin)
            ->put(route('admin.orders.update', $this->order), [
                'status' => 'confirmed',
                'admin_notes' => 'Status updated by admin',
            ]);

        $response->assertRedirect(route('admin.orders.show', $this->order));
        $response->assertSessionHas('success');

        $this->order->refresh();
        $this->assertEquals('confirmed', $this->order->status);
        $this->assertEquals('Status updated by admin', $this->order->admin_notes);

        // Original amount and payment method should remain unchanged
        $this->assertEquals(1500.00, $this->order->amount);
    }

    /** @test */
    public function admin_can_update_order_status_with_timestamps()
    {
        $response = $this->actingAs($this->admin)
            ->put(route('admin.orders.update', $this->order), [
                'status' => 'confirmed',
                'admin_notes' => 'Order confirmed by admin',
            ]);

        $response->assertRedirect(route('admin.orders.show', $this->order));

        $this->order->refresh();
        $this->assertEquals('confirmed', $this->order->status);
        $this->assertNotNull($this->order->confirmed_at);
        $this->assertEquals('Order confirmed by admin', $this->order->admin_notes);
    }

    /** @test */
    public function admin_can_reject_order_with_reason()
    {
        $rejectionReason = 'Invalid payment proof';

        $response = $this->actingAs($this->admin)
            ->put(route('admin.orders.update', $this->order), [
                'status' => 'rejected',
                'admin_notes' => 'Order rejected',
                'rejection_reason' => $rejectionReason,
            ]);

        $response->assertRedirect(route('admin.orders.show', $this->order));

        $this->order->refresh();
        $this->assertEquals('rejected', $this->order->status);
        $this->assertEquals($rejectionReason, $this->order->rejection_reason);
        $this->assertNotNull($this->order->rejected_at);
    }

    /** @test */
    public function rejection_reason_is_required_when_rejecting_order()
    {
        $response = $this->actingAs($this->admin)
            ->put(route('admin.orders.update', $this->order), [
                'status' => 'rejected',
                'admin_notes' => 'Order rejected',
                'rejection_reason' => '', // Empty rejection reason
            ]);

        $response->assertSessionHasErrors(['rejection_reason']);
    }

    /** @test */
    public function admin_can_add_admin_notes()
    {
        $newNotes = 'Updated admin notes for this order';

        $response = $this->actingAs($this->admin)
            ->put(route('admin.orders.update', $this->order), [
                'status' => $this->order->status,
                'admin_notes' => $newNotes,
            ]);

        $response->assertRedirect(route('admin.orders.show', $this->order));

        $this->order->refresh();
        $this->assertEquals($newNotes, $this->order->admin_notes);
    }

    /** @test */
    public function status_validation_works_correctly()
    {
        $response = $this->actingAs($this->admin)
            ->put(route('admin.orders.update', $this->order), [
                'status' => 'invalid_status',
                'admin_notes' => 'Test notes',
            ]);

        $response->assertSessionHasErrors(['status']);
    }

    /** @test */
    public function admin_notes_validation_works_correctly()
    {
        $longNotes = str_repeat('a', 1001); // Exceeds 1000 character limit

        $response = $this->actingAs($this->admin)
            ->put(route('admin.orders.update', $this->order), [
                'status' => $this->order->status,
                'admin_notes' => $longNotes,
            ]);

        $response->assertSessionHasErrors(['admin_notes']);
    }

    /** @test */
    public function timestamps_are_updated_correctly_on_status_change()
    {
        // Test confirmed status
        $this->actingAs($this->admin)
            ->put(route('admin.orders.update', $this->order), [
                'status' => 'confirmed',
                'admin_notes' => 'Confirmed',
            ]);

        $this->order->refresh();
        $this->assertNotNull($this->order->confirmed_at);

        // Test completed status
        $this->actingAs($this->admin)
            ->put(route('admin.orders.update', $this->order), [
                'status' => 'completed',
                'admin_notes' => 'Completed',
            ]);

        $this->order->refresh();
        $this->assertNotNull($this->order->file_sent_at);
        $this->assertNotNull($this->order->completed_at);
    }
}
