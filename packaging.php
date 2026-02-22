<?php
session_start();
require 'db_connect.php'; // التأكد من الاتصال بقاعدة البيانات
include 'includes/header.php'; 

// ⚙️ التعديل الجوهري: الاستعلام من جدول users الموحد بدلاً من الجدول المحذوف
$stmt = $conn->prepare("SELECT * FROM users WHERE role = 'packaging' AND status = 'approved' ORDER BY id DESC");
$stmt->execute();
$all_packaging = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "خدمات التغليف المتميزة 🎁";
?>

<div class="bg-gray-50 min-h-screen pb-24 pt-4 px-4 font-['Cairo']">
    <div class="flex items-center gap-2 mb-6">
        <a href="index.php" class="text-2xl text-gray-700">➜</a> 
        <h1 class="text-xl font-black text-gray-900"><?php echo $title; ?></h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (!empty($all_packaging)): ?>
            <?php foreach ($all_packaging as $p): ?>
                
                <?php include 'tailor_card_style.php'; ?>

            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-20 bg-white rounded-3xl border border-dashed border-gray-200">
                <p class="text-gray-400 font-bold">لا يوجد مقدمو خدمات تغليف حالياً ✨</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="fixed bottom-0 w-full z-50 right-0 left-0">
    <?php include 'includes/navbar.php'; ?>
</div>