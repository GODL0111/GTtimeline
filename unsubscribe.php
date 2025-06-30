<?php
require_once 'functions.php';

session_start();
$message = '';

if (isset($_GET['email'])) {
    $_SESSION['unsubscribe_email'] = $_GET['email'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['unsubscribe_email'])) {
        $email = trim($_POST['unsubscribe_email']);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $code = generateVerificationCode();
            $_SESSION['unsubscribe_email'] = $email;
            $_SESSION['unsubscribe_code'] = $code;
            sendUnsubscribeCode($email, $code);
            $message = "Unsubscribe code sent to your email.";
        } else {
            $message = "Invalid email address.";
        }
    } elseif (isset($_POST['unsubscribe_verification_code'])) {
        $user_code = trim($_POST['unsubscribe_verification_code']);
        if (isset($_SESSION['unsubscribe_code'], $_SESSION['unsubscribe_email'])) {
            if ($user_code === $_SESSION['unsubscribe_code']) {
                unsubscribeEmail($_SESSION['unsubscribe_email']);
                $message = "You have been unsubscribed.";
                unset($_SESSION['unsubscribe_code'], $_SESSION['unsubscribe_email']);
            } else {
                $message = "Invalid code.";
            }
        } else {
            $message = "No unsubscribe code requested.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Unsubscribe from GitHub Timeline</title>
</head>
<body>
    <h1>Unsubscribe from GitHub Timeline Updates</h1>
    <?php if ($message) echo "<p>$message</p>"; ?>
    <form method="POST">
        <input type="email" name="unsubscribe_email" required value="<?php echo isset($_SESSION['unsubscribe_email']) ? htmlspecialchars($_SESSION['unsubscribe_email']) : ''; ?>">
        <button id="submit-unsubscribe">Unsubscribe</button>
    </form>
    <form method="POST">
        <input type="text" name="unsubscribe_verification_code">
        <button id="verify-unsubscribe">Verify</button>
    </form>
</body>
</html>
