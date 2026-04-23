<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delegation extends Model
{
    use HasFactory;

    public const SCOPES = [
        'inform' => 'Inform only',
        'consult' => 'Consult before deciding',
        'decide' => 'Decide autonomously',
    ];

    public const STATUS_INVITED = 'invited';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'task_id',
        'delegator_id',
        'delegate_user_id',
        'delegate_name_fallback',
        'original_owner_id',
        'goal',
        'decision_scope',
        'deadline',
        'resources',
        'inform_list',
        'no_micromanagement',
        'status',
        'health_score',
        'last_checkin_at',
        'invited_at',
        'accepted_at',
        'declined_at',
        'decline_reason',
        'invite_token',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'last_checkin_at' => 'datetime',
        'invited_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
        'inform_list' => 'array',
        'no_micromanagement' => 'boolean',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function delegator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegator_id');
    }

    public function delegateUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegate_user_id');
    }

    public function originalOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'original_owner_id');
    }
}
