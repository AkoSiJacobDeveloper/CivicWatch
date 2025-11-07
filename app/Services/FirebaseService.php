<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $projectId;
    protected $accessToken;

    public function __construct()
    {
        $this->projectId = env('FIREBASE_PROJECT_ID', 'civicwatch-7ca5e');
    }

    public function sendReportToFirestore($reportId, $reportData)
    {
        try {
            // Firestore REST API URL
            $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/reports/{$reportId}";
            
            Log::info("📡 Sending to Firebase URL: {$url}");
            
            // Firestore requires a specific document structure
            $firestoreData = [
                'fields' => [
                    'id' => ['stringValue' => (string)$reportId],
                    'type' => ['stringValue' => $reportData['type']],
                    'severity' => ['stringValue' => $reportData['severity']],
                    'description' => ['stringValue' => $reportData['description']],
                    'location' => ['stringValue' => $reportData['location']],
                    'timestamp' => ['timestampValue' => now()->toISOString()],
                    'handled' => ['booleanValue' => false]
                ]
            ];

            Log::info("📦 Firebase payload prepared");

            // Make the API request
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->patch($url, $firestoreData);

            Log::info("📊 Firebase response status: " . $response->status());

            if ($response->successful()) {
                Log::info("✅ Report {$reportId} sent to Firebase successfully");
                return true;
            } else {
                Log::error('❌ Firebase API Error - Status: ' . $response->status() . ' | Body: ' . $response->body());
                return false;
            }

        } catch (\Exception $e) {
            Log::error('🔥 Firebase Firestore Error: ' . $e->getMessage());
            return false;
        }
    }
}