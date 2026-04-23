<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEvent extends Model
{
    use HasFactory;

    public const COLORS = ['accent', 'navy', 'emerald', 'amber', 'rose', 'graphite'];

    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_FOCUS_BLOCK = 'focus_block';
    public const SOURCE_TASK_DUE = 'task_due';

    protected $fillable = [
        'user_id',
        'team_id',
        'title',
        'description',
        'location',
        'color',
        'all_day',
        'starts_at',
        'ends_at',
        'source',
        'task_id',
    ];

    protected $casts = [
        'all_day' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
