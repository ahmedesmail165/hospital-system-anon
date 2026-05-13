<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sql_database_edoc";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION['user']; 
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $emergency_name = $_POST['emergency-name'];
    $emergency_phone = $_POST['emergency-phone'];
    $emergency_relationship = $_POST['emergency-relationship'];
    $insurance_provider = $_POST['insurance-provider'];
    $policy_number = $_POST['policy-number'];
    $group_number = $_POST['group-number'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $blood_type = $_POST['blood-type'];
    $allergies = $_POST['allergies'];
    $medications = $_POST['medications'];
    $chronic_conditions = $_POST['chronic-conditions'];
    $medical_history = $_POST['medical-history'];
    $family_history = $_POST['family-history'];
    $symptoms = $_POST['symptoms'];
    $smoking = $_POST['smoking'];
    $alcohol = $_POST['alcohol'];
    $exercise = $_POST['exercise'];
    
    
    $sql = "UPDATE patient SET 
            pdob = ?, 
            gender = ?, 
            ptel = ?, 
            emergency_name = ?, 
            emergency_phone = ?, 
            emergency_relationship = ?, 
            insurance_provider = ?, 
            policy_number = ?, 
            group_number = ?, 
            height = ?, 
            weight = ?, 
            blood_type = ?, 
            allergies = ?, 
            medications = ?, 
            chronic_conditions = ?, 
            medical_history = ?, 
            family_history = ?, 
            symptoms = ?, 
            smoking = ?, 
            alcohol = ?, 
            exercise = ? 
            WHERE pemail = ?"; 
    echo $email;
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssssssssssssss", 
                      $dob, $gender, $phone, 
                      $emergency_name, $emergency_phone, $emergency_relationship, 
                      $insurance_provider, $policy_number, $group_number, 
                      $height, $weight, $blood_type, $allergies, $medications, 
                      $chronic_conditions, $medical_history, $family_history, 
                      $symptoms, $smoking, $alcohol, $exercise, $email);
    
    if ($stmt->execute()) {
header("location: ../");
    } else {
        echo "Error: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
}
?>