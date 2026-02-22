<?php
// wishlist_action.php
session_start();
require 'db_connect.php'; // ضروري جداً للاتصال بالقاعدة

header('Content-Type: application/json');

// 1. حماية: التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'يجب تسجيل الدخول أولاً 🔒',
        'redirect' => 'login.php'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
// نستقبل item_id أو product_id لضمان عمل الكود في كل الحالات
$item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
if ($item_id == 0 && isset($_POST['product_id'])) {
    $item_id = (int)$_POST['product_id'];
}

if ($item_id > 0) {
    try {
        // 2. التحقق: هل المنتج موجود في مفضلة هذا المستخدم؟
        $check = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $check->execute([$user_id, $item_id]);
        
        if ($check->rowCount() > 0) {
            // -- موجود مسبقاً: نقوم بالحذف (Remove) --
            $del = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
            $del->execute([$user_id, $item_id]);
            $status = 'removed';
        } else {
            // -- غير موجود: نقوم بالإضافة (Add) --
            $add = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
            $add->execute([$user_id, $item_id]);
            $status = 'added';
        }

        // 3. تحديث السيشن (خطوة مهمة جداً)
        // نقوم بجلب أحدث قائمة IDs للمستخدم ونحدث الجلسة لكي تظهر القلوب حمراء فوراً
        $stmt = $conn->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $_SESSION['wishlist'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode([
            'status' => 'success',
            'action' => $status,
            'count'  => count($_SESSION['wishlist'])
        ]);

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'خطأ في قاعدة البيانات']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'بيانات المنتج غير صحيحة']);
}
?>
