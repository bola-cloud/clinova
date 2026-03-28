<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

echo "=== Testing Wikidata WITH User-Agent ===" . PHP_EOL;
try {
    $r = Http::timeout(10)->withoutVerifying()
        ->withHeaders(['User-Agent' => 'Clinova/1.0 (Medical Clinic App)'])
        ->get('https://www.wikidata.org/w/api.php', [
            'action' => 'wbsearchentities',
            'search' => 'fever',
            'language' => 'en',
            'format' => 'json',
            'limit' => 5,
            'type' => 'item'
        ]);
    echo "Status: " . $r->status() . PHP_EOL;
    $data = $r->json();
    if (isset($data['search'])) {
        foreach ($data['search'] as $item) {
            echo "  - " . ($item['label'] ?? 'N/A') . PHP_EOL;
        }
    } else {
        echo "  No results. Body: " . substr($r->body(), 0, 300) . PHP_EOL;
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== Testing NLM CONDITIONS (plural) ===" . PHP_EOL;
try {
    $r = Http::timeout(10)->withoutVerifying()->get('https://clinicaltables.nlm.nih.gov/api/conditions/v3/search', [
        'terms' => 'fever',
        'max' => 5
    ]);
    echo "Status: " . $r->status() . PHP_EOL;
    $data = $r->json();
    echo "  Total: " . ($data[0] ?? 0) . PHP_EOL;
    if (isset($data[3])) {
        foreach ($data[3] as $item) {
            echo "  - " . (is_array($item) ? ($item[0]) : $item) . PHP_EOL;
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== Testing Wikidata Arabic WITH User-Agent ===" . PHP_EOL;
try {
    $r = Http::timeout(10)->withoutVerifying()
        ->withHeaders(['User-Agent' => 'Clinova/1.0 (Medical Clinic App)'])
        ->get('https://www.wikidata.org/w/api.php', [
            'action' => 'wbsearchentities',
            'search' => 'حمى',
            'language' => 'ar',
            'format' => 'json',
            'limit' => 5,
            'type' => 'item'
        ]);
    echo "Status: " . $r->status() . PHP_EOL;
    $data = $r->json();
    if (isset($data['search'])) {
        foreach ($data['search'] as $item) {
            echo "  - " . ($item['label'] ?? 'N/A') . PHP_EOL;
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== Testing NLM Lab Tests (LOINC) ===" . PHP_EOL;
try {
    $r = Http::timeout(10)->withoutVerifying()->get('https://clinicaltables.nlm.nih.gov/api/loinc_items/v3/search', [
        'terms' => 'blood',
        'max' => 5,
        'df' => 'LONG_COMMON_NAME'
    ]);
    echo "Status: " . $r->status() . PHP_EOL;
    $data = $r->json();
    echo "  Total: " . ($data[0] ?? 0) . PHP_EOL;
    if (isset($data[3])) {
        foreach ($data[3] as $item) {
            echo "  - " . (is_array($item) ? ($item[0]) : $item) . PHP_EOL;
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
