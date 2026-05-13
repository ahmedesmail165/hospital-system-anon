<?php
// تحديد مسار ملف app.py (عدلي المسار حسب مكان الملف عندك)
$pythonScript = 'C:\\xampp\\htdocs\\HelthCare-System-main mostfa\\patient\\brain tumor\\app.py';

// تشغيل سكربت بايثون في الخلفية
pclose(popen("start /B python \"$pythonScript\"", "r"));

// إعادة توجيه المستخدم إلى تطبيق Flask
header("Location: http://127.0.0.1:5000/");
exit();
?>
