<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JwtToken extends Model
{
    protected $fillable = [
        'user_id',
        'jti',
        'refresh_token_hash',
        'access_expires_at',
        'refresh_expires_at',
        'revoked_at',
    ];

    protected $casts = [
        'access_expires_at' => 'datetime',
        'refresh_expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }
}
