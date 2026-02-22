<?php
require 'db_connect.php';

// استلام القيم من الرابط
$country_id = $_GET['country_id'] ?? null;
$region_id  = $_GET['region_id'] ?? null;
$city_id    = $_GET['city_id'] ?? null;

$providers = [];

// 1. نحاول المدينة
if ($city_id) {
    $stmt = $conn->prepare("SELECT * FROM provider_profiles WHERE city_id = ?");
    $stmt->execute([$city_id]);
    $providers = $stmt->fetchAll();
}

// 2. لو مفيش → المنطقة
if (empty($providers) && $region_id) {
    $stmt = $conn->prepare("SELECT * FROM provider_profiles WHERE region_id = ?");
    $stmt->execute([$region_id]);
    $providers = $stmt->fetchAll();
}

// 3. لو مفيش → الدولة
if (empty($providers) && $country_id) {
    $stmt = $conn->prepare("SELECT * FROM provider_profiles WHERE country_id = ?");
    $stmt->execute([$country_id]);
    $providers = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html dir="rtl">
<head>
<meta charset="UTF-8">
<title>نتائج البحث</title>
</head>

<body>

<h2>النتائج:</h2>

<?php if(empty($providers)): ?>
    <p>❌ لا يوجد خدمات حالياً</p>
<?php else: ?>
    <?php foreach($providers as $p): ?>
        <div style="border:1px solid #ccc; padding:10px; margin:10px;">
            <h3><?= $p['provider_name'] ?></h3>
            <p>التخصص: <?= $p['specialty'] ?></p>
            <p>السعر: <?= $p['starting_price'] ?></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>