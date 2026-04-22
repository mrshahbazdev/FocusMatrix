<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Jetstream\Team;

class OrgCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'user_id',
        'year',
        'week',
        'decides_what_clear',
        'responsibilities_clear',
        'reports_short',
        'teams_small',
        'notes',
        'health_score',
    ];

    protected $casts = [
        'decides_what_clear' => 'boolean',
        'responsibilities_clear' => 'boolean',
        'reports_short' => 'boolean',
        'teams_small' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
