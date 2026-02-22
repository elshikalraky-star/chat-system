<?php
// 1. حساب عدد المنتجات مباشرة من القاعدة (أكثر أماناً وسرعة)
$stmt_prod = $conn->query("SELECT COUNT(*) FROM products");
$product_count = $stmt_prod->fetchColumn();

// 2. حساب عدد طلبات الانضمام المعلقة (باستثناء العملاء العاديين)
$stmt_req = $conn->query("SELECT COUNT(*) FROM users WHERE status = 'pending' AND role != 'client'");
$pending_requests_count = $stmt_req->fetchColumn();
?>

<div class="grid grid-cols-2 gap-3 mb-4 mt-6">
    
    <div class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm flex flex-col items-center justify-center text-center">
        <p class="text-gray-400 text-[9px] font-bold uppercase mb-1">إجمالي المنتجات</p>
        <div class="bg-rose-600 text-white w-10 h-10 rounded-2xl flex items-center justify-center font-black text-lg shadow-lg shadow-rose-200">
            <?= $product_count ?>
        </div>
    </div>

    <a href="manage_requests.php?role=tailor" class="bg-white p-4 rounded-3xl border border-blue-100 shadow-sm flex flex-col items-center justify-center text-center relative active:scale-95 transition">
        <p class="text-gray-400 text-[9px] font-bold uppercase mb-1">طلبات جديدة</p>
        <div class="bg-blue-600 text-white w-10 h-10 rounded-2xl flex items-center justify-center font-black text-lg shadow-lg shadow-blue-200">
            <?= $pending_requests_count ?>
        </div>
        
        <?php if($pending_requests_count > 0): ?>
            <span class="absolute top-2 right-2 flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
            </span>
        <?php endif; ?>
    </a>
</div>