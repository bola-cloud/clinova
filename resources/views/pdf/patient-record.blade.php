<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #1e293b;
            direction: rtl;
            line-height: 1.6;
            margin: 30px;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #6366f1;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #6366f1;
            font-size: 22px;
            margin: 0 0 5px 0;
        }
        .header .clinic-name {
            font-size: 14px;
            color: #64748b;
        }
        .header .date {
            font-size: 10px;
            color: #94a3b8;
            margin-top: 5px;
        }
        .section {
            margin-bottom: 18px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }
        .section-title {
            background: #f1f5f9;
            padding: 8px 14px;
            font-weight: bold;
            font-size: 13px;
            color: #334155;
            border-bottom: 1px solid #e2e8f0;
        }
        .section-body {
            padding: 12px 14px;
        }
        .info-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .info-grid td {
            padding: 5px 10px;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            color: #475569;
            width: 30%;
        }
        .info-value {
            color: #1e293b;
        }
        .visit-card {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-bottom: 12px;
            overflow: hidden;
        }
        .visit-header {
            background: #ede9fe;
            padding: 6px 12px;
            font-weight: bold;
            font-size: 11px;
            color: #5b21b6;
        }
        .visit-body {
            padding: 10px 12px;
        }
        .visit-body p {
            margin: 4px 0;
        }
        .tag {
            display: inline-block;
            background: #dbeafe;
            color: #1e40af;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            margin: 2px;
        }
        .footer {
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            margin-top: 30px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-checkup { background: #dbeafe; color: #1e40af; }
        .badge-followup { background: #fef3c7; color: #92400e; }
        .files-list {
            margin: 5px 0;
            padding: 0;
            list-style: none;
        }
        .files-list li {
            padding: 3px 0;
            font-size: 10px;
            color: #64748b;
        }
        .files-list li::before {
            content: "📎 ";
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $clinicName }}</h1>
        <div class="clinic-name">{{ $doctorName }} — ملف المريض الطبي</div>
        <div class="date">تاريخ التصدير: {{ $exportDate }}</div>
    </div>

    <!-- Patient Basic Info -->
    <div class="section">
        <div class="section-title">بيانات المريض الأساسية</div>
        <div class="section-body">
            <table class="info-grid">
                <tr>
                    <td class="info-label">الاسم:</td>
                    <td class="info-value">{{ $patient->name }}</td>
                    <td class="info-label">الهاتف:</td>
                    <td class="info-value">{{ $patient->phone }}</td>
                </tr>
                <tr>
                    <td class="info-label">السن:</td>
                    <td class="info-value">
                        @if($patient->age_years) {{ $patient->age_years }} سنة @endif
                        @if($patient->age_months) {{ $patient->age_months }} شهر @endif
                        @if($patient->age_days) {{ $patient->age_days }} يوم @endif
                        @if(!$patient->age_years && !$patient->age_months && !$patient->age_days) — @endif
                    </td>
                    <td class="info-label">الوزن:</td>
                    <td class="info-value">{{ $patient->weight ? $patient->weight . ' كجم' : '—' }}</td>
                </tr>
                <tr>
                    <td class="info-label">العنوان:</td>
                    <td class="info-value" colspan="3">{{ $patient->address ?: '—' }}</td>
                </tr>
                <tr>
                    <td class="info-label">تاريخ التسجيل:</td>
                    <td class="info-value">{{ $patient->created_at->format('Y-m-d') }}</td>
                    <td class="info-label">الحالة:</td>
                    <td class="info-value">{{ $patient->deleted_at ? 'محذوف بتاريخ ' . $patient->deleted_at->format('Y-m-d') : 'نشط' }}</td>
                </tr>
            </table>

            @if($patient->tags && count($patient->tags))
            <div style="margin-top: 8px;">
                <strong>العلامات:</strong>
                @foreach($patient->tags as $tag)
                    <span class="tag">{{ $tag }}</span>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <!-- Medical History -->
    @if($patient->personal_history || $patient->family_history)
    <div class="section">
        <div class="section-title">التاريخ المرضي</div>
        <div class="section-body">
            <table class="info-grid">
                @if($patient->personal_history)
                <tr>
                    <td class="info-label">التاريخ الشخصي:</td>
                    <td class="info-value">{{ $patient->personal_history }}</td>
                </tr>
                @endif
                @if($patient->family_history)
                <tr>
                    <td class="info-label">التاريخ العائلي:</td>
                    <td class="info-value">{{ $patient->family_history }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>
    @endif

    <!-- Visits History -->
    @if($patient->visits && count($patient->visits))
    <div class="section">
        <div class="section-title">سجل الزيارات ({{ count($patient->visits) }} زيارة)</div>
        <div class="section-body">
            @foreach($patient->visits->sortByDesc('created_at') as $visit)
            <div class="visit-card">
                <div class="visit-header">
                    {{ $visit->created_at->format('Y-m-d H:i') }}
                    —
                    <span class="badge {{ $visit->type === 'followup' ? 'badge-followup' : 'badge-checkup' }}">
                        {{ $visit->type === 'followup' ? 'متابعة' : 'كشف' }}
                    </span>
                    @if($visit->deleted_at)
                        <span style="color: #dc2626; font-size: 10px;">(محذوف)</span>
                    @endif
                </div>
                <div class="visit-body">
                    @if($visit->complaint)
                        <p><strong>الشكوى:</strong> {{ $visit->complaint }}</p>
                    @endif
                    @if($visit->diagnosis)
                        <p><strong>التشخيص:</strong> {{ $visit->diagnosis }}</p>
                    @endif
                    @if($visit->treatment_text)
                        <p><strong>العلاج:</strong> {{ $visit->treatment_text }}</p>
                    @endif
                    @if($visit->follow_up_notes)
                        <p><strong>ملاحظات المتابعة:</strong> {{ $visit->follow_up_notes }}</p>
                    @endif
                    @if($visit->specialty_data && is_array($visit->specialty_data))
                        @foreach($visit->specialty_data as $key => $value)
                            @if($value)
                                <p><strong>{{ $key }}:</strong> {{ is_array($value) ? implode(', ', $value) : $value }}</p>
                            @endif
                        @endforeach
                    @endif
                    @if($visit->treatment_file_path)
                        <p style="font-size: 10px; color: #64748b;">📎 مرفق علاج: {{ basename($visit->treatment_file_path) }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Standalone Files -->
    @if($patient->files && count($patient->files))
    <div class="section">
        <div class="section-title">الملفات الطبية المرفقة ({{ count($patient->files) }} ملف)</div>
        <div class="section-body">
            <ul class="files-list">
                @foreach($patient->files as $file)
                    <li>
                        {{ $file->file_name }}
                        ({{ $file->file_type }})
                        — تاريخ الرفع: {{ $file->created_at->format('Y-m-d') }}
                        @if($file->deleted_at) — <span style="color: #dc2626;">(محذوف)</span> @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="footer">
        تم إنشاء هذا الملف بواسطة نظام كلينوفا — Clinova Smart Clinic System — {{ $exportDate }}
    </div>
</body>
</html>
