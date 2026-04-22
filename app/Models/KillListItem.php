<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KillListItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'task_id',
        'item_type',
        'title',
        'reason',
        'was_necessary',
        'served_clear_goal',
        'anything_missing',
        'killed_at',
    ];

    protected $casts = [
        'killed_at' => 'datetime',
        'was_necessary' => 'boolean',
        'served_clear_goal' => 'boolean',
        'anything_missing' => 'boolean',
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
