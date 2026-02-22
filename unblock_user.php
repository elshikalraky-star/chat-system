<?php
// messages/unblock_user.php
session_start();
require '../db_connect.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]); exit;
}

$user_id    = (int)$_SESSION['user_id'];
$data       = json_decode(file_get_contents('php://input'), true);
$unblock_id = (int)($data['blocked_user_id'] ?? 0);

if ($unblock_id <= 0) { echo json_encode(['success' => false]); exit; }

try {
    $conn->prepare("DELETE FROM blocked_users WHERE user_id=? AND blocked_user_id=?")->execute([$user_id, $unblock_id]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false]);
}
