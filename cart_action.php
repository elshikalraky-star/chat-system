<?php
session_start();

// 1. كود الحذف
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $id = (int)$_GET['id']; // تأمين: تحويل الرقم لعدد صحيح
    
    if (isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }
    
    // يرجع للصفحة مع "إشارة" الحذف (عشان الإشعار يظهر)
    header("Location: cart.php?status=deleted");
    exit();
}

// 2. كود الإضافة
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    
    $product_id = (int)$_POST['product_id']; // تأمين
    // تأمين: الكمية لازم تكون 1 على الأقل (عشان ميبقاش فيه سالب)
    $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    
    // لو الطلب AJAX (من الصفحة الرئيسية أو المنتج)
    if(isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['count' => array_sum($_SESSION['cart'])]);
        exit;
    }

    // لو الطلب عادي (زر اشتري الآن)
    if(isset($_POST['buy_now'])) {
        header('Location: cart.php');
    } else {
        // الرجوع للصفحة السابقة (مع احتياطي لو الرابط مش موجود)
        $return_url = $_SERVER['HTTP_REFERER'] ?? 'index.php';
        header('Location: ' . $return_url);
    }
    exit();
}

// لو تم فتح الملف مباشر
header("Location: index.php");
exit();
?>