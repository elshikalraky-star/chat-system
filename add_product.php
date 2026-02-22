<?php
ob_start();
session_start();

// منع الكاش (لضمان عدم تكرار الإرسال بالخطأ)
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// حماية الصفحة: للمدراء فقط
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require '../db_connect.php';

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // تنظيف المدخلات
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $old_price = trim($_POST['old_price']);
    $description = trim($_POST['description']);

    // 🛑 الحماية الشاملة: فحص الـ 5 خانات (اسم، سعر، سعر قديم، وصف، صورة)
    if (empty($name) || empty($price) || empty($old_price) || empty($description) || empty($_FILES['image']['name'])) {
        $error_message = "❌ خطأ: جميع الخانات مطلوبة! (الاسم، السعر، السعر القديم، الوصف، والصورة).";
    } 
    else {
        // تجهيز مجلد الصور
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        
        // معالجة الصورة (تسمية فريدة)
        $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = uniqid() . '.' . $fileExtension;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . $imageName)) {
            try {
                // ✅ التعديل هنا: أضفنا category وقيمتها 'fabrics'
                // لكي يظهر المنتج في الصفحة الرئيسية فوراً
                $sql = "INSERT INTO products (name, price, old_price, description, image, category) VALUES (?, ?, ?, ?, ?, 'fabrics')";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$name, $price, $old_price, $description, $imageName]);

                $_SESSION['success_message'] = "✅ تم إضافة المنتج بنجاح!";
                header("Location: index.php");
                exit;
            } catch (PDOException $e) {
                $error_message = "خطأ في قاعدة البيانات: " . $e->getMessage();
            }
        } else {
            $error_message = "فشل في رفع الصورة.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة منتج</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Cairo', sans-serif; }</style>
</head>
<body class="bg-gray-50 pb-20">

    <div class="bg-white p-4 border-b flex justify-between items-center sticky top-0 z-10 shadow-sm">
        <h1 class="text-xl font-black text-gray-800">إضافة منتج جديد 🛍️</h1>
        <a href="index.php" class="bg-black text-white text-[10px] font-bold px-4 py-2 rounded-xl shadow-lg active:scale-95 transition flex flex-col items-center justify-center leading-tight gap-0.5">
            <span>الغاء ورجوع</span>
            <span class="flex items-center gap-1">للوحة التحكم ↩️</span>
        </a>
    </div>

    <div class="p-4 max-w-lg mx-auto mt-6">
        
        <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4 font-bold text-center shadow-sm">
                <?= $error_message ?>
            </div>
        <?php endif; ?>

        <form id="productForm" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-3xl shadow-sm border space-y-4">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">اسم المنتج <span class="text-red-500">*</span></label>
                <input type="text" name="name" required class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 outline-none focus:ring-2 focus:ring-rose-500 transition">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">سعر البيع <span class="text-red-500">*</span></label>
                    <input type="number" name="price" required class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-500 mb-1">السعر قبل الخصم <span class="text-red-500">*</span></label>
                    <input type="number" name="old_price" required class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 outline-none transition">
                </div>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">الوصف <span class="text-red-500">*</span></label>
                <textarea name="description" rows="3" required class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 outline-none transition"></textarea>
            </div>
            <div class="bg-red-50 p-2 rounded-xl border border-red-100">
                <label class="block text-sm font-bold text-gray-700 mb-1">الصورة (إجبارية) <span class="text-red-500">*</span></label>
                <input type="file" name="image" accept="image/*" required class="w-full bg-white border border-gray-200 rounded-xl p-2 text-sm">
            </div>
            <button type="submit" class="w-full bg-black text-white py-4 rounded-xl font-black text-lg shadow-lg active:scale-95 transition">حفظ المنتج الان✨</button>
        </form>
    </div>

    <script>
        // تفريغ الفورم عند العودة للصفحة لضمان عدم تكرار البيانات
        window.onpageshow = function(event) {
            if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
                document.getElementById('productForm').reset();
            }
        };
    </script>
</body>
</html>