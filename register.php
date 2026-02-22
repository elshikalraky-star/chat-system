<?php
// register.php — صفحة اختيار نوع الحساب
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// حفظ هدف التوجيه إن جاء من زر تواصل
if (!empty($_GET['chat_with'])) {
    $cw = (int) $_GET['chat_with'];
    $_SESSION['redirect_after_login'] = "/dashboard/chat.php?user_id={$cw}";
}

$pending_chat = !empty($_SESSION['redirect_after_login']) &&
                strpos($_SESSION['redirect_after_login'], 'chat.php') !== false;

include 'includes/header.php';
?>

<main class="min-h-screen bg-gray-50 pt-4 pb-12 px-4 font-['Cairo']" dir="rtl">
    <div class="max-w-md w-full mx-auto text-center">

        <?php if ($pending_chat): ?>
        <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-4 mb-5">
            <p class="text-base font-black text-indigo-800">💬 ستفتح المحادثة فور تسجيل دخولك</p>
            <p class="text-xs text-indigo-500 font-bold mt-1">اختر نوع حسابك أو سجّل دخولك</p>
        </div>
        <?php endif; ?>

        <h1 class="text-2xl font-black text-gray-900 mb-2 flex items-center justify-center gap-2">
            أهلاً بك في عائلة كُرّة <span>👋</span>
        </h1>
        <p class="text-gray-500 font-bold mb-6 text-sm">اختر نوع حسابك لنبدأ</p>

        <!-- تسجيل الدخول للحسابات الموجودة -->
        <div class="mb-5">
            <a href="/login.php"
               class="block w-full bg-blue-500 hover:bg-blue-600 text-white font-black py-4 rounded-2xl shadow-md transition-all text-base">
                🔐 لديّ حساب — سجّل الدخول
            </a>
        </div>

        <p class="text-xs text-gray-400 font-bold mb-4">— أو أنشئ حساباً جديداً —</p>

        <div class="grid grid-cols-2 gap-3">

            <a href="/register_client.php"
               class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all flex flex-col items-center justify-between gap-3 h-full">
                <div class="flex flex-col items-center gap-2 mt-2">
                    <div class="w-14 h-14 bg-blue-50 rounded-full flex items-center justify-center text-2xl">👤</div>
                    <div>
                        <h3 class="font-black text-base text-gray-800">عميل جديد</h3>
                        <p class="text-[10px] text-gray-400 font-bold mt-1">أريد تصفح الخدمات والطلب</p>
                    </div>
                </div>
                <div class="w-full bg-blue-500 text-white py-2.5 rounded-xl font-bold text-sm text-center">إنشاء حساب</div>
            </a>

            <a href="/register_tailor.php"
               class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all flex flex-col items-center justify-between gap-3 h-full">
                <div class="flex flex-col items-center gap-2 mt-2">
                    <div class="w-14 h-14 bg-emerald-50 rounded-full flex items-center justify-center text-2xl">✂️</div>
                    <div>
                        <h3 class="font-black text-base text-gray-800">خياط / مشغل</h3>
                        <p class="text-[10px] text-gray-400 font-bold mt-1">أريد استقبال الطلبات</p>
                    </div>
                </div>
                <div class="w-full bg-emerald-500 text-white py-2.5 rounded-xl font-bold text-sm text-center">إنشاء حساب</div>
            </a>

            <a href="/register_designer.php"
               class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all flex flex-col items-center justify-between gap-3 h-full">
                <div class="flex flex-col items-center gap-2 mt-2">
                    <div class="w-14 h-14 bg-purple-50 rounded-full flex items-center justify-center text-2xl">🎨</div>
                    <div>
                        <h3 class="font-black text-base text-gray-800">مصمم / جرافيك</h3>
                        <p class="text-[10px] text-gray-400 font-bold mt-1">أريد عرض تصاميمي</p>
                    </div>
                </div>
                <div class="w-full bg-purple-400 text-white py-2.5 rounded-xl font-bold text-sm text-center">إنشاء حساب</div>
            </a>

            <a href="/register_packaging.php"
               class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all flex flex-col items-center justify-between gap-3 h-full">
                <div class="flex flex-col items-center gap-2 mt-2">
                    <div class="w-14 h-14 bg-cyan-50 rounded-full flex items-center justify-center text-2xl">📦</div>
                    <div>
                        <h3 class="font-black text-base text-gray-800">خدمات تغليف</h3>
                        <p class="text-[10px] text-gray-400 font-bold mt-1">أقدم خدمات تغليف الهدايا</p>
                    </div>
                </div>
                <div class="w-full bg-cyan-500 text-white py-2.5 rounded-xl font-bold text-sm text-center">إنشاء حساب</div>
            </a>

        </div>
    </div>
</main>
