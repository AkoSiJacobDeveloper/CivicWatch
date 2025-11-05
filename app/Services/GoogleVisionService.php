<?php
// app/Services/GoogleVisionService.php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GoogleVisionService
{
    protected $isAvailable = false;
    protected $errorMessage = '';
    protected $credentials = [];

    public function __construct()
    {
        $this->initializeService();
    }

    private function initializeService()
    {
        try {
            $keyFilePath = storage_path('app/google-cloud-key.json');
            
            if (!file_exists($keyFilePath)) {
                $this->errorMessage = 'Google Cloud credentials file not found.';
                return;
            }

            $fileContent = file_get_contents($keyFilePath);
            $this->credentials = json_decode($fileContent, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->errorMessage = 'Invalid JSON in credentials file.';
                return;
            }

            $requiredFields = ['type', 'project_id', 'private_key_id', 'private_key', 'client_email'];
            foreach ($requiredFields as $field) {
                if (!isset($this->credentials[$field])) {
                    $this->errorMessage = "Missing required field: {$field}";
                    return;
                }
            }

            $this->isAvailable = true;
            Log::info('Google Vision HTTP service initialized successfully');

        } catch (Exception $e) {
            $this->errorMessage = 'Service initialization failed: ' . $e->getMessage();
            $this->isAvailable = false;
        }
    }

    public function analyzeImageForEmergency($imagePath)
    {
        if (!$this->isAvailable) {
            Log::warning('Google Vision service not available: ' . $this->errorMessage);
            return false;
        }

        try {
            if (!file_exists($imagePath)) {
                Log::error('Image file not found: ' . $imagePath);
                return false;
            }

            // Get access token for authentication
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                Log::error('Failed to get access token');
                return false;
            }

            // Use HTTP API with OAuth2 token
            $imageContent = base64_encode(file_get_contents($imagePath));
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ])
                ->post('https://vision.googleapis.com/v1/images:annotate', [
                    'requests' => [
                        [
                            'image' => ['content' => $imageContent],
                            'features' => [
                                ['type' => 'LABEL_DETECTION', 'maxResults' => 10],
                                ['type' => 'SAFE_SEARCH_DETECTION']
                            ]
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Google Vision API request successful');
                return $this->processApiResponse($data);
            }

            Log::error('Google Vision API request failed: ' . $response->status() . ' - ' . $response->body());
            return false;

        } catch (Exception $e) {
            Log::error('Google Vision analysis failed: ' . $e->getMessage());
            return false;
        }
    }

    private function getAccessToken()
    {
        try {
            // Create JWT token for service account
            $jwt = $this->createJwtToken();
            
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['access_token'] ?? null;
            }

            Log::error('Failed to get access token: ' . $response->body());
            return null;

        } catch (Exception $e) {
            Log::error('Access token generation failed: ' . $e->getMessage());
            return null;
        }
    }

    private function createJwtToken()
    {
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];

        $now = time();
        $payload = [
            'iss' => $this->credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/cloud-vision',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));
        
        $signature = $this->createSignature($headerEncoded . '.' . $payloadEncoded);
        
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signature;
    }

    private function createSignature($data)
    {
        $privateKey = $this->credentials['private_key'];
        $signature = '';
        
        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        
        return $this->base64UrlEncode($signature);
    }

    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function processApiResponse($data)
    {
        $emergencyKeywords = [
            'fire', 'flame', 'smoke', 'burning', 'ambulance', 'police', 
            'fire engine', 'accident', 'crash', 'blood', 'injured', 'emergency'
        ];

        $emergencyScore = 0;

        // Process labels from API response
        if (isset($data['responses'][0]['labelAnnotations'])) {
            foreach ($data['responses'][0]['labelAnnotations'] as $label) {
                $description = strtolower($label['description']);
                $confidence = $label['score'] ?? 0;
                
                foreach ($emergencyKeywords as $keyword) {
                    if (str_contains($description, $keyword)) {
                        if ($confidence > 0.85) {
                            $emergencyScore += 3;
                        } elseif ($confidence > 0.70) {
                            $emergencyScore += 2;
                        } else {
                            $emergencyScore += 1;
                        }
                        Log::info("Emergency detected: {$description} (confidence: {$confidence})");
                    }
                }
            }
        }

        // Process safe search
        if (isset($data['responses'][0]['safeSearchAnnotation'])) {
            $safeSearch = $data['responses'][0]['safeSearchAnnotation'];
            $violence = $this->violenceToScore($safeSearch['violence'] ?? 'UNKNOWN');
            
            if ($violence >= 4) {
                $emergencyScore += 2;
                Log::info("Violence detected in image");
            }
        }

        $isEmergency = $emergencyScore >= 2;
        Log::info("Final emergency result: " . ($isEmergency ? 'EMERGENCY' : 'No emergency') . " (Score: {$emergencyScore})");
        
        return $isEmergency;
    }

    private function violenceToScore($level)
    {
        $levels = [
            'UNKNOWN' => 0,
            'VERY_UNLIKELY' => 1,
            'UNLIKELY' => 2,
            'POSSIBLE' => 3,
            'LIKELY' => 4,
            'VERY_LIKELY' => 5
        ];
        
        return $levels[$level] ?? 0;
    }

    public function isServiceAvailable()
    {
        return $this->isAvailable;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    private function isEmergencySituation($labels, $safeSearch)
{
    $emergencyKeywords = [
        'fire', 'flame', 'smoke', 'burning', 'blaze', 'ambulance', 'police', 
        'fire engine', 'fire truck', 'accident', 'crash', 'collision', 'blood', 
        'injured', 'emergency', 'disaster', 'rescue'
    ];

    $emergencyScore = 0;
    
    if ($labels) {
        foreach ($labels as $label) {
            $description = strtolower($label->getDescription());
            $confidence = $label->getScore();
            
            foreach ($emergencyKeywords as $keyword) {
                if (str_contains($description, $keyword)) {
                    // LOWER THRESHOLDS for testing
                    if ($confidence > 0.70) { // was 0.85
                        $emergencyScore += 3;
                        Log::info("🚨 HIGH emergency match: {$description} ({$confidence})");
                    } elseif ($confidence > 0.50) { // was 0.70
                        $emergencyScore += 2;
                        Log::info("🚨 MEDIUM emergency match: {$description} ({$confidence})");
                    } else {
                        $emergencyScore += 1;
                        Log::info("🚨 LOW emergency match: {$description} ({$confidence})");
                    }
                }
            }
        }
    }

    if ($safeSearch && $safeSearch->getViolence() >= 3) { // was 4
        $emergencyScore += 2;
        Log::info("🚨 Violence detected in image");
    }

    // LOWER THRESHOLD - require only 1 point instead of 2
    $isEmergency = $emergencyScore >= 1;
    Log::info("FINAL EMERGENCY RESULT: " . ($isEmergency ? 'EMERGENCY' : 'No emergency') . " (Score: {$emergencyScore})");
    
    return $isEmergency;
}
}