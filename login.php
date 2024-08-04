<?php
session_start();
require 'config.php';

// Function to generate OTP
function generateOTP() {
    return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
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
    $headers = "From: no-reply@yourdomain.com";

    mail($email, $subject, $message, $headers);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);

    // Check if the user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch();
        $user_id = $user['id'];

        // Generate a unique OTP
        $otp = generateOTP();

        // Store OTP in the database
        storeOTP($pdo, $user_id, $otp);

        // Send OTP to user's email
        sendOTP($email, $otp);

        // Store user ID in session
        $_SESSION['user_id'] = $user_id;
        $_SESSION['email'] = $email;

        // Redirect to OTP verification page
        header("Location: verify_otp.php");
        exit;
    } else {
        echo "Email not registered.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form method="POST">
            Email: <input type="email" name="email" required><br>
            <button type="submit" name="login">Login</button>
        </form>
    </div>
</body>
</html>
