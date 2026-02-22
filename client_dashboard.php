<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require '../db_connect.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'client') {
    header("Location: ../login.php");
    exit();
}

$user_id       = $_SESSION['user_id'];
$customer_name = $_SESSION['username'] ?? 'العميل';

$stats  = ['total'=>0,'active'=>0,'completed'=>0];
$orders = [];

try {
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status IN ('pending','in_progress') THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
        FROM orders WHERE client_id = ?
    ");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: $stats;

    $stmtOrders = $conn->prepare("
        SELECT id, service_type, price, status, created_at
        FROM orders WHERE client_id = ?
        ORDER BY id DESC LIMIT 5
    ");
    $stmtOrders->execute([$user_id]);
    $orders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {
    die("❌ خطأ: " . $e->getMessage());
}

// عدد الرسائل غير المقروءة
$unread_msgs = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    $unread_msgs = (int) $stmt->fetchColumn();
} catch (Throwable $e) {}
?>

<?php include '../includes/header.php'; ?>

<div class="px-4 pb-28 mt-4">

    <!-- الترحيب وزر الخروج -->
    <div class="mb-5 flex justify-between items-center">
        <div>
            <h2 class="text-lg font-black text-gray-800">
                👋 مرحباً <?= htmlspecialchars($customer_name) ?>
            </h2>
            <p class="text-sm text-gray-500 mt-1">لوحة تحكم حسابك</p>
        </div>
        <a href="../logout.php"
           class="bg-red-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow hover:bg-red-600 transition">
           تسجيل الخروج
        </a>
    </div>

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
            <span class="bg-red-400 text-white text-xs font-black w-7 h-7 rounded-full flex items-center justify-center">
                <?= $unread_msgs > 9 ? '+9' : $unread_msgs ?>
            </span>
        <?php else: ?>
            <span class="text-white text-lg opacity-60">›</span>
        <?php endif; ?>
    </a>

    <!-- الإحصائيات -->
    <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 text-center">
            <h3 class="text-2xl font-black text-violet-600"><?= (int)$stats['active'] ?></h3>
            <p class="text-xs font-bold text-gray-500 mt-1">طلبات جارية</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 text-center">
            <h3 class="text-2xl font-black text-green-600"><?= (int)$stats['completed'] ?></h3>
            <p class="text-xs font-bold text-gray-500 mt-1">مكتملة</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 text-center">
            <h3 class="text-2xl font-black text-gray-800"><?= (int)$stats['total'] ?></h3>
            <p class="text-xs font-bold text-gray-500 mt-1">الكل</p>
        </div>
    </div>

    <!-- آخر الطلبات -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-black text-gray-800 text-sm">آخر الطلبات</h3>
            <a href="../orders.php" class="text-violet-600 text-xs font-bold">عرض الكل</a>
        </div>

        <?php if (empty($orders)): ?>
            <p class="text-gray-400 text-sm text-center py-6">لا توجد طلبات بعد</p>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
            <div class="flex justify-between items-center border-b border-gray-100 py-3 last:border-none">
                <div>
                    <p class="text-sm font-bold text-gray-800">#<?= (int)$order['id'] ?></p>
                    <p class="text-xs text-gray-500"><?= htmlspecialchars($order['service_type']) ?></p>
                    <p class="text-xs text-gray-400 mt-1"><?= date('Y-m-d', strtotime($order['created_at'])) ?></p>
                </div>
                <div class="text-left">
                    <p class="text-sm font-black text-gray-800"><?= number_format($order['price'],2) ?> ر.س</p>
                    <?php
                    echo match($order['status']) {
                        'completed'   => "<span class='text-xs bg-green-100 text-green-600 px-2 py-1 rounded-full font-bold'>مكتمل</span>",
                        'pending'     => "<span class='text-xs bg-yellow-100 text-yellow-600 px-2 py-1 rounded-full font-bold'>قيد الانتظار</span>",
                        'in_progress' => "<span class='text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded-full font-bold'>قيد التنفيذ</span>",
                        default       => "<span class='text-xs bg-red-100 text-red-600 px-2 py-1 rounded-full font-bold'>ملغي</span>",
                    };
                    ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<!-- شريط التنقل السفلي المُحدَّث -->
<nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 shadow-lg z-50">
    <div class="max-w-2xl mx-auto flex justify-around py-3">
        <a href="client_dashboard.php" class="flex flex-col items-center text-violet-600">
            <span class="text-xl">🏠</span>
            <span class="text-[10px] font-black mt-0.5">الرئيسية</span>
        </a>
        <a href="../orders.php" class="flex flex-col items-center text-gray-400">
            <span class="text-xl">📋</span>
            <span class="text-[10px] font-bold mt-0.5">طلباتي</span>
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
        <a href="../register.php" class="flex flex-col items-center text-gray-400">
            <span class="text-xl">👤</span>
            <span class="text-[10px] font-bold mt-0.5">حسابي</span>
        </a>
    </div>
</nav>

<script>
    // تحديث badge الرسائل كل 10 ثواني
    function refreshMsgBadge() {
        fetch('../messages/get_unread_count.php')
            .then(r => r.json())
            .then(d => {
                const badges = document.querySelectorAll('.msg-badge');
                badges.forEach(b => {
                    if (d.count > 0) {
                        b.textContent = d.count > 9 ? '9+' : d.count;
                        b.classList.remove('hidden');
                    } else {
                        b.classList.add('hidden');
                    }
                });
            }).catch(() => {});
    }
    setInterval(refreshMsgBadge, 10000);
</script>
