<?php
require "vendor/autoload.php";

function getDatabaseConnection() {
    $host = 'localhost';
    $dbname = 'examsystem';
    $username = 'root';
    $password = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

try {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->query("SELECT DISTINCT subject_name FROM student_answers");
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['subjects' => $subjects]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to retrieve subjects: ' . $e->getMessage()]);
}
?>
