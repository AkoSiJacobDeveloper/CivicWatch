<?php

namespace App\Services;

use App\Models\Report;
use Carbon\Carbon;

class DuplicateDetectionService
{
    private $similarityThreshold = 0.7; // 70% similarity threshold
    private $timeWindowHours = 24; // Default time window

    public function __construct($timeWindowHours = 24, $similarityThreshold = 0.7)
    {
        $this->timeWindowHours = $timeWindowHours;
        $this->similarityThreshold = $similarityThreshold;
    }

    /**
     * Check for duplicate reports
     */
    public function checkForDuplicates($reportData)
    {
        $potentialDuplicates = Report::findPotentialDuplicates(
            $reportData['issue_type'],
            $reportData['barangay_id'],
            $reportData['sitio_id'],
            $this->timeWindowHours
        );

        if ($potentialDuplicates->isEmpty()) {
            return [
                'is_duplicate' => false,
                'duplicates' => [],
                'parent_report' => null
            ];
        }

        $duplicates = [];
        $bestMatch = null;
        $highestSimilarity = 0;

        foreach ($potentialDuplicates as $existingReport) {
            $similarity = $this->calculateSimilarity($reportData, $existingReport);
            
            if ($similarity >= $this->similarityThreshold) {
                $duplicates[] = [
                    'report' => $existingReport,
                    'similarity' => $similarity,
                    'reasons' => $this->getSimilarityReasons($reportData, $existingReport)
                ];

                if ($similarity > $highestSimilarity) {
                    $highestSimilarity = $similarity;
                    $bestMatch = $existingReport;
                }
            }
        }

        return [
            'is_duplicate' => !empty($duplicates),
            'duplicates' => $duplicates,
            'parent_report' => $bestMatch,
            'highest_similarity' => $highestSimilarity
        ];
    }

    /**
     * Calculate similarity between two reports
     */
    private function calculateSimilarity($newReportData, $existingReport)
    {
        $weights = [
            'location' => 0.4,      // Same location is very important
            'issue_type' => 0.3,    // Same issue type is important
            'title' => 0.2,         // Title similarity
            'description' => 0.1    // Description similarity
        ];

        $scores = [];

        // Location similarity (exact match)
        $scores['location'] = ($newReportData['barangay_id'] == $existingReport->barangay_id && 
                              $newReportData['sitio_id'] == $existingReport->sitio_id) ? 1.0 : 0.0;

        // Issue type similarity (exact match)
        $scores['issue_type'] = ($newReportData['issue_type'] == $existingReport->issue_type) ? 1.0 : 0.0;

        // Title similarity (using Levenshtein distance)
        $scores['title'] = $this->calculateTextSimilarity($newReportData['title'], $existingReport->title);

        // Description similarity
        $scores['description'] = $this->calculateTextSimilarity($newReportData['description'], $existingReport->description);

        // Calculate weighted average
        $totalScore = 0;
        foreach ($weights as $key => $weight) {
            $totalScore += $scores[$key] * $weight;
        }

        return round($totalScore, 3);
    }

    /**
     * Calculate text similarity using Levenshtein distance
     */
    private function calculateTextSimilarity($text1, $text2)
    {
        $text1 = strtolower(trim($text1));
        $text2 = strtolower(trim($text2));

        if ($text1 === $text2) {
            return 1.0;
        }

        $maxLength = max(strlen($text1), strlen($text2));
        if ($maxLength === 0) {
            return 1.0;
        }

        $distance = levenshtein($text1, $text2);
        return max(0, 1 - ($distance / $maxLength));
    }

    /**
     * Get reasons why reports are considered similar
     */
    private function getSimilarityReasons($newReportData, $existingReport)
    {
        $reasons = [];

        if ($newReportData['barangay_id'] == $existingReport->barangay_id && 
            $newReportData['sitio_id'] == $existingReport->sitio_id) {
            $reasons[] = 'Same location (Barangay and Sitio)';
        }

        if ($newReportData['issue_type'] == $existingReport->issue_type) {
            $reasons[] = 'Same issue type';
        }

        $titleSimilarity = $this->calculateTextSimilarity($newReportData['title'], $existingReport->title);
        if ($titleSimilarity > 0.7) {
            $reasons[] = sprintf('Similar title (%.1f%% match)', $titleSimilarity * 100);
        }

        $descSimilarity = $this->calculateTextSimilarity($newReportData['description'], $existingReport->description);
        if ($descSimilarity > 0.5) {
            $reasons[] = sprintf('Similar description (%.1f%% match)', $descSimilarity * 100);
        }

        $timeAgo = Carbon::parse($existingReport->created_at)->diffForHumans();
        $reasons[] = "Original report submitted {$timeAgo}";

        return $reasons;
    }

    /**
     * Merge duplicate reports
     */
    public function mergeDuplicates($parentReportId, $duplicateReportIds)
    {
        $parentReport = Report::findOrFail($parentReportId);
        
        foreach ($duplicateReportIds as $duplicateId) {
            $duplicate = Report::findOrFail($duplicateId);
            
            // Mark as duplicate and link to parent
            $duplicate->markAsDuplicate($parentReportId);
            
            // Optionally merge additional information
            if ($duplicate->remarks && !$parentReport->remarks) {
                $parentReport->remarks = $duplicate->remarks;
            }
            
            // Add contact information if not already present
            if ($duplicate->contact_number && $parentReport->contact_number !== $duplicate->contact_number) {
                $additionalContacts = $parentReport->remarks ? $parentReport->remarks . "\n" : "";
                $additionalContacts .= "Additional contact: {$duplicate->contact_number} ({$duplicate->sender_name})";
                $parentReport->remarks = $additionalContacts;
            }
        }
        
        $parentReport->save();
        
        return $parentReport->fresh();
    }
}