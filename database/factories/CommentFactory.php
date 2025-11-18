<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'user_id' => User::factory(),
            'comment_text' => $this->faker->paragraph(2),
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'updated_at' => now(),
        ];
    }

    public function fromAgent()
    {
        return $this->state(function (array $attributes) {
            return [
                'user_id' => User::factory()->agent(),
            ];
        });
    }

    public function fromAdmin()
    {
        return $this->state(function (array $attributes) {
            return [
                'user_id' => User::factory()->admin(),
            ];
        });
    }

    public function fromUser()
    {
        return $this->state(function (array $attributes) {
            return [
                'user_id' => User::factory()->user(),
            ];
        });
    }
}