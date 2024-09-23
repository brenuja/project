<?php
session_start(); // Start session to store user data

require 'vendor/phpmailer/autoload.php'; // Ensure the path is correct

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database credentials
$servername = "localhost";
$username = "Uptime_mng123";
$password = "uptimemng";
$dbname = "uptimemng";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $alert_when = $_POST['alert_when'] ?? null; // Use null coalescing to handle undefined index
    $web_url = $_POST['monitor_url'] ?? null;
    $notifications = isset($_POST['notify']) ? implode(", ", $_POST['notify']) : '';
    $email = $_POST['email'] ?? null;

    // Debugging output
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $alert_when = $_POST['alert_when'] ?? null;
        $web_url = $_POST['monitor_url'] ?? null;
        $notifications = isset($_POST['notify']) ? implode(", ", $_POST['notify']) : '';
        $email = $_POST['email'] ?? null;

        // Debugging output
        echo "Email: " . htmlspecialchars($email) . "<br>";
        echo "Alert When: " . htmlspecialchars($alert_when) . "<br>";
        echo "Web URL: " . htmlspecialchars($web_url) . "<br>";
        echo "Notifications: " . htmlspecialchars($notifications) . "<br>";

        // Check if variables are not empty
        if (!empty($email) && !empty($alert_when) && !empty($web_url)) {
            // Prepare and bind the statement to avoid SQL injection
            $stmt = $conn->prepare("INSERT INTO alerts (alert_when, web_url, email, notifications) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                die("Preparation failed: " . $conn->error);
            }
            $stmt->bind_param("ssss", $email, $alert_when, $web_url, $notifications);

            if ($stmt->execute()) {
                echo "Alert settings submitted successfully!<br>";
                checkWebsiteStatus($web_url, $notifications, $email);
            } else {
                echo "Error executing statement: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Please fill out all required fields.";
        }
    } else {
        echo "Please fill out all required fields.";
    }
}

$conn->close();

function checkWebsiteStatus($url, $notifications, $recipientEmail)
{
    $response = @get_headers($url); // Get the headers of the URL

    // Check if the response is false or the status code is not 200
    if ($response === false || strpos($response[0], '200') === false) {
        sendEmailAlert($url, $notifications, $recipientEmail, "down"); // Send email if down
    } else {
        sendEmailAlert($url, $notifications, $recipientEmail, "up"); // Send email if up
    }
}

function sendEmailAlert($web_url, $notifications, $recipientEmail, $status)
{
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Set your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'sahu92083@gmail.com'; // Your email
        $mail->Password = 'Rahul@2004'; // Your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('sahu92083@gmail.com', 'Alert System');
        $mail->addAddress($recipientEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Website Monitoring Alert';

        if ($status === "down") {
            $mail->Body = "Website URL: $web_url<br>Notifications: $notifications<br>Status: Website is DOWN!";
        } else {
            $mail->Body = "Website URL: $web_url<br>Notifications: $notifications<br>Status: Website is UP!";
        }

        $mail->send();
        echo 'Email alert has been sent to ' . htmlspecialchars($recipientEmail);
    } catch (Exception $e) {
        echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>