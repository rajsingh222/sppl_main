<?php
// Handle preflight OPTIONS request to avoid CORS issues
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
header("Content-Type: application/json");

require_once 'db_connection.php';

// Ensure an email parameter is provided and valid
if (!isset($_GET['email']) || !filter_var($_GET['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'A valid email is required']);
    exit;
}

$email = $_GET['email'];

// Fetch the user based on the email
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
}

$userId = $user['id'];

// Fetch all projects associated with the user using user_id
$stmt = $pdo->prepare("SELECT id, user_id, project_title, structure_type, structure_name, project_images, project_videos, created_at, updated_at, longitude, latitude, country, state, city, area, construction_material, construction_date, structure_dimension, sppl_nde_reports,	sppl_dpc_reports,	sppl_lt_reports,	others_nde_reports,	others_dpc_reports,	others_lt_reports, structure_model, sensor_info, alarms FROM projects WHERE user_id = ?");
$stmt->execute([$userId]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return the projects in JSON format
echo json_encode(['projects' => $projects]);
?>
