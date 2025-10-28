<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AchievementGallery;
use App\Models\Location;

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
        'archived_at'
    ];

    protected $casts = [
        'date_of_achievement' => 'date',
        'document_attachments' => 'array',
        'archived_at' => 'datetime',
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

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function isArchived(): bool
    {
        return !is_null($this->archived_at);
    }

    public function archive()
    {
        $this->update(['archived_at' => now()]);
    }

    public function unarchive()
    {
        $this->update(['archived_at' => null]);
    }

}
