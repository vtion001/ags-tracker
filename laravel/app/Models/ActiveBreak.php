<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActiveBreak extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'break_id',
        'user_id',
        'user_name',
        'user_email',
        'department',
        'tl_email',
        'break_type',
        'break_label',
        'allowed_minutes',
        'started_at',
        'expected_end_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'expected_end_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOverbreak(): bool
    {
        return now()->greaterThan($this->expected_end_at);
    }

    public function getElapsedSeconds(): int
    {
        return now()->diffInSeconds($this->started_at);
    }
}
