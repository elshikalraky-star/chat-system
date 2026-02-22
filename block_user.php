<?php
// messages/block_user.php
session_start();
require '../db_connect.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'غير مسجل الدخول']); exit;
}

$user_id  = (int)$_SESSION['user_id'];
$data     = json_decode(file_get_contents('php://input'), true);
$block_id = (int)($data['blocked_user_id'] ?? 0);
$reason   = trim($data['reason'] ?? '');

if ($block_id <= 0 || $block_id === $user_id) {
    echo json_encode(['success' => false, 'error' => 'بيانات غير صحيحة']); exit;
}

try {
    $stmt = $conn->prepare("INSERT IGNORE INTO blocked_users (user_id, blocked_user_id, reason) VALUES (?,?,?)");
    $stmt->execute([$user_id, $block_id, $reason]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'خطأ في الخادم']);
}
