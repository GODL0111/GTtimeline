<?php
require_once 'functions.php';

session_start();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $code = generateVerificationCode();
            $_SESSION['pending_email'] = $email;
            $_SESSION['verification_code'] = $code;
            sendVerificationEmail($email, $code);
            $message = "Verification code sent to your email.";
        } else {
            $message = "Invalid email address.";
        }
    } elseif (isset($_POST['verification_code'])) {
        $user_code = trim($_POST['verification_code']);
        if (isset($_SESSION['verification_code'], $_SESSION['pending_email'])) {
            if ($user_code === $_SESSION['verification_code']) {
                registerEmail($_SESSION['pending_email']);
                $message = "Email verified and registered for updates!";
                unset($_SESSION['verification_code'], $_SESSION['pending_email']);
            } else {
                $message = "Invalid verification code.";
            }
        } else {
            $message = "No verification code requested.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>GitHub Timeline Subscription</title>
</head>
<body>
    <h1>Subscribe to GitHub Timeline Updates</h1>
    <?php if ($message) echo "<p>$message</p>"; ?>
    <form method="POST">
        <input type="email" name="email" required>
        <button id="submit-email">Submit</button>
    </form>
    <form method="POST">
        <input type="text" name="verification_code" maxlength="6" required>
        <button id="submit-verification">Verify</button>
    </form>
</body>
</html>
