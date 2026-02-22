<?php
// messages/get_messages.php
session_start();
require '../db_connect.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'غير مسجل الدخول']); exit;
}

$user_id  = (int)$_SESSION['user_id'];
$conv_id  = (int)($_GET['conversation_id'] ?? 0);
$page     = max(1, (int)($_GET['page'] ?? 1));
$limit    = 50;
$offset   = ($page - 1) * $limit;

if ($conv_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'conversation_id مطلوب']); exit;
}

// التحقق أن المستخدم طرف في هذه المحادثة
$stmt = $conn->prepare("SELECT id FROM conversations WHERE id=? AND (user_id_1=? OR user_id_2=?)");
$stmt->execute([$conv_id, $user_id, $user_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'غير مصرح']); exit;
}

try {
    $stmt = $conn->prepare("
        SELECT m.id, m.sender_id, m.receiver_id, m.message_text, m.is_read, m.read_at,
               m.created_at, u.username as sender_name
        FROM messages m
        LEFT JOIN users u ON u.id = m.sender_id
        WHERE m.conversation_id = ?
        ORDER BY m.created_at ASC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$conv_id, $limit, $offset]);
    $messages = $stmt->fetchAll();

    // تحديد الرسائل كمقروءة
    $stmt2 = $conn->prepare("UPDATE messages SET is_read=1, read_at=NOW() WHERE conversation_id=? AND receiver_id=? AND is_read=0");
    $stmt2->execute([$conv_id, $user_id]);

    // إعادة ضبط إشعارات هذه المحادثة
    $stmt3 = $conn->prepare("UPDATE message_notifications SET unread_count=0 WHERE user_id=? AND conversation_id=?");
    $stmt3->execute([$user_id, $conv_id]);

    echo json_encode(['success' => true, 'messages' => $messages, 'current_user' => $user_id]);

} catch (PDOException $e) {
    error_log("GetMessages: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'خطأ في الخادم']);
}
