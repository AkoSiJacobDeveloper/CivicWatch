<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AchievementGallery;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'summary',
        'featured_image',
        'date_of_achievement',
        'location_id',
        'document_attachments',
        'status',
        // 'author_id',
        'category_id',
    ];

    protected $casts = [
        'date_of_achievement' => 'date',
        'document_attachments' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(AchievementGallery::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
