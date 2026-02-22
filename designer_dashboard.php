<?php
session_start();
require '../db_connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'designer') {
    header("Location: ../login.php");
    exit;
}

$stmt = $conn->prepare("SELECT u.*, p.provider_name, p.specialty, p.starting_price FROM users u 
                         LEFT JOIN provider_profiles p ON p.user_id = u.id WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$designer = $stmt->fetch();

$stmt = $conn->prepare("SELECT COUNT(*) FROM portfolio WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$work_count = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE provider_id = ? AND status = 'pending'");
$stmt->execute([$_SESSION['user_id']]);
$new_orders = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE provider_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_orders = $stmt->fetchColumn();

// عدد الرسائل غير المقروءة
$unread_msgs = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user_id']]);
    $unread_msgs = (int) $stmt->fetchColumn();
} catch (Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استوديو التصميم - كُرّة</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Cairo', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- الهيدر -->
    <header class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
        <div class="max-w-2xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center text-xl">🎨</div>
                <div>
                    <p class="text-xs text-gray-400 font-bold">مرحباً</p>
                    <p class="text-sm font-black text-gray-800"><?= htmlspecialchars($designer['username'] ?? 'مصممة') ?></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <?php if($new_orders > 0): ?>
                    <span class="bg-red-500 text-white text-xs font-black px-2 py-1 rounded-full"><?= $new_orders ?> جديد</span>
                <?php endif; ?>
                <a href="messages_inbox.php" class="relative">
                    <span class="text-xl">📬</span>
                    <?php if ($unread_msgs > 0): ?>
                        <span class="absolute -top-1 -left-1 bg-red-500 text-white text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center">
                            <?= $unread_msgs > 9 ? '9+' : $unread_msgs ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="../logout.php" class="text-xs font-bold text-gray-400 hover:text-red-500 transition bg-gray-50 px-3 py-2 rounded-xl">خروج</a>
            </div>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 py-6">

        <?php if($designer['status'] === 'pending'): ?>
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-6 flex items-center gap-3">
            <span class="text-2xl">⏳</span>
            <div>
                <p class="font-black text-amber-800 text-sm">حسابك قيد المراجعة</p>
                <p class="text-xs text-amber-600 font-bold">سيتم تفعيله من قِبل الإدارة قريباً</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- بطاقة الرسائل -->
        <a href="messages_inbox.php"
           class="flex items-center justify-between bg-indigo-600 text-white rounded-2xl p-4 mb-5 shadow-lg hover:bg-indigo-700 transition">
            <div class="flex items-center gap-3">
                <span class="text-2xl">📬</span>
                <div>
                    <p class="font-black text-sm">صندوق الرسائل</p>
                    <p class="text-xs opacity-80 font-bold">
                        <?= $unread_msgs > 0 ? "لديك {$unread_msgs} رسالة جديدة" : 'لا توجد رسائل جديدة' ?>
                    </p>
                </div>
            </div>
            <?php if ($unread_msgs > 0): ?>
                <span class="bg-red-400 text-white text-xs font-black w-7 h-7 rounded-full flex items-center justify-center"><?= $unread_msgs > 9 ? '+9' : $unread_msgs ?></span>
            <?php else: ?>
                <span class="text-white text-lg opacity-60">›</span>
            <?php endif; ?>
        </a>

        <!-- بطاقة البروفايل -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5 mb-5">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center text-3xl flex-shrink-0">🎨</div>
                <div class="flex-1">
                    <h2 class="font-black text-gray-900 text-lg"><?= htmlspecialchars($designer['provider_name'] ?? 'استوديو التصميم') ?></h2>
                    <p class="text-xs text-gray-500 font-bold"><?= htmlspecialchars($designer['specialty'] ?? '') ?></p>
                    <p class="text-xs text-purple-600 font-black mt-1">يبدأ من <?= $designer['starting_price'] ?? '0' ?> ريال</p>
                </div>
                <a href="edit_profile.php" class="text-xs font-bold text-gray-400 bg-gray-50 px-3 py-2 rounded-xl hover:bg-gray-100 transition">تعديل</a>
            </div>
        </div>

        <!-- الإحصائيات -->
        <div class="grid grid-cols-3 gap-3 mb-5">
            <div class="bg-white rounded-2xl p-4 text-center shadow-sm border border-gray-100">
                <p class="text-2xl font-black text-purple-600"><?= $work_count ?></p>
                <p class="text-xs text-gray-500 font-bold mt-1">في المعرض</p>
            </div>
            <div class="bg-white rounded-2xl p-4 text-center shadow-sm border border-gray-100">
                <p class="text-2xl font-black text-amber-500"><?= $new_orders ?></p>
                <p class="text-xs text-gray-500 font-bold mt-1">طلب جديد</p>
            </div>
            <div class="bg-white rounded-2xl p-4 text-center shadow-sm border border-gray-100">
                <p class="text-2xl font-black text-green-500"><?= $total_orders ?></p>
                <p class="text-xs text-gray-500 font-bold mt-1">إجمالي</p>
            </div>
        </div>

        <!-- الأزرار الرئيسية -->
        <div class="grid grid-cols-2 gap-3 mb-5">
            <a href="upload_design.php" class="bg-purple-600 text-white py-4 rounded-2xl font-black text-center shadow-lg hover:bg-purple-700 transition text-sm">
                📸 رفع تصميم جديد
            </a>
            <a href="orders.php" class="bg-white border-2 border-purple-100 py-4 rounded-2xl font-black text-purple-600 text-center hover:bg-purple-50 transition text-sm relative">
                📋 طلبات العملاء
                <?php if($new_orders > 0): ?>
                    <span class="absolute -top-2 -left-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center"><?= $new_orders ?></span>
                <?php endif; ?>
            </a>
        </div>

        <!-- روابط إضافية -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <a href="portfolio.php" class="flex items-center justify-between p-4 border-b border-gray-50 hover:bg-gray-50 transition">
                <div class="flex items-center gap-3"><span class="text-xl">🖼️</span><span class="font-bold text-gray-800 text-sm">معرض أعمالي</span></div>
                <span class="text-gray-400 text-lg">›</span>
            </a>
            <a href="reviews.php" class="flex items-center justify-between p-4 border-b border-gray-50 hover:bg-gray-50 transition">
                <div class="flex items-center gap-3"><span class="text-xl">⭐</span><span class="font-bold text-gray-800 text-sm">تقييماتي</span></div>
                <span class="text-gray-400 text-lg">›</span>
            </a>
            <a href="earnings.php" class="flex items-center justify-between p-4 hover:bg-gray-50 transition">
                <div class="flex items-center gap-3"><span class="text-xl">💰</span><span class="font-bold text-gray-800 text-sm">أرباحي</span></div>
                <span class="text-gray-400 text-lg">›</span>
            </a>
        </div>

    </main>

    <!-- شريط التنقل السفلي -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 shadow-lg">
        <div class="max-w-2xl mx-auto flex justify-around py-3">
            <a href="designer_dashboard.php" class="flex flex-col items-center text-purple-600">
                <span class="text-xl">🏠</span>
                <span class="text-[10px] font-black mt-0.5">الرئيسية</span>
            </a>
            <a href="portfolio.php" class="flex flex-col items-center text-gray-400">
                <span class="text-xl">🖼️</span>
                <span class="text-[10px] font-bold mt-0.5">معرضي</span>
            </a>
            <a href="orders.php" class="flex flex-col items-center text-gray-400 relative">
                <span class="text-xl">📋</span>
                <span class="text-[10px] font-bold mt-0.5">الطلبات</span>
                <?php if($new_orders > 0): ?>
                    <span class="absolute -top-1 right-2 bg-red-500 text-white text-[8px] w-4 h-4 rounded-full flex items-center justify-center"><?= $new_orders ?></span>
                <?php endif; ?>
            </a>
            <a href="messages_inbox.php" class="flex flex-col items-center text-gray-400 relative">
                <span class="text-xl">📬</span>
                <span class="text-[10px] font-bold mt-0.5">الرسائل</span>
                <?php if ($unread_msgs > 0): ?>
                    <span class="absolute -top-1 right-1 bg-red-500 text-white text-[8px] w-4 h-4 rounded-full flex items-center justify-center font-black">
                        <?= $unread_msgs > 9 ? '9+' : $unread_msgs ?>
                    </span>
                <?php endif; ?>
            </a>
            <a href="edit_profile.php" class="flex flex-col items-center text-gray-400">
                <span class="text-xl">👤</span>
                <span class="text-[10px] font-bold mt-0.5">حسابي</span>
            </a>
        </div>
    </nav>

    <div class="h-20"></div>
</body>
</html>
