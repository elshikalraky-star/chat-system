<?php
require 'db_connect.php'; 
include 'includes/header.php'; 

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$p = null;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$p) {
    echo "<div class='h-screen flex flex-col items-center justify-center text-center bg-white'><h2 class='font-bold text-gray-900'>المنتج غير موجود</h2><a href='index.php' class='mt-4 bg-black text-white px-6 py-2 rounded-xl font-bold'>عودة للرئيسية</a></div>";
    include 'includes/navbar.php'; 
    exit;
}

$price = $p['price'];
$old_price = $p['old_price'];
$has_discount = ($old_price > $price && $old_price > 0);
$discount_percent = $has_discount ? floor((($old_price - $price) / $old_price) * 100) : 0;
$imgSrc = (!empty($p['image']) && file_exists("uploads/" . $p['image'])) ? "uploads/" . $p['image'] : "";
$is_fav = isset($_SESSION['wishlist']) && in_array($id, $_SESSION['wishlist']);
?>

<style>
    .price-strike-line { 
        position: relative; 
        text-decoration: none; 
        color: #9ca3af; 
        font-weight: 700; 
        font-size: 16px; 
        display: inline-block; 
    }
    .price-strike-line::after { 
        content: ''; 
        position: absolute; 
        left: -4px; right: -4px; height: 2px; 
        background: #f43f5e; 
        top: 50%; 
        transform: rotate(-14deg); 
        opacity: 0.8;
    }
</style>

<main class="min-h-screen bg-white pb-48 relative font-['Cairo'] overflow-x-hidden">

    <div class="w-full aspect-square bg-white flex items-center justify-center relative border-b border-gray-50">
        
        <div class="absolute top-4 left-0 z-30 flex flex-col items-end gap-2">
            
            <?php if($has_discount): ?>
                <span class="bg-rose-600 text-white text-[11px] font-black px-3 py-1.5 rounded-r-md shadow-sm whitespace-nowrap">
                    خصم <?= $discount_percent ?>%
                </span>
            <?php endif; ?>

            <div class="flex flex-col gap-2 pl-2">
                
                <button onclick="toggleWishlistDetails(this, <?= $id ?>)" class="w-11 h-11 bg-[#ffe4e6] rounded-full flex items-center justify-center border border-rose-100 active:scale-90 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transition-colors <?= $is_fav ? 'text-rose-500 fill-current' : 'text-gray-900 fill-none' ?>" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                    </svg>
                </button>
                
                <button onclick="shareProduct()" class="w-11 h-11 bg-[#ffe4e6] rounded-full flex items-center justify-center border border-rose-100 active:scale-90 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" />
                    </svg>
                </button>
            </div>
        </div>

        <?php if($imgSrc): ?>
            <img src="<?= $imgSrc ?>" class="w-full h-full object-contain p-6" alt="product">
        <?php else: ?>
            <span class="text-6xl grayscale opacity-20">👕</span>
        <?php endif; ?>

    </div>

    <div class="px-5 mt-5 text-center">
        <h1 class="text-lg font-black text-gray-900 mb-2 leading-relaxed break-words"><?= htmlspecialchars($p['name']) ?></h1>
        
        <p class="text-[10px] text-gray-400 font-bold mb-4 tracking-widest uppercase">قسم الأقمشة والمنسوجات</p>

        <div class="flex flex-col items-center justify-center gap-1 mb-6">
            <div class="flex items-center gap-2">
                <span class="text-3xl font-black text-gray-900"><?= $price ?></span>
                <span class="text-sm font-bold text-gray-900">ر.س</span>
            </div>
            <?php if($has_discount): ?>
                <div class="flex items-center gap-3 mt-1">
                    <span class="price-strike-line"><?= $old_price ?></span>
                    <span class="text-[10px] bg-green-50 text-green-600 px-2 py-0.5 font-black rounded-sm">وفرت الكثير!</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="flex items-center justify-center gap-4 mb-8">
            <span class="text-sm font-bold text-gray-500">الكمية:</span>
            <div class="bg-gray-50 px-2 h-10 flex items-center gap-2 border border-gray-200 w-fit shadow-sm">
                <button onclick="updateQty(1)" class="w-8 h-full font-black text-black text-lg active:scale-90 transition">+</button>
                <input type="number" id="qty" value="1" min="1" readonly class="w-8 text-center bg-transparent font-black text-lg outline-none text-gray-900">
                <button onclick="updateQty(-1)" class="w-8 h-full font-black text-gray-400 text-lg active:scale-90 transition">-</button>
            </div>
        </div>
        
        <hr class="border-gray-50 w-1/2 mx-auto my-6">

        <div class="-mx-5 mt-6">
            <div class="w-full bg-[#ffe4e6] py-3 px-5 flex items-center justify-start shadow-sm border-t border-rose-100">
                <span class="font-black text-gray-900 text-sm">تفاصيل المنتج</span>
            </div>
            <div class="w-full bg-white px-5 py-4 text-right">
                <p class="text-gray-800 text-sm font-medium leading-[2.2] text-right break-words w-full overflow-hidden">
                    <?php echo nl2br(htmlspecialchars($p['description'] ?? 'لا يوجد وصف متوفر لهذا المنتج حالياً.')); ?>
                </p>
            </div>
        </div>
    </div>

    <div class="fixed bottom-[68px] left-0 w-full px-4 py-2 z-40 flex items-center gap-3 bg-white/90 backdrop-blur-sm border-t border-gray-100">
        <button onclick="addToCart(<?= $id ?>, false)" class="flex-1 bg-black text-white h-12 font-black text-sm flex items-center justify-center gap-2 shadow-lg shadow-gray-200 active:scale-95 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <span id="btn-text-<?= $id ?>">إضافة للسلة</span>
        </button>

        <button onclick="addToCart(<?= $id ?>, true)" class="flex-1 bg-white text-gray-900 border border-gray-200 h-12 font-black text-sm flex items-center justify-center gap-2 shadow-md active:bg-gray-50 active:scale-95 transition-all">
            <span>شراء الآن</span>
        </button>
    </div>

</main>

<script>
    function shareProduct() {
        if (navigator.share) {
            navigator.share({
                title: '<?= htmlspecialchars($p['name']) ?>',
                url: window.location.href
            });
        } else {
            alert('تم نسخ الرابط!');
        }
    }

    function toggleWishlistDetails(btn, itemId) {
        const icon = btn.querySelector('svg');
        const formData = new FormData();
        formData.append('item_id', itemId);
        fetch('wishlist_action.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'added') {
                icon.classList.remove('text-gray-900', 'fill-none');
                icon.classList.add('text-rose-500', 'fill-current');
            } else {
                icon.classList.remove('text-rose-500', 'fill-current');
                icon.classList.add('text-gray-900', 'fill-none');
            }
        });
    }

    function updateQty(change) {
        const q = document.getElementById('qty');
        let v = parseInt(q.value) + change;
        if(v >= 1) q.value = v;
    }

    function addToCart(pId, isBuyNow) {
        const qty = document.getElementById('qty').value;
        const btn = event.currentTarget; 
        const originalContent = btn.innerHTML;

        if (!isBuyNow) {
            btn.innerHTML = '<span class="animate-pulse">⏳ جاري...</span>';
        }
        btn.disabled = true;

        const formData = new FormData();
        formData.append('product_id', pId);
        formData.append('quantity', qty);
        formData.append('ajax', 1);

        fetch('cart_action.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('header-cart-count');
            if(badge) {
                const count = parseInt(data.count);
                badge.innerText = count > 9 ? '+9' : count;
                badge.classList.add('show');
            }
            if (isBuyNow) {
                window.location.href = 'cart.php';
            } else {
                btn.innerHTML = '✔ تمت الإضافة';
                btn.classList.remove('bg-black');
                btn.classList.add('bg-green-600', 'border-green-600');
                
                setTimeout(() => {
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                    btn.classList.remove('bg-green-600', 'border-green-600');
                    btn.classList.add('bg-black');
                }, 1500);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btn.innerHTML = 'خطأ ❌';
            setTimeout(() => {
                btn.innerHTML = originalContent;
                btn.disabled = false;
            }, 1500);
        });
    }
</script>

<style>
    .wa-float-nav, div[class*="whatsapp"], a[href*="wa.me"] { display: none !important; }
    input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    html { scroll-behavior: smooth; }
</style>

<?php include 'includes/navbar.php'; ?>