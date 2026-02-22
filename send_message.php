<?php
// messages/send_message.php
session_start();
require '../db_connect.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'غير مسجل الدخول']);
    exit;
}

$data         = json_decode(file_get_contents('php://input'), true);
$receiver_id  = (int)($data['receiver_id'] ?? 0);
$message_text = trim($data['message'] ?? '');
$sender_id    = (int)$_SESSION['user_id'];

if (empty($message_text) || $receiver_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'بيانات غير صحيحة']); exit;
}
if ($sender_id === $receiver_id) {
    echo json_encode(['success' => false, 'error' => 'لا يمكنك مراسلة نفسك']); exit;
}
if (mb_strlen($message_text) > 2000) {
    echo json_encode(['success' => false, 'error' => 'الرسالة طويلة جداً']); exit;
}

$stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
$stmt->execute([$receiver_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'المستخدم غير موجود']); exit;
}

$stmt = $conn->prepare("SELECT id FROM blocked_users WHERE (user_id=? AND blocked_user_id=?) OR (user_id=? AND blocked_user_id=?)");
$stmt->execute([$sender_id, $receiver_id, $receiver_id, $sender_id]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'لا يمكن إرسال الرسالة لهذا المستخدم']); exit;
}

try {
    $stmt = $conn->prepare("SELECT id FROM conversations WHERE (user_id_1=? AND user_id_2=?) OR (user_id_1=? AND user_id_2=?)");
    $stmt->execute([$sender_id, $receiver_id, $receiver_id, $sender_id]);
    $conv = $stmt->fetch();

    if (!$conv) {
        $stmt = $conn->prepare("INSERT INTO conversations (user_id_1, user_id_2, last_message_at) VALUES (?,?,NOW())");
        $stmt->execute([$sender_id, $receiver_id]);
        $conv_id = (int)$conn->lastInsertId();
    } else {
        $conv_id = (int)$conv['id'];
        $conn->prepare("UPDATE conversations SET last_message_at=NOW(), is_active=1 WHERE id=?")->execute([$conv_id]);
    }

    $stmt = $conn->prepare("INSERT INTO messages (conversation_id, sender_id, receiver_id, message_text) VALUES (?,?,?,?)");
    $stmt->execute([$conv_id, $sender_id, $receiver_id, $message_text]);
    $msg_id = $conn->lastInsertId();

    $stmt = $conn->prepare("INSERT INTO message_notifications (user_id, conversation_id, unread_count) VALUES (?,?,1) ON DUPLICATE KEY UPDATE unread_count=unread_count+1");
    $stmt->execute([$receiver_id, $conv_id]);

    echo json_encode(['success' => true, 'message_id' => $msg_id, 'conversation_id' => $conv_id]);

} catch (PDOException $e) {
    error_log("SendMsg: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'خطأ في الخادم']);
}
