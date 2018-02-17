<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoinPrice extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $dates = [
        'sourced_at',
    ];

    public function coin(): BelongsTo
    {
        return $this->belongsTo(Coin::class);
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    /**
     * Scope a query to only include popular users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByLatest($query)
    {
        return $query->orderBy('sourced_at', 'DESC');
    }
}
