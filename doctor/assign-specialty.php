<?php
session_start();
if(!isset($_SESSION["user"]) || $_SESSION["user"] == "" || $_SESSION['usertype'] != 'd') {
    header("location: ../login.php");
    exit();
}

include("../connection.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['patient_id']) && isset($_POST['specialty_id'])) {
    $patientId = $_POST['patient_id'];
    $specialtyId = $_POST['specialty_id'];
    

    $checkColumn = $database->query("SHOW COLUMNS FROM patient LIKE 'specialty_id'");
    if ($checkColumn->num_rows == 0) {
   
        $database->query("ALTER TABLE patient ADD COLUMN specialty_id INT");
    }
    
    try {
      
        $updateQuery = "UPDATE patient SET specialty_id = ? WHERE pid = ?";
        $stmt = $database->prepare($updateQuery);
        $stmt->bind_param("is", $specialtyId, $patientId);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "The patient has been effectively assigned.";
        } else {
            throw new Exception("An error occurred while setting the specialization.");
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
}


header("Location: reco.php?patient_id=" . $patientId);
exit();
?> 