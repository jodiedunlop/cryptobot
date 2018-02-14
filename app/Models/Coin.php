<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coin extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'total_supply' => 'integer',
        'is_premined' => 'boolean',
        'is_trading' => 'boolean',
    ];

    public function toValueObject()
    {
        return new \App\Models\VO\Coin($this->symbol, [
            'name' => $this->name,
            'full_name' => $this->full_name,
            'image_url' => $this->image_url,
            'info_url' => $this->info_url,
        ]);
    }
}
