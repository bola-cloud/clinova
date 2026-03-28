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

        $cacheKey = "med_sug_v9_{$category}_" . md5($query);

        return Cache::remember($cacheKey, 3600, function () use ($category, $query) {
            $results = [];

            // Detect Language
            $isArabic = preg_match('/\p{Arabic}/u', $query);
            $lang = $isArabic ? 'ar' : 'en';

            try {
                if ($isArabic) {
                    // 1. Wikidata Only (For Arabic)
                    $results = self::fetchFromWikidata($query, 'ar');
                } else {
                    // 2. English: Concurrent API Execution using Laravel Http::pool
                    $responses = Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($category, $query) {
                        $reqs = [
                            $pool->as('wikidata')->timeout(3)
                                ->withoutVerifying()
                                ->withHeaders(['User-Agent' => 'Clinova/1.0 (Medical Clinic App)'])
                                ->get('https://www.wikidata.org/w/api.php', [
                                    'action'   => 'wbsearchentities',
                                    'search'   => $query,
                                    'language' => 'en',
                                    'uselang'  => 'en',
                                    'format'   => 'json',
                                    'limit'    => 7,
                                    'type'     => 'item',
                                ])
                        ];

                        if (in_array($category, ['diagnosis', 'complaints', 'investigations'])) {
                            $reqs[] = $pool->as('nlm_conds')->timeout(3)->withoutVerifying()
                                ->get('https://clinicaltables.nlm.nih.gov/api/conditions/v3/search', ['terms' => $query, 'max' => 5]);
                            $reqs[] = $pool->as('medline')->timeout(3)->withoutVerifying()
                                ->get('https://clinicaltables.nlm.nih.gov/api/medlineplus_health_topics/v3/search', ['terms' => $query, 'max' => 5]);
                        } elseif ($category === 'treatments') {
                            $reqs[] = $pool->as('rxterms')->timeout(3)->withoutVerifying()
                                ->get('https://clinicaltables.nlm.nih.gov/api/rxterms/v3/search', ['terms' => $query, 'max' => 8]);
                        }

                        return $reqs;
                    });

                    // Parse pool responses securely
                    if (isset($responses['wikidata']) && $responses['wikidata']->successful()) {
                        $data = $responses['wikidata']->json();
                        if (isset($data['search'])) {
                            $results = array_merge($results, array_map(fn($item) => $item['label'] ?? null, $data['search']));
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
                    return collect($data['search'])
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
