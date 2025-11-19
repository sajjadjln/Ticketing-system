<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'comment_text',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function canBeEdited(): bool
    {
        return $this->created_at->diffInHours(now()) <= 1;
    }

    public function isAuthor(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function scopeForTicket($query, $ticketId)
    {
        return $query->where('ticket_id', $ticketId);
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeOldestFirst($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    public function getExcerptAttribute(): string
    {
        return strlen($this->comment_text) > 100
            ? substr($this->comment_text, 0, 100) . '...'
            : $this->comment_text;
    }

    public function isFromAgent(): bool
    {
        return $this->user && in_array($this->user->role, ['agent', 'admin']);
    }
}
