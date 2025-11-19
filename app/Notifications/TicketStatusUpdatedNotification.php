<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ticket $ticket, public string $oldStatus)
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
            ->subject('Ticket Status Updated: ' . $this->ticket->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('The status of your ticket has been updated.')
            ->line('Ticket: ' . $this->ticket->title)
            ->line('Status changed from: ' . ucfirst($this->oldStatus) . ' to: ' . ucfirst($this->ticket->status))
            ->action('View Ticket', $url)
            ->line('Thank you for using our ticketing system!');
    }
}