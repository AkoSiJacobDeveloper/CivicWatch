<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_available',
        'description'
    ];

    protected $casts = [
        'is_available' => 'boolean'
    ];

    public function sitios()
    {
        return $this->hasMany(Sitio::class);
    }

    public function availableSitios()
    {
        return $this->hasMany(Sitio::class)->where('is_available', true);
    }

    // Scope for available barangays
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }
}