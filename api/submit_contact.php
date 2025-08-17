<?php
// Allow from localhost (or use "*" to allow any origin)
header("Access-Control-Allow-Origin: https://dashboard.spplindia.org");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// If this is a preflight request, just return 200
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ——— The rest of your script follows ———

// 1) Collect and validate form inputs
$name    = isset($_POST['name'])    ? trim($_POST['name'])    : '';
$email   = isset($_POST['email'])   ? trim($_POST['email'])   : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if (empty($name) || empty($email) || empty($message)) {
    http_response_code(400);
    echo 'Please fill in all required fields.';
    exit;
}

// 2) Setup addresses
$adminEmail = 'csadmin@spplindia.org';
$fromEmail  = 'no-reply@spplindia.org';

// 3) Create a unique MIME boundary
$boundary = '==Multipart_Boundary_x' . md5(time()) . 'x';

// 4) Build headers for the admin email
$headers  = "From: {$fromEmail}\r\n";
$headers .= "Reply-To: {$email}\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";

// 5) Start the message body (text part)
$body  = "--{$boundary}\r\n";
$body .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";
$body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
$body .= "You have received a new contact form submission:\r\n\r\n";
$body .= "Name:    {$name}\r\n";
$body .= "Email:   {$email}\r\n\r\n";
$body .= "Message:\r\n{$message}\r\n\r\n";

// 6) Attach any uploaded files
if (!empty($_FILES['attachments']['name'][0])) {
    foreach ($_FILES['attachments']['name'] as $i => $filename) {
        $tmpPath  = $_FILES['attachments']['tmp_name'][$i];
        $fileType = $_FILES['attachments']['type'][$i];

        // Read the file and encode it
        $fileData = chunk_split(base64_encode(file_get_contents($tmpPath)));

        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: {$fileType}; name=\"{$filename}\"\r\n";
        $body .= "Content-Disposition: attachment; filename=\"{$filename}\"\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= $fileData . "\r\n\r\n";
    }
}

// 7) End the MIME message
$body .= "--{$boundary}--\r\n";

// 8) Send to admin
if (!mail($adminEmail, "New Contact from {$name}", $body, $headers)) {
    http_response_code(500);
    echo 'Failed to deliver message to admin.';
    exit;
}

// 9) Send confirmation back to the user
$confirmSubject = 'Thank you for contacting SPPL India';
$confirmBody    = "Hello {$name},\n\n"
                . "Thanks for reaching out to SPPL India. We have received your message and will get back to you soon.\n\n"
                . "Best regards,\n"
                . "The SPPL India Team";

$confirmHeaders  = "From: {$fromEmail}\r\n";
$confirmHeaders .= "MIME-Version: 1.0\r\n";
$confirmHeaders .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";

mail($email, $confirmSubject, $confirmBody, $confirmHeaders);

// 10) Return success
echo 'success';
?>
