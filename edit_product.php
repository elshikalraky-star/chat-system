<?php
ob_start();
session_start();

// منع الكاش لضمان ظهور التعديلات فوراً
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

require '../db_connect.php';

// 1. حماية الصفحة
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$error_message = "";
$product = null;
$id = null;

// 2. تحديد المعرف (ID) سواء جئنا من الرابط (GET) أو من الفورم (POST)
if (isset($_GET['id'])) {
    $id = $_GET['id'];
} elseif (isset($_POST['id'])) {
    $id = $_POST['id'];
}

// إذا لم يوجد ID، نعود للرئيسية فوراً
if (!$id) {
    header("Location: index.php");
    exit;
}

// 3. جلب بيانات المنتج الحالية (هذا هو الإصلاح: نجلب البيانات أولاً دائماً)
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// إذا لم يتم العثور على المنتج في القاعدة
if (!$product) {
    header("Location: index.php");
    exit;
}

// 4. معالجة الحفظ (عند ضغط زر الحفظ)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $old_price = trim($_POST['old_price']);
    $description = trim($_POST['description']);
    $imageName = $_POST['old_image']; // الصورة القديمة افتراضياً

    // التحقق من الحقول الفارغة
    if (empty($name) || empty($price) || empty($description)) {
        $error_message = "❌ عذراً، جميع الحقول (الاسم، السعر، الوصف) مطلوبة!";
    } else {
        // معالجة الصورة الجديدة إذا تم رفعها
        if (!empty($_FILES['image']['name'])) {
            $targetDir = "../uploads/";
            // استخدام time() لتجنب تكرار الأسماء
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            if(move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . $fileName)){
                $imageName = $fileName;
            }
        }

        // التحديث في قاعدة البيانات
        try {
            $sql = "UPDATE products SET name=?, price=?, old_price=?, description=?, image=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $price, $old_price, $description, $imageName, $id]);
            
            $_SESSION['success_message'] = "✅ تم التعديل بنجاح!";
            header("Location: index.php"); // العودة لصفحة الأقمشة
            exit;
        } catch (PDOException $e) {
            $error_message = "حدث خطأ أثناء التحديث: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل المنتج</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Cairo', sans-serif; }</style>
</head>
<body class="bg-gray-50 pb-20">

    <div class="bg-white p-4 border-b flex justify-between items-center sticky top-0 z-10 shadow-sm">
        <h1 class="text-xl font-black text-gray-800">تعديل المنتج الحالي✏️</h1>
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

        <form id="editForm" method="POST" enctype="multipart/form-data" autocomplete="off" class="bg-white p-6 rounded-3xl shadow-sm border space-y-4">
            
            <input type="hidden" name="id" value="<?= $product['id'] ?>">
            <input type="hidden" name="old_image" value="<?= htmlspecialchars($product['image']) ?>">

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">اسم المنتج <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="nameInput" value="<?= htmlspecialchars($product['name']) ?>" required class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 outline-none focus:border-black transition">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">سعر البيع الان <span class="text-red-500">*</span></label>
                    <input type="number" name="price" id="priceInput" value="<?= htmlspecialchars($product['price']) ?>" required class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 outline-none focus:border-black transition">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-500 mb-1">السعر قبل الخصم <span class="text-red-500">*</span></label>
                    <input type="number" name="old_price" id="oldPriceInput" value="<?= htmlspecialchars($product['old_price']) ?>" required class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 outline-none focus:border-black transition">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1"> وصف المنتج<span class="text-red-500">*</span></label>
                <textarea name="description" id="descInput" rows="4" required class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 outline-none focus:border-black transition"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <div class="bg-gray-50 p-4 rounded-2xl border border-dashed border-gray-200 text-center relative">
                <label class="block text-sm font-bold text-gray-700 mb-2">تغيير الصورة</label>
                <input type="file" name="image" id="fileInput" accept="image/*" class="text-xs mx-auto block z-10 relative">
                
                <?php if(!empty($product['image'])): ?>
                    <div id="imagePreviewBox" class="mt-3 flex justify-center transition-all">
                        <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" 
                             alt="صورة المنتج" 
                             class="w-20 h-20 object-cover rounded-lg shadow-md border border-gray-300">
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit" class="w-full bg-black text-white py-4 rounded-xl font-black text-lg shadow-lg active:scale-95 transition">حفظ التعديلات الان ✨</button>
        </form>
    </div>
</body>
</html>