<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = [
        'announcement_id',
        'file_path',
        'file_name',
        'file_size',
        'mime_type,'
    ];
    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }
}
