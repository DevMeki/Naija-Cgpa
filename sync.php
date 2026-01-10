<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized', 'isLoggedIn' => false]);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = file_get_contents('php://input');

    try {
        $stmt = $pdo->prepare("INSERT INTO academic_data (user_id, data) VALUES (?, ?) 
                                ON DUPLICATE KEY UPDATE data = VALUES(data)");
        $stmt->execute([$user_id, $data]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("SELECT data FROM academic_data WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch();

        echo json_encode([
            'success' => true,
            'data' => $row ? json_decode($row['data'], true) : null,
            'isLoggedIn' => true,
            'username' => $_SESSION['username']
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>