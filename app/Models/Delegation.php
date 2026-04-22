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

    protected $fillable = [
        'task_id',
        'delegator_id',
        'delegate_user_id',
        'delegate_name_fallback',
        'goal',
        'decision_scope',
        'deadline',
        'resources',
        'inform_list',
        'no_micromanagement',
        'status',
        'health_score',
        'last_checkin_at',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'last_checkin_at' => 'datetime',
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
}
