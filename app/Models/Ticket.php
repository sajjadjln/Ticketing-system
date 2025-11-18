<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'assigned_to',
        'title',
        'description',
        'category',
        'priority',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const CATEGORY_TECHNICAL = 'technical';
    const CATEGORY_BILLING = 'billing';
    const CATEGORY_GENERAL = 'general';
    const CATEGORY_OTHER = 'other';

    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';

    const STATUS_OPEN = 'open';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_CLOSED = 'closed';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function latestComment()
    {
        return $this->hasOne(Comment::class)->latest();
    }

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', self::PRIORITY_HIGH);
    }

    public function assignTo(User $user): bool
    {
        if (!$user->canAssignTickets()) {
            return false;
        }

        return $this->update(['assigned_to' => $user->id]);
    }

    public function updateStatus(string $status): bool
    {
        if (!$this->isValidStatusTransition($status)) {
            return false;
        }

        return $this->update(['status' => $status]);
    }

    public function isValidStatusTransition(string $newStatus): bool
    {
        $allowed = [
            'open' => ['in_progress', 'closed'],
            'in_progress' => ['resolved', 'closed'],
            'resolved' => ['closed'],
        ];

        return in_array($newStatus, $allowed[$this->status] ?? []);
    }

    public function isAssigned(): bool
    {
        return !is_null($this->assigned_to);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_OPEN, self::STATUS_IN_PROGRESS]);
    }

    public function getResolutionTime()
    {
        if (!in_array($this->status, [self::STATUS_RESOLVED, self::STATUS_CLOSED])) {
            return null;
        }

        return $this->updated_at->diffInHours($this->created_at);
    }
}
