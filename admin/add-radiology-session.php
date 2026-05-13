<?php
session_start();

if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" || $_SESSION['usertype']!='a'){
        header("location: ../login.php");
        exit();
    }
}else{
    header("location: ../login.php");
    exit();
}

include("../connection.php");

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $title = $database->real_escape_string($_POST['title']);
    $techid = $database->real_escape_string($_POST['techid']);
    $session_type = $database->real_escape_string($_POST['session_type']);
    $scheduledate = $database->real_escape_string($_POST['scheduledate']);
    $scheduletime = $database->real_escape_string($_POST['scheduletime']);
    $nop = $database->real_escape_string($_POST['nop']); // Number of patients / Max appointments
    $duration = $database->real_escape_string($_POST['duration']); // Duration in minutes

    // Basic validation
    if(empty($title) || empty($techid) || empty($session_type) || empty($scheduledate) || empty($scheduletime) || empty($nop) || empty($duration)){
        header("location: radiology_schedule.php?error=empty_fields");
        exit();
    }

    // Insert into database
    $sql = "INSERT INTO radiology_schedule (title, techid, session_type, scheduledate, scheduletime, nop, duration) 
            VALUES ('$title', '$techid', '$session_type', '$scheduledate', '$scheduletime', '$nop', '$duration')";
    
    $result = $database->query($sql);

    if($result){
        header("location: radiology_schedule.php?success=added");
    } else {
        error_log("Database error: " . $database->error);
        header("location: radiology_schedule.php?error=db_error");
    }
    exit();
} else {
    header("location: radiology_schedule.php");
    exit();
}
?> 