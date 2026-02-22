<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db_connect.php';
require 'includes/functions.php';

$countries = $conn->query("SELECT id, name_ar FROM countries WHERE is_active = 1")->fetchAll();

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $basicData = [
        'username' => $_POST['full_name'], 
        'phone'    => $_POST['phone'], 
        'password' => $_POST['password'],
        'role'     => 'tailor',
        'status'   => 'pending'
    ];

    $profileData = [
        'business_name' => $_POST['business_name'], 
        'specialty'     => $_POST['specialty'],
        'price'         => (float)$_POST['price'],
        'time'          => $_POST['time'] ?? '',
        'location'      => $_POST['address_text'] ?? '',
        'country_id'    => $_POST['country_id'] ?? null,
        'region_id'     => $_POST['region_id'] ?? null,
        'city_id'       => $_POST['city_id'] ?? null
    ];

    $new_user_id = registerUserSecurely($conn, $basicData, $profileData);

    if ($new_user_id) {
        session_regenerate_id(true);
        $_SESSION['user_id']    = $new_user_id;
        $_SESSION['username']   = $_POST['full_name'];
        $_SESSION['role']       = 'tailor';
        $_SESSION['login_time'] = time();

        echo "<script>
            alert('تم التسجيل بنجاح! طلبك قيد المراجعة من الإدارة.');
            window.location.href = '/dashboard/tailor_dashboard.php';
        </script>";
        exit();
    } else {
        $error = "حدث خطأ أثناء التسجيل، ربما رقم الجوال مستخدم بالفعل.";
    }
}

include 'includes/header.php';
?>

<main class="min-h-screen bg-gray-50 py-10 px-4 font-['Cairo']" dir="rtl">
    <div class="max-w-md mx-auto bg-white rounded-[35px] shadow-sm border border-gray-100 p-6 md:p-8">
        
        <div class="text-center mb-8">
            <h1 class="text-2xl font-black text-gray-900">بيانات المشغل 🪡</h1>
            <p class="text-xs text-gray-400 font-bold mt-1">تحديد موقعك بدقة يساعد الزبائن في الوصول إليك</p>

            <?php if($error): ?>
                <div class="mt-4 bg-red-50 text-red-600 p-3 rounded-xl text-xs font-bold">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
        </div>

        <form method="POST" class="space-y-4">
            
            <input type="text" name="full_name" required placeholder="اسم المالك"
                   class="w-full p-3 bg-gray-50 rounded-xl font-bold text-sm border outline-none">

            <input type="text" name="business_name" required placeholder="اسم المشغل / البراند"
                   class="w-full p-3 bg-gray-50 rounded-xl font-bold text-sm border outline-none">

            <?php include 'includes/smart_phone.php'; ?>

            <!-- الموقع -->
            <div class="space-y-3 bg-blue-50 p-4 rounded-2xl border border-blue-100">
                <h3 class="text-xs font-black text-blue-800 mb-2">📍 نطاق الخدمة</h3>

                <select name="country_id" id="country"
                        class="w-full p-3 bg-white rounded-xl font-bold border outline-none"
                        onchange="loadRegions()">
                    <option value="">اختر الدولة</option>
                    <?php foreach($countries as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name_ar']) ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="region_id" id="region"
                        class="w-full p-3 bg-white rounded-xl font-bold border outline-none"
                        onchange="loadCities()" disabled>
                    <option value="">اختر الدولة أولاً</option>
                </select>

                <select name="city_id" id="city"
                        class="w-full p-3 bg-white rounded-xl font-bold border outline-none"
                        disabled>
                    <option value="">اختر المنطقة أولاً</option>
                </select>

                <input type="text" name="address_text"
                       placeholder="اسم الحي / الشارع"
                       class="w-full p-3 bg-white rounded-xl font-bold border outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <select name="specialty"
                        class="w-full p-3 bg-gray-50 rounded-xl font-bold text-sm border outline-none">
                    <option value="عبايات">عبايات</option>
                    <option value="فساتين">فساتين</option>
                    <option value="تطريز">تطريز</option>
                </select>

                <input type="number" name="price"
                       placeholder="السعر"
                       class="w-full p-3 bg-gray-50 rounded-xl font-bold text-sm border outline-none">
            </div>

            <input type="password" name="password" required
                   placeholder="كلمة المرور"
                   class="w-full p-3 bg-gray-50 rounded-xl font-bold text-sm border outline-none">

            <button type="submit"
                    class="w-full bg-gray-900 text-white py-4 rounded-2xl font-black shadow-lg">
                تسجيل
            </button>
        </form>
    </div>
</main>

<script>
async function loadRegions() {
    const countryId = document.getElementById('country').value;
    const region = document.getElementById('region');
    const city = document.getElementById('city');

    region.innerHTML = '<option value="">جاري التحميل...</option>';
    region.disabled = true;
    city.innerHTML = '<option value="">اختر المنطقة أولاً</option>';
    city.disabled = true;

    if (!countryId) return;

    try {
        const res = await fetch('api/get_geo.php?type=regions&id=' + countryId);
        const data = await res.json();

        if (!data.length) {
            region.innerHTML = '<option value="">لا توجد مناطق</option>';
            return;
        }

        region.innerHTML = '<option value="">اختر المنطقة</option>';
        data.forEach(r => {
            const option = document.createElement('option');
            option.value = r.id;
            option.textContent = r.name_ar;
            region.appendChild(option);
        });
        region.disabled = false;

    } catch (e) {
        region.innerHTML = '<option value="">خطأ في التحميل</option>';
    }
}

async function loadCities() {
    const regionId = document.getElementById('region').value;
    const city = document.getElementById('city');

    city.innerHTML = '<option value="">جاري التحميل...</option>';
    city.disabled = true;

    if (!regionId) return;

    try {
        const res = await fetch('api/get_geo.php?type=cities&id=' + regionId);
        const data = await res.json();

        if (!data.length) {
            city.innerHTML = '<option value="">لا توجد مدن</option>';
            return;
        }

        city.innerHTML = '<option value="">اختر المدينة</option>';
        data.forEach(c => {
            const option = document.createElement('option');
            option.value = c.id;
            option.textContent = c.name_ar;
            city.appendChild(option);
        });
        city.disabled = false;

    } catch (e) {
        city.innerHTML = '<option value="">خطأ في التحميل</option>';
    }
}
</script>

<?php include 'includes/form_guard.php'; ?>
