<?php
session_start();
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify'])) {
    $otp = trim($_POST['otp']); // Trim white spaces from the OTP input
    $user_id = $_SESSION['user_id'];

    try {
        // Prepare the query to check the OTP
        $stmt = $pdo->prepare("SELECT * FROM otp_codes WHERE user_id = ? AND otp = ? AND expires_at > NOW()");
        $stmt->execute([$user_id, $otp]);

        if ($stmt->rowCount() > 0) {
            // OTP verified successfully
            echo "<p style='color:green;'>OTP verified successfully. You can now <a href='profile.php'>view your profile</a>.</p>";
            
            // Optionally, delete the OTP after successful verification
            $stmt = $pdo->prepare("DELETE FROM otp_codes WHERE user_id = ? AND otp = ?");
            $stmt->execute([$user_id, $otp]);

        } else {
            // Invalid or expired OTP
            echo "<p style='color:red;'>Invalid or expired OTP. Please try again.</p>";
        }
    } catch (PDOException $e) {
        // Handle potential database errors
        echo "<p style='color:red;'>An error occurred: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Verify OTP</h2>
        <form method="POST">
            <label for="otp">OTP:</label>
            <input type="text" id="otp" name="otp" required><br>
            <button type="submit" name="verify">Verify</button>
        </form>
        <p>If you didn't receive the OTP, <a href="resend_otp.php">click here</a> to resend it.</p>
    </div>
</body>
</html>
