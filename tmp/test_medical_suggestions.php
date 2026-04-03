<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\MedicalDictionary;

function testQuery($category, $query) {
    echo "Testing Category: [$category], Query: [$query]\n";
    $results = MedicalDictionary::getSuggestions($category, $query);
    if (empty($results)) {
        echo " - No results found.\n";
    } else {
        foreach ($results as $res) {
            echo " - $res\n";
        }
    }
    echo "---------------------------------\n";
}

echo "VERIFYING MEDICAL SUGGESTIONS REFINEMENTS\n\n";

// Test 1: Verification that general noise is REMOVED
testQuery('complaints', 'fernan'); // Should NOT have 'Fernando'
testQuery('investigations', 'suda'); // Should NOT have 'Sudan'

// Test 2: Verification that clinical tables WORK for English
testQuery('diagnosis', 'flu'); 
testQuery('investigations', 'cbc'); 

// Test 3: Verification for Arabic (Filtering working)
testQuery('complaints', 'الصداع'); // Headache
testQuery('investigations', 'تحليل'); // Analysis/Test

echo "\nVerification complete.\n";
