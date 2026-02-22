<?php
session_start();
require 'db_connect.php';

// ==========================================
// 1. التأكد من أن الطلب POST
// ==========================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

// ==========================================
// 2. استلام البيانات
// ==========================================
$phone    = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';

if (!$phone || !$password) {
    header("Location: login.php?error=" . urlencode("يرجى إدخال جميع البيانات") . "&phone=" . urlencode($phone));
    exit();
}

// ==========================================
// 3. الحماية من المحاولات الكثيرة (Brute Force)
// ==========================================
$ip = $_SERVER['REMOTE_ADDR'];
$max_attempts = 5;
$lockout_time = 900; // 15 دقيقة

try {
    // عدد المحاولات خلال الفترة المحددة
    $stmt = $conn->prepare("
        SELECT COUNT(*) as attempts, MAX(attempt_time) as last_attempt 
        FROM login_attempts 
        WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)
    ");
    $stmt->execute([$ip, $lockout_time]);
    $attempt_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($attempt_data['attempts'] >= $max_attempts) {
        $wait_time = ceil(($lockout_time - (time() - strtotime($attempt_data['last_attempt']))) / 60);
        header("Location: login.php?error=" . urlencode("تم حظر المحاولات مؤقتًا. حاول بعد {$wait_time} دقيقة") . "&phone=" . urlencode($phone));
        exit();
    }

    // ==========================================
    // 4. البحث عن المستخدم
    // ==========================================
    $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ==========================================
    // 5. التحقق من كلمة المرور
    // ==========================================
    $login_success = false;

    if ($user && password_verify($password, $user['password'])) {
        $login_success = true;
    }

    // ==========================================
    // 6. في حالة فشل تسجيل الدخول
    // ==========================================
    if (!$login_success) {
        // تسجيل المحاولة
        $stmt = $conn->prepare("
            INSERT INTO login_attempts (ip_address, phone, attempt_time) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$ip, $phone]);

        header("Location: login.php?error=" . urlencode("رقم الهاتف أو كلمة المرور غير صحيحة") . "&phone=" . urlencode($phone));
        exit();
    }

    // ==========================================
    // 7. تسجيل الدخول بنجاح
    // ==========================================

    // حماية من Session Fixation
    session_regenerate_id(true);

    $_SESSION['user_id']    = $user['id'];
    $_SESSION['username']   = $user['username'];
    $_SESSION['role']       = $user['role'];
    $_SESSION['login_time'] = time();
    $_SESSION['ip_address'] = $ip;

    // حذف المحاولات الفاشلة
    $stmt = $conn->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
    $stmt->execute([$ip]);

    // تحديث آخر دخول
    $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);

    // ==========================================
    // 8. التوجيه حسب نوع المستخدم
    // ==========================================
    $redirect_urls = [
        'client'    => '/dashboard/client_dashboard.php',
        'tailor'    => '/dashboard/tailor_dashboard.php',
        'designer'  => '/dashboard/designer_dashboard.php',
        'packaging' => '/dashboard/packaging_dashboard.php'
    ];

    $redirect = $redirect_urls[$user['role']] ?? '/index.php';
    header("Location: $redirect");
    exit();

} catch (PDOException $e) {
    error_log("Login Error: " . $e->getMessage());

    header("Location: login.php?error=" . urlencode("حدث خطأ أثناء تسجيل الدخول"));
    exit();
}
?>