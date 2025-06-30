<?php

function generateVerificationCode() {
    return strval(rand(100000, 999999));
}

function registerEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!in_array($email, $emails)) {
        file_put_contents($file, $email . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

function unsubscribeEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $emails = array_filter($emails, fn($e) => trim($e) !== trim($email));
    file_put_contents($file, implode(PHP_EOL, $emails) . PHP_EOL, LOCK_EX);
}

function sendVerificationEmail($email, $code) {
    $subject = "Your Verification Code";
    $message = "<p>Your verification code is: <strong>$code</strong></p>";
    $headers = "From: no-reply@example.com\r\n";
    $headers .= "Content-type: text/html\r\n";
    mail($email, $subject, $message, $headers);
}

function sendUnsubscribeCode($email, $code) {
    $subject = "Confirm Unsubscription";
    $message = "<p>To confirm unsubscription, use this code: <strong>$code</strong></p>";
    $headers = "From: no-reply@example.com\r\n";
    $headers .= "Content-type: text/html\r\n";
    mail($email, $subject, $message, $headers);
}

function fetchGitHubTimeline() {
    // GitHub timeline is deprecated, but for demo, we'll fetch the public events API
    $url = 'https://api.github.com/events';
    $opts = [
        "http" => [
            "header" => "User-Agent: PHP\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    $data = file_get_contents($url, false, $context);
    return $data ? json_decode($data, true) : [];
}

function formatGitHubData($data) {
    $html = '<h2>GitHub Timeline Updates</h2>';
    $html .= '<table border="1"><tr><th>Event</th><th>User</th></tr>';
    $count = 0;
    foreach ($data as $event) {
        if ($count++ >= 10) break;
        $type = htmlspecialchars($event['type']);
        $user = htmlspecialchars($event['actor']['login']);
        $html .= "<tr><td>$type</td><td>$user</td></tr>";
    }
    $html .= '</table>';
    return $html;
}

function sendGitHubUpdatesToSubscribers() {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return;
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $data = fetchGitHubTimeline();
    $body = formatGitHubData($data);

    foreach ($emails as $email) {
        $unsubscribe_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/unsubscribe.php?email=" . urlencode($email);
        $full_body = $body . '<p><a href="' . $unsubscribe_link . '" id="unsubscribe-button">Unsubscribe</a></p>';
        $subject = "Latest GitHub Updates";
        $headers = "From: no-reply@example.com\r\n";
        $headers .= "Content-type: text/html\r\n";
        mail($email, $subject, $full_body, $headers);
    }
}
