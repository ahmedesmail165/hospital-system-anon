<?php
// SwiftMailer Configuration
require_once __DIR__ . '/vendor/autoload.php';

// Create the Transport with more permissive SSL settings
$transport = (new Swift_SmtpTransport('smtp.gmail.com', 587))
    ->setUsername('your-email@gmail.com')
    ->setPassword('your-app-password')
    ->setEncryption('tls')
    ->setStreamOptions([
        'ssl' => [
            'allow_self_signed' => true,
            'verify_peer' => false,
            'verify_peer_name' => false,
            'disable_compression' => true,
            'ciphers' => 'ALL:!ADH:!LOW:!EXP:!MD5:@STRENGTH',
        ],
    ]);

// Set timeout to a higher value
$transport->setTimeout(30);

// Create the Mailer using your created Transport
$mailer = new Swift_Mailer($transport);

// Function to send email with better error handling
function sendEmail($to, $subject, $body) {
    global $mailer;
    
    try {
        // Create a message
        $message = (new Swift_Message($subject))
            ->setFrom(['your-email@gmail.com' => 'Your Name'])
            ->setTo([$to])
            ->setBody($body, 'text/html');
        
        // Send the message
        $result = $mailer->send($message);
        return true;
    } catch (Swift_TransportException $e) {
        error_log('SwiftMailer Transport Error: ' . $e->getMessage());
        return false;
    } catch (Exception $e) {
        error_log('General Error: ' . $e->getMessage());
        return false;
    }
}

// Test connection function
function testMailConnection() {
    global $transport;
    try {
        $transport->start();
        return true;
    } catch (Exception $e) {
        error_log('Connection Test Failed: ' . $e->getMessage());
        return false;
    }
}
?> 