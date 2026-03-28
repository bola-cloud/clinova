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
        // Find last separator (comma or newline) to allow multi-select
        $lastComma = mb_strrpos($value, ',');
        $lastNewline = mb_strrpos($value, "\n");
        $lastArabicComma = mb_strrpos($value, '،');
        
        $lastSeparatorPoint = max(
            $lastComma !== false ? $lastComma : -1, 
            $lastNewline !== false ? $lastNewline : -1,
            $lastArabicComma !== false ? $lastArabicComma : -1
        );
        
        // Extract only the current term being typed
        $lastTerm = $lastSeparatorPoint !== -1 ? mb_substr($value, $lastSeparatorPoint + 1) : $value;
        $lastTerm = trim($lastTerm);

        if (mb_strlen($lastTerm) < 2) {
            return [];
        }

        // Get Purely from Dynamic Dictionary (NLM/Wikidata/MedlinePlus)
        return MedicalDictionary::getSuggestions($category, $lastTerm);
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
