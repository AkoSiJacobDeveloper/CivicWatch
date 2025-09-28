<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sitio extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'barangay_id',
        'is_available'
    ];

    protected $casts = [
        'is_available' => 'boolean'
    ];

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    // Scope for available sitios
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }
}
