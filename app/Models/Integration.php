<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Integration extends Model
{
    use HasFactory;

    public const PROVIDER_GOOGLE = 'google';

    protected $fillable = [
        'user_id', 'provider', 'account_email',
        'access_token', 'refresh_token', 'expires_at',
        'scopes', 'meta', 'last_synced_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'scopes' => 'array',
        'meta' => 'array',
    ];

    protected $hidden = [
        'access_token', 'refresh_token',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
