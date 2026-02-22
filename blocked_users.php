<?php
// dashboard/blocked_users.php - قائمة المحظورين
session_start();
require '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); exit;
}

$user_id   = (int)$_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'client';

$stmt = $conn->prepare("
    SELECT b.id, b.blocked_user_id, b.reason, b.blocked_at,
           u.username, u.role
    FROM blocked_users b
    LEFT JOIN users u ON u.id = b.blocked_user_id
    WHERE b.user_id = ?
    ORDER BY b.blocked_at DESC
");
$stmt->execute([$user_id]);
$blocked = $stmt->fetchAll();

$roleLabels = ['tailor'=>'خياط','designer'=>'مصمم','packaging'=>'تغليف','client'=>'عميل'];
$roleIcons  = ['tailor'=>'✂️','designer'=>'🎨','packaging'=>'🎁','client'=>'👤'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المحظورون - كُرّة</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Cairo', sans-serif; background: #f9fafb; }</style>
</head>
<body class="min-h-screen pb-16">

    <header class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
        <div class="max-w-2xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="messages_inbox.php" class="w-9 h-9 bg-gray-50 rounded-xl flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            <h1 class="font-black text-gray-800 text-base">🚫 المحظورون</h1>
            <div class="w-9"></div>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 pt-4">

        <?php if (empty($blocked)): ?>
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <div class="text-5xl mb-3">✅</div>
            <h2 class="font-black text-gray-700 text-base">لا يوجد أحد محظور</h2>
            <p class="text-xs text-gray-400 font-bold mt-1">قائمة المحظورين فارغة</p>
        </div>

        <?php else: ?>
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <?php foreach ($blocked as $i => $b):
                $icon  = $roleIcons[$b['role']] ?? '👤';
                $label = $roleLabels[$b['role']] ?? $b['role'];
                $date  = date('Y/m/d', strtotime($b['blocked_at']));
            ?>
            <div class="flex items-center gap-3 px-4 py-4 <?= $i < count($blocked)-1 ? 'border-b border-gray-50' : '' ?>">
                <div class="w-11 h-11 bg-red-50 rounded-2xl flex items-center justify-center text-xl flex-shrink-0">
                    <?= $icon ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-black text-gray-800 text-sm"><?= htmlspecialchars($b['username'] ?? 'مستخدم') ?></p>
                    <p class="text-xs text-gray-400 font-bold"><?= $label ?> · <?= $date ?></p>
                    <?php if ($b['reason']): ?>
                    <p class="text-xs text-gray-500 font-bold mt-0.5">السبب: <?= htmlspecialchars($b['reason']) ?></p>
                    <?php endif; ?>
                </div>
                <button onclick="unblock(<?= $b['blocked_user_id'] ?>, this)"
                        class="bg-emerald-50 text-emerald-600 text-xs font-black px-3 py-2 rounded-xl hover:bg-emerald-100 transition flex-shrink-0">
                    إلغاء
                </button>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </main>

    <script>
    function unblock(userId, btn) {
        if (!confirm('إلغاء الحظر عن هذا المستخدم؟')) return;
        btn.disabled = true; btn.textContent = '...';
        fetch('/messages/unblock_user.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({blocked_user_id: userId})
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                const row = btn.closest('div.flex');
                row.style.opacity = '0';
                row.style.transition = 'opacity 0.3s';
                setTimeout(() => row.remove(), 300);
            } else {
                btn.disabled = false; btn.textContent = 'إلغاء';
            }
        });
    }
    </script>
</body>
</html>
