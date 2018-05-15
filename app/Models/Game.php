<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $started_by_player_id
 * @property double $start_balance
 * @property \Carbon\Carbon $finishes_at
 */
class Game extends Model
{
    use SoftDeletes;

    protected $guarded = ['id', 'created_at'];

    protected $dates = [
        'starts_at',
        'finishes_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function startedByPlayer(): ?Player
    {
        return $this->started_by_player_id ? Player::find($this->started_by_player_id) : null;
    }

    public function hasStarted(): bool
    {
        return $this->starts_at->lessThanOrEqualTo(new Carbon());
    }

    public function hasFinished(): bool
    {
        return $this->finishes_at->lessThanOrEqualTo(new Carbon());
    }

    public function canAddPlayer(): bool
    {
        return !$this->hasFinished();
    }

    public function canAddTrade(): bool
    {
        return ($this->hasStarted() && !$this->hasFinished());
    }

    public function canAddTransaction(): bool
    {
        return !$this->hasFinished();
    }
}
