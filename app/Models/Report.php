<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    protected $fillable = [
        'tracking_code',
        'title',
        'issue_type',
        'description',
        'image',
        'barangay_name',
        'sitio_name',
        'sender_name',
        'contact_number',
        'remarks',
        'status',
        'priority_level',
        'rejection_reason',
    ];

    public function getStatusAttribute($value) {
        return ucwords(str_replace('_', ' ', $value));
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