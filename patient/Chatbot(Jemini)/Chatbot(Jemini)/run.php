<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost:9000");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5); // حد أقصى 5 ثواني

$response = curl_exec($ch);

if ($response === false) {
    echo "❌ فشل الاتصال بـ Flask: " . curl_error($ch);
} else {
    echo "<pre>$response</pre>";
}

curl_close($ch);
?>
