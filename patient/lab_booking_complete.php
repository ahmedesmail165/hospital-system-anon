<?php
require_once 'vendor/autoload.php';
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION["user"]) || $_SESSION["user"] == "" || $_SESSION['usertype'] != 'p') {
    header("location: ../login.php");
    exit();
}

$useremail = $_SESSION["user"];
include("../connection.php");

// جلب بيانات المريض
$userrow = $database->query("SELECT * FROM patient WHERE pemail='$useremail'");
$userfetch = $userrow->fetch_assoc();
$userid = $userfetch["pid"];
$username = $userfetch["pname"];

// معالجة طلب الحجز
if ($_POST && isset($_POST["booknow"])) {
    $apponum = $_POST["apponum"];
    $scheduleid = $_POST["scheduleid"];
    $date = $_POST["date"];
    
    // بدء المعاملة
    $database->begin_transaction();

    try {
        // إدخال الموعد في جدول حجوزات المعمل
        $sql1 = "INSERT INTO lab_appointments (pid, apponum, schedule_id, appodate, status) 
                VALUES (?, ?, ?, ?, 'Pending')";
        $stmt1 = $database->prepare($sql1);
        $stmt1->bind_param("iiis", $userid, $apponum, $scheduleid, $date);
        $stmt1->execute();
        $appoid = $database->insert_id;
        $stmt1->close();

        // إعداد البريد الإلكتروني
        $transport = (new Swift_SmtpTransport('smtp.gmail.com', 587))
            ->setUsername('mostfahatem669@gmail.com')
            ->setPassword('ixok fpmb jqro gyex')
            ->setEncryption('tls');

        $options = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
        $transport->setStreamOptions($options);

        $mailer = new Swift_Mailer($transport);

        // محتوى البريد
        $emailContent = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .header { color: #2c3e50; font-size: 24px; }
                    .details { margin: 20px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #3498db; }
                </style>
            </head>
            <body>
                <div class='header'>Lab Test Booking Confirmation</div>
                <p>Dear $username,</p>
                
                <div class='details'>
                    <strong>Appointment Number:</strong> $apponum<br>
                    <strong>Test Date:</strong> $date<br>
                </div>
                
                <p>Thank you for choosing our lab services!</p>
            </body>
            </html>";

        $message = (new Swift_Message('Lab Test Booking Confirmation'))
            ->setFrom(['mostfahatem669@gmail.com' => 'Opticare Hospital'])
            ->setTo([$useremail => $username])
            ->setBody($emailContent, 'text/html');

        // إرسال البريد
        try {
            $result = $mailer->send($message);
            
            if ($result) {
                $database->commit();
                header("location: appointment.php?action=lab-booking-added&id=".$apponum);
                exit();
            } else {
                throw new Exception("Failed to send email (no error details available)");
            }
        } catch (Exception $e) {
            throw new Exception("Email sending failed: " . $e->getMessage());
        }

    } catch (Exception $e) {
        $database->rollback();
        die("Error: " . $e->getMessage());
    }
}
?>