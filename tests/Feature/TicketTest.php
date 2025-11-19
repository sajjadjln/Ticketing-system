<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\TestCaseHelper;

class TicketTest extends TestCase
{
    use RefreshDatabase, TestCaseHelper;

    /** @test */
    public function user_can_create_ticket()
    {
        $user = $this->createUser();
        $headers = $this->getAuthHeaders($user);

        $response = $this->postJson('/api/tickets', [
            'title' => 'Test Ticket',
            'description' => 'This is a test ticket description',
            'category' => 'technical',
            'priority' => 'high'
        ], $headers);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id', 'title', 'description', 'category', 'priority', 'status', 'user_id'
            ])
            ->assertJson([
                'title' => 'Test Ticket',
                'description' => 'This is a test ticket description',
                'category' => 'technical',
                'priority' => 'high',
                'status' => 'open',
                'user_id' => $user->id
            ]);

        $this->assertDatabaseHas('tickets', [
            'title' => 'Test Ticket',
            'user_id' => $user->id
        ]);
    }

    /** @test */
    public function user_cannot_create_ticket_with_invalid_data()
    {
        $user = $this->createUser();
        $headers = $this->getAuthHeaders($user);

        $response = $this->postJson('/api/tickets', [
            'title' => '',
            'description' => '',
            'category' => 'invalid',
            'priority' => 'invalid'
        ], $headers);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description', 'category', 'priority']);
    }

    /** @test */
    public function user_can_view_their_own_tickets()
    {
        $user = $this->createUser();
        $ticket = $this->createTicket(['user_id' => $user->id]);
        $headers = $this->getAuthHeaders($user);

        $response = $this->getJson('/api/tickets', $headers);

        $response->assertStatus(200)
            ->assertJsonStructure(['data'])
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $ticket->id]);
    }

    /** @test */
    public function user_cannot_view_other_users_tickets()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $ticket = $this->createTicket(['user_id' => $user1->id]);
        $headers = $this->getAuthHeaders($user2);

        $response = $this->getJson("/api/tickets/{$ticket->id}", $headers);

        $response->assertStatus(403);
    }

    /** @test */
    public function agent_can_view_assigned_tickets()
    {
        $agent = $this->createAgent();
        $ticket = $this->createTicket(['assigned_to' => $agent->id]);
        $headers = $this->getAuthHeaders($agent);

        $response = $this->getJson('/api/tickets', $headers);

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $ticket->id]);
    }

    /** @test */
    public function agent_can_view_unassigned_tickets()
    {
        $agent = $this->createAgent();
        $ticket = $this->createTicket(['assigned_to' => null]);
        $headers = $this->getAuthHeaders($agent);

        $response = $this->getJson('/api/tickets', $headers);

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $ticket->id]);
    }

    /** @test */
    public function admin_can_view_all_tickets()
    {
        $admin = $this->createAdmin();
        $ticket1 = $this->createTicket();
        $ticket2 = $this->createTicket();
        $headers = $this->getAuthHeaders($admin);

        $response = $this->getJson('/api/tickets', $headers);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function user_can_update_their_own_ticket()
    {
        $user = $this->createUser();
        $ticket = $this->createTicket(['user_id' => $user->id]);
        $headers = $this->getAuthHeaders($user);

        $response = $this->putJson("/api/tickets/{$ticket->id}", [
            'title' => 'Updated Title',
            'description' => 'Updated description'
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['title' => 'Updated Title']);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'title' => 'Updated Title'
        ]);
    }

    /** @test */
    public function user_cannot_update_ticket_status()
    {
        $user = $this->createUser();
        $ticket = $this->createTicket(['user_id' => $user->id, 'status' => 'open']);
        $headers = $this->getAuthHeaders($user);

        $response = $this->putJson("/api/tickets/{$ticket->id}", [
            'status' => 'closed'
        ], $headers);

        $response->assertStatus(200);

        // Status should remain unchanged
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'open'
        ]);
    }

    /** @test */
    public function agent_can_update_ticket_status()
    {
        $agent = $this->createAgent();
        $ticket = $this->createTicket(['assigned_to' => $agent->id, 'status' => 'open']);
        $headers = $this->getAuthHeaders($agent);

        $response = $this->putJson("/api/tickets/{$ticket->id}", [
            'status' => 'in_progress'
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['status' => 'in_progress']);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'in_progress'
        ]);
    }

    /** @test */
    public function agent_cannot_make_invalid_status_transition()
    {
        $agent = $this->createAgent();
        $ticket = $this->createTicket(['assigned_to' => $agent->id, 'status' => 'open']);
        $headers = $this->getAuthHeaders($agent);

        $response = $this->putJson("/api/tickets/{$ticket->id}", [
            'status' => 'resolved' // Cannot jump from open to resolved
        ], $headers);

        $response->assertStatus(422)
            ->assertJson(['error' => 'Invalid status transition']);
    }

    /** @test */
    public function user_can_delete_their_own_ticket()
    {
        $user = $this->createUser();
        $ticket = $this->createTicket(['user_id' => $user->id]);
        $headers = $this->getAuthHeaders($user);

        $response = $this->deleteJson("/api/tickets/{$ticket->id}", [], $headers);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Ticket deleted successfully']);

        $this->assertSoftDeleted('tickets', ['id' => $ticket->id]);
    }

    /** @test */
    public function user_cannot_delete_other_users_ticket()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $ticket = $this->createTicket(['user_id' => $user1->id]);
        $headers = $this->getAuthHeaders($user2);

        $response = $this->deleteJson("/api/tickets/{$ticket->id}", [], $headers);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_delete_any_ticket()
    {
        $admin = $this->createAdmin();
        $ticket = $this->createTicket();
        $headers = $this->getAuthHeaders($admin);

        $response = $this->deleteJson("/api/tickets/{$ticket->id}", [], $headers);

        $response->assertStatus(200);
        $this->assertSoftDeleted('tickets', ['id' => $ticket->id]);
    }

    /** @test */
    public function tickets_can_be_filtered_by_status()
    {
        $user = $this->createUser();
        $openTicket = $this->createTicket(['user_id' => $user->id, 'status' => 'open']);
        $closedTicket = $this->createTicket(['user_id' => $user->id, 'status' => 'closed']);
        $headers = $this->getAuthHeaders($user);

        $response = $this->getJson('/api/tickets?status=open', $headers);

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $openTicket->id])
            ->assertJsonMissing(['id' => $closedTicket->id]);
    }

    /** @test */
    public function tickets_can_be_filtered_by_priority()
    {
        $user = $this->createUser();
        $highPriority = $this->createTicket(['user_id' => $user->id, 'priority' => 'high']);
        $lowPriority = $this->createTicket(['user_id' => $user->id, 'priority' => 'low']);
        $headers = $this->getAuthHeaders($user);

        $response = $this->getJson('/api/tickets?priority=high', $headers);

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $highPriority->id])
            ->assertJsonMissing(['id' => $lowPriority->id]);
    }
}