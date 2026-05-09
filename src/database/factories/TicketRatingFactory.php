<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketRating;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketRating>
 */
class TicketRatingFactory extends Factory
{
    protected $model = TicketRating::class;

    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory()->closed(),
            'user_id'   => User::factory(),
            'rating'    => fake()->numberBetween(1, 5),
            'comment'   => fake()->optional(0.7)->sentence(10),
        ];
    }
}
