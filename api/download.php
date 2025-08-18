<?php
// download.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1) get & sanitize
if (empty($_GET['url'])) {
    http_response_code(400);
    exit('No file specified.');
}
$fileUrl = filter_var($_GET['url'], FILTER_SANITIZE_URL);
if (!filter_var($fileUrl, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    exit('Invalid URL.');
}

// 2) restrict to your domain & folder
$parts = parse_url($fileUrl);
if (
    empty($parts['host']) ||
    strcasecmp($parts['host'], 'spplindia.org') !== 0 ||
    strpos($parts['path'], '/api/uploads/reports/') !== 0
) {
    http_response_code(403);
    exit('Unauthorized file location.');
}

// 3) derive a safe filename
$filename = basename($parts['path']);
if (!preg_match('/^[A-Za-z0-9_\-\.]+$/', $filename)) {
    http_response_code(400);
    exit('Invalid file name.');
}

// 4) HEAD-check via cURL (with redirects)
$ch = curl_init($fileUrl);
curl_setopt($ch, CURLOPT_NOBODY,         true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT,        10);
curl_exec($ch);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$filesize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
curl_close($ch);

if ($httpCode < 200 || $httpCode >= 300) {
    // bubble the real code back to the browser
    http_response_code($httpCode);
    exit("Remote server returned HTTP $httpCode");
}

// 5) force-download headers
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
if ($filesize > 0) {
    header('Content-Length: ' . $filesize);
}

// 6) GET stream via cURL
$ch = curl_init($fileUrl);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
curl_setopt($ch, CURLOPT_HEADER,         false);
curl_setopt($ch, CURLOPT_TIMEOUT,        0);
curl_exec($ch);

if (curl_errno($ch)) {
    error_log('cURL error: ' . curl_error($ch));
}
curl_close($ch);
exit;
