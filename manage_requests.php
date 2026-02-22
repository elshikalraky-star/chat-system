<?php
session_start();
require '../db_connect.php'; 

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$role = isset($_GET['role']) ? $_GET['role'] : 'tailor';

$role_info = [
    'tailor' => ['name' => 'طلبات الخياطين', 'icon' => '✂️'],
    'designer' => ['name' => 'طلبات المصممات', 'icon' => '👗'],
    'packaging' => ['name' => 'طلبات التغليف', 'icon' => '🎁']
];
$current_role = $role_info[$role];

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id']; 
    if ($_GET['action'] == 'approve') {
        $conn->prepare("UPDATE users SET status = 'approved' WHERE id = ?")->execute([$id]);
    } elseif ($_GET['action'] == 'reject') {
        $conn->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    }
    header("Location: manage_requests.php?role=" . $role);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE role = ? AND status = 'pending' ORDER BY id DESC");
$stmt->execute([$role]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'parts/header.php'; 
?>

<div class="max-w-md mx-auto px-4 mt-6 pb-20 font-['Cairo']">
    <div class="mb-8 text-center">
        <h2 class="text-2xl font-black text-gray-900"><?= $current_role['name'] ?></h2>
        <p class="text-gray-400 font-bold text-[10px]">نظام المراجعة الشامل</p>
    </div>

    <div class="space-y-10">
        <?php if (empty($requests)): ?>
            <p class="text-center text-gray-400 font-bold">لا توجد طلبات معلقة ☕</p>
        <?php else: foreach ($requests as $req): ?>
            
            <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-xl overflow-hidden relative">
                <div class="w-full h-52 bg-gray-50 relative">
                    <img src="../uploads/<?= htmlspecialchars($req['work_image']) ?>" class="w-full h-full object-cover">
                    <div class="absolute -bottom-6 right-6 w-16 h-16 bg-white rounded-full p-1 shadow-lg border-2 border-white">
                        <img src="../uploads/<?= htmlspecialchars($req['provider_image']) ?>" class="w-full h-full object-cover rounded-full">
                    </div>
                </div>

                <div class="p-6 pt-10 text-center">
                    <h3 class="font-black text-gray-900 text-xl"><?= htmlspecialchars($req['provider_name']) ?></h3>
                    
                    <div class="grid grid-cols-2 gap-3 mb-4 mt-4">
                        <div class="bg-amber-50 p-3 rounded-2xl border border-amber-100">
                            <p class="text-[8px] text-amber-600 font-bold mb-1">💰 السعر</p>
                            <p class="text-[11px] font-black text-amber-800"><?= number_format($req['starting_price'], 2) ?> ر.س</p>
                        </div>
                        <div class="bg-blue-50 p-3 rounded-2xl border border-blue-100">
                            <p class="text-[8px] text-blue-600 font-bold mb-1">✨ التخصص</p>
                            <p class="text-[11px] font-black text-blue-800 truncate"><?= htmlspecialchars($req['specialty'] ?: 'عام') ?></p>
                        </div>
                        
                        <?php if(!empty($req['delivery_time'])): ?>
                        <div class="col-span-2 bg-purple-50 p-3 rounded-2xl border border-purple-100 flex justify-between items-center px-6">
                            <p class="text-[10px] text-purple-600 font-bold">⏱️ مدة التنفيذ:</p>
                            <p class="text-[12px] font-black text-purple-800"><?= htmlspecialchars($req['delivery_time']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-6">
                        <div class="bg-gray-50 p-3 rounded-2xl">
                            <p class="text-[8px] text-gray-400 font-bold mb-1">📍 الموقع</p>
                            <p class="text-[11px] font-black text-gray-800"><?= htmlspecialchars($req['location']) ?></p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-2xl">
                            <p class="text-[8px] text-gray-400 font-bold mb-1">📞 الجوال</p>
                            <p class="text-[11px] font-black text-gray-800"><?= htmlspecialchars($req['phone']) ?></p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <a href="manage_requests.php?action=approve&id=<?= $req['id'] ?>&role=<?= $role ?>" class="flex-[2] bg-black text-white py-4 rounded-2xl text-center font-black text-xs shadow-lg active:scale-95 transition">قبول واعتماد ✅</a>
                        <a href="manage_requests.php?action=reject&id=<?= $req['id'] ?>&role=<?= $role ?>" onclick="return confirm('رفض الطلب؟')" class="flex-1 bg-rose-50 text-rose-600 py-4 rounded-2xl text-center font-black text-xs border border-rose-100 active:scale-95 transition">رفض ❌</a>
                    </div>
                </div>
            </div>

        <?php endforeach; endif; ?>
    </div>
</div>
<?php include 'parts/footer.php'; ?>