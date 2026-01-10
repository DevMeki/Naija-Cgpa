<?php
header('Content-Type: application/json');
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit;
    }

    $uuid = bin2hex(random_bytes(16)); // Generate 32-char UUID

    try {
        $stmt = $pdo->prepare("INSERT INTO shared_results (uuid, data) VALUES (?, ?)");
        $stmt->execute([$uuid, json_encode($data)]);

        echo json_encode(['success' => true, 'uuid' => $uuid]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}
