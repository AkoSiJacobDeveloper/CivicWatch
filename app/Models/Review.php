<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface; 

class Review extends Model
{
    protected $fillable = ['name', 'location', 'review_message', 'created_at'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('F j, Y');
    }
}
