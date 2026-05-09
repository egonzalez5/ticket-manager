<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketMessage>
 */
class TicketMessageFactory extends Factory
{
    protected $model = TicketMessage::class;

    public function definition(): array
    {
        return [
            'ticket_id'   => Ticket::factory(),
            'user_id'     => User::factory(),
            'message'     => fake()->paragraph(fake()->numberBetween(1, 4)),
            'is_internal' => false,
        ];
    }

    public function internal(): static
    {
        return $this->state(['is_internal' => true]);
    }
}
