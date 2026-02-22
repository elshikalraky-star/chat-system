<?php
/**
 * search.php - محرك البحث الشامل لمنصة كُرّة
 * يبحث في الصفحات، المنتجات، والمبدعين (خياطين، مصممين، تغليف)
 */
session_start();
require 'db_connect.php'; 
include 'includes/header.php'; 

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$searchTerm = "%" . $query . "%";

// مصفوفات النتائج
$found_pages = [];
$products = [];
$providers = []; // خياطين، مصممين، تغليف

if (!empty($query)) {

    // 1. الخريطة الذكية للصفحات والخدمات
    $site_map = [
        ['title' => 'الأقمشة والمنسوجات', 'desc' => 'تصفح أحدث الخامات والمنسوجات', 'link' => 'fabrics.php', 'keywords' => 'قماش اقمشة منسوجات خامات قطن صوف', 'icon' => '🧵'],
        ['title' => 'دليل الخياطين', 'desc' => 'ابحث عن أمهر الخياطين والمشاغل', 'link' => 'tailors.php', 'keywords' => 'خياطين تفصيل خياطة ترزي مقاسات مشاغل', 'icon' => '✂️'],
        ['title' => 'استوديو التصميم', 'desc' => 'خدمات التصميم والجرافيك والتطريز', 'link' => 'design.php', 'keywords' => 'تصميم جرافيك شعار لوجو رسم طباعة تطريز', 'icon' => '🎨'],
        ['title' => 'خدمات التغليف', 'desc' => 'تغليف هدايا وبوكسات فاخرة', 'link' => 'packaging.php', 'keywords' => 'تغليف بوكس هدايا علب صندوق مناسبات', 'icon' => '🎁'],
        ['title' => 'سلة المشتريات', 'desc' => 'مراجعة طلباتك وإتمام الشراء', 'link' => 'cart.php', 'keywords' => 'سلة شراء طلبات دفع كاش اتمام', 'icon' => '🛒'],
        ['title' => 'حسابي الشخصي', 'desc' => 'إدارة بياناتك وطلباتك', 'link' => 'login.php', 'keywords' => 'دخول تسجيل حساب بروفايل', 'icon' => '👤']
    ];

    foreach ($site_map as $page) {
        if (mb_strpos($page['keywords'], $query) !== false || mb_strpos($page['title'], $query) !== false) {
            $found_pages[] = $page;
        }
    }

    // 2. البحث في مقدمي الخدمة (جدول users الموحد)
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE (provider_name LIKE ? OR username LIKE ? OR specialty LIKE ?) AND status = 'approved' LIMIT 10");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $providers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {}

    // 3. البحث في المنتجات
    try {
        $stmt = $conn->prepare("SELECT * FROM products WHERE (name LIKE ? OR description LIKE ?) LIMIT 10");
        $stmt->execute([$searchTerm, $searchTerm]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {}
}
?>

<main class="min-h-screen bg-[#fcfcfc] pb-32 pt-6 px-4 font-['Cairo']">

    <div class="mb-10">
        <form action="search.php" method="GET" class="relative">
            <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" 
                   placeholder="عن ماذا تبحث اليوم؟" 
                   class="w-full h-14 pr-14 pl-6 rounded-2xl border-2 border-black/5 bg-white shadow-sm focus:border-black transition-all text-sm font-bold outline-none">
            <div class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </form>
    </div>

    <?php if (empty($query)): ?>
        <div class="text-center py-20">
            <p class="text-gray-400 font-black text-xs uppercase tracking-widest">اكتب كلمة البحث للبدء...</p>
        </div>
    <?php elseif (empty($products) && empty($providers) && empty($found_pages)): ?>
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center text-4xl mb-4 grayscale opacity-30">🔍</div>
            <h3 class="text-gray-900 font-black">لم نجد نتائج لـ "<?= htmlspecialchars($query) ?>"</h3>
            <p class="text-gray-400 text-xs font-bold mt-2">جرب البحث بكلمات أبسط أو تصفح الأقسام.</p>
        </div>
    <?php else: ?>

        <h1 class="text-xl font-black text-gray-900 mb-8 border-r-4 border-black pr-3">
            نتائج البحث عن <span class="text-rose-600">"<?= htmlspecialchars($query) ?>"</span>
        </h1>

        <?php if (!empty($found_pages)): ?>
        <div class="mb-10">
            <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">الصفحات والخدمات</h2>
            <div class="grid gap-3">
                <?php foreach ($found_pages as $page): ?>
                <a href="<?= $page['link'] ?>" class="bg-white p-4 border-2 border-black/5 flex items-center gap-4 hover:border-black transition-all group">
                    <div class="text-2xl"><?= $page['icon'] ?></div>
                    <div class="flex-1">
                        <h3 class="font-black text-sm text-gray-900"><?= $page['title'] ?></h3>
                        <p class="text-[10px] text-gray-400 font-bold"><?= $page['desc'] ?></p>
                    </div>
                    <span class="text-gray-300 group-hover:text-black transition-colors">➜</span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($providers)): ?>
        <div class="mb-10">
            <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">الخبراء والمبدعين</h2>
            <div class="grid grid-cols-1 gap-3">
                <?php foreach ($providers as $u): ?>
                <a href="<?= ($u['role'] == 'tailor' ? 'tailor_details.php' : ($u['role'] == 'designer' ? 'design_details.php' : 'packaging_details.php')) ?>?id=<?= $u['id'] ?>" 
                   class="bg-white p-4 border-2 border-black/5 flex items-center gap-4 hover:shadow-lg transition-all">
                    <div class="w-12 h-12 bg-gray-50 rounded-xl overflow-hidden flex-shrink-0 border border-gray-100">
                        <?php if($u['provider_image']): ?>
                            <img src="uploads/<?= $u['provider_image'] ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-xl bg-gray-50">👤</div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-black text-sm text-gray-900"><?= htmlspecialchars($u['provider_name'] ?? $u['username']) ?></h3>
                        <p class="text-[10px] text-rose-500 font-black uppercase tracking-tight"><?= $u['role'] ?> • <?= htmlspecialchars($u['location'] ?? 'متوفر أونلاين') ?></p>
                    </div>
                    <div class="bg-black text-white px-4 py-2 text-[10px] font-black uppercase">مشاهدة</div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($products)): ?>
        <div class="mb-10">
            <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">الأقمشة والمنتجات</h2>
            <div class="grid grid-cols-2 gap-4">
                <?php foreach ($products as $p): ?>
                    <?php 
                    // استخدام كارت المنتج الموحد لضمان تناسق التصميم
                    if(file_exists('card.php')) include 'card.php'; 
                    ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    <?php endif; ?>

</main>

<div class="w-full fixed bottom-0 left-0 right-0 z-50">
    <?php include 'includes/navbar.php'; ?>
</div>
