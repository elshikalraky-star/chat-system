<?php
session_start();
require_once 'db_connect.php';

// تفعيل الأخطاء (مؤقتاً للتجربة)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// التأكد من أن الطلب POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // استقبال البيانات
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    // تحقق بسيط
    if (empty($name) || empty($phone) || empty($password) || empty($role)) {
        die("❌ كل الحقول مطلوبة");
    }

    // تشفير الباسورد
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {

        // 1. إدخال المستخدم
        $stmt = $conn->prepare("INSERT INTO users (name, phone, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $phone, $hashed_password, $role]);

        $user_id = $conn->lastInsertId();

        // 2. لو مزود خدمة (خياط / مصمم / تغليف)
        if (in_array($role, ['tailor', 'designer', 'packaging'])) {

            // 👇 حل المشكلة هنا (بدون country حالياً)
            $country_id = null;

            $stmt2 = $conn->prepare("
                INSERT INTO provider_profiles (user_id, country_id)
                VALUES (?, ?)
            ");
            $stmt2->execute([$user_id, $country_id]);
        }

        // 3. إنشاء سيشن
        $_SESSION['user_id'] = $user_id;
        $_SESSION['role'] = $role;

        // 4. تحويل للداشبورد
        header("Location: dashboard.php");
        exit();

    } catch (PDOException $e) {
        echo "❌ خطأ: " . $e->getMessage();
    }

} else {
    header("Location: register.php");
    exit();
}
?>