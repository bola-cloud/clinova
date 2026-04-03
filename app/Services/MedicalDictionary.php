<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class MedicalDictionary
{
    /**
     * Get medical suggestions from external APIs (Purely Dynamic, Multilingual).
     * Sources: Wikidata (AR+EN), NLM Conditions (EN diagnoses), NLM LOINC (EN labs), NLM RxTerms (EN drugs)
     */
    public static function getSuggestions($category, $query = '')
    {
        if (empty($query) || strlen($query) < 2) {
            return [];
        }

        $cacheKey = "med_sug_v10_{$category}_" . md5($query);

        return Cache::remember($cacheKey, 3600, function () use ($category, $query) {
            $results = [];

            // Detect Language
            $isArabic = preg_match('/\p{Arabic}/u', $query);
            $lang = $isArabic ? 'ar' : 'en';

            try {
                if ($isArabic) {
                    // 1. Wikidata with Filtering for Arabic
                    $results = self::fetchFromWikidata($query, 'ar');
                } else {
                    // 2. English: Concurrent API Execution using specialized clinical tables
                    $responses = Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($category, $query) {
                        $reqs = [];

                        if (in_array($category, ['diagnosis', 'complaints'])) {
                            // ICD-10-CM is the gold standard for clinical diagnoses
                            $reqs[] = $pool->as('icd10')->timeout(3)->withoutVerifying()
                                ->get('https://clinicaltables.nlm.nih.gov/api/icd10cm/v3/search', ['terms' => $query, 'max' => 8]);
                            
                            $reqs[] = $pool->as('nlm_conds')->timeout(3)->withoutVerifying()
                                ->get('https://clinicaltables.nlm.nih.gov/api/conditions/v3/search', ['terms' => $query, 'max' => 5]);
                            
                            $reqs[] = $pool->as('medline')->timeout(3)->withoutVerifying()
                                ->get('https://clinicaltables.nlm.nih.gov/api/medlineplus_health_topics/v3/search', ['terms' => $query, 'max' => 5]);
                        } elseif ($category === 'investigations') {
                            // LOINC is the standard for lab tests and investigations
                            $reqs[] = $pool->as('loinc')->timeout(3)->withoutVerifying()
                                ->get('https://clinicaltables.nlm.nih.gov/api/loinc_items/v3/search', ['terms' => $query, 'max' => 10]);
                        } elseif ($category === 'treatments') {
                            $reqs[] = $pool->as('rxterms')->timeout(3)->withoutVerifying()
                                ->get('https://clinicaltables.nlm.nih.gov/api/rxterms/v3/search', ['terms' => $query, 'max' => 10]);
                        }

                        return $reqs;
                    });

                    // Parse pool responses - Prioritize specialized tables
                    if (isset($responses['icd10']) && $responses['icd10']->successful()) {
                        $data = $responses['icd10']->json();
                        if (isset($data[3])) {
                            $results = array_merge($results, array_map(fn($item) => is_array($item) ? ($item[0] . ' (' . $item[1] . ')') : $item, $data[3]));
                        }
                    }

                    if (isset($responses['loinc']) && $responses['loinc']->successful()) {
                        $data = $responses['loinc']->json();
                        if (isset($data[3])) {
                            $results = array_merge($results, array_map(fn($item) => is_array($item) ? ($item[1] ?? $item[0]) : $item, $data[3]));
                        }
                    }

                    if (isset($responses['nlm_conds']) && $responses['nlm_conds']->successful()) {
                        $data = $responses['nlm_conds']->json();
                        if (isset($data[3])) {
                            $results = array_merge($results, array_map(fn($item) => is_array($item) ? ($item[0]) : $item, $data[3]));
                        }
                    }

                    if (isset($responses['medline']) && $responses['medline']->successful()) {
                        $data = $responses['medline']->json();
                        if (isset($data[3])) {
                            $results = array_merge($results, array_map(fn($item) => is_array($item) ? ($item[0]) : $item, $data[3]));
                        }
                    }

                    if (isset($responses['rxterms']) && $responses['rxterms']->successful()) {
                        $data = $responses['rxterms']->json();
                        if (isset($data[1])) {
                            $results = array_merge($results, $data[1]);
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error("Medical API Error: " . $e->getMessage());
            }

            return collect($results)
                ->filter()
                ->unique()
                ->values()
                ->take(15)
                ->toArray();
        });
    }

    // ─────────────────────────────────────────────
    // Wikidata — Multilingual (Arabic + English)
    // ─────────────────────────────────────────────
    private static function fetchFromWikidata($query, $lang = 'en')
    {
        try {
            $response = Http::timeout(5)
                ->withoutVerifying()
                ->withHeaders(['User-Agent' => 'Clinova/1.0 (Medical Clinic App)'])
                ->get('https://www.wikidata.org/w/api.php', [
                    'action'   => 'wbsearchentities',
                    'search'   => $query,
                    'language' => $lang,
                    'uselang'  => $lang, // Force returned labels to be in this language
                    'format'   => 'json',
                    'limit'    => 10,
                    'type'     => 'item',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['search'])) {
                    // Medical keywords to look for in descriptions or labels to ensure context
                    $medicalKeywords = [
                        'en' => ['disease', 'syndrome', 'symptom', 'drug', 'medication', 'protein', 'gene', 'anatomy', 'muscle', 'bone', 'medical', 'clinical', 'test', 'surgery', 'treatment', 'health'],
                        'ar' => ['مرض', 'متلازمة', 'علاج', 'دواء', 'جراحة', 'طب', 'تشريح', 'عضلة', 'عظم', 'فحص', 'تحليل', 'ألم', 'صحة', 'عقار']
                    ];

                    $keywords = $medicalKeywords[$lang] ?? $medicalKeywords['en'];

                    return collect($data['search'])
                        ->filter(function($item) use ($keywords) {
                            $label = mb_strtolower($item['label'] ?? '');
                            $desc = mb_strtolower($item['description'] ?? '');
                            
                            // Exclude obvious noise like "country", "city", "human", "person" if not medical
                            $noise = ['country', 'city', 'continent', 'footballer', 'politician', 'village', 'دولة', 'مدينة', 'قارة', 'لاعب', 'سياسي', 'قرية'];
                            foreach($noise as $n) {
                                if (str_contains($label, $n) || str_contains($desc, $n)) return false;
                            }

                            // If it's medical or has no description, keep it for now but prioritize medical
                            foreach($keywords as $kw) {
                                if (str_contains($label, $kw) || str_contains($desc, $kw)) return true;
                            }
                            
                            // If no medical keywords found, we check if it's potentially medical (lenient for now)
                            return empty($desc) || strlen($desc) < 5;
                        })
                        ->map(fn($item) => $item['label'] ?? null)
                        ->filter()
                        ->toArray();
                }
            }
        } catch (\Exception $e) {
        }
        return [];
    }

    // ─────────────────────────────────────────────
    // NLM Conditions — English Diagnoses
    // ─────────────────────────────────────────────
    private static function fetchFromNlmConditions($query)
    {
        try {
            $response = Http::timeout(5)
                ->withoutVerifying()
                ->get('https://clinicaltables.nlm.nih.gov/api/conditions/v3/search', [
                    'terms' => $query,
                    'max'   => 7,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data[3])) {
                    return array_map(
                        fn($item) => is_array($item) ? ($item[0]) : $item,
                        $data[3]
                    );
                }
            }
        } catch (\Exception $e) {
        }
        return [];
    }

    // ─────────────────────────────────────────────
    // MedlinePlus — User-Friendly English Health Topics
    // ─────────────────────────────────────────────
    private static function fetchFromMedlinePlus($query)
    {
        try {
            $response = Http::timeout(5)
                ->withoutVerifying()
                ->get('https://clinicaltables.nlm.nih.gov/api/medlineplus_health_topics/v3/search', [
                    'terms' => $query,
                    'max'   => 5,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data[3])) {
                    return array_map(
                        fn($item) => is_array($item) ? ($item[0]) : $item,
                        $data[3]
                    );
                }
            }
        } catch (\Exception $e) {
        }
        return [];
    }

    // ─────────────────────────────────────────────
    // NLM RxTerms — Medications & Drugs
    // ─────────────────────────────────────────────
    private static function fetchFromNlmRxTerms($query)
    {
        try {
            $response = Http::timeout(5)
                ->withoutVerifying()
                ->get('https://clinicaltables.nlm.nih.gov/api/rxterms/v3/search', [
                    'terms' => $query,
                    'max'   => 10,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data[1])) {
                    return $data[1];
                }
            }
        } catch (\Exception $e) {
        }
        return [];
    }
}
