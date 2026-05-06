<?php
$file = 'lang/ar.json';
$content = file_get_contents($file);

// If JSON is corrupted (invalid syntax), we need to try to clean it
// The duplication might have caused invalid syntax
$data = json_decode($content, true);

if ($data === null) {
    // Try to fix common syntax errors or just read as lines and re-parse
    // But since I know I messed up the end, I'll just try to parse up to the last valid brace
    $last_brace = strrpos($content, '}');
    if ($last_brace !== false) {
        $content = substr($content, 0, $last_brace + 1);
        $data = json_decode($content, true);
    }
}

if ($data === null) {
    die("Still corrupted. Please check manually.\n");
}

// Update keys
$data["Uploading files..."] = "جاري رفع الملفات...";
$data["Update Patient Data"] = "تحديث بيانات المريض";
$data["Saving..."] = "جاري الحفظ...";
$data["Investigations & Tests"] = "الفحوصات والتحاليل";
$data["Max 5MB"] = "بحد أقصى 5 ميجا";
$data["Select Files (Max 5MB)"] = "اختر الملفات (بحد أقصى 5 ميجا)";
$data["Attach Files (Max 5MB per file)"] = "إرفاق ملفات (بحد أقصى 5 ميجا للملف)";
$data["The file size must not exceed 5MB."] = "يجب ألا يتجاوز حجم الملف 5 ميجابايت.";
$data["Your account is pending approval. Please contact the administrator to activate your account."] = "حسابك قيد المراجعة حالياً. يرجى التواصل مع الإدارة لتفعيل الحساب.";
$data["Your doctor's account is pending approval."] = "حساب الطبيب الخاص بك قيد المراجعة حالياً.";

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Done\n";
