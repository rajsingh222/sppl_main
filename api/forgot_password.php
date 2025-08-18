<?php
header("Access-Control-Allow-Origin: *"); // Allow specific origin
header("Access-Control-Allow-Methods: POST, OPTIONS"); // Allowed HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allowed headers
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit;
}
require 'db_connection.php'; // Your database connection file

$data = json_decode(file_get_contents("php://input"), true);

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}

try {
    $email = $data['email'];
    $token = bin2hex(random_bytes(50));
    $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Store token in password_resets table
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expires]);

        // Send email with reset link (implementation example)
        $resetLink = "https://spplindia.org/api/reset_password.php?token=$token";
        $to = $email;
        $subject = "Password Reset Request";
        $message = "Click the link to reset your password: $resetLink";
        $headers = "From: no-reply@spplindia.org";

        // In production, use proper email library like PHPMailer
        mail($to, $subject, $message, $headers);
    }

    // Always return success to prevent email enumeration
    echo json_encode(['message' => 'If an account exists, a reset email has been sent']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}