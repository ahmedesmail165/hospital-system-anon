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

if(isset($_GET['id'])) {
    $med_id = $_GET['id'];
    
    // Delete the medication
    $sql = "DELETE FROM meds WHERE med_id = ?";
    $stmt = $database->prepare($sql);
    $stmt->bind_param("i", $med_id);
    
    if($stmt->execute()) {
        echo "<script>alert('Medication deleted successfully!'); window.location.href='medications.php';</script>";
    } else {
        echo "<script>alert('Error deleting medication!'); window.location.href='medications.php';</script>";
    }
} else {
    header("location: medications.php");
}
?> 