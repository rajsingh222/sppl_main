<?php
// Database configuration
$host = 'localhost';
$dbname = 'u571784600_sppl_database';
$username = 'u571784600_sppl_db_main';
$password = 'R#nG3K#11@_G@U$$'; // dummy password

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false
        ]
    );
} catch (PDOException $e) {
    // Do NOT output anything here - let the main script handle errors
    throw new Exception('Database connection failed: ' . $e->getMessage());
}