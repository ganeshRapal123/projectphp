<?php
session_start();
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];

// Function to generate a numeric OTP
function generateNumericOTP() {
    return sprintf('%06d', mt_rand(0, 999999));
}

// Function to store OTP in the database
function storeOTP($pdo, $user_id, $otp) {
    $expires_at = date('Y-m-d H:i:s', strtotime('+2 minutes'));

    // Remove any existing OTP for the user
    $stmt = $pdo->prepare("DELETE FROM otp_codes WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Insert the new OTP
    $stmt = $pdo->prepare("INSERT INTO otp_codes (user_id, otp, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $otp, $expires_at]);
}

// Function to send OTP via email
function sendOTP($email, $otp) {
    $subject = "Your OTP Code";
    $message = "Your OTP code is: $otp. It is valid for 2 minutes.";
    $headers = "From: no-reply@yourdomain.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    return mail($email, $subject, $message, $headers);
}

// Handle OTP resend request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['resend'])) {
    // Generate a new OTP
    $otp = generateNumericOTP();

    // Store the new OTP in the database
    storeOTP($pdo, $user_id, $otp);

    // Send the new OTP to the user's email
    if (sendOTP($email, $otp)) {
        echo "<p style='color:green;'>A new OTP has been sent to your email.</p>";
    } else {
        echo "<p style='color:red;'>Failed to send OTP. Please try again.</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Resend OTP</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Resend OTP</h2>
        <form method="POST">
            <button type="submit" name="resend">Resend OTP</button>
        </form>
        <p><a href="verify_otp.php">Back to OTP verification</a></p>
    </div>
</body>
</html>
