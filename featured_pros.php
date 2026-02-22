<?php
/**
 * favorites.php - قائمة الرغبات والمفضلات
 * يعرض المنتجات التي أبدى العميل إعجابه بها.
 */

require 'db_connect.php';
include 'includes/header.php'; // الهيدر يتكفل ببدء الجلسة والحماية

$wishlist_ids = $_SESSION['wishlist'] ?? [];
$products = [];

// جلب المنتجات المفضلة بأمان
if (!empty($wishlist_ids)) {
    // تأمين المعرفات: تحويل كل معرف لرقم صحيح لضمان عدم الاختراق
    $ids_string = implode(',', array_map('intval', $wishlist_ids));
    try {
        // جلب البيانات مع ترتيبها من الأحدث للأقدم
        $stmt = $conn->query("SELECT * FROM products WHERE id IN ($ids_string) ORDER BY id DESC");
        if($stmt) $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        // في حال حدوث خطأ في القاعدة، نبقي القائمة فارغة
        $products = [];
    }
}
?>

<div class="min-h-screen bg-[#f9fafb] pb-40 pt-8 px-4 font-['Cairo'] overflow-x-hidden">
    <div class="max-w-screen-xl mx-auto">
        
        <div class="text-center mb-12 relative">
            <h1 class="text-2xl font-black text-gray-900 uppercase tracking-tighter flex items-center justify-center gap-2">
                قائمة مفضلاتي <span class="text-rose-600 animate-pulse">❤️</span>
            </h1>
            <p class="text-[10px] text-gray-400 font-black tracking-[0.2em] uppercase mt-2">عناصر تم اختيارها بعناية فائقة</p>
            <div class="w-12 h-1 bg-black mx-auto mt-4"></div>
        </div>

        <?php if (empty($products)): ?>
            <div class="h-[50vh] flex flex-col items-center justify-center text-center">
                <div class="w-24 h-24 bg-rose-50 border-2 border-rose-100 flex items-center justify-center mb-6 shadow-[8px_8px_0px_0px_rgba(244,63,94,0.1)] rotate-3">
                    <span class="text-5xl opacity-30 -rotate-3 grayscale">🖤</span>
                </div>
                <h3 class="text-gray-900 text-lg font-black mb-2 uppercase">لا توجد رغبات حالية</h3>
                <p class="text-[10px] text-gray-400 font-bold mb-8 max-w-[200px] leading-relaxed">المس القلب الموجود على أي منتج ليظهر هنا في قائمتك الخاصة.</p>
                <a href="index.php" class="bg-black text-white px-12 py-4 font-black text-xs uppercase tracking-widest active:scale-95 transition-all shadow-xl" style="border-radius: 0 !important;">ابدأ باكتشاف الأقمشة</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-3 gap-y-6">
                <?php foreach($products as $p): ?>
                    <div class="w-full">
                        <?php 
                        // استدعاء الكارت الموحد (Brutalist Style)
                        if(file_exists('parts/product_card.php')) {
                            include 'parts/product_card.php';
                        } else {
                            include 'card.php';
                        }
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="favModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-md opacity-0 invisible transition-all duration-300 px-6">
    <div class="bg-white p-8 w-full max-w-sm text-center shadow-[12px_12px_0px_0px_rgba(244,63,94,1)] border-2 border-black transform scale-90 transition-all duration-300" id="favModalBox" style="border-radius: 0 !important;">
        <div class="mx-auto mb-6 w-16 h-16 bg-rose-50 border border-rose-100 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
            </svg>
        </div>
        <h3 class="text-xl font-black text-gray-900 mb-2 uppercase tracking-tighter">إزالة من المفضلة؟</h3>
        <p class="text-[10px] text-gray-400 font-bold mb-10 leading-relaxed uppercase">سوف تفقد هذا المنتج من قائمتك الخاصة، هل أنت متأكد؟</p>
        <div class="flex gap-4">
            <button onclick="hideFavModal()" class="flex-1 bg-gray-100 text-black py-4 font-black text-xs hover:bg-gray-200 transition" style="border-radius: 0 !important;">تراجع</button>
            <button id="confirmFavBtn" class="flex-1 bg-black text-white py-4 font-black text-xs shadow-lg active:scale-95 transition" style="border-radius: 0 !important;">نعم، حذف</button>
        </div>
    </div>
</div>

<div id="favToast" class="fixed top-24 left-1/2 -translate-x-1/2 z-[110] flex items-center gap-3 bg-black text-white px-6 py-3 shadow-2xl border border-white/10 transition-all duration-500 transform -translate-y-[200%] opacity-0" style="border-radius: 0 !important;">
    <span class="text-xs font-black tracking-widest uppercase">تمت الإزالة بنجاح 🖤</span>
</div>

<div class="w-full fixed bottom-0 left-0 right-0 z-50">
    <?php include 'includes/navbar.php'; ?>
</div>

<script>
    let itemToFavDelete = null;

    // فتح المودال عند ضغط القلب
    function toggleWishlist(btn, itemId) {
        // ملاحظة: إذا كنا في صفحة المفضلة، الضغط يعني حذف
        itemToFavDelete = itemId;
        const modal = document.getElementById('favModal');
        const box = document.getElementById('favModalBox');
        modal.classList.remove('opacity-0', 'invisible');
        box.classList.replace('scale-90', 'scale-100');
    }

    function hideFavModal() {
        const modal = document.getElementById('favModal');
        const box = document.getElementById('favModalBox');
        modal.classList.add('opacity-0', 'invisible');
        box.classList.replace('scale-100', 'scale-90');
        itemToFavDelete = null;
    }

    // تنفيذ الحذف الفعلي عبر Fetch
    document.getElementById('confirmFavBtn').onclick = function() {
        if(itemToFavDelete) {
            const formData = new FormData();
            formData.append('item_id', itemToFavDelete);
            formData.append('ajax', 1);
            
            fetch('wishlist_action.php', { method: 'POST', body: formData })
            .then(() => {
                // التوجيه لإعادة التحميل وإظهار التوست
                window.location.href = 'favorites.php?status=removed';
            });
        }
    };

    // معالجة التوست عند التحميل
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('status') === 'removed') {
            const toast = document.getElementById('favToast');
            setTimeout(() => {
                toast.classList.remove('-translate-y-[200%]', 'opacity-0');
            }, 300);

            setTimeout(() => {
                toast.classList.add('-translate-y-[200%]', 'opacity-0');
                window.history.replaceState(null, null, window.location.pathname);
            }, 3000);
        }
    });

    // دالة السلة السريعة
    function addToCartHome(btn, pId) {
        const original = btn.innerHTML;
        btn.innerHTML = '<span class="animate-spin text-lg">⏳</span>'; btn.disabled = true;
        const formData = new FormData(); formData.append('product_id', pId); formData.append('quantity', 1); formData.append('ajax', 1);
        fetch('cart_action.php', { method: 'POST', body: formData }).then(res => res.json()).then(data => {
            const b = document.getElementById('cart-badge-count'); if(b) { b.innerText = data.count > 9 ? '+9' : data.count; b.classList.add('show'); }
            btn.innerHTML = 'تم الإضافة ✅'; btn.classList.add('bg-green-600');
            setTimeout(() => { btn.innerHTML = original; btn.classList.remove('bg-green-600'); btn.disabled = false; }, 1500);
        }).catch(() => { btn.innerHTML = original; btn.disabled = false; });
    }
</script>
</body>
</html>