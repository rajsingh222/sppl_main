<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$host = 'localhost';
$dbname = 'u571784600_sensor';
$user = 'u571784600_sensor';
$pass = 'K3darn@th'; // change to your actual password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT * FROM shahjahapurftp_data ORDER BY id DESC LIMIT 20");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Reverse to chronological order
    $rows = array_reverse($rows);

    // JSON decode tm_1 and tm_2
    foreach ($rows as &$row) {
        $row['tm_1'] = json_decode($row['tm_1'], true);
        $row['tm_2'] = json_decode($row['tm_2'], true);
    }

    echo json_encode($rows);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
