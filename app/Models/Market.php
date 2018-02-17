<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Market extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function exchange(): BelongsTo
    {
        return $this->belongsTo(Exchange::class);
    }
}
