<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAssignedNotification extends Notification implements ShouldQueue
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
            ->subject('Ticket Assigned to You: ' . $this->ticket->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have been assigned to a new ticket.')
            ->line('Ticket Details:')
            ->line('- Title: ' . $this->ticket->title)
            ->line('- Priority: ' . ucfirst($this->ticket->priority))
            ->line('- Submitted by: ' . $this->ticket->user->name)
            ->action('View Ticket', $url)
            ->line('Please review and take appropriate action.');
    }
}