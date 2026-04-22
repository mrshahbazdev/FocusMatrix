<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SelfCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'year',
        'week',
        'q1_others_could_do',
        'q2_delegated_late',
        'q3_to_omit_next_week',
        'q4_focused_decisions',
        'focus_score',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
