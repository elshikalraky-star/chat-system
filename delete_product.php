<?php
session_start();
require '../db_connect.php';

// التأكد من صلاحية الإدمن
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // 1. جلب اسم الصورة قبل حذف السجل لنتمكن من الوصول للملف في السيرفر
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if ($product) {
        $image_name = $product['image'];
        $image_path = "../uploads/" . $image_name;

        // 2. حذف ملف الصورة فعلياً من السيرفر إذا كان موجوداً
        if (!empty($image_name) && file_exists($image_path)) {
            unlink($image_path); // هذا الأمر يمسح الملف نهائياً من الهارد ديسك
        }

        // 3. حذف سجل المنتج من قاعدة البيانات
        $del_stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $del_stmt->execute([$id]);

        // ✅ تم إضافة رسالة النجاح هنا
        $_SESSION['success_message'] = "تم حذف المنتج بنجاح 🗑️";
    }
}

// العودة للوحة التحكم بعد المسح الشامل
header("Location: index.php");
exit;