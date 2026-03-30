<?php
// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

header('Content-Type: application/json');

// ── Configuration ──────────────────────────────────────────────
define('RECIPIENT_EMAIL', 'info@trinovalabs.ai');
define('RECIPIENT_NAME',  'TriNova AI Labs');
define('FROM_DOMAIN',     'trinovalabs.ai');
// ───────────────────────────────────────────────────────────────

/**
 * Sanitise a plain-text field: strip tags, trim whitespace.
 */
function clean(string $value): string {
    return trim(strip_tags($value));
}

/**
 * Remove newlines from a value that will go into a mail header.
 */
function clean_header(string $value): string {
    return str_replace(["\r", "\n", "%0a", "%0d"], '', clean($value));
}

// ── Collect & validate input ────────────────────────────────────
$errors = [];

$first_name = clean_header($_POST['first_name'] ?? '');
$last_name  = clean_header($_POST['last_name']  ?? '');
$email      = clean_header($_POST['email']      ?? '');
$company    = clean($_POST['company']    ?? '');
$interest   = clean($_POST['interest']   ?? '');
$message    = clean($_POST['message']    ?? '');

if (empty($first_name)) $errors[] = 'First name is required.';
if (empty($last_name))  $errors[] = 'Last name is required.';

if (empty($email)) {
    $errors[] = 'Email address is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email address is invalid.';
}

if (empty($message)) $errors[] = 'Please include a message.';

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// ── Build email ─────────────────────────────────────────────────
$full_name   = $first_name . ' ' . $last_name;
$from_header = 'noreply@' . FROM_DOMAIN;

$subject = 'New Enquiry from ' . $full_name . ' — TriNova AI Labs';

$body  = "You have received a new enquiry via the TriNova AI Labs website.\n";
$body .= str_repeat('-', 60) . "\n\n";
$body .= "Name    : {$full_name}\n";
$body .= "Email   : {$email}\n";
$body .= "Company : " . ($company  ?: '(not provided)') . "\n";
$body .= "Interest: " . ($interest ?: '(not provided)') . "\n\n";
$body .= "Message:\n";
$body .= wordwrap($message, 72, "\n", false) . "\n\n";
$body .= str_repeat('-', 60) . "\n";
$body .= "Sent from trinovalabs.ai contact form\n";

$headers  = "From: TriNova AI Labs Website <{$from_header}>\r\n";
$headers .= "Reply-To: {$full_name} <{$email}>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "Content-Transfer-Encoding: 8bit\r\n";
$headers .= "X-Mailer: PHP/" . PHP_VERSION . "\r\n";

// ── Send ────────────────────────────────────────────────────────
$sent = mail(
    RECIPIENT_EMAIL,
    $subject,
    $body,
    $headers
);

if ($sent) {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Enquiry sent successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Mail could not be sent. Please try again later.']);
}
