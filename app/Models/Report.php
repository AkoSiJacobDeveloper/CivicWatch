<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'tracking_code',
        'title',
        'issue_type',
        'custom_issue_description',
        'description',
        'image',
        'latitude',          
        'longitude',           
        'gps_accuracy',      
        'barangay_name',
        'sitio_name',
        'sender_name',
        'contact_number',
        'remarks',
        'status',
        'priority_level',
        'rejection_reason',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float', 
        'gps_accuracy' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getSenderAttribute()
    {
        return $this->sender_name;
    }

    public function getStatusAttribute($value) {
        return ucwords(str_replace('_', ' ', $value));
    }

    public function setStatusAttribute($value) {
        $this->attributes['status'] = strtolower(str_replace(' ', '_', $value));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(IssueType::class, 'issue_type_id', 'id');
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function sitio(): BelongsTo
    {
        return $this->belongsTo(Sitio::class);
    }

    public function primaryReport(): BelongsTo
    {
        return $this->belongsTo(Report::class, 'duplicate_of_report_id');
    }

    public function duplicates(): HasMany
    {
        return $this->hasMany(Report::class, 'duplicate_of_report_id');
    }
}