<?php

    session_start();

    if(!isset($_SESSION["user"]) || empty($_SESSION["user"]) || $_SESSION['usertype'] != 'p') {
        header("location: ../login.php");
        exit();
    }

    include("../connection.php");

    // Get patient ID
    $stmt = $database->prepare("SELECT pid FROM patient WHERE pemail = ?");
    $stmt->bind_param("s", $_SESSION["user"]);
    $stmt->execute();
    $result = $stmt->get_result();
    $patient = $result->fetch_assoc();
    $pid = $patient['pid'];
    $stmt->close();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id = $_POST['id'] ?? '';
        $type = $_POST['type'] ?? '';
        
        if (empty($id) || empty($type)) {
            header("location: appointment.php?error=Invalid request parameters");
            exit();
        }

        $success = false;
        $error = '';

        try {
            switch ($type) {
                case 'doctor':
                    $stmt = $database->prepare("DELETE FROM appointment WHERE appoid = ? AND pid = ?");
                    $stmt->bind_param("ii", $id, $pid);
                    break;
                    
                case 'radiology':
                    $stmt = $database->prepare("DELETE FROM radiology_appointment WHERE appoid = ? AND pid = ?");
                    $stmt->bind_param("ii", $id, $pid);
                    break;
                    
                case 'lab':
                    $stmt = $database->prepare("DELETE FROM lab_appointments WHERE appoid = ? AND pid = ?");
                    $stmt->bind_param("ii", $id, $pid);
                    break;
                    
                default:
                    throw new Exception("Invalid appointment type");
            }

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $success = true;
                } else {
                    throw new Exception("No appointment found or you don't have permission to delete it");
                }
            } else {
                throw new Exception("Database error: " . $stmt->error);
            }
            $stmt->close();

        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        if ($success) {
            header("location: appointment.php?action=cancelled");
        } else {
            header("location: appointment.php?error=" . urlencode($error));
        }
        exit();
    } else {
        header("location: appointment.php");
        exit();
    }


?>