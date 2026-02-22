<?php 
/**
 * register_designer.php - واجهة انضمام المصممات وفنانات التطريز
 */
session_start();
require 'db_connect.php';       
require 'includes/functions.php'; // تم التعديل هنا لاستدعاء الدالة الموحدة الصحيحة

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. معالجة الصور باحترافية
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    function uploadKorraFile($file, $prefix) {
        if (!empty($file['name'])) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $newName = uniqid($prefix . '_') . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], "uploads/" . $newName)) {
                return $newName;
            }
        }
        return null;
    }

    $provider_image = uploadKorraFile($_FILES['provider_image'], 'avatar');
    $work_image     = uploadKorraFile($_FILES['work_image'], 'art');

    // 2. تقسيم البيانات لتطابق النظام الموحد (تم التعديل هنا)
    $basicData = [
        'username' => trim($_POST['username']),
        'phone'    => trim($_POST['phone']), 
        'password' => $_POST['password'],
        'role'     => 'designer',
        'status'   => 'pending' 
    ];

    $profileData = [
        'business_name'  => trim($_POST['provider_name']), 
        'specialty'      => $_POST['design_type'],
        'price'          => (float)$_POST['price'],
        'time'           => 'حسب الطلب',
        'location'       => $_POST['location'],
        'provider_image' => $provider_image ?? 'default_designer.png',
        'work_image'     => $work_image
    ];

    // 3. التنفيذ بالدالة الصحيحة
    $new_user_id = registerUserSecurely($conn, $basicData, $profileData);

    if ($new_user_id) {
        header("Location: pending_approval.php");
        exit();
    } else {
        $error = "⚠️ رقم الجوال مسجل مسبقاً، حاولي تسجيل الدخول.";
    }
}

include 'includes/header.php'; 
?>

<main class="min-h-screen bg-gray-50 py-10 px-4 font-['Cairo']" dir="rtl">
    <div class="max-w-xl mx-auto bg-white rounded-[30px] shadow-sm border border-gray-100 p-6 md:p-10 relative overflow-hidden">
        
        <div class="text-center mb-8 relative z-10">
            <div class="w-16 h-16 bg-purple-50 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-3">🎨</div>
            <h1 class="text-2xl font-black text-gray-900">انضمي لمبدعات كُرّة</h1>
            <p class="text-sm text-gray-500 font-bold mt-1">مساحة مخصصة لفن التطريز والرسم اليدوي</p>
            
            <?php if($error): ?>
                <div class="mt-4 bg-red-50 text-red-600 p-3 rounded-xl text-sm font-bold border border-red-100">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
        </div>

        <form action="" method="POST" enctype="multipart/form-data" class="space-y-5 relative z-10">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black text-gray-700 mb-2">الاسم الشخصي</label>
                    <input type="text" name="username" required placeholder="مثال: نورة محمد" class="w-full p-3 bg-gray-50 rounded-xl outline-none font-bold text-sm border focus:border-purple-500 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-700 mb-2">رقم الجوال</label>
                    <?php include 'includes/smart_phone.php'; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black text-gray-700 mb-2">كلمة المرور</label>
                    <input type="password" name="password" required class="w-full p-3 bg-gray-50 rounded-xl outline-none font-bold text-sm border focus:border-purple-500 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-700 mb-2">المدينة / الحي</label>
                    <input type="text" name="location" required placeholder="مثال: الرياض" class="w-full p-3 bg-gray-50 rounded-xl outline-none font-bold text-sm border focus:border-purple-500 transition-all">
                </div>
            </div>

            <hr class="border-dashed border-gray-200 my-4">

            <div>
                <label class="block text-xs font-black text-gray-800 mb-2">اسم المعمل أو البراند الإبداعي</label>
                <input type="text" name="provider_name" placeholder="مثال: تطريز غصن / ريشة فنانة" required class="w-full p-3 bg-purple-50/50 rounded-xl outline-none font-black text-sm border border-purple-100 focus:border-purple-500 transition-all">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black text-gray-700 mb-2">يبدأ سعر الخدمة من (ريال)</label>
                    <input type="number" name="price" placeholder="0.00" required class="w-full p-3 bg-gray-50 rounded-xl outline-none font-bold text-sm focus:border-purple-500 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-700 mb-2">نوع الفن 🪡</label>
                    <select name="design_type" class="w-full p-3 bg-gray-50 rounded-xl outline-none font-bold text-sm focus:border-purple-500 transition-all cursor-pointer">
                        <option value="تطريز يدوي">تطريز يدوي (Hand Made)</option>
                        <option value="رسم على القماش">رسم فني (Painting)</option>
                        <option value="شك وخرز">شك وخرز (Beading)</option>
                        <option value="تصميم عبايات">تصميم وتفصيل كامل</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                <div class="border-2 border-dashed border-gray-200 rounded-2xl p-4 text-center hover:bg-gray-50 transition-colors relative">
                    <input type="file" name="provider_image" accept="image/*" required class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                    <span class="text-2xl block mb-1">📸</span>
                    <span class="text-[10px] font-bold text-gray-600">ارفعي شعار البراند</span>
                </div>

                <div class="border-2 border-dashed border-purple-200 rounded-2xl p-4 text-center hover:bg-purple-50 transition-colors relative">
                    <input type="file" name="work_image" accept="image/*" required class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                    <span class="text-2xl block mb-1">👘</span>
                    <span class="text-[10px] font-bold text-gray-600">أجمل قطعة نفذتيها</span>
                </div>
            </div>

            <button type="submit" class="w-full bg-gray-900 text-white py-4 rounded-2xl font-black shadow-lg hover:scale-[0.98] transition-transform mt-6">
                إرسال طلب الانضمام للمصممات 🚀
            </button>
        </form>
    </div>
</main>

<style>
    .iti__country-list { z-index: 9999 !important; text-align: left; }
    /* تحسين شكل رفع الملفات */
    input[type="file"]::file-selector-button { display: none; }
</style>

<?php include 'includes/navbar.php'; ?>
