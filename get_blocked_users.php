<?php
// messages/get_blocked_users.php
session_start();
require '../db_connect.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]); exit;
}

$user_id = (int)$_SESSION['user_id'];

try {
    $stmt = $conn->prepare("
        SELECT b.id, b.blocked_user_id, b.reason, b.blocked_at,
               u.username, u.role
        FROM blocked_users b
        LEFT JOIN users u ON u.id = b.blocked_user_id
        WHERE b.user_id = ?
        ORDER BY b.blocked_at DESC
    ");
    $stmt->execute([$user_id]);
    echo json_encode(['success' => true, 'blocked' => $stmt->fetchAll()]);
} catch (PDOException $e) {
    echo json_encode(['success' => false]);
}
