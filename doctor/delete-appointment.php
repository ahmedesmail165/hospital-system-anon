<?php
session_start();

// 1. التحقق من تسجيل الدخول والصحة - تعديل الشرط للسماح للمسؤولين والأطباء
if(!isset($_SESSION["user"]) || $_SESSION["user"] == "" || ($_SESSION['usertype'] != 'a' && $_SESSION['usertype'] != 'd')) {
    header("location: ../login.php");
    exit();
}

// 2. التحقق من وجود ID
if(!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    $_SESSION['error'] = "Invalid appointment ID";
    header("location: appointment.php");
    exit();
}

// 3. الاتصال بقاعدة البيانات
include("../connection.php");
if($database->connect_error) {
    $_SESSION['error'] = "Database connection failed: " . $database->connect_error;
    header("location: appointment.php");
    exit();
}

// 4. التحقق من وجود الموعد أولاً
$id = intval($_GET["id"]);
$checkSql = "SELECT appoid FROM appointment WHERE appoid = ?";
$checkStmt = $database->prepare($checkSql);

if(!$checkStmt) {
    $_SESSION['error'] = "Query preparation failed: " . $database->error;
    header("location: appointment.php");
    exit();
}

$checkStmt->bind_param("i", $id);
$checkStmt->execute();
$result = $checkStmt->get_result();

if($result->num_rows == 0) {
    $_SESSION['error'] = "Appointment not found";
    $checkStmt->close();
    header("location: appointment.php");
    exit();
}

$checkStmt->close();

// 5. تنفيذ عملية الحذف
$sql = "DELETE FROM appointment WHERE appoid = ?";
$stmt = $database->prepare($sql);

if(!$stmt) {
    $_SESSION['error'] = "Query preparation failed: " . $database->error;
    header("location: appointment.php");
    exit();
}

$stmt->bind_param("i", $id);
if($stmt->execute()) {
    if($stmt->affected_rows > 0) {
        $_SESSION['success'] = "Appointment cancelled successfully";
    } else {
        $_SESSION['error'] = "No appointment was deleted";
    }
} else {
    $_SESSION['error'] = "Failed to delete appointment: " . $stmt->error;
}

$stmt->close();
$database->close();

// 6. إعادة التوجيه
header("location: appointment.php");
exit();
?>