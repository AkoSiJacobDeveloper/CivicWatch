<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pending extends Model
{
    protected $fillable = ['title', 'issue_type', 'description', 'image', 'location', 'sender_name', 'contact_number', 'remarks', 'status'];
}
