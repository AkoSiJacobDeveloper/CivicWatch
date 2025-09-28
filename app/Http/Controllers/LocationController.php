<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\Sitio;
use App\Models\IssueType;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $barangays = Barangay::with(['sitios' => function($query) {
            $query->where('is_available', true);
        }])->where('is_available', true)->get();
        
        return response()->json($barangays);
    }
    
    public function issueTypes()
    {
        $issueTypes = IssueType::all();
        return response()->json($issueTypes);
    }
}