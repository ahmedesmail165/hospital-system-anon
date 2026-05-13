<?php
// Start the session
session_start();

// Include the database connection
require '../connection.php';

// Check if the user is logged in
if (!isset($_SESSION["user"])) {
    header("location: ../login.php");
    exit();
}

// Get the logged-in user's email
$useremail = $_SESSION["user"];

// Fetch the user's ID from the database
$userrow = $database->query("SELECT pid FROM patient WHERE pemail='$useremail'");
$userfetch = $userrow->fetch_assoc();
$userid = $userfetch["pid"];

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input data
    $patient_name = trim($_POST["patient_name"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);
    $location = trim($_POST["location"]);
    $emergency_type = trim($_POST["emergency_type"]);

    // Basic validation
    if (empty($patient_name) || empty($phone) || empty($address) || empty($emergency_type)) {
        // Redirect back with an error message if required fields are empty
        header("location: form.php?error=Please fill in all required fields.");
        exit();
    }

    // Insert the data into the database
    $sql = "INSERT INTO ambulance_requests (user_email, patient_name, phone, address, location, emergency_type, request_time) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";

    // Prepare the SQL statement
    $stmt = $database->prepare($sql);
    if ($stmt) {
        // Bind the parameters
        $stmt->bind_param("ssssss", $useremail, $patient_name, $phone, $address, $location, $emergency_type);

        // Execute the statement
        if ($stmt->execute()) {
            // Redirect back with a success message
            header("location: Ambulance Booking.php?success=Ambulance request submitted successfully!");
            exit();
        } else {
            // Redirect back with an error message if the query fails
            header("location: Ambulance Booking.php?error=Failed to submit the request. Please try again.");
            exit();
        }

        // Close the statement
        $stmt->close();
    } else {
        // Redirect back with an error message if the statement preparation fails
        header("location: form.php?error=Database error. Please try again.");
        exit();
    }
} else {
    // Redirect back if the form was not submitted
    header("location: form.php");
    exit();
}
?>