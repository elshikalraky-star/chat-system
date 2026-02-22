<?php
// register_client.php — تسجيل العميل مع التوجيه الذكي
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_connect.php';
require 'includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $basicData = [
        'username' => trim($_POST['full_name'] ?? ''),
        'phone'    => trim($_POST['phone'] ?? ''),
        'password' => trim($_POST['password'] ?? ''),
        'role'     => 'client',
        'status'   => 'approved',
    ];

    $profileData = [
        'business_name'  => 'حساب شخصي',
        'specialty'      => 'عميل',
        'price'          => 0,
        'time'           => '-',
        'location'       => '-',
        'provider_image' => 'default_client.png',
        'work_image'     => 'default_client.png',
    ];

    $new_user_id = registerUserSecurely($conn, $basicData, $profileData);

    if ($new_user_id) {
        session_regenerate_id(true);
        $_SESSION['user_id']    = $new_user_id;
        $_SESSION['username']   = $basicData['username'];
        $_SESSION['role']       = 'client';
        $_SESSION['login_time'] = time();

        // ── التوجيه الذكي ────────────────────────────────────────────────
        $dest = $_SESSION['redirect_after_login'] ?? '';
        unset($_SESSION['redirect_after_login']);
        if (!$dest) {
            $dest = '/dashboard/client_dashboard.php';
        }

        echo "<script>
            alert('✨ أهلاً بك! تم إنشاء حسابك بنجاح.');
            window.location.href = '" . addslashes($dest) . "';
        </script>";
        exit;
    } else {
        $error = 'لم ينجح التسجيل، ربما رقم الجوال مستخدم بالفعل.';
    }
}

$pending_chat = !empty($_SESSION['redirect_after_login']) &&
                strpos($_SESSION['redirect_after_login'], 'chat.php') !== false;

include 'includes/header.php';
?>

<main class="min-h-screen bg-gray-50 py-10 px-4 font-['Cairo']" dir="rtl">
    <div class="max-w-md mx-auto bg-white rounded-[30px] shadow-sm border border-gray-100 p-6 md:p-10">

        <?php if ($pending_chat): ?>
        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-3 mb-5 text-center">
            <p class="text-sm font-black text-blue-700">💬 أنشئ حساباً لفتح المحادثة فوراً</p>
        </div>
        <?php endif; ?>

        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-rose-50 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-3">👤</div>
            <h1 class="text-2xl font-black text-gray-900">حياك الله عميلنا الغالي</h1>
            <p class="text-sm text-gray-500 font-bold mt-1">حسابك جاهز للاستخدام فوراً</p>
            <?php if ($error): ?>
                <div class="mt-4 bg-red-50 text-red-600 p-3 rounded-xl text-sm font-bold border border-red-100">
                    ❌ <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
        </div>

        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-xs font-black text-gray-700 mb-2">الاسم الكامل</label>
                <input type="text" name="full_name" required
                       class="w-full p-3 bg-gray-50 rounded-xl outline-none font-bold text-sm border focus:border-rose-500 transition text-right shadow-sm">
            </div>
            <div>
                <?php include 'includes/smart_phone.php'; ?>
            </div>
            <div>
                <label class="block text-xs font-black text-gray-700 mb-2">كلمة المرور</label>
                <input type="password" name="password" required
                       class="w-full p-3 bg-gray-50 rounded-xl outline-none font-bold text-sm border focus:border-rose-500 transition text-right shadow-sm">
            </div>
            <button type="submit"
                    class="w-full bg-gray-900 text-white py-4 rounded-2xl font-black shadow-lg hover:scale-[0.98] transition-transform mt-6">
                إنشاء حساب والبدء فوراً ✨
            </button>
        </form>

        <div class="text-center mt-5">
            <p class="text-sm text-gray-500 font-bold">
                لديك حساب؟
                <a href="/login.php" class="text-rose-500 font-black hover:underline mr-1">سجّل دخولك</a>
            </p>
        </div>
    </div>
</main>
<?php if (file_exists('includes/form_guard.php')) include 'includes/form_guard.php'; ?>
