<?php
session_start();
// 1. الاتصال بقاعدة البيانات
if (file_exists('db_connect.php')) { 
    require 'db_connect.php'; 
} else {
    die("خطأ: ملف db_connect.php غير موجود.");
}

include 'includes/header.php'; 

// 2. 🟢 التعديل الجوهري: جلب الخياطين من جدول users الموحد
// جلب المستخدمين الذين دورهم 'tailor' وحالتهم 'approved'
$stmt = $conn->prepare("SELECT * FROM users WHERE role = 'tailor' AND status = 'approved' ORDER BY id DESC");
$stmt->execute();
$all_tailors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="bg-gray-50 min-h-screen pb-24 pt-4 px-4 font-['Cairo']">
    
    <div class="flex items-center gap-2 mb-6">
        <a href="index.php" class="text-2xl text-gray-700 hover:text-rose-600 transition-colors">➜</a> 
        <h1 class="text-xl font-black text-gray-900">كل الخياطين المميزين ✂️</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (!empty($all_tailors)): ?>
            <?php foreach ($all_tailors as $p): ?>
                
                <div class="flex justify-center">
                    <?php if (file_exists('tailor_card_style.php')) {
                        include 'tailor_card_style.php'; 
                    } else {
                        echo "<p class='text-red-500'>ملف الكارت مفقود</p>";
                    } ?>
                </div>

            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-20 bg-white rounded-3xl border border-dashed border-gray-200">
                <span class="text-5xl block mb-4">😴</span>
                <p class="text-gray-400 font-bold">لا يوجد خياطين متاحين حالياً</p>
                <a href="index.php" class="text-rose-500 text-sm underline mt-2 inline-block">العودة للرئيسية</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="fixed bottom-0 w-full z-50 right-0 left-0">
    <?php if (file_exists('includes/navbar.php')) include 'includes/navbar.php'; ?>
</div>