<?php
$file = 'lang/ar.json';
$content = file_get_contents($file);
$data = json_decode($content, true);

if ($data === null) {
    die("JSON error: " . json_last_error_msg());
}

$data["Your account is pending approval. Please contact the administrator to activate your account."] = "حسابك قيد المراجعة حالياً. يرجى التواصل مع الإدارة لتفعيل الحساب.";
$data["Your doctor's account is pending approval."] = "حساب الطبيب الخاص بك قيد المراجعة حالياً.";

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Done\n";
