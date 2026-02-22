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

$stats = ['total'=>0,'active'=>0,'completed'=>0];
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

    // عدد الرسائل غير المقروءة
    $stmtMsg = $conn->prepare("SELECT COALESCE(SUM(unread_count),0) as cnt FROM message_notifications WHERE user_id=?");
    $stmtMsg->execute([$user_id]);
    $unread_messages = (int)$stmtMsg->fetchColumn();

} catch (Throwable $e) {
    die("❌ خطأ: " . $e->getMessage());
}
?>

<?php include '../includes/header.php'; ?>

<div class="px-4 pb-28 mt-4">

    <!-- ترحيب وخروج -->
    <div class="mb-5 flex justify-between items-center">
        <div>
            <h2 class="text-lg font-black text-gray-800">
                👋 مرحباً <?php echo htmlspecialchars($customer_name); ?>
            </h2>
            <p class="text-sm text-gray-500 mt-1">لوحة تحكم حسابك</p>
        </div>
        <a href="../logout.php" class="bg-red-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow hover:bg-red-600 transition">
            تسجيل الخروج
        </a>
    </div>

    <!-- ═══ زر الرسائل مع Badge ═══ -->
    <a href="messages_inbox.php"
       class="flex items-center justify-between bg-violet-600 text-white rounded-2xl px-5 py-4 mb-5 shadow-lg hover:bg-violet-700 transition active:scale-[0.98]">
        <div class="flex items-center gap-3">
            <span class="text-2xl">💬</span>
            <div>
                <p class="font-black text-sm">صندوق الرسائل</p>
                <p class="text-xs opacity-75 font-bold">تواصل مع الخياطين والمصممين</p>
            </div>
        </div>
        <?php if ($unread_messages > 0): ?>
        <span class="bg-white text-violet-700 font-black text-xs px-2.5 py-1 rounded-full shadow">
            <?= $unread_messages > 9 ? '+9' : $unread_messages ?> جديد
        </span>
        <?php else: ?>
        <svg class="w-5 h-5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        <?php endif; ?>
    </a>

    <!-- الإحصائيات -->
    <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 text-center">
            <h3 class="text-2xl font-black text-violet-600"><?php echo (int)$stats['active']; ?></h3>
            <p class="text-xs font-bold text-gray-500 mt-1">طلبات جارية</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 text-center">
            <h3 class="text-2xl font-black text-green-600"><?php echo (int)$stats['completed']; ?></h3>
            <p class="text-xs font-bold text-gray-500 mt-1">مكتملة</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 text-center">
            <h3 class="text-2xl font-black text-gray-800"><?php echo (int)$stats['total']; ?></h3>
            <p class="text-xs font-bold text-gray-500 mt-1">الإجمالي</p>
        </div>
    </div>

    <!-- آخر الطلبات -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-black text-gray-800 text-sm">آخر الطلبات</h3>
            <a href="../orders.php" class="text-violet-600 text-xs font-bold">عرض الكل</a>
        </div>

        <?php if(empty($orders)): ?>
            <p class="text-gray-400 text-sm text-center py-6">لا توجد طلبات بعد</p>
        <?php else: ?>
            <?php foreach($orders as $order): ?>
                <div class="flex justify-between items-center border-b border-gray-100 py-3 last:border-none">
                    <div>
                        <p class="text-sm font-bold text-gray-800">#<?php echo (int)$order['id']; ?></p>
                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($order['service_type']); ?></p>
                        <p class="text-xs text-gray-400 mt-1"><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></p>
                    </div>
                    <div class="text-left">
                        <p class="text-sm font-black text-gray-800"><?php echo number_format($order['price'],2); ?> ر.س</p>
                        <?php
                        $statusMap = [
                            'completed'   => ['bg-green-100',  'text-green-600',  'مكتمل'],
                            'pending'     => ['bg-yellow-100', 'text-yellow-600', 'قيد الانتظار'],
                            'in_progress' => ['bg-blue-100',   'text-blue-600',   'قيد التنفيذ'],
                        ];
                        [$bg, $tc, $label] = $statusMap[$order['status']] ?? ['bg-red-100','text-red-600','ملغي'];
                        echo "<span class='text-xs {$bg} {$tc} px-2 py-1 rounded-full font-bold'>{$label}</span>";
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<!-- الشريط السفلي مع badge الرسائل -->
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
        <!-- زر الرسائل مع badge -->
        <a href="messages_inbox.php" class="flex flex-col items-center text-gray-400 relative">
            <span class="text-xl">💬</span>
            <span class="text-[10px] font-bold mt-0.5">الرسائل</span>
            <span id="nav-msg-badge" class="msg-badge msg-nav-badge <?= $unread_messages > 0 ? 'show' : '' ?>">
                <?= $unread_messages > 0 ? ($unread_messages > 9 ? '+9' : $unread_messages) : '' ?>
            </span>
        </a>
        <a href="../register.php" class="flex flex-col items-center text-gray-400">
            <span class="text-xl">👤</span>
            <span class="text-[10px] font-bold mt-0.5">حسابي</span>
        </a>
    </div>
</nav>

<link rel="stylesheet" href="../css/messages.css">
<script src="../js/messages.js"></script>
