<?php
// messages/get_conversations.php
session_start();
require '../db_connect.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'غير مسجل الدخول']); exit;
}

$user_id = (int)$_SESSION['user_id'];

try {
    $stmt = $conn->prepare("
        SELECT 
            c.id as conversation_id,
            c.last_message_at,
            -- الطرف الآخر
            CASE WHEN c.user_id_1 = ? THEN c.user_id_2 ELSE c.user_id_1 END as other_user_id,
            -- بيانات الطرف الآخر
            u.username as other_username,
            u.role as other_role,
            -- آخر رسالة
            (SELECT message_text FROM messages WHERE conversation_id=c.id ORDER BY created_at DESC LIMIT 1) as last_message,
            -- عدد غير المقروءة
            COALESCE((SELECT unread_count FROM message_notifications WHERE user_id=? AND conversation_id=c.id), 0) as unread_count
        FROM conversations c
        LEFT JOIN users u ON u.id = CASE WHEN c.user_id_1=? THEN c.user_id_2 ELSE c.user_id_1 END
        WHERE (c.user_id_1=? OR c.user_id_2=?) AND c.is_active=1
        ORDER BY c.last_message_at DESC
    ");
    $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id]);
    $conversations = $stmt->fetchAll();

    echo json_encode(['success' => true, 'conversations' => $conversations]);

} catch (PDOException $e) {
    error_log("GetConversations: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'خطأ في الخادم']);
}
