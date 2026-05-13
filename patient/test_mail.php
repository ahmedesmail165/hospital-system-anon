<?php
require_once 'mail_config.php';

// Test the connection
if (testMailConnection()) {
    echo "Connection test successful!\n";
} else {
    echo "Connection test failed. Check error logs for details.\n";
}

// Test sending an email
$to = 'your-email@gmail.com'; // Replace with your email
$subject = 'Test Email';
$body = 'This is a test email to verify the configuration.';

if (sendEmail($to, $subject, $body)) {
    echo "Test email sent successfully!\n";
} else {
    echo "Failed to send test email. Check error logs for details.\n";
}
?> 