<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS
$allowed_origins = [
    "https://dashboard.spplindia.org",
    "http://localhost:5173"
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
}

header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

// DB connection (must define $pdo)
require_once 'db_connection.php';

// Sanitize input
$fullName      = trim($_POST['fullName'] ?? '');
$email         = trim($_POST['email'] ?? '');
$userName      = trim($_POST['userName'] ?? '');
$pass          = $_POST['password'] ?? '';
$alt_email     = trim($_POST['alternateEmail'] ?? '');
$address       = trim($_POST['address'] ?? '');
$phone_number  = trim($_POST['phoneNumber'] ?? '');
$idProofType   = trim($_POST['idProofType'] ?? '');
$idProofNumber = trim($_POST['idProofNumber'] ?? '');

// Validate password strength
function validatePassword($password) {
    return strlen($password) >= 8 &&
           preg_match('/[A-Z]/', $password) &&
           preg_match('/[a-z]/', $password) &&
           preg_match('/[0-9]/', $password) &&
           preg_match('/[\W_]/', $password);
}

if (!validatePassword($pass)) {
    http_response_code(400);
    die("Password must be at least 8 characters long and include uppercase, lowercase, number, and symbol.");
}

// Check duplicate email
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    http_response_code(409);
    die("Email address already exists.");
}
$userName = trim($_POST['userName'] ?? '');
if ($userName === '') {
    $userName = null;
}



// Hash password
$hashedPassword = password_hash($pass, PASSWORD_DEFAULT);

// Insert new user
$insertStmt = $pdo->prepare("
    INSERT INTO users 
    (fullname, email, username, password, address, phone_number, alt_email, id_proof_type, id_proof_number)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if ($insertStmt->execute([
    $fullName, $email, $userName, $hashedPassword,
    $address, $phone_number, $alt_email, $idProofType, $idProofNumber
])) {
    // Response
    header('Content-Type: application/json');
    echo json_encode([
        "success" => true,
        "message" => "User registered successfully",
        "user"    => [
            "fullname"      => $fullName,
            "username"      => $userName,
            "email"         => $email,
            "address"       => $address,
            "alt_email"     => $alt_email,
            "phone_number"  => $phone_number,
            "idProofType"   => $idProofType,
            "idProofNumber" => $idProofNumber
        ]
    ]);
} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        "success" => false,
        "message" => "Registration failed: " . $insertStmt->errorInfo()[2]
    ]);
}
