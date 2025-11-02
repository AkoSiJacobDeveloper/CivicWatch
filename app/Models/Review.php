<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface; 

class Review extends Model
{
    protected $fillable = ['name', 'location', 'review_message', 'is_anonymous', 'created_at', 'rating'];

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
}
