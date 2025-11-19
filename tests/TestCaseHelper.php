<?php

namespace Tests;

use App\Models\User;
use App\Models\Ticket;
use App\Models\Comment;
use App\Models\Attachment;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

trait TestCaseHelper
{
    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    protected function createAdmin(array $attributes = []): User
    {
        return User::factory()->admin()->create($attributes);
    }

    protected function createAgent(array $attributes = []): User
    {
        return User::factory()->agent()->create($attributes);
    }

    protected function createTicket(array $attributes = []): Ticket
    {
        return Ticket::factory()->create($attributes);
    }

    protected function createComment(array $attributes = []): Comment
    {
        return Comment::factory()->create($attributes);
    }

    protected function createAttachment(array $attributes = []): Attachment
    {
        return Attachment::factory()->create($attributes);
    }

    protected function getAuthHeaders(User $user): array
    {
        $token = $user->createToken('test-token')->plainTextToken;
        return ['Authorization' => 'Bearer ' . $token];
    }
}