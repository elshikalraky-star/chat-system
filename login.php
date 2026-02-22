<?php
// 1. تفعيل ظهور الأخطاء (عشان نعرف الشاشة البيضاء سببها إيه)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// 2. التحقق من وجود ملف الاتصال قبل استدعائه
$db_file = '../db_connect.php';

if (!file_exists($db_file)) {
    // لو الملف مش موجود هيطلعلك الرسالة دي
    echo "<div style='background:red; color:white; padding:20px; text-align:center; font-family:sans-serif;'>
            <h1>🚨 خطأ مسار خطير! 🚨</h1>
            <p>ملف <b>db_connect.php</b> غير موجود في المسار المحدد (../db_connect.php).</p>
            <p>تأكد أن ملف db_connect.php موجود بجانب مجلد admin وليس بداخله.</p>
          </div>";
    exit; // وقف الكود
}

// لو الملف موجود، استدعيه
require $db_file;

// لو هو مسجل دخول أصلاً، حوله للوحة
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // 3. التأكد من الاتصال بقاعدة البيانات
    if (!isset($conn)) {
        die("خطأ: المتغير conn غير معرف. راجع ملف db_connect.php");
    }

    // هنا افترضت أن اسم الجدول admins (غيره لو عندك users)
    try {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && $password == $admin['password']) {
            $_SESSION['admin_id'] = $admin['id'];
            header("Location: index.php");
            exit;
        } else {
            $error = "بيانات الدخول غير صحيحة ❌";
        }
    } catch (PDOException $e) {
        $error = "خطأ في قاعدة البيانات: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل دخول الأدمن</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Cairo', sans-serif; }</style>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-3xl shadow-xl w-full max-w-sm border border-gray-200">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-black text-gray-800">لوحة التحكم 🔒</h1>
        </div>

        <?php if(!empty($error)): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded-xl text-sm font-bold mb-4 text-center border border-red-100">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">اسم المستخدم</label>
                <input type="text" name="username" required class="w-full border bg-gray-50 p-3 rounded-xl outline-none text-left" dir="ltr">
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">كلمة المرور</label>
                <input type="password" name="password" required class="w-full border bg-gray-50 p-3 rounded-xl outline-none text-left" dir="ltr">
            </div>
            <button type="submit" class="w-full bg-black text-white py-3.5 rounded-xl font-black shadow-lg hover:bg-gray-800 transition">دخول</button>
        </form>
        <div class="mt-6 text-center">
            <a href="../index.php" class="text-xs font-bold text-gray-400 hover:text-black transition">عودة للموقع</a>
        </div>
    </div>
</body>
</html>