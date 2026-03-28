<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreakHistory extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'break_history';
    public $timestamps = false;

    protected $fillable = [
        'break_id',
        'user_id',
        'user_name',
        'user_email',
        'department',
        'tl_email',
        'break_type',
        'break_category',
        'break_label',
        'allowed_minutes',
        'started_at',
        'ended_at',
        'duration_minutes',
        'duration_seconds',
        'over_minutes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOverbreak(): bool
    {
        return $this->over_minutes > 0;
    }
}
