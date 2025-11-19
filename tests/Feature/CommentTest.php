<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\TestCaseHelper;

class CommentTest extends TestCase
{
    use RefreshDatabase, TestCaseHelper;

    /** @test */
    public function user_can_add_comment_to_their_ticket()
    {
        $user = $this->createUser();
        $ticket = $this->createTicket(['user_id' => $user->id]);
        $headers = $this->getAuthHeaders($user);

        $response = $this->postJson("/api/tickets/{$ticket->id}/comments", [
            'comment_text' => 'This is a test comment'
        ], $headers);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'comment_text', 'user_id', 'ticket_id'])
            ->assertJson([
                'comment_text' => 'This is a test comment',
                'user_id' => $user->id,
                'ticket_id' => $ticket->id
            ]);

        $this->assertDatabaseHas('comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'comment_text' => 'This is a test comment'
        ]);
    }

    /** @test */
    public function user_cannot_add_comment_to_other_users_ticket()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $ticket = $this->createTicket(['user_id' => $user1->id]);
        $headers = $this->getAuthHeaders($user2);

        $response = $this->postJson("/api/tickets/{$ticket->id}/comments", [
            'comment_text' => 'This should fail'
        ], $headers);

        $response->assertStatus(403);
    }

    /** @test */
    public function agent_can_add_comment_to_assigned_ticket()
    {
        $agent = $this->createAgent();
        $ticket = $this->createTicket(['assigned_to' => $agent->id]);
        $headers = $this->getAuthHeaders($agent);

        $response = $this->postJson("/api/tickets/{$ticket->id}/comments", [
            'comment_text' => 'Agent comment'
        ], $headers);

        $response->assertStatus(201);
    }

    /** @test */
    public function cannot_add_comment_to_closed_ticket()
    {
        $user = $this->createUser();
        $ticket = $this->createTicket(['user_id' => $user->id, 'status' => 'closed']);
        $headers = $this->getAuthHeaders($user);

        $response = $this->postJson("/api/tickets/{$ticket->id}/comments", [
            'comment_text' => 'This should fail'
        ], $headers);

        $response->assertStatus(422)
            ->assertJson(['error' => 'Cannot comment on closed tickets']);
    }

    /** @test */
    public function user_can_view_comments_on_their_ticket()
    {
        $user = $this->createUser();
        $ticket = $this->createTicket(['user_id' => $user->id]);
        $comment = $this->createComment(['ticket_id' => $ticket->id, 'user_id' => $user->id]);
        $headers = $this->getAuthHeaders($user);

        $response = $this->getJson("/api/tickets/{$ticket->id}/comments", $headers);

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['comment_text' => $comment->comment_text]);
    }

    /** @test */
    public function user_can_edit_their_own_comment_within_time_limit()
    {
        $user = $this->createUser();
        $comment = $this->createComment([
            'user_id' => $user->id,
            'created_at' => now()->subMinutes(30)
        ]);
        $headers = $this->getAuthHeaders($user);

        $response = $this->putJson("/api/comments/{$comment->id}", [
            'comment_text' => 'Updated comment text'
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['comment_text' => 'Updated comment text']);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'comment_text' => 'Updated comment text'
        ]);
    }

    /** @test */
    public function user_cannot_edit_comment_after_time_limit()
    {
        $user = $this->createUser();
        $comment = $this->createComment([
            'user_id' => $user->id,
            'created_at' => now()->subHours(2)
        ]);
        $headers = $this->getAuthHeaders($user);

        $response = $this->putJson("/api/comments/{$comment->id}", [
            'comment_text' => 'Updated comment text'
        ], $headers);

        $response->assertStatus(422)
            ->assertJson(['error' => 'Can only edit comments within 1 hour']);
    }

    /** @test */
    public function user_cannot_edit_other_users_comments()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $comment = $this->createComment(['user_id' => $user1->id]);
        $headers = $this->getAuthHeaders($user2);

        $response = $this->putJson("/api/comments/{$comment->id}", [
            'comment_text' => 'Updated comment text'
        ], $headers);

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_delete_their_own_comment()
    {
        $user = $this->createUser();
        $comment = $this->createComment(['user_id' => $user->id]);
        $headers = $this->getAuthHeaders($user);

        $response = $this->deleteJson("/api/comments/{$comment->id}", [], $headers);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Comment deleted successfully']);

        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    /** @test */
    public function admin_can_delete_any_comment()
    {
        $admin = $this->createAdmin();
        $comment = $this->createComment();
        $headers = $this->getAuthHeaders($admin);

        $response = $this->deleteJson("/api/comments/{$comment->id}", [], $headers);

        $response->assertStatus(200);
        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }
}