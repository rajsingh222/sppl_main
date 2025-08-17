<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Require your DB connection file
    require 'db_connection.php'; // this defines $pdo

    // Test query to confirm connection
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll();

    echo "✅ Connected successfully!<br>";
    echo "Tables:<br>";
    foreach ($tables as $table) {
        echo "- " . $table[0] . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
