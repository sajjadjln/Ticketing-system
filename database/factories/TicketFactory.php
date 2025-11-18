<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'assigned_to' => null,
            'title' => $this->faker->sentence(6),
            'description' => $this->faker->paragraph(3),
            'category' => $this->faker->randomElement(['technical', 'billing', 'general', 'other']),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
            'status' => 'open',
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'updated_at' => now(),
        ];
    }

    public function assigned()
    {
        return $this->state(function (array $attributes) {
            return [
                'assigned_to' => User::factory()->agent(),
                'status' => 'in_progress',
            ];
        });
    }

    public function resolved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'resolved',
                'assigned_to' => User::factory()->agent(),
            ];
        });
    }

    public function closed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'closed',
                'assigned_to' => User::factory()->agent(),
            ];
        });
    }

    public function highPriority()
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => 'high',
            ];
        });
    }

    public function technical()
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => 'technical',
            ];
        });
    }

    public function billing()
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => 'billing',
            ];
        });
    }
}