<?php 
// register_packaging.php - صفحة تسجيل خدمات التغليف
session_start();
require 'db_connect.php';
require 'includes/functions.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // 1. معالجة رفع الصور
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    function uploadMyFile($fileInputName, $defaultName) {
        global $uploadDir;
        if (!empty($_FILES[$fileInputName]['name'])) {
            $fileName = time() . '_' . rand(1000, 9999) . '_' . basename($_FILES[$fileInputName]['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $targetPath)) {
                return $fileName;
            }
        }
        return $defaultName;
    }

    $provider_image = uploadMyFile('provider_image', 'default_packaging.png');
    $work_image     = uploadMyFile('work_image', 'default_gift.png');

    // 2. تقسيم البيانات بشكل صحيح (تم التصحيح هنا)
    $basicData = [
        'username' => $_POST['username'],
        'phone'    => $_POST['phone'],
        'password' => $_POST['password'],
        'role'     => 'packaging',
        'status'   => 'pending'
    ];

    $profileData = [
        'provider_name'  => $_POST['provider_name'],  // تم التصحيح: كان 'business_name'
        'provider_type'  => $_POST['packaging_type'], // تم التصحيح: كان 'specialty'
        'price'          => $_POST['starting_price'],
        'time'           => 'حسب الطلب',
        'location'       => $_POST['location'],
        'provider_image' => $provider_image,
        'work_image'     => $work_image
    ];

    // 3. التنفيذ وتسجيل الجلسة والتوجيه
    $new_user_id = registerUserSecurely($conn, $basicData, $profileData);

    if ($new_user_id) {
        session_regenerate_id(true);
        $_SESSION['user_id']    = $new_user_id;
        $_SESSION['username']   = $_POST['username'];
        $_SESSION['role']       = 'packaging';
        $_SESSION['login_time'] = time();

        echo "<script>
            alert('🎉 تم استلام طلبك! حساب خدمة التغليف الآن قيد المراجعة.');
            window.location.href = '/dashboard/packaging_dashboard.php';
        </script>";
        exit();
    } else {
        $error = "❌ خطأ! رقم الجوال هذا مسجل لدينا بالفعل.";
    }
}

include 'includes/header.php'; 
?>

<main class="min-h-screen bg-gray-50 py-10 px-4 font-['Cairo']">
    <div class="max-w-xl mx-auto bg-white rounded-[30px] shadow-sm border border-gray-100 p-6 md:p-10 relative overflow-hidden">
        
        <div class="text-center mb-8 relative z-10">
            <div class="w-16 h-16 bg-amber-50 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-3">🎁</div>
            <h1 class="text-2xl font-black text-gray-900">انضم كـ مقدم خدمات تغليف</h1>
            <p class="text-sm text-gray-500 font-bold mt-1">ساعد العملاء في تغليف هداياهم ومشترياتهم</p>
            
            <?php if($error): ?>
                <div class="mt-4 bg-red-50 text-red-600 p-3 rounded-xl text-sm font-bold border border-red-100">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
        </div>

        <form action="" method="POST" enctype="multipart/form-data" class="space-y-5 relative z-10">
            
            <input type="hidden" name="role" value="packaging">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black text-gray-700 mb-2">الاسم الكامل</label>
                    <input type="text" name="username" required class="w-full p-3 bg-gray-50 rounded-xl outline-none font-bold text-sm border focus:border-amber-500 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-700 mb-2">رقم الجوال</label>
                    <input type="tel" name="phone" placeholder="05xxxxxxxx" required class="w-full p-3 bg-gray-50 rounded-xl outline-none font-bold text-sm text-left border focus:border-amber-500 transition-all" dir="ltr">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black text-gray-700 mb-2">كلمة المرور</label>
                    <input type="password" name="password" required class="w-full p-3 bg-gray-50 rounded-xl outline-none font-bold text-sm border focus:border-amber-500 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-700 mb-2">المدينة / الحي</label>
                    <input type="text" name="location" placeholder="مثال: الرياض، حي الياسمين" required class="w-full p-3 bg-gray-50 rounded-xl outline-none font-bold text-sm border focus:border-amber-500 transition-all">
                </div>
            </div>

            <hr class="border-dashed border-gray-200 my-4">

            <div>
                <label class="block text-xs font-black text-gray-800 mb-2">اسم الخدمة / البراند</label>
                <input type="text" name="provider_name" placeholder="مثال: تغليف هدايا فاخر" required class="w-full p-3 bg-amber-50/50 rounded-xl outline-none font-black text-sm border border-amber-100 focus:border-amber-500 transition-all">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black text-gray-700 mb-2">سعر البدء (ريال)</label>
                    <input type="number" name="starting_price" placeholder="يبدأ من..." required class="w-full p-3 bg-gray-50 rounded-xl outline-none font-bold text-sm">
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-700 mb-2">نوع التغليف</label>
                    <select name="packaging_type" class="w-full p-3 bg-gray-50 rounded-xl outline-none font-bold text-sm">
                        <option value="علب وهدايا">علب وهدايا</option>
                        <option value="تغليف ورد">تغليف ورد</option>
                        <option value="بوكسات عطور">بوكسات عطور</option>
                        <option value="تغليف شامل">تغليف شامل</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                <div class="border-2 border-dashed border-gray-200 rounded-2xl p-4 text-center hover:bg-gray-50 transition-colors relative">
                    <input type="file" name="provider_image" accept="image/*" required class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                    <span class="text-2xl block mb-1">📸</span>
                    <span class="text-[10px] font-bold text-gray-600">ارفع الشعار أو صورتك الشخصية</span>
                </div>

                <div class="border-2 border-dashed border-amber-200 rounded-2xl p-4 text-center hover:bg-amber-50 transition-colors relative">
                    <input type="file" name="work_image" accept="image/*" required class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                    <span class="text-2xl block mb-1">📦</span>
                    <span class="text-[10px] font-bold text-gray-600">ارفع صورة نموذج تغليف</span>
                </div>
            </div>

            <button type="submit" class="w-full bg-gray-900 text-white py-4 rounded-2xl font-black shadow-lg hover:scale-[0.98] transition-transform mt-6">
                إنشاء حساب تغليف ✨
            </button>
        </form>
    </div>
</main>
<?php include 'includes/form_guard.php'; ?>
