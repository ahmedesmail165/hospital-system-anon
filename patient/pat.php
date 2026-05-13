<?php
/*
    // مسار app.py (غيّره حسب مكان الملف عندك)
    $pythonScript = 'C:\\xampp\\htdocs\\pneumonia\\2app.py';
    
    // أمر تشغيل السيرفر Flask في الخلفية على Windows
    pclose(popen("start /B python \"$pythonScript\"", "r"));
    
    // تحويل المستخدم مباشرة للموقع
    header("Location: http://127.0.0.1:5000/");
    
    exit();
*/
?>
<?php
// تحديد مسار ملف app.py (عدلي المسار حسب مكان الملف عندك)
$pythonScript = 'C:\\xampp\\htdocs\\HelthCare-System-main mostfa\\patient\Chatbot(Jemini)\\app.py';
// تشغيل سكربت بايثون في الخلفية
pclose(popen("start /B python \"$pythonScript\"", "r"));

// إعادة توجيه المستخدم إلى تطبيق Flask
header("Location: http://127.0.0.1:9000/");
exit();
?>
