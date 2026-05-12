<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class TicketCreatedNotification extends Notification implements ShouldQueue
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
        $this->ticket->loadMissing(['status', 'priority']);

        return (new MailMessage)
            ->subject("Ticket #{$this->ticket->id} created successfully")
            ->greeting("Hello {$notifiable->name},")
            ->line('Your support ticket has been submitted and our team will review it shortly.')
            ->line('')
            ->line("**{$this->ticket->title}**")
            ->line("Status: **{$this->ticket->status?->name}**")
            ->line("Priority: **{$this->ticket->priority?->name}**")
            ->action('View Ticket', route('tickets.show', $this->ticket->id))
            ->salutation('— ' . config('app.name'));
    }
}
