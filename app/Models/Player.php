<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Player extends Model
{
    use SoftDeletes;

    protected $guarded = ['id', 'created_at'];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PlayerTransaction::class);
    }

    public function trades(): HasMany
    {
        return $this->hasMany(PlayerTrade::class);
    }

    /**
     * @return mixed
     */
    public function getTransactionBalance()
    {
        return $this->transactions()
            ->sum('amount');
    }

    /**
     * @return mixed
     */
    public function getPortfolioValue()
    {


        return $this->transactions()
            ->sum('amount');
    }
}
