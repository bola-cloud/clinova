<?php

namespace App\Traits;

use App\Models\Visit;
use App\Services\MedicalDictionary;

trait HasMedicalSuggestions
{
    public $complaintSuggestions = [];
    public $diagnosisSuggestions = [];
    public $treatmentSuggestions = [];
    public $investigationSuggestions = [];
    public $familyHistorySuggestions = [];
    public $personalHistorySuggestions = [];

    public function getMedicalSuggestions($field, $value, $category)
    {
        // Robustly extract the last term by splitting at the last newline or comma
        if (preg_match('/([\s\S]*)([\n\r،,])([^\n\r،,]*)$/u', $value, $matches)) {
            $lastTerm = trim($matches[3]);
        } else {
            $lastTerm = trim($value);
        }

        if (mb_strlen($lastTerm) < 2) {
            return [];
        }

        $suggestions = [];

        // 1. Local Search (Doctor's past entries - acts as their personal dictionary for trade names)
        if (auth()->check()) {
            $doctorId = auth()->user()->isDoctor() ? auth()->id() : auth()->user()->doctor_id;
            
            // Map the field name to the database column
            $column = $field;
            if ($field === 'treatment_text') $column = 'treatment_text';
            if ($field === 'history') $column = 'history';

            if (in_array($column, ['complaint', 'diagnosis', 'treatment_text', 'history'])) {
                $pastEntries = \App\Models\Visit::where('doctor_id', $doctorId)
                    ->where($column, 'like', '%' . $lastTerm . '%')
                    ->distinct()
                    ->limit(20)
                    ->pluck($column)
                    ->toArray();

                foreach ($pastEntries as $entry) {
                    if (!$entry) continue;
                    // Split the entry by newlines and commas to isolate individual items
                    $parts = preg_split('/[\n،,]+/', $entry);
                    foreach ($parts as $part) {
                        $part = trim($part);
                        // If this part contains the search term, add it to suggestions
                        if (mb_stripos($part, $lastTerm) !== false && mb_strlen($part) > 2) {
                            $suggestions[] = $part;
                        }
                    }
                }
            }
        }

        // 2. Get Purely from Dynamic Dictionary (NLM/Wikidata/MedlinePlus)
        $apiSuggestions = MedicalDictionary::getSuggestions($category, $lastTerm);
        
        // Merge and unique
        $suggestions = array_merge($suggestions, $apiSuggestions);
        
        return collect($suggestions)
            ->map(fn($item) => trim($item))
            ->filter()
            ->unique(fn($item) => mb_strtolower($item))
            ->values()
            ->take(15)
            ->toArray();
    }

    public function selectSuggestion($field, $value)
    {
        $this->$field = $value;
        $suggestionField = $field . 'Suggestions';
        if (property_exists($this, $suggestionField)) {
            $this->$suggestionField = [];
        }
    }
}
