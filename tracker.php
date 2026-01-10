<?php
require_once 'db.php';

function trackVisit($pdo)
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $url = $_SERVER['REQUEST_URI'] ?? 'unknown';

    try {
        $stmt = $pdo->prepare("INSERT INTO site_visits (ip_address, page_url) VALUES (?, ?)");
        $stmt->execute([$ip, $url]);
    } catch (PDOException $e) {
        // Silently fail to not disrupt user experience
    }
}

if (isset($pdo)) {
    trackVisit($pdo);
}
?>