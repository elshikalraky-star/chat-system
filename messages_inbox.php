<?php
// dashboard/messages_inbox.php - صندوق الرسائل
session_start();
require '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); exit;
}

$user_id   = (int)$_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'client';

// جلب المحادثات
$stmt = $conn->prepare("
    SELECT 
        c.id as conversation_id,
        c.last_message_at,
        CASE WHEN c.user_id_1=? THEN c.user_id_2 ELSE c.user_id_1 END as other_id,
        u.username as other_name,
        u.role as other_role,
        (SELECT message_text FROM messages WHERE conversation_id=c.id ORDER BY created_at DESC LIMIT 1) as last_msg,
        COALESCE((SELECT unread_count FROM message_notifications WHERE user_id=? AND conversation_id=c.id), 0) as unread
    FROM conversations c
    LEFT JOIN users u ON u.id = CASE WHEN c.user_id_1=? THEN c.user_id_2 ELSE c.user_id_1 END
    WHERE (c.user_id_1=? OR c.user_id_2=?) AND c.is_active=1
    ORDER BY c.last_message_at DESC
");
$stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id]);
$conversations = $stmt->fetchAll();

// تحديد لون الدور
$roleColors = [
    'tailor'    => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'خياط',    'icon' => '✂️'],
    'designer'  => ['bg' => 'bg-purple-100',  'text' => 'text-purple-700',  'label' => 'مصمم',    'icon' => '🎨'],
    'packaging' => ['bg' => 'bg-amber-100',   'text' => 'text-amber-700',   'label' => 'تغليف',   'icon' => '🎁'],
    'client'    => ['bg' => 'bg-blue-100',    'text' => 'text-blue-700',    'label' => 'عميل',    'icon' => '👤'],
];

function getRoleMeta($role, $roleColors) {
    return $roleColors[$role] ?? ['bg'=>'bg-gray-100','text'=>'text-gray-700','label'=>$role,'icon'=>'💬'];
}

// رابط الرجوع حسب الدور
$backLinks = [
    'client'    => 'client_dashboard.php',
    'tailor'    => 'tailor_dashboard.php',
    'designer'  => 'designer_dashboard.php',
    'packaging' => 'packaging_dashboard.php',
];
$backLink = $backLinks[$user_role] ?? '../index.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صندوق الرسائل - كُرّة</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/messages.css">
    <style>body { font-family: 'Cairo', sans-serif; background: #f9fafb; }</style>
</head>
<body class="min-h-screen pb-24">

    <!-- الهيدر -->
    <header class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
        <div class="max-w-2xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="<?= $backLink ?>" class="w-9 h-9 bg-gray-50 rounded-xl flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            <h1 class="font-black text-gray-800 text-base">📬 صندوق الرسائل</h1>
            <a href="blocked_users.php" class="text-xs font-bold text-gray-400 hover:text-red-500 transition">
                🚫 المحظورون
            </a>
        </div>
    </header>

    <main class="max-w-2xl mx-auto">

        <?php if (empty($conversations)): ?>
            <!-- حالة فارغة -->
            <div class="flex flex-col items-center justify-center py-24 px-8 text-center">
                <div class="text-6xl mb-4">💬</div>
                <h2 class="font-black text-gray-700 text-lg mb-2">لا توجد محادثات بعد</h2>
                <p class="text-sm text-gray-400 font-bold">
                    <?php if ($user_role === 'client'): ?>
                        تواصل مع أحد الخياطين أو المصممين من الصفحة الرئيسية
                    <?php else: ?>
                        ستظهر هنا رسائل العملاء
                    <?php endif; ?>
                </p>
                <a href="<?= $backLink ?>" class="mt-6 bg-violet-600 text-white px-6 py-3 rounded-2xl font-black text-sm hover:bg-violet-700 transition">
                    العودة للرئيسية
                </a>
            </div>

        <?php else: ?>
            <!-- قائمة المحادثات -->
            <div class="bg-white mt-0 rounded-b-none overflow-hidden shadow-sm">
                <?php foreach ($conversations as $conv):
                    $meta    = getRoleMeta($conv['other_role'], $roleColors);
                    $timeAgo = '';
                    if ($conv['last_message_at']) {
                        $diff = time() - strtotime($conv['last_message_at']);
                        if ($diff < 60)         $timeAgo = 'الآن';
                        elseif ($diff < 3600)   $timeAgo = floor($diff/60) . ' د';
                        elseif ($diff < 86400)  $timeAgo = floor($diff/3600) . ' س';
                        else                    $timeAgo = date('d/m', strtotime($conv['last_message_at']));
                    }
                    $isUnread = $conv['unread'] > 0;
                ?>
                <div class="conv-item <?= $isUnread ? 'unread' : '' ?> group"
                     onclick="window.location='chat.php?conversation_id=<?= $conv['conversation_id'] ?>&user_id=<?= $conv['other_id'] ?>'">

                    <!-- الأفاتار -->
                    <div class="conv-avatar <?= $meta['bg'] ?>">
                        <?= $meta['icon'] ?>
                    </div>

                    <!-- المعلومات -->
                    <div class="conv-info">
                        <div class="flex items-center gap-2">
                            <span class="conv-name"><?= htmlspecialchars($conv['other_name'] ?? 'مستخدم') ?></span>
                            <span class="text-[9px] font-black <?= $meta['text'] ?> <?= $meta['bg'] ?> px-1.5 py-0.5 rounded-full">
                                <?= $meta['label'] ?>
                            </span>
                        </div>
                        <p class="conv-last-msg <?= $isUnread ? 'unread-text' : '' ?>">
                            <?= htmlspecialchars(mb_substr($conv['last_msg'] ?? 'ابدأ المحادثة', 0, 45)) ?>
                            <?= mb_strlen($conv['last_msg'] ?? '') > 45 ? '...' : '' ?>
                        </p>
                    </div>

                    <!-- الوقت والعدد -->
                    <div class="conv-meta flex flex-col items-end gap-1">
                        <span class="conv-time"><?= $timeAgo ?></span>
                        <?php if ($isUnread): ?>
                            <span class="conv-unread-count">
                                <?= $conv['unread'] > 9 ? '+9' : $conv['unread'] ?>
                            </span>
                        <?php endif; ?>
                        <button onclick="event.stopPropagation(); deleteConv(<?= $conv['conversation_id'] ?>)"
                                class="opacity-0 group-hover:opacity-100 transition text-gray-300 hover:text-red-400 text-xs mt-1">
                            🗑
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>

    <script src="../js/messages.js"></script>
    <script>
        function deleteConv(id) {
            MessagingSystem.deleteConversation(id);
        }
    </script>
</body>
</html>
