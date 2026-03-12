<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gateway extends Model
{
    protected $fillable = [
        'name',
        'is_active',
        'priority',
    ];

    public functions transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
