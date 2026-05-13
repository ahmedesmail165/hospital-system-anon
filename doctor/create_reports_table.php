<?php
    include("../connection.php");

    // التحقق من الاتصال بقاعدة البيانات
    if ($database->connect_error) {
        die("Connection failed: " . $database->connect_error);
    }

    // طباعة اسم قاعدة البيانات الحالية
    echo "Current database: " . $database->database . "<br>";

    // التأكد من استخدام قاعدة البيانات الصحيحة
    $database->select_db("SQL_Database_edoc");

    $sql = "CREATE TABLE IF NOT EXISTS patient_reports (
        report_id INT AUTO_INCREMENT PRIMARY KEY,
        pid INT NOT NULL,
        docid INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        diagnosis TEXT NOT NULL,
        treatment TEXT NOT NULL,
        medications TEXT NOT NULL,
        notes TEXT,
        report_date DATE NOT NULL,
        FOREIGN KEY (pid) REFERENCES patient(pid),
        FOREIGN KEY (docid) REFERENCES doctor(docid)
    )";

    if($database->query($sql)){
        echo "Table patient_reports created successfully";
    }else{
        echo "Error creating table: " . $database->error;
    }

    // إغلاق الاتصال
    $database->close();
?> 