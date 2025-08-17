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

// Upload directory
$uploadDirReports = "uploads/reports/";
if (!is_dir($uploadDirReports)) {
    mkdir($uploadDirReports, 0777, true);
}

/**
 * Save uploaded files and return an array of public URLs.
 */
function saveFiles($files, $dir) {
    $urls = [];
    foreach ($files['tmp_name'] as $index => $tmpName) {
        if (!empty($tmpName)) {
            $originalName = basename($files['name'][$index]);
            $sanitizedFileName = preg_replace('/[^A-Za-z0-9_.-]/', '_', $originalName);
            $fileName = uniqid() . '_' . $sanitizedFileName;
            $targetPath = $dir . $fileName;
            if (move_uploaded_file($tmpName, $targetPath)) {
                $urls[] = "https://spplindia.org/api/" . $targetPath;
            }
        }
    }
    return $urls;
}

// Get project ID
$projectId = $_POST['project_id'] ?? '';
if (!$projectId) {
    http_response_code(400);
    echo json_encode(["error" => "Project ID is required."]);
    exit;
}

try {
    // Include DB connection
    require_once 'db_connection.php'; // should define $pdo

    // Get existing project record
    $stmt = $pdo->prepare("SELECT others_dpc_reports FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        http_response_code(404);
        echo json_encode(["error" => "Project not found."]);
        exit;
    }

    // Decode existing report URLs
    $existingReports = json_decode($project['others_dpc_reports'], true);
    if (!is_array($existingReports)) {
        $existingReports = [];
    }

    // Upload new reports
    $newReportUrls = [];
    if (isset($_FILES['reports'])) {
        $newReportUrls = saveFiles($_FILES['reports'], $uploadDirReports);
    }

    // Merge and update
    $mergedReports = array_merge($existingReports, $newReportUrls);
    $stmtUpdate = $pdo->prepare("UPDATE projects SET others_dpc_reports = ? WHERE id = ?");
    $stmtUpdate->execute([
        json_encode($mergedReports, JSON_UNESCAPED_SLASHES),
        $projectId
    ]);

    echo json_encode([
        "message" => "Reports successfully updated.",
        "others_dpc_reports" => $mergedReports
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB Error: " . $e->getMessage()]);
}
?>
