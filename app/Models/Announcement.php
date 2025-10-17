<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'category_id',
        'level',
        'is_pinned',
        'content',
        'image',
        'publish_at', // Fixed: was 'published_at' in fillable but 'publish_at' in migration
        'event_date',
        'venue',
        'expiry_date',
        'contact_person',
        'contact_number',
        'instructions',
        'counts',
        'reg_deadline',
        'other_document',
        'purok',
        'specific_area',
    ];

    protected $casts = [
        'publish_at' => 'datetime',
        'event_date' => 'datetime',
        'expiry_date' => 'datetime',
        'reg_deadline' => 'date',
        'is_pinned' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AnnouncementCategory::class, 'category_id');
    }

    public function audiences(): BelongsToMany
    {
        return $this->belongsToMany(Audience::class, 'announcement_audiences');
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'announcement_departments');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function documents()
    {
        return $this->belongsToMany(Document::class, 'announcement_documents');
    }

    public function getPublishDateAttribute() {
        return $this->publish_at ? $this->publish_at->format('Y-m-d') : null;
    }

    public function getPublishTimeAttribute()
    {
        return $this->publish_at ? $this->publish_at->format('H:i') : null;
    }

    public function getEventDateAttribute($value)
    {
        return $value ? $this->asDateTime($value)->format('Y-m-d') : null;
    }

    public function getEventTimeAttribute($value)
    {
        return $value ? $this->asDateTime($value)->format('H:i') : null;
    }
}