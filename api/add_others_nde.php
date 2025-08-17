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

// Define the upload directory for reports
$uploadDirReports = "uploads/reports/";
if (!is_dir($uploadDirReports)) {
    mkdir($uploadDirReports, 0777, true);
}

/**
 * Save uploaded files and return an array of their URLs
 * @param array $files The $_FILES['reports'] array
 * @param string $dir  Directory to save the files
 * @return array       List of public URLs for saved files
 */
function saveFiles($files, $dir) {
    $urls = [];
    foreach ($files['tmp_name'] as $index => $tmpName) {
        if (!empty($tmpName)) {
            $originalName = basename($files['name'][$index]);
            $sanitizedFileName = str_replace(' ', '_', $originalName);
            $fileName = uniqid() . '_' . $sanitizedFileName;
            $targetPath = $dir . $fileName;
            if (move_uploaded_file($tmpName, $targetPath)) {
                // Adjust URL path as needed
                $urls[] = "https://spplindia.org/api/" . $targetPath;
            }
        }
    }
    return $urls;
}

// Retrieve the project ID from POST data
$projectId = $_POST['project_id'] ?? '';
if (!$projectId) {
    http_response_code(400);
    echo json_encode(["error" => "Project ID is required."]);
    exit;
}

try {
    // Database connection
    require_once 'db_connection.php';
    // Retrieve existing JSON array of report URLs
    $stmt = $pdo->prepare("SELECT others_nde_reports FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        http_response_code(404);
        echo json_encode(["error" => "Project not found."]);
        exit;
    }

    // Decode existing reports or start fresh
    $existingReports = json_decode($project['others_nde_reports'], true);
    if (!is_array($existingReports)) {
        $existingReports = [];
    }

    // Process newly uploaded report files under 'reports' key
    $newReportUrls = [];
    if (isset($_FILES['reports'])) {
        $newReportUrls = saveFiles($_FILES['reports'], $uploadDirReports);
    }

    // Merge and update in database
    $merged = array_merge($existingReports, $newReportUrls);
    $stmtUpdate = $pdo->prepare("UPDATE projects SET others_nde_reports = ? WHERE id = ?");
    $stmtUpdate->execute([json_encode($merged, JSON_UNESCAPED_SLASHES), $projectId]);

    echo json_encode([
        "message" => "Reports successfully updated.",
        "others_nde_reports" => $merged
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB Error: " . $e->getMessage()]);
}
?>
