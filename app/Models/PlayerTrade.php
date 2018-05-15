<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerTrade extends Model
{
    protected $guarded = ['id', 'created_at'];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
