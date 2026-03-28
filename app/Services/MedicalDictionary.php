<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class MedicalDictionary
{
    /**
     * Get medical suggestions from external APIs and local history.
     */
    public static function getSuggestions($category, $query = '')
    {
        if (empty($query) || strlen($query) < 2) {
            return [];
        }

        $query = mb_strtolower($query);
        $cacheKey = "med_sug_{$category}_" . md5($query);

        return Cache::remember($cacheKey, 3600, function() use ($category, $query) {
            $apiResults = self::fetchFromApi($category, $query);
            $localResults = self::getLocalArabicSuggestions($category, $query);

            return array_unique(array_merge($localResults, $apiResults));
        });
    }

    /**
     * Fetch from NLM ClinicalTables API (Real-time millions of terms)
     */
    private static function fetchFromApi($category, $query)
    {
        try {
            // Priority 1: Main clinical search
            $results = self::queryNlm($category, $query);
            
            // Priority 2: Fallback for diagnosis if no results (e.g., try ICD-10 if Condition failed)
            if (empty($results) && $category === 'diagnosis') {
                $results = self::queryNlm('icd10cm', $query);
            }

            return $results;
        } catch (\Exception $e) {
            \Log::error("Medical API Error: " . $e->getMessage());
        }

        return [];
    }

    private static function queryNlm($category, $query)
    {
        $endpoint = match($category) {
            'diagnosis' => 'https://clinicaltables.nlm.nih.gov/api/condition/v3/search',
            'icd10cm' => 'https://clinicaltables.nlm.nih.gov/api/icd10cm/v3/search',
            'treatments' => 'https://clinicaltables.nlm.nih.gov/api/rxterms/v3/search',
            'investigations' => 'https://clinicaltables.nlm.nih.gov/api/rxterms/v3/search',
            default => 'https://clinicaltables.nlm.nih.gov/api/condition/v3/search',
        };

        $response = Http::timeout(2)->get($endpoint, [
            'terms' => $query,
            'max' => 10
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            // Standard NLM Search Result Format: [total, codes, null, descriptions]
            // We usually want descriptions in data[3]
            if (isset($data[3]) && count($data[3]) > 0) {
                return collect($data[3])->map(function($item) {
                    return is_array($item) ? ($item[1] ?? $item[0]) : $item;
                })->toArray();
            }

            // Some simplified results in data[1]
            if (isset($data[1]) && count($data[1]) > 0 && !is_array($data[1][0])) {
                return $data[1];
            }
        }

        return [];
    }

    /**
     * High-impact common Arabic clinical terms (to keep DB small)
     */
    private static function getLocalArabicSuggestions($category, $query)
    {
        $arabicList = [
            'complaints' => [
                'صداع', 'سخونية', 'كحة', 'نهجان', 'مغص', 'ألم في الظهر', 'دوخة', 
                'إرهاق', 'غثيان', 'ترجيع', 'إسهال', 'إمساك', 'حساسية', 'طفح جلدي',
                'ألم في المفاصل', 'احتقان زور', 'زغللة', 'صعوبة تنفس', 'وجع جسم'
            ],
            'diagnosis' => [
                'نزلة برد', 'أنفلونزا', 'التهاب شعبي', 'التهاب رئوي', 'حساسية صدرية',
                'ضغط دم مرتفع', 'مرض السكر', 'أنيميا', 'فقر دم', 'التهاب معدة',
                'قرحة معدة', 'التهاب اللوزتين', 'حصوات كلى', 'حصوات مرارة', 'روماتيزم',
                'التهاب مفاصل', 'خشونة ركبة', 'نزلة معوية', 'كورونا', 'تسمم غذائي',
                'ضعف عام', 'نقص فيتامينات', 'التهاب جيوب أنفية', 'شقيقة', 'ضغط مرتفع',
                'ضغط واطي', 'التهاب الكبد', 'تضخم كبد', 'تضخم طحال', 'سرطان', 
                'ورم حميد', 'ورم خبيث', 'نقرس', 'ارتجاع مريء', 'قولون عصبي'
            ],
            'treatments' => [

                'باراسيتامول', 'ايبوبروفين', 'اموكسيسيلين', 'مضاد حيوي', 'انتينال للإسهال',
                'كونترولوك للمعدة', 'بنادول', 'كتافلام', 'فيتامين د3', 'مكمل غذائي',
                'جلسة بخار', 'راحة تامة', 'سوائل بكثرة'
            ],
            'investigations' => [
                'صورة دم كاملة (CBC)', 'سكر صائم', 'سكر فاطر', 'سكر تراكمي',
                'وظائف كبد', 'وظائف كلى', 'تحليل بول', 'تحليل براز', 'أشعة صدر',
                'أشعة تليفزيونية سونار', 'رسم قلب'
            ]
        ];

        $terms = $arabicList[$category] ?? [];
        return array_values(array_filter($terms, function($term) use ($query) {
            return str_contains($term, $query);
        }));
    }
}
