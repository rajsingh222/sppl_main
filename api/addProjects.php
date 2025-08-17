<?php
// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Only POST method allowed."]);
    exit;
}

// Directories
$uploadDirImages = "uploads/images/";
$uploadDirVideos = "uploads/videos/";
@mkdir($uploadDirImages, 0777, true);
@mkdir($uploadDirVideos, 0777, true);

function saveFiles($files, $dir) {
    $urls = [];
    foreach ($files['tmp_name'] as $index => $tmpName) {
        $originalName = basename($files['name'][$index]);
        $sanitizedFileName = preg_replace('/[^A-Za-z0-9_.-]/', '_', $originalName);
        $fileName = uniqid() . '_' . $sanitizedFileName;
        $targetPath = $dir . $fileName;
        if (move_uploaded_file($tmpName, $targetPath)) {
            $urls[] = "https://spplindia.org/api/$targetPath";
        }
    }
    return $urls;
}

// Extract form data
$email = $_POST['email'] ?? '';
$project_title = $_POST['project_title'] ?? '';
$area = $_POST['area'] ?? '';
$city = $_POST['city'] ?? '';
$state = $_POST['state'] ?? '';
$country = $_POST['country'] ?? '';
$structure_type = $_POST['structure_type'] ?? '';
$structure_name = $_POST['structure_name'] ?? '';
$latitude = $_POST['latitude'] ?? '';
$longitude = $_POST['longitude'] ?? '';
$structure_dimension = $_POST['structure_dimension'] ?? '';
$construction_material = $_POST['construction_material'] ?? '';
$construction_date = $_POST['construction_date'] ?? '';

// Validation
if (!$email || !$project_title || !$structure_name) {
    http_response_code(400);
    echo json_encode(['error' => 'Required fields missing']);
    exit;
}

try {
    // Database connection
    require_once 'db_connection.php'; // This should define $pdo

    // Fetch user ID by email or session (you need to implement this logic properly)
    $stmtUser = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmtUser->execute([$email]);
    $user = $stmtUser->fetch();

    if (!$user) {
        http_response_code(404);
        echo json_encode(["error" => "User not found"]);
        exit;
    }

    $userId = $user['id'];

    // Handle file uploads
    $imageUrls = isset($_FILES['project_images']) ? saveFiles($_FILES['project_images'], $uploadDirImages) : [];
    $videoUrls = isset($_FILES['project_videos']) ? saveFiles($_FILES['project_videos'], $uploadDirVideos) : [];

    // Insert project
    $stmt = $pdo->prepare("
        INSERT INTO projects (
            user_id, 
            project_title, 
            area, 
            city, 
            state, 
            country, 
            structure_type, 
            structure_name, 
            latitude, 
            longitude, 
            structure_dimension, 
            construction_material, 
            construction_date, 
            project_images, 
            project_videos
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $userId,
        $project_title,
        $area,
        $city,
        $state,
        $country,
        $structure_type,
        $structure_name,
        $latitude,
        $longitude,
        $structure_dimension,
        $construction_material,
        $construction_date,
        json_encode($imageUrls, JSON_UNESCAPED_SLASHES),
        json_encode($videoUrls, JSON_UNESCAPED_SLASHES)
    ]);

    echo json_encode(["message" => "Project successfully uploaded."]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database Error: " . $e->getMessage()]);
}
