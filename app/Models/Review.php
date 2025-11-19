<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use DateTimeInterface; 

class Review extends Model
{
    use HasFactory; // Add this line

    protected $fillable = ['name', 'location', 'review_message', 'is_anonymous', 'created_at', 'rating', 'status'];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'rating' => 'integer'
    ];
    
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('F j, Y');
    }

    public function getDisplayNameAttribute()
    {
        return $this->is_anonymous ? 'Anonymous User' : $this->name;
    }

    public function getDisplayLocationAttribute()
    {
        return $this->location;
    }

    // Scope for approved reviews
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Scope for pending reviews
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Scope for rejected reviews
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}