<?php
require_once 'vendor/autoload.php';
session_start();


if (!isset($_SESSION["user"]) || $_SESSION["user"] == "" || $_SESSION['usertype'] != 'p') {
    header("location: ../login.php");
    exit();
}

$useremail = $_SESSION["user"];
include("../connection.php");


$userrow = $database->query("SELECT * FROM patient WHERE pemail='$useremail'");
if ($userrow->num_rows > 0) {
    $userfetch = $userrow->fetch_assoc();
    $userid = $userfetch["pid"];
    $username = $userfetch["pname"];
} else {
    die("Patient data not found.");
}

if ($_POST && isset($_POST["booknow"])) {
    $apponum = $_POST["apponum"];
    $scheduleid = $_POST["scheduleid"];
    $date = $_POST["date"];
    $type = $_POST["type"] ?? 'doctor'; 


    $database->begin_transaction();

    try {
     
        $sql1 = "INSERT INTO appointment(pid, apponum, scheduleid, appodate, type) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt1 = $database->prepare($sql1);
        $stmt1->bind_param("iisss", $userid, $apponum, $scheduleid, $date, $type);
        $stmt1->execute();
        $appoid = $database->insert_id;
        $stmt1->close();

    
        if ($type == 'radiology') {
            $sql2 = "INSERT INTO radiology_appointment(appoid, scheduleid) VALUES (?, ?)";
            $stmt2 = $database->prepare($sql2);
            $stmt2->bind_param("ii", $appoid, $scheduleid);
            $stmt2->execute();
            $stmt2->close();
        }

   
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
                <div class='header'>Appointment Confirmation</div>
                <p>Dear $username,</p>
                
                <div class='details'>
                    <strong>Appointment Number:</strong> $apponum<br>
                    <strong>Type:</strong> " . ucfirst($type) . "<br>
                    <strong>Date:</strong> $date<br>
                </div>
                
                <p>Thank you for choosing our clinic!</p>
            </body>
            </html>";

        $message = (new Swift_Message('Appointment Booking Confirmation'))
            ->setFrom(['mostfahatem669@gmail.com' => 'Opticare Hospital'])
            ->setTo([$useremail => $username])
            ->setBody($emailContent, 'text/html');

 
        try {
            $result = $mailer->send($message);
            
            if ($result) {
                $database->commit();
                header("location: appointment.php?action=booking-added&id=" . $apponum . "&titleget=none");
                exit();
            } else {
                throw new Exception("Failed to send email (no error details available)");
            }
        } catch (Exception $e) {
            throw new Exception("Email sending failed: " . $e->getMessage());
        }

    } catch (Exception $e) {
        $database->rollback();
 
        error_log("Booking Error: " . $e->getMessage() . " - User: $useremail");
        header("location: appointment.php?action=booking-failed&reason=email-error");
        exit();
    }
}
?>