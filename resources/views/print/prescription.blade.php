<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Print Prescription') }} - {{ $patient->name }}</title>
    @vite(['resources/css/app.css'])
    <style>
        /* A5 size is 148 x 210 mm */
        @page {
            size: A5;
            margin: 0; /* Important: removes browser margins */
        }
        
        body {
            margin: 0;
            padding: 0;
            background-color: white;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .prescription-container {
            width: 148mm;
            height: 209mm; /* Slightly less than 210 to avoid overflowing to page 2 */
            position: relative;
            background-image: url('{{ $backgroundUrl }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            overflow: hidden; /* Prevents anything from going out of bounds */
            box-sizing: border-box;
            page-break-after: avoid;
            page-break-before: avoid;
            margin: 0 auto;
        }

        /* Screen preview styling */
        @media screen {
            body {
                background-color: #f3f4f6;
                display: flex;
                flex-direction: column;
                align-items: center;
                padding-top: 2rem;
                padding-bottom: 2rem;
            }
            .prescription-container {
                box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            }
            .print-btn-wrapper {
                margin-bottom: 2rem;
            }
        }

        @media print {
            .print-btn-wrapper {
                display: none !important;
            }
            body {
                background-color: transparent;
            }
        }

        /* Elements strictly break words if they exceed boundaries */
        .print-element {
            position: absolute;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: pre-wrap;
            line-height: 1.5;
            /* Ensures it never overflows the container width */
            max-width: 100%;
            /* If an element height exceeds, we hide overflow to respect boundaries */
            overflow: hidden; 
        }

        /* Specifically for canvas image to maintain aspect ratio but fit within width */
        .canvas-image {
            width: 100%;
            height: auto;
            max-height: 300px;
            object-fit: contain;
            mix-blend-multiply: multiply;
        }
    </style>
</head>
<body>
    
    <div class="print-btn-wrapper flex gap-4">
        <button onclick="window.print()" class="px-8 py-3 bg-indigo-600 text-white rounded-xl font-bold shadow-lg hover:bg-indigo-700 transition-colors">
            {{ __('Print') }}
        </button>
        <button onclick="window.close()" class="px-8 py-3 bg-white text-gray-700 border border-gray-200 rounded-xl font-bold shadow-sm hover:bg-gray-50 transition-colors">
            {{ __('Close') }}
        </button>
    </div>

    <div class="prescription-container font-sans text-gray-900">
        
        @if(!empty($elements['patient_name']) && $elements['patient_name']['visible'])
            <div class="print-element font-bold" 
                 style="top: {{ $elements['patient_name']['y'] }}%; 
                        {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}: {{ $elements['patient_name']['x'] }}%; 
                        width: {{ $elements['patient_name']['width'] }}%; 
                        font-size: {{ $elements['patient_name']['fontSize'] }}px;">
                {{ $patient->name }}
            </div>
        @endif

        @if(!empty($elements['patient_age']) && $elements['patient_age']['visible'])
            <div class="print-element" 
                 style="top: {{ $elements['patient_age']['y'] }}%; 
                        {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}: {{ $elements['patient_age']['x'] }}%; 
                        width: {{ $elements['patient_age']['width'] }}%; 
                        font-size: {{ $elements['patient_age']['fontSize'] }}px;">
                @if($patient->age_years) {{ $patient->age_years }} {{ __('Y') }} @endif
                @if($patient->age_months) {{ $patient->age_months }} {{ __('M') }} @endif
                @if($patient->age_days) {{ $patient->age_days }} {{ __('D') }} @endif
            </div>
        @endif

        @if(!empty($elements['date']) && $elements['date']['visible'])
            <div class="print-element" 
                 style="top: {{ $elements['date']['y'] }}%; 
                        {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}: {{ $elements['date']['x'] }}%; 
                        width: {{ $elements['date']['width'] }}%; 
                        font-size: {{ $elements['date']['fontSize'] }}px;">
                {{ $visit->created_at->format('Y-m-d') }}
            </div>
        @endif

        @if(!empty($elements['diagnosis']) && $elements['diagnosis']['visible'] && $visit->diagnosis && $visit->visit_mode !== 'canvas')
            <div class="print-element" 
                 style="top: {{ $elements['diagnosis']['y'] }}%; 
                        {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}: {{ $elements['diagnosis']['x'] }}%; 
                        width: {{ $elements['diagnosis']['width'] }}%; 
                        font-size: {{ $elements['diagnosis']['fontSize'] }}px;">
                {{ $visit->diagnosis }}
            </div>
        @endif

        @if(!empty($elements['treatment']) && $elements['treatment']['visible'] && $visit->treatment_text && $visit->visit_mode !== 'canvas')
            <div class="print-element" 
                 style="top: {{ $elements['treatment']['y'] }}%; 
                        {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}: {{ $elements['treatment']['x'] }}%; 
                        width: {{ $elements['treatment']['width'] }}%; 
                        font-size: {{ $elements['treatment']['fontSize'] }}px;">
                {{ $visit->treatment_text }}
            </div>
        @endif

        @if(!empty($elements['investigations']) && $elements['investigations']['visible'] && $visit->history && $visit->visit_mode !== 'canvas')
            <div class="print-element" 
                 style="top: {{ $elements['investigations']['y'] }}%; 
                        {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}: {{ $elements['investigations']['x'] }}%; 
                        width: {{ $elements['investigations']['width'] }}%; 
                        font-size: {{ $elements['investigations']['fontSize'] }}px;">
                {{ $visit->history }}
            </div>
        @endif

        @if(!empty($elements['canvas']) && $elements['canvas']['visible'] && $visit->visit_mode === 'canvas' && $visit->canvas_image_path)
            <div class="print-element" 
                 style="top: {{ $elements['canvas']['y'] }}%; 
                        {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}: {{ $elements['canvas']['x'] }}%; 
                        width: {{ $elements['canvas']['width'] }}%;">
                <img src="{{ Storage::url($visit->canvas_image_path) }}" class="canvas-image" alt="Canvas Record">
            </div>
        @endif

    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            setTimeout(() => {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
