<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exchange extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function markets(): HasMany
    {
        return $this->hasMany(Market::class);
    }
}
