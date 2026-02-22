<?php
// messages/get_unread_count.php
session_start();
require '../db_connect.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]); exit;
}

$user_id = (int)$_SESSION['user_id'];

try {
    $stmt = $conn->prepare("SELECT COALESCE(SUM(unread_count), 0) as total FROM message_notifications WHERE user_id=?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();
    echo json_encode(['count' => (int)$row['total']]);
} catch (PDOException $e) {
    echo json_encode(['count' => 0]);
}
