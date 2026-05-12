<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class NewTicketReplyNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket        $ticket,
        public TicketMessage $message,
    ) {}

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
        $this->ticket->loadMissing(['status']);
        $this->message->loadMissing('user');

        $senderName = $this->message->user?->name ?? 'Someone';
        $preview    = mb_strimwidth(strip_tags($this->message->message), 0, 200, '…');

        return (new MailMessage)
            ->subject("New reply on Ticket #{$this->ticket->id}")
            ->greeting("Hello {$notifiable->name},")
            ->line("**{$senderName}** replied to: **{$this->ticket->title}**")
            ->line('')
            ->line("> {$preview}")
            ->action('View & Reply', route('tickets.show', $this->ticket->id))
            ->salutation('— ' . config('app.name'));
    }
}
