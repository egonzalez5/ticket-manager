<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Priority;
use App\Models\Sla;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'title'       => rtrim(fake()->sentence(fake()->numberBetween(4, 8)), '.'),
            'description' => fake()->paragraphs(fake()->numberBetween(1, 3), true),
            'user_id'     => User::factory(),
            'assigned_to' => null,
            'category_id' => Category::inRandomOrder()->value('id'),
            'priority_id' => Priority::inRandomOrder()->value('id'),
            'status_id'   => TicketStatus::where('slug', 'open')->value('id'),
            'sla_id'      => fake()->boolean(60) ? Sla::inRandomOrder()->value('id') : null,
            'due_date'    => null,
        ];
    }

    public function open(): static
    {
        return $this->state([
            'status_id' => TicketStatus::where('slug', 'open')->value('id'),
            'due_date'  => null,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state([
            'status_id' => TicketStatus::where('slug', 'in_progress')->value('id'),
            'due_date'  => fake()->dateTimeBetween('now', '+7 days'),
        ]);
    }

    public function pending(): static
    {
        return $this->state([
            'status_id' => TicketStatus::where('slug', 'pending')->value('id'),
            'due_date'  => fake()->dateTimeBetween('now', '+14 days'),
        ]);
    }

    public function resolved(): static
    {
        return $this->state([
            'status_id' => TicketStatus::where('slug', 'resolved')->value('id'),
            'due_date'  => fake()->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }

    public function closed(): static
    {
        return $this->state([
            'status_id' => TicketStatus::where('slug', 'closed')->value('id'),
            'due_date'  => fake()->dateTimeBetween('-60 days', '-1 day'),
        ]);
    }
}
