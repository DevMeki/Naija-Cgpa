<?php
require_once 'db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS shared_results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        uuid VARCHAR(32) NOT NULL UNIQUE,
        data JSON NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql);
    echo "Table 'shared_results' created successfully.";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
