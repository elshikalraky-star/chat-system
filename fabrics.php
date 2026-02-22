<?php
ob_start();
// ربط الملف بقاعدة البيانات والملفات الأساسية للمشروع
if (file_exists('db_connect.php')) { require 'db_connect.php'; }
if (file_exists('security.php')) { include 'security.php'; }

/** * هذا الملف هو "الأساس" لقسم الأقمشة.
 * أي منتج حالي أو مستقبلي يحمل تصنيف 'fabrics' سيظهر هنا تلقائياً.
 */
try {
    // جلب البيانات من جدول المنتجات بناءً على تصنيف الأقمشة
    $stmt = $conn->prepare("SELECT * FROM products WHERE category = 'fabrics' ORDER BY id DESC");
    $stmt->execute();
    $fabrics_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) { $fabrics_data = []; }
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>قسم الأقمشة والمنتجات</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; -webkit-tap-highlight-color: transparent; }
    </style>
</head>
<body class="bg-gray-50 pb-24">

    <?php if(file_exists('includes/header.php')) { include 'includes/header.php'; } ?>

    <main class="min-h-screen p-4 md:px-8 max-w-screen-xl mx-auto">
        
        <div class="flex items-center gap-3 mb-6 bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
            <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center text-2xl">👕</div>
            <div>
                <h1 class="font-black text-gray-900 text-lg leading-none">قسم الأقمشة والمنسوجات</h1>
                <p class="text-[10px] text-gray-400 font-bold mt-1">تجد هنا كافة الخامات الحالية والمضافة حديثاً</p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php if(!empty($fabrics_data)): ?>
                <?php foreach($fabrics_data as $p): ?>
                    <div class="w-full">
                        <?php if(file_exists('card.php')) { include 'card.php'; } ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-2 py-24 text-center">
                    <p class="text-gray-400 font-bold text-sm">جاري تحديث المنتجات حالياً...</p>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <div class="w-full fixed bottom-0 left-0 right-0 z-50">
        <?php if(file_exists('includes/navbar.php')) { include 'includes/navbar.php'; } ?>
    </div>

</body>
</html>