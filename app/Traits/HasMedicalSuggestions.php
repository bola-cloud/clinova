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
        if (strlen($value) < 2) {
            return [];
        }

        // Get from Dictionary
        $dictionaryResults = MedicalDictionary::getSuggestions($category, $value);

        // Get from Database (History)
        $dbField = ($field === 'investigation' || $field === 'history') ? 'history' : 
                   (($field === 'treatmentText' || $field === 'treatment_text') ? 'treatment_text' : $field);
        
        $dbResults = [];
        if (in_array($dbField, ['complaint', 'diagnosis', 'history', 'treatment_text'])) {
            $dbResults = Visit::where($dbField, 'like', '%'.$value.'%')
                ->distinct()
                ->pluck($dbField)
                ->take(5)
                ->toArray();
        }

        return array_unique(array_merge($dictionaryResults, $dbResults));
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
