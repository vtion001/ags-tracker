<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'category',
        'priority',
        'description',
        'status',
    ];

    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';

    public const CATEGORY_BUG = 'bug_error';
    public const CATEGORY_FEATURE = 'feature_request';
    public const CATEGORY_SCHEDULE = 'schedule_issue';
    public const CATEGORY_ACCESS = 'access_problem';
    public const CATEGORY_OTHER = 'other';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(SupportTicketComment::class, 'ticket_id')->orderBy('created_at', 'asc');
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function getCategoryLabel(): string
    {
        return match($this->category) {
            self::CATEGORY_BUG => 'Bug/Error',
            self::CATEGORY_FEATURE => 'Feature Request',
            self::CATEGORY_SCHEDULE => 'Schedule Issue',
            self::CATEGORY_ACCESS => 'Access Problem',
            self::CATEGORY_OTHER => 'Other',
            default => ucfirst($this->category),
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_OPEN => 'Open',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_RESOLVED => 'Resolved',
            self::STATUS_CLOSED => 'Closed',
            default => ucfirst($this->status),
        };
    }

    public function scopeForUser($query, User $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }
        if ($user->isTeamLead()) {
            return $query->whereHas('user', function ($q) use ($user) {
                $q->where('tl_email', $user->email);
            });
        }
        return $query->where('user_id', $user->id);
    }
}
