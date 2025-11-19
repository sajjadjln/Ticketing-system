<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ticket $ticket, public Comment $comment)
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
            ->subject('New Comment on Ticket: ' . $this->ticket->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new comment has been added to your ticket.')
            ->line('Comment: "' . $this->comment->comment_text . '"')
            ->line('Added by: ' . $this->comment->user->name)
            ->action('View Ticket', $url)
            ->line('Thank you for using our ticketing system!');
    }
}