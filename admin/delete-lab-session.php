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

if(isset($_GET['id'])){
    $id = intval($_GET['id']);
    
    // First check if there are any appointments
    $check_query = $database->query("SELECT COUNT(*) as count FROM lab_appointments WHERE schedule_id = $id");
    $appointments = $check_query->fetch_assoc()['count'];
    
    if($appointments > 0){
        // If there are appointments, don't allow deletion
        header("location: lab_schedule.php?error=has_appointments");
        exit();
    }
    
    // If no appointments, proceed with deletion
    $delete_query = $database->query("DELETE FROM lab_schedule WHERE schedule_id = $id");
    
    if($delete_query){
        header("location: lab_schedule.php?action=session-deleted");
    } else {
        header("location: lab_schedule.php?error=db_error");
    }
} else {
    header("location: lab_schedule.php?error=invalid_request");
}
?> 