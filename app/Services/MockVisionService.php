<?php
// app/Services/MockVisionService.php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MockVisionService
{
    public function analyzeImageForEmergency($imagePath)
    {
        $filename = basename($imagePath);
        
        Log::info("🔍 MOCK SERVICE ANALYZING: {$filename}");
        
        // SIMPLE LOGIC: If filename contains any of these, it's emergency
        $emergencyWords = ['fire', 'burning', 'ambulance', 'accident', 'crash', 'demo', 'test', 'emergency'];
        
        $filenameLower = strtolower($filename);
        
        foreach ($emergencyWords as $word) {
            if (str_contains($filenameLower, $word)) {
                Log::info("🚨 EMERGENCY DETECTED: Contains '{$word}'");
                return true;
            }
        }
        
        Log::info("✅ NO EMERGENCY: No keywords found");
        return false;
    }

    public function isServiceAvailable()
    {
        return true;
    }
}