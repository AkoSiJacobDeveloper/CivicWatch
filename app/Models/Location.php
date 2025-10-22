<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'purok_number',
        'sitio_name',
    ];

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }
}
