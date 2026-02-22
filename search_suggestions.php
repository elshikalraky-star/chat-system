<?php
require 'db_connect.php'; // اتأكد إن ملف الاتصال موجود
header('Content-Type: application/json; charset=utf-8');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$limit = 6; // أقصى عدد للاقتراحات

// لو مفيش حروف مكتوبة، وقف
if (mb_strlen($q) < 1) { echo json_encode([]); exit; }

$suggestions = [];
$term = "%$q%";

// 1. البحث في الصفحات والخدمات الثابتة (زي نون لما يقترح أقسام)
$site_map = [
    ['title'=>'سياسة الخصوصية', 'link'=>'privacy.php', 'type'=>'page', 'icon'=>'📜'],
    ['title'=>'خدمات التغليف', 'link'=>'packaging.php', 'type'=>'service', 'icon'=>'🎁'],
    ['title'=>'استوديو التصميم', 'link'=>'design.php', 'type'=>'service', 'icon'=>'🎨'],
    ['title'=>'خياطين ومشاغل', 'link'=>'tailors.php', 'type'=>'service', 'icon'=>'✂️'],
    ['title'=>'السلة', 'link'=>'cart.php', 'type'=>'page', 'icon'=>'🛒']
];

foreach ($site_map as $page) {
    // لو العنوان يحتوي على الحروف المكتوبة
    if (mb_strpos($page['title'], $q) !== false) {
        $suggestions[] = [
            'text' => $page['title'],
            'sub'  => 'قسم',
            'url'  => $page['link'],
            'icon' => $page['icon']
        ];
    }
}

// 2. البحث في الخياطين (من الداتابيز)
try {
    $stmt = $conn->prepare("SELECT shop_name, location FROM tailors WHERE shop_name LIKE ? LIMIT 3");
    $stmt->execute([$term]);
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $suggestions[] = [
            'text' => $row['shop_name'],
            'sub'  => 'خياط - ' . $row['location'],
            'url'  => 'https://wa.me/966500000000', // أو رابط تفاصيل الخياط
            'icon' => '👨🏻‍🎨'
        ];
    }
} catch(PDOException $e) {}

// 3. البحث في المنتجات (من الداتابيز)
try {
    $stmt = $conn->prepare("SELECT name, image FROM products WHERE name LIKE ? LIMIT 4");
    $stmt->execute([$term]);
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $suggestions[] = [
            'text' => $row['name'],
            'sub'  => 'منتج',
            'url'  => 'search.php?q=' . urlencode($row['name']),
            'icon' => '👕' // ممكن نستبدلها بصورة المنتج لو حبيت
        ];
    }
} catch(PDOException $e) {}

// إرسال النتائج للموقع
echo json_encode(array_slice($suggestions, 0, 8)); // نرجع أقصى حاجة 8 نتايج
?>