<?php
require 'db_connect.php';

// هل تم إرسال الطلب؟
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. استلام البيانات
    $product_id = $_POST['product_id'];
    $price = $_POST['price'];
    $delivery_method = $_POST['delivery_method']; 
    $target_tailor_id = !empty($_POST['target_tailor_id']) ? $_POST['target_tailor_id'] : NULL;

    // 2. تحديد العميل (تلقائياً) لتجنب الأخطاء
    $stmt_user = $conn->query("SELECT id FROM users LIMIT 1");
    $user = $stmt_user->fetch();
    
    if ($user) {
        $customer_id = $user['id'];
    } else {
        // إنشاء عميل وهمي إذا لم يوجد
        $conn->exec("INSERT INTO users (username, email, password, phone) VALUES ('User', 'auto@test.com', '123', '0500000000')");
        $customer_id = $conn->lastInsertId();
    }

    try {
        // 3. حفظ الطلب في قاعدة البيانات
        $stmt = $conn->prepare("INSERT INTO orders (customer_id, delivery_method, target_tailor_id, total_amount, status) VALUES (?, ?, ?, ?, 'pending_payment')");
        $stmt->execute([$customer_id, $delivery_method, $target_tailor_id, $price]);
        
        $order_id = $conn->lastInsertId();

        // 4. حفظ تفاصيل المنتج
        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, price) VALUES (?, ?, ?)");
        $stmt_item->execute([$order_id, $product_id, $price]);

        // ==========================================
        // هنا الجزء الجديد: عرض رسالة النجاح مباشرة
        // ==========================================
        ?>
        <!DOCTYPE html>
        <html lang="ar" dir="rtl">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>تم الطلب بنجاح</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <style>body { font-family: sans-serif; }</style>
        </head>
        <body class="bg-white min-h-screen flex flex-col items-center justify-center p-6 text-center">
            <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center text-5xl mb-6 shadow-lg">✅</div>
            <h1 class="text-3xl font-bold mb-2">تم استلام طلبك!</h1>
            <p class="text-gray-500 mb-8">رقم الطلب: <b>#<?php echo $order_id; ?></b></p>
            
            <div class="bg-gray-50 p-6 rounded-xl w-full max-w-sm border border-gray-200 mb-8 text-right">
                <p class="font-bold text-gray-800 mb-2">ملخص:</p>
                <p>💰 المبلغ: <?php echo $price; ?> ر.س</p>
                <p>🚚 التوصيل: <?php echo ($delivery_method == 'home') ? 'للمنزل' : 'للخياط مباشرة'; ?></p>
            </div>

            <a href="index.php" class="bg-black text-white px-8 py-3 rounded-xl font-bold shadow-lg">العودة للرئيسية</a>
        </body>
        </html>
        <?php
        exit(); // إنهاء الكود هنا لضمان عدم حدوث مشاكل

    } catch(PDOException $e) {
        die("حدث خطأ أثناء الحفظ: " . $e->getMessage());
    }
} else {
    // إذا فتح شخص الملف مباشرة نعيده للرئيسية
    header("Location: index.php");
    exit();
}
?>