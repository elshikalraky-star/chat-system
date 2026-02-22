<?php
session_start();
require '../db_connect.php';

// التأكد من صلاحية الإدمن
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// جلب المنتجات (الأقمشة) من قاعدة البيانات - كودك القديم كما هو
$stmt = $conn->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$product_count = count($products);

// 1. استدعاء الهيدر الموحد (الذي يحتوي على الجرس والستايل)
include 'parts/header.php'; 
?>

<div class="max-w-md mx-auto px-4 mt-6">
    
    <a href="index.php" class="flex items-center gap-2 text-gray-500 font-bold text-xs mb-6 hover:text-black transition">
        <span>↩️</span> عودة لمركز القيادة الرئيسي
    </a>

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-xl font-black text-gray-900">إدارة الأقمشة 🧵</h2>
            <p class="text-[10px] text-gray-400 font-bold">لديك حالياً <?= $product_count ?> منتج معروض</p>
        </div>
        <a href="add_product.php" class="bg-black text-white px-4 py-2 rounded-xl font-black text-xs shadow-lg active:scale-95 transition">
            + إضافة جديد
        </a>
    </div>

    <div class="space-y-3">
        <?php foreach ($products as $p): 
            $price = $p['price'];
            $old_price = $p['old_price'];
            $has_discount = ($old_price > $price && $old_price > 0);
            $discount_percent = $has_discount ? floor((($old_price - $price) / $old_price) * 100) : 0;
        ?>
        <div class="bg-white p-3 rounded-3xl border border-gray-100 shadow-sm flex items-center justify-between gap-3">
            
            <div class="w-20 h-20 bg-gray-50 rounded-2xl overflow-hidden shrink-0 border border-gray-100 relative">
                <?php if (!empty($p['image'])): ?>
                    <img src="../uploads/<?= $p['image'] ?>" class="w-full h-full object-cover">
                    <?php if($has_discount): ?>
                        <span class="absolute top-0 left-0 bg-red-600 text-white text-[9px] px-1.5 font-black rounded-br-lg z-10">-%<?= $discount_percent ?></span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="flex-1 text-right">
                <h3 class="font-black text-sm text-gray-900 mb-1 truncate w-32"><?= htmlspecialchars($p['name']) ?></h3>
                <div class="flex flex-col gap-0.5">
                    <span class="text-sm font-black text-gray-900"><?= $price ?> ر.س</span>
                    <?php if($has_discount): ?>
                        <span class="text-[10px] text-gray-400 line-through"><?= $old_price ?> ر.س</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex flex-col gap-2 shrink-0 w-20">
                <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn-click w-full py-2 bg-blue-50 text-blue-700 rounded-xl flex items-center justify-center gap-1 text-[11px] font-black border border-blue-100">
                    <span>✏️</span> <span>تعديل</span>
                </a>
                <a href="#" onclick="confirmDelete(<?= $p['id'] ?>)" class="btn-click w-full py-2 bg-rose-50 text-rose-700 rounded-xl flex items-center justify-center gap-1 text-[11px] font-black border border-rose-100">
                    <span>🗑️</span> <span>حذف</span>
                </a>
            </div>

        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php 
// 2. استدعاء الفوتر (الذي يحتوي على مودال الحذف والسكربتات)
include 'parts/footer.php'; 
?>