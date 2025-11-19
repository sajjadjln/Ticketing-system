<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\TestCaseHelper;

class TicketAssignmentTest extends TestCase
{
    use RefreshDatabase, TestCaseHelper;

    /** @test */
    public function admin_can_assign_ticket_to_agent()
    {
        $admin = $this->createAdmin();
        $agent = $this->createAgent();
        $ticket = $this->createTicket(['status' => 'open', 'assigned_to' => null]);
        $headers = $this->getAuthHeaders($admin);

        $response = $this->postJson("/api/tickets/{$ticket->id}/assign", [
            'agent_id' => $agent->id
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Ticket assigned successfully'])
            ->assertJsonFragment(['assigned_to' => $agent->id, 'status' => 'in_progress']);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_to' => $agent->id,
            'status' => 'in_progress'
        ]);
    }


    /** @test */
    public function test_admin_can_auto_assign_ticket()
    {
        $admin = User::factory()->admin()->create();
        $agent = User::factory()->agent()->create();
        
        $ticket = Ticket::factory()->create(['assigned_to' => null]);
        $headers = ['Authorization' => 'Bearer ' . $admin->createToken('test-token')->plainTextToken];

        $response = $this->postJson("/api/tickets/{$ticket->id}/auto-assign", [], $headers);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Ticket auto-assigned successfully']);

        $ticket->refresh();
        
        $assignedUser = User::find($ticket->assigned_to);
        
        $this->assertEquals($agent->id, $ticket->assigned_to);
        $this->assertEquals('agent', $assignedUser->role);
        $this->assertEquals('in_progress', $ticket->status);
    }

    /** @test */
    public function agent_can_assign_ticket_to_another_agent()
    {
        $agent1 = $this->createAgent();
        $agent2 = $this->createAgent();
        $ticket = $this->createTicket(['assigned_to' => $agent1->id]);
        $headers = $this->getAuthHeaders($agent1);

        $response = $this->postJson("/api/tickets/{$ticket->id}/assign", [
            'agent_id' => $agent2->id
        ], $headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_to' => $agent2->id
        ]);
    }

    /** @test */
    public function user_cannot_assign_tickets()
    {
        $user = $this->createUser();
        $agent = $this->createAgent();
        $ticket = $this->createTicket(['user_id' => $user->id]);
        $headers = $this->getAuthHeaders($user);

        $response = $this->postJson("/api/tickets/{$ticket->id}/assign", [
            'agent_id' => $agent->id
        ], $headers);

        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_assign_ticket_to_non_agent()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $ticket = $this->createTicket();
        $headers = $this->getAuthHeaders($admin);

        $response = $this->postJson("/api/tickets/{$ticket->id}/assign", [
            'agent_id' => $user->id
        ], $headers);

        $response->assertStatus(422)
            ->assertJson(['error' => 'Can only assign to agents or admins']);
    }

    /** @test */
    public function admin_can_auto_assign_ticket()
    {
        $admin = User::factory()->admin()->create();
        $agent = User::factory()->agent()->create();

        $ticket = Ticket::factory()->create(['assigned_to' => null]);
        $headers = ['Authorization' => 'Bearer ' . $admin->createToken('test-token')->plainTextToken];

        $response = $this->postJson("/api/tickets/{$ticket->id}/auto-assign", [], $headers);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Ticket auto-assigned successfully']);

        $ticket->refresh();

        $this->assertEquals($agent->id, $ticket->assigned_to);
        $this->assertEquals('in_progress', $ticket->status);
    }

    /** @test */
    public function admin_can_unassign_ticket()
    {
        $admin = $this->createAdmin();
        $agent = $this->createAgent();
        $ticket = $this->createTicket(['assigned_to' => $agent->id, 'status' => 'in_progress']);
        $headers = $this->getAuthHeaders($admin);

        $response = $this->postJson("/api/tickets/{$ticket->id}/unassign", [], $headers);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Ticket unassigned successfully']);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_to' => null,
            'status' => 'open'
        ]);
    }

    /** @test */
    public function test_admin_can_auto_assign_ticket_to_least_busy_agent()
    {
        $admin = User::factory()->admin()->create();
        
        $busyAgent = User::factory()->agent()->create();
        $freeAgent = User::factory()->agent()->create();
        
        Ticket::factory()->count(3)->create([
            'assigned_to' => $busyAgent->id,
            'status' => 'in_progress'
        ]);
        
        $ticket = Ticket::factory()->create(['assigned_to' => null]);
        $headers = ['Authorization' => 'Bearer ' . $admin->createToken('test-token')->plainTextToken];

        $response = $this->postJson("/api/tickets/{$ticket->id}/auto-assign", [], $headers);

        $response->assertStatus(200);

        $ticket->refresh();
        
        $assignedUser = User::find($ticket->assigned_to);
        
        $this->assertEquals($freeAgent->id, $ticket->assigned_to);
        $this->assertEquals('agent', $assignedUser->role);
    }

    /** @test */
    public function test_auto_assign_returns_error_when_no_agents_available()
    {
        $admin = User::factory()->admin()->create();

        $ticket = Ticket::factory()->create(['assigned_to' => null]);
        $headers = ['Authorization' => 'Bearer ' . $admin->createToken('test-token')->plainTextToken];

        $response = $this->postJson("/api/tickets/{$ticket->id}/auto-assign", [], $headers);

        $response->assertStatus(422)
            ->assertJson(['error' => 'No available agents']);
    }
}
