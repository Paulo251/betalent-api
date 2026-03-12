<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gateway extends Model
{
    protected $fillable = ["name", "is_active", "priority"];

    public function transaction()
    {
        return $this->hasMany(Transaction::class);
    }

    protected $casts = [
        "is_active" => "boolean",
    ];
}
