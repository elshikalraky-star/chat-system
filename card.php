<?php
// منع الوصول المباشر
if (!isset($p)) { exit; }

$p_id           = $p['id'] ?? 0;
$p_name         = htmlspecialchars($p['name'] ?? 'منتج جديد');
$current_price  = number_format($p['price'] ?? 0, 2);
$original_price = number_format($p['old_price'] ?? 0, 2);
$has_discount   = ($p['old_price'] > $p['price'] && $p['old_price'] > 0);
$discount_percent = $has_discount ? floor((($p['old_price'] - $p['price']) / $p['old_price']) * 100) : 0;
$imgSrc         = (!empty($p['image'])) ? "uploads/" . $p['image'] : "img/default.png"; 
$link           = "product_details.php?id=$p_id"; 
$is_in_wishlist = isset($_SESSION['wishlist']) && in_array($p_id, $_SESSION['wishlist']);
?>

<style>
    .price-strike-line { position: relative; text-decoration: none; color: #9ca3af; font-weight: 700; font-size: 13px; display: inline-block; }
    .price-strike-line::after { content: ''; position: absolute; left: -3px; right: -3px; height: 1.8px; background: #f43f5e; top: 50%; transform: rotate(-14deg); box-shadow: 0 0 2px rgba(244, 63, 94, 0.2); }
    .card-touch-fix { -webkit-tap-highlight-color: transparent; }
</style>

<div id="product-<?= $p_id ?>" class="card-touch-fix bg-white p-2 border border-gray-100 shadow-sm block relative h-full flex flex-col overflow-hidden" style="border-radius: 0 !important;">
    
    <button onclick="toggleWishlist(this, <?= $p_id ?>)" class="absolute top-0 right-0 m-0.5 z-20 active:scale-125 transition-all duration-300">
        <span class="bg-[#ffe4e6] w-7 h-7 flex items-center justify-center shadow-sm" style="border-radius: 50% !important;">
            <svg xmlns="http://www.w3.org/2000/svg" 
                 id="heart-icon-<?= $p_id ?>"
                 style="color: #f43f5e !important;" 
                 class="h-4 w-4 transition-all <?= $is_in_wishlist ? 'fill-current' : 'fill-none' ?>" 
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
            </svg>
        </span>
    </button>

    <?php if($has_discount): ?>
    <span class="absolute top-0 left-0 m-0.5 bg-[#f43f5e] text-white text-[10px] font-black px-2 py-1 z-10" style="border-radius: 0 !important;">خصم <?= $discount_percent ?>%</span>
    <?php endif; ?>
    
    <a href="<?= $link ?>" class="w-full aspect-[3/4] bg-white border border-gray-50 mb-2 flex items-center justify-center overflow-hidden">
        <img src="<?= $imgSrc ?>" class="w-full h-full object-contain" loading="lazy">
    </a>
    
    <div class="px-1 flex-1 flex flex-col text-center">
        <a href="<?= $link ?>" class="font-black text-[11px] truncate mb-1 text-gray-900 block"><?= $p_name ?></a>
        
        <div class="flex items-center justify-center gap-5 mb-3">
            <div class="flex flex-col leading-none">
                <span class="text-lg font-black text-gray-900"><?= $current_price ?></span>
                <span class="text-[9px] font-black text-gray-900 mt-1">ر.س</span>
            </div>
            
            <?php if($has_discount): ?>
            <div class="flex flex-col leading-none">
                <span class="price-strike-line"><?= $original_price ?></span>
                <span class="text-[9px] font-bold text-gray-400 mt-1">ر.س</span>
            </div>
            <?php endif; ?>
        </div>

        <a href="<?= $link ?>" class="flex items-center justify-center gap-1.5 mb-4 group">
            <span class="text-[10px] font-bold text-gray-500">تفاصيل المنتج</span>
            <span class="bg-[#ffe4e6] w-6 h-6 flex items-center justify-center shadow-sm" style="border-radius: 0 !important;">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
            </span>
        </a>
    </div>

    <button onclick="addToCartHome(this, <?= $p_id ?>)" 
            data-name="<?= $p_name ?>" 
            data-img="<?= $imgSrc ?>" 
            class="bg-black text-white py-3.5 text-xs font-black shadow-md flex items-center justify-center gap-2 mt-auto active:bg-gray-800 transition-all -mx-2 mb-0 w-[calc(100%+1rem)]" 
            style="border-radius: 0 !important;">
        
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
        </svg>
        <span>أضف إلي السلة</span>
    </button>
</div>