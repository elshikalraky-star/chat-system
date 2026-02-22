<?php
// dashboard/chat.php - صفحة الدردشة
session_start();
require '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); exit;
}

$user_id   = (int)$_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'client';

// يمكن الوصول من خلال:
// 1. conversation_id مباشرة
// 2. user_id (الطرف الآخر) - يُنشئ/يفتح المحادثة
$other_user_id  = (int)($_GET['user_id'] ?? 0);
$conversation_id= (int)($_GET['conversation_id'] ?? 0);

// إذا تم تمرير user_id فقط، نبحث عن المحادثة أو نُعدّها للإنشاء
if ($other_user_id > 0 && $conversation_id === 0) {
    $stmt = $conn->prepare("SELECT id FROM conversations WHERE (user_id_1=? AND user_id_2=?) OR (user_id_1=? AND user_id_2=?)");
    $stmt->execute([$user_id, $other_user_id, $other_user_id, $user_id]);
    $cv = $stmt->fetch();
    if ($cv) $conversation_id = (int)$cv['id'];
}

// جلب بيانات الطرف الآخر
if ($other_user_id <= 0 && $conversation_id > 0) {
    $stmt = $conn->prepare("SELECT user_id_1, user_id_2 FROM conversations WHERE id=? AND (user_id_1=? OR user_id_2=?)");
    $stmt->execute([$conversation_id, $user_id, $user_id]);
    $cv = $stmt->fetch();
    if ($cv) {
        $other_user_id = ($cv['user_id_1'] === $user_id) ? (int)$cv['user_id_2'] : (int)$cv['user_id_1'];
    }
}

if ($other_user_id <= 0) {
    header("Location: messages_inbox.php"); exit;
}

// جلب بيانات الطرف الآخر
$stmt = $conn->prepare("SELECT u.id, u.username, u.role, p.provider_name, p.provider_image FROM users u LEFT JOIN provider_profiles p ON p.user_id=u.id WHERE u.id=?");
$stmt->execute([$other_user_id]);
$other = $stmt->fetch();

if (!$other) {
    header("Location: messages_inbox.php"); exit;
}

// التحقق من الحظر
$stmt = $conn->prepare("SELECT id FROM blocked_users WHERE (user_id=? AND blocked_user_id=?) OR (user_id=? AND blocked_user_id=?)");
$stmt->execute([$user_id, $other_user_id, $other_user_id, $user_id]);
$isBlocked = (bool)$stmt->fetch();

// جلب الرسائل الأولية
$messages = [];
if ($conversation_id > 0) {
    $stmt = $conn->prepare("
        SELECT m.id, m.sender_id, m.message_text, m.is_read, m.created_at
        FROM messages m
        WHERE m.conversation_id=?
        ORDER BY m.created_at ASC
        LIMIT 100
    ");
    $stmt->execute([$conversation_id]);
    $messages = $stmt->fetchAll();

    // تحديد كمقروءة
    $conn->prepare("UPDATE messages SET is_read=1, read_at=NOW() WHERE conversation_id=? AND receiver_id=? AND is_read=0")->execute([$conversation_id, $user_id]);
    $conn->prepare("UPDATE message_notifications SET unread_count=0 WHERE user_id=? AND conversation_id=?")->execute([$user_id, $conversation_id]);
}

$displayName = $other['provider_name'] ?: $other['username'];

$roleIcons = ['tailor'=>'✂️','designer'=>'🎨','packaging'=>'🎁','client'=>'👤'];
$roleIcon  = $roleIcons[$other['role']] ?? '💬';

$backLink = 'messages_inbox.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دردشة مع <?= htmlspecialchars($displayName) ?> - كُرّة</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/messages.css">
    <style>
        body { font-family: 'Cairo', sans-serif; background: #f3f4f6; overflow: hidden; }
        .full-chat { height: 100dvh; display: flex; flex-direction: column; }
    </style>
</head>
<body>

<div class="full-chat">

    <!-- هيدر الدردشة -->
    <header class="bg-white border-b border-gray-100 shadow-sm flex-shrink-0">
        <div class="max-w-2xl mx-auto px-4 py-3 flex items-center gap-3">
            <a href="<?= $backLink ?>" class="w-9 h-9 bg-gray-50 rounded-xl flex items-center justify-center text-gray-500 hover:bg-gray-100 transition flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            <div class="w-10 h-10 bg-violet-100 rounded-full flex items-center justify-center text-lg flex-shrink-0">
                <?= $roleIcon ?>
            </div>

            <div class="flex-1 min-w-0">
                <p class="font-black text-gray-800 text-sm truncate"><?= htmlspecialchars($displayName) ?></p>
                <p class="text-xs text-gray-400 font-bold" id="chat-status">
                    <?php
                    $roleLabels = ['tailor'=>'خياط','designer'=>'مصمم','packaging'=>'تغليف','client'=>'عميل'];
                    echo $roleLabels[$other['role']] ?? '';
                    ?>
                </p>
            </div>

            <!-- قائمة الخيارات -->
            <div class="relative" id="options-menu-wrapper">
                <button onclick="toggleOptions()" class="w-9 h-9 bg-gray-50 rounded-xl flex items-center justify-center text-gray-400 hover:bg-gray-100 transition text-lg font-black">
                    ⋮
                </button>
                <div id="options-dropdown" class="hidden absolute left-0 top-11 bg-white rounded-2xl shadow-xl border border-gray-100 w-44 z-50 overflow-hidden">
                    <?php if (!$isBlocked): ?>
                    <button onclick="blockUser(<?= $other_user_id ?>, '<?= htmlspecialchars(addslashes($displayName)) ?>')"
                            class="w-full text-right px-4 py-3 text-sm font-bold text-red-500 hover:bg-red-50 transition flex items-center gap-2">
                        🚫 حظر المستخدم
                    </button>
                    <?php else: ?>
                    <button onclick="unblockUser(<?= $other_user_id ?>)"
                            class="w-full text-right px-4 py-3 text-sm font-bold text-emerald-600 hover:bg-emerald-50 transition flex items-center gap-2">
                        ✅ إلغاء الحظر
                    </button>
                    <?php endif; ?>
                    <?php if ($conversation_id > 0): ?>
                    <button onclick="MessagingSystem.deleteConversation(<?= $conversation_id ?>)"
                            class="w-full text-right px-4 py-3 text-sm font-bold text-gray-500 hover:bg-gray-50 transition flex items-center gap-2 border-t border-gray-50">
                        🗑 حذف المحادثة
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- تنبيه الحظر -->
    <?php if ($isBlocked): ?>
    <div class="bg-red-50 border-b border-red-100 px-4 py-3 text-center flex-shrink-0">
        <p class="text-sm font-bold text-red-600">🚫 هذا المستخدم محظور — لا يمكن إرسال رسائل جديدة</p>
    </div>
    <?php endif; ?>

    <!-- صندوق الرسائل -->
    <div id="chat-messages-box" class="flex-1 overflow-y-auto px-4 py-4 flex flex-col gap-2.5">

        <?php if (empty($messages) && !$isBlocked): ?>
        <div class="flex flex-col items-center justify-center flex-1 py-12 text-center">
            <div class="text-5xl mb-3"><?= $roleIcon ?></div>
            <p class="font-black text-gray-600 text-sm"><?= htmlspecialchars($displayName) ?></p>
            <p class="text-xs text-gray-400 font-bold mt-1">ابدأ المحادثة بإرسال رسالة</p>
        </div>
        <?php endif; ?>

        <?php foreach ($messages as $msg):
            $isSent = ((int)$msg['sender_id'] === $user_id);
            $time   = date('H:i', strtotime($msg['created_at']));
        ?>
        <div class="msg-wrapper <?= $isSent ? 'sent' : 'received' ?>">
            <div class="msg-bubble <?= $isSent ? 'sent' : 'received' ?>">
                <div><?= nl2br(htmlspecialchars($msg['message_text'])) ?></div>
                <div class="flex items-center gap-1 <?= $isSent ? 'justify-end' : 'justify-start' ?> mt-1">
                    <span class="msg-time"><?= $time ?></span>
                    <?php if ($isSent): ?>
                    <span class="msg-read-tick <?= $msg['is_read'] ? 'read' : '' ?>">
                        <?= $msg['is_read'] ? '✓✓' : '✓' ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- حقل الإدخال -->
    <?php if (!$isBlocked): ?>
    <div class="chat-input-bar flex-shrink-0 max-w-2xl w-full mx-auto">
        <input type="hidden" id="receiver-id-input" value="<?= $other_user_id ?>">
        <textarea id="msg-input" class="chat-input" placeholder="اكتب رسالتك..." rows="1"></textarea>
        <button id="send-btn" class="send-btn">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
        </button>
    </div>
    <?php else: ?>
    <div class="bg-white border-t border-gray-100 px-4 py-4 text-center flex-shrink-0">
        <button onclick="unblockUser(<?= $other_user_id ?>)"
                class="bg-emerald-500 text-white px-6 py-2.5 rounded-2xl font-black text-sm hover:bg-emerald-600 transition">
            ✅ إلغاء الحظر للمراسلة
        </button>
    </div>
    <?php endif; ?>

</div>

<script src="../js/messages.js"></script>
<script>
    <?php if (!$isBlocked): ?>
    MessagingSystem.initChat(<?= $conversation_id ?>, <?= $user_id ?>);
    <?php else: ?>
    // لا تحديث تلقائي عند الحظر
    document.addEventListener('DOMContentLoaded', () => {
        MessagingSystem.scrollToBottom();
    });
    <?php endif; ?>

    function toggleOptions() {
        const d = document.getElementById('options-dropdown');
        d.classList.toggle('hidden');
    }

    document.addEventListener('click', (e) => {
        const wrapper = document.getElementById('options-menu-wrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            document.getElementById('options-dropdown')?.classList.add('hidden');
        }
    });

    function blockUser(uid, name) {
        MessagingSystem.blockUser(uid, name);
    }

    function unblockUser(uid) {
        if (!confirm('إلغاء الحظر عن هذا المستخدم؟')) return;
        fetch('/messages/unblock_user.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({blocked_user_id: uid})
        }).then(r => r.json()).then(d => {
            if (d.success) location.reload();
        });
    }
</script>
</body>
</html>
