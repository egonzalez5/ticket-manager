<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class TicketAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Ticket $ticket) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function shouldSend($notifiable, string $channel): bool
    {
        return !empty($notifiable->email);
    }

    public function toMail($notifiable): MailMessage
    {
        $this->ticket->loadMissing(['status', 'priority', 'user']);

        return (new MailMessage)
            ->subject("Ticket #{$this->ticket->id} assigned to you")
            ->greeting("Hello {$notifiable->name},")
            ->line("A ticket has been assigned to you and requires your attention.")
            ->line('')
            ->line("**{$this->ticket->title}**")
            ->line("Reported by: **{$this->ticket->user?->name}**")
            ->line("Status: **{$this->ticket->status?->name}**")
            ->line("Priority: **{$this->ticket->priority?->name}**")
            ->action('View Ticket', route('tickets.show', $this->ticket->id))
            ->salutation('— ' . config('app.name'));
    }
}
