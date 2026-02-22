<?php
// messages/mark_as_read.php
session_start();
require '../db_connect.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]); exit;
}

$user_id = (int)$_SESSION['user_id'];
$data    = json_decode(file_get_contents('php://input'), true);
$conv_id = (int)($data['conversation_id'] ?? 0);

if ($conv_id <= 0) { echo json_encode(['success' => false]); exit; }

try {
    $conn->prepare("UPDATE messages SET is_read=1, read_at=NOW() WHERE conversation_id=? AND receiver_id=? AND is_read=0")->execute([$conv_id, $user_id]);
    $conn->prepare("UPDATE message_notifications SET unread_count=0 WHERE user_id=? AND conversation_id=?")->execute([$user_id, $conv_id]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false]);
}
