<?php
session_start();

// Verify user permissions
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

if(isset($_GET['id'])){
    $id = intval($_GET['id']);
    
    // Check if session has any appointments
    $check_query = $database->query("SELECT COUNT(*) as count FROM radiology_appointment WHERE scheduleid = " . $id);
    $check_result = $check_query->fetch_assoc();
    
    if($check_result['count'] > 0) {
        header("location: radiology_schedule.php?error=has_appointments");
        exit();
    }
    
    // Delete the session
    $delete_query = $database->query("DELETE FROM radiology_schedule WHERE scheduleid = " . $id);
    
    if($delete_query === false) {
        header("location: radiology_schedule.php?error=db_error");
        exit();
    }
    
    header("location: radiology_schedule.php?success=deleted");
    exit();
} else {
    header("location: radiology_schedule.php");
    exit();
}
?> 