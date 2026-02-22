<?php
// messages/delete_conversation.php
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

// تحقق أن المستخدم طرف في هذه المحادثة
$stmt = $conn->prepare("SELECT id FROM conversations WHERE id=? AND (user_id_1=? OR user_id_2=?)");
$stmt->execute([$conv_id, $user_id, $user_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'غير مصرح']); exit;
}

try {
    // حذف منطقي (soft delete)
    $conn->prepare("UPDATE conversations SET is_active=0 WHERE id=?")->execute([$conv_id]);
    $conn->prepare("DELETE FROM message_notifications WHERE conversation_id=? AND user_id=?")->execute([$conv_id, $user_id]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false]);
}
