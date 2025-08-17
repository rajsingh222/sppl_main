<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Handle preflight OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    http_response_code(200);
    exit();
}

// Allow cross-origin requests for actual requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Include PDO DB connection
require_once 'db_connection.php';

// Get JSON input from the frontend
$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data["email"]) || !isset($data["password"])) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

$email = $data["email"];
$password = $data["password"];

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        if (password_verify($password, $user["password"])) {
            $userObj = [
                "username" => $user["username"] ?? null,
                "email" => $user["email"] ?? null,
                "fullname" => $user["fullname"] ?? null,
                "alt_email" => $user["alt_email"] ?? null,
                "phone_number" => $user["phone_number"] ?? null,
                "address" => $user["address"] ?? null,
                "member_since" => $user["created_at"] ?? null
            ];
            echo json_encode(["success" => true, "message" => "Login successful", "user" => $userObj]);
        } else {
            echo json_encode(["success" => false, "message" => "Incorrect password"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "User not found"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "An error occurred: " . $e->getMessage()]);
}
?>
