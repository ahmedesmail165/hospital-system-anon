<?php
    session_start();
    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='d'){
            header("location: ../login.php");
        }
    }else{
        header("location: ../login.php");
    }

    include("../connection.php");

    // التحقق من وجود الجدول وإنشائه إذا لم يكن موجوداً
    $check_table = "SHOW TABLES LIKE 'patient_reports'";
    $table_exists = $database->query($check_table);

    if($table_exists->num_rows == 0) {
        // إنشاء الجدول بدون مفاتيح خارجية
        $create_table = "CREATE TABLE IF NOT EXISTS patient_reports (
            report_id INT AUTO_INCREMENT PRIMARY KEY,
            pid INT NOT NULL,
            docid INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            diagnosis TEXT NOT NULL,
            treatment TEXT NOT NULL,
            medications TEXT NOT NULL,
            notes TEXT,
            report_date DATE NOT NULL
        )";
        
        if(!$database->query($create_table)) {
            die("Error creating table: " . $database->error);
        }
    }

    if($_POST){
        $pid = $_POST["pid"];
        $docid = $_POST["docid"];
        $title = $_POST["title"];
        $diagnosis = $_POST["diagnosis"];
        $treatment = $_POST["treatment"];
        $medications = $_POST["medications"];
        $notes = $_POST["notes"];
        $date = date("Y-m-d");

        $sql = "INSERT INTO patient_reports (pid, docid, title, diagnosis, treatment, medications, notes, report_date) 
                VALUES ('$pid', '$docid', '$title', '$diagnosis', '$treatment', '$medications', '$notes', '$date')";
        
        if($database->query($sql)){
            header("location: patient.php?action=report_added");
        }else{
            header("location: add-report.php?id=$pid&error=1");
        }
    }
?> 