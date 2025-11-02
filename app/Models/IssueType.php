<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssueType extends Model
{
    use HasFactory;

    protected $table = 'issue_types';

    protected $fillable = [ 'name', 'active', 'priority_level' ];

    protected $casts = [
        'active' => 'boolean',
        'priority_level' => 'string'
    ];
    // Get the active issue type
    public function scopeActive($query) {
        return $query->where('active', true);
    }

    // Get the reports for this issue type
    public function reports() {
        return $this->hasMany(Report::class, 'issue_type', 'name');
    }
}
