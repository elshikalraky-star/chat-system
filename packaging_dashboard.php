<?php
session_start();
require '../db_connect.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'packaging') { 
    header("Location: ../login.php"); 
    exit; 
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("SELECT 
        COUNT(*) as total, 
        SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending_gifts,
        SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as done_gifts
        FROM orders WHERE provider_id = ?");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch();
} catch (PDOException $e) { $stats = ['total'=>0, 'pending_gifts'=>0, 'done_gifts'=>0]; }

// عدد الرسائل غير المقروءة
$unread_msgs = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    $unread_msgs = (int) $stmt->fetchColumn();
} catch (Throwable $e) {}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم التغليف</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@700;900&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-50 font-['Cairo'] pb-24">

    <div class="p-5 bg-white border-b flex justify-between items-center shadow-sm">
        <div class="flex items-center gap-2">
            <span class="text-2xl">🎁</span>
            <h1 class="font-black text-gray-800 text-lg">قسم التغليف</h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="messages_inbox.php" class="relative">
                <span class="text-2xl">📬</span>
                <?php if ($unread_msgs > 0): ?>
                    <span class="absolute -top-1 -left-1 bg-red-500 text-white text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center">
                        <?= $unread_msgs > 9 ? '9+' : $unread_msgs ?>
                    </span>
                <?php endif; ?>
            </a>
            <a href="../logout.php" class="bg-red-50 text-red-500 px-4 py-2 rounded-xl text-xs font-black">خروج</a>
        </div>
    </div>

    <div class="p-4">

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

        <div class="grid grid-cols-2 gap-4 mb-8">
            <div class="bg-white p-6 rounded-[30px] border border-gray-100 shadow-sm text-center">
                <span class="text-3xl block mb-2">🎀</span>
                <span class="text-2xl font-black text-gray-900"><?= $stats['pending_gifts'] ?></span>
                <p class="text-[10px] text-gray-400 font-black mt-1">هدايا قيد التجهيز</p>
            </div>
            <div class="bg-green-50 p-6 rounded-[30px] border border-green-100 text-center">
                <span class="text-3xl block mb-2">✅</span>
                <span class="text-2xl font-black text-green-600"><?= $stats['done_gifts'] ?></span>
                <p class="text-[10px] text-green-400 font-black mt-1">طلبات تم تسليمها</p>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-3xl p-2 border border-gray-100 shadow-sm">
                <a href="incoming_gifts.php" class="flex items-center gap-4 p-4 hover:bg-gray-50 rounded-2xl transition">
                    <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-xl">📝</div>
                    <div class="flex-1 text-right">
                        <h3 class="font-black text-gray-900 text-sm">عرض طلبات التغليف</h3>
                        <p class="text-[10px] text-gray-400 font-bold">شاهد تفاصيل الهدايا والكرت المرفق</p>
                    </div>
                    <span class="text-gray-300">⬅️</span>
                </a>
                
                <hr class="border-gray-50 mx-4">
                
                <a href="packaging_styles.php" class="flex items-center gap-4 p-4 hover:bg-gray-50 rounded-2xl transition">
                    <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-xl">🖼️</div>
                    <div class="flex-1 text-right">
                        <h3 class="font-black text-gray-900 text-sm">أنواع التغليف المتاحة</h3>
                        <p class="text-[10px] text-gray-400 font-bold">إضافة أشكال كراتين أو شرائط جديدة</p>
                    </div>
                    <span class="text-gray-300">⬅️</span>
                </a>
            </div>

            <div class="bg-indigo-600 p-6 rounded-[35px] text-white relative overflow-hidden shadow-xl">
                <div class="relative z-10">
                    <h4 class="font-black text-sm mb-1">نصيحة اليوم ✨</h4>
                    <p class="text-[10px] opacity-80 font-bold leading-relaxed">التغليف الأنيق هو ما يجعل العميل يعود مرة أخرى. تأكد من جودة الأشرطة وتناسق الألوان!</p>
                </div>
                <span class="absolute -bottom-4 -left-4 text-8xl opacity-10">🎁</span>
            </div>
        </div>
    </div>

    <!-- شريط التنقل السفلي المُحدَّث -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 shadow-lg z-50">
        <div class="max-w-2xl mx-auto flex justify-around py-3">
            <a href="packaging_dashboard.php" class="flex flex-col items-center text-amber-500">
                <span class="text-xl">🏠</span>
                <span class="text-[10px] font-black mt-0.5">الرئيسية</span>
            </a>
            <a href="incoming_gifts.php" class="flex flex-col items-center text-gray-400">
                <span class="text-xl">📦</span>
                <span class="text-[10px] font-bold mt-0.5">الطلبات</span>
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

</body>
</html>
