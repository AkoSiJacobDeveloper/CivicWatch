<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = ['announcement_id', 'file_path'];
    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }
}
