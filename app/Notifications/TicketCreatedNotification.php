<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ticket $ticket)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/tickets/' . $this->ticket->id);

        return (new MailMessage)
            ->subject('New Ticket Created: ' . $this->ticket->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new ticket has been created in the system.')
            ->line('Ticket Details:')
            ->line('- Title: ' . $this->ticket->title)
            ->line('- Priority: ' . ucfirst($this->ticket->priority))
            ->line('- Category: ' . ucfirst($this->ticket->category))
            ->action('View Ticket', $url)
            ->line('Thank you for using our ticketing system!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_title' => $this->ticket->title,
            'message' => 'New ticket created: ' . $this->ticket->title,
        ];
    }
}