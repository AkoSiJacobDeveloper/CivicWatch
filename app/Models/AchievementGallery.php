<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class AchievementGallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'achievement_id',
        'image_path',
        'caption',
        'order_index',
    ];

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }
}
