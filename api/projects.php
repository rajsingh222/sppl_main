<?php
// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    http_response_code(200);
    exit();
}

// Allow cross-origin requests for actual requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// // Database connection settings (update these for your environment)
// $dsn = "mysql:host=localhost;dbname=u571784600_login_database;charset=utf8mb4";
// $dbUser = "u571784600_loginDB";
// $dbPass = "ARN5/j*]h/3"; // Dummy password, replace with actual

// try {
//     $pdo = new PDO($dsn, $dbUser, $dbPass);
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// } catch (PDOException $e) {
//     http_response_code(500);
//     echo json_encode(['error' => "Database Connection Failed: " . $e->getMessage()]);
//     exit;
// }
require_once 'db_connection.php';
// Handle GET request to fetch projects for a user using user_id or email
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
        $userId = intval($_GET['user_id']);
        $stmt = $pdo->prepare("SELECT projects FROM users WHERE id = ?");
        $stmt->execute([$userId]);
    } elseif (isset($_GET['email']) && filter_var($_GET['email'], FILTER_VALIDATE_EMAIL)) {
        $email = $_GET['email'];
        $stmt = $pdo->prepare("SELECT projects FROM users WHERE email = ?");
        $stmt->execute([$email]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Valid User ID or Email is required']);
        exit;
    }

    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        // Decode JSON string into an associative array
        $projects = json_decode($data['projects'], true);
        echo json_encode(['projects' => $projects]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
    }
    exit;
}

// Handle POST request to update projects for a user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input from the request body
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit;
    }

    // Identify the user by user_id or email
    if (isset($input['user_id']) && is_numeric($input['user_id'])) {
        $identifier = $input['user_id'];
        $query = "UPDATE users SET projects = :projects WHERE id = :identifier";
    } elseif (isset($input['email']) && filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $identifier = $input['email'];
        $query = "UPDATE users SET projects = :projects WHERE email = :identifier";
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Valid User ID or Email is required']);
        exit;
    }

    // Ensure new projects data is provided
    if (!isset($input['projects'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Projects data is required']);
        exit;
    }

    // Encode projects data to JSON for storage
    $projectsJson = json_encode($input['projects']);

    try {
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':projects', $projectsJson);
        $stmt->bindParam(':identifier', $identifier);
        $stmt->execute();

        // Check if any row was updated
        if ($stmt->rowCount() > 0) {
            echo json_encode(['message' => 'Projects updated successfully']);
        } else {
            // Optionally handle cases where no row was affected
            http_response_code(404);
            echo json_encode(['error' => 'User not found or no changes made']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => "Database Error: " . $e->getMessage()]);
    }
    exit;
}

// For any other HTTP methods, return 405 Method Not Allowed.
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>
