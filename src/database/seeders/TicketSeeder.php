<?php

namespace Database\Seeders;

use App\Models\Attachment;
use App\Models\Category;
use App\Models\Priority;
use App\Models\Sla;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketRating;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $statusIds   = TicketStatus::pluck('id', 'slug');
        $categoryIds = Category::pluck('id');
        $priorityIds = Priority::pluck('id');
        $slaIds      = Sla::pluck('id');
        $tagIds      = Tag::pluck('id');

        $rateableStatusIds = collect([
            $statusIds['resolved'],
            $statusIds['closed'],
        ]);

        $agents = User::factory(5)->agent()->create();
        $users  = User::factory(15)->user()->create();

        $testAgent = User::where('email', 'agent@test.com')->first();
        $testUser  = User::where('email', 'user@test.com')->first();

        if ($testAgent) $agents->push($testAgent);
        if ($testUser)  $users->push($testUser);

        $allTickets = collect();

        foreach ($users as $user) {
            $agent = $agents->random();

            foreach (['open', 'in_progress', 'pending', 'resolved', 'closed'] as $slug) {
                $isResolved = in_array($slug, ['resolved', 'closed']);

                $ticket = Ticket::factory()->create([
                    'user_id'     => $user->id,
                    'assigned_to' => $slug === 'open' ? null : $agent->id,
                    'status_id'   => $statusIds[$slug],
                    'category_id' => $categoryIds->random(),
                    'priority_id' => $priorityIds->random(),
                    'sla_id'      => fake()->boolean(60) ? $slaIds->random() : null,
                    'due_date'    => $isResolved
                        ? fake()->dateTimeBetween('-60 days', '-1 day')
                        : (fake()->boolean(50) ? fake()->dateTimeBetween('now', '+30 days') : null),
                ]);

                $allTickets->push($ticket);
            }
        }

        foreach ($allTickets as $ticket) {
            // Mensajes públicos del dueño del ticket
            TicketMessage::factory(fake()->numberBetween(1, 3))->create([
                'ticket_id'   => $ticket->id,
                'user_id'     => $ticket->user_id,
                'is_internal' => false,
            ]);

            // Respuestas públicas del agente
            TicketMessage::factory(fake()->numberBetween(1, 2))->create([
                'ticket_id'   => $ticket->id,
                'user_id'     => $agents->random()->id,
                'is_internal' => false,
            ]);

            // Nota interna del agente (50% de probabilidad)
            if (fake()->boolean(50)) {
                TicketMessage::factory()->create([
                    'ticket_id'   => $ticket->id,
                    'user_id'     => $agents->random()->id,
                    'is_internal' => true,
                ]);
            }

            // Adjuntos directos al ticket (0–2)
            $attachmentCount = fake()->numberBetween(0, 2);
            if ($attachmentCount > 0) {
                Attachment::factory($attachmentCount)->create([
                    'ticket_id'  => $ticket->id,
                    'message_id' => null,
                ]);
            }
        }

        // Ratings solo en tickets resueltos/cerrados
        foreach ($allTickets->filter(fn($t) => $rateableStatusIds->contains($t->status_id)) as $ticket) {
            if (fake()->boolean(75)) {
                TicketRating::factory()->create([
                    'ticket_id' => $ticket->id,
                    'user_id'   => $ticket->user_id,
                ]);
            }
        }

        // Tags en ~60% de los tickets
        $tagSample = $allTickets->random((int) ($allTickets->count() * 0.6));
        foreach ($tagSample as $ticket) {
            $ticket->tags()->sync(
                $tagIds->random(fake()->numberBetween(1, min(3, $tagIds->count())))->toArray()
            );
        }
    }
}
