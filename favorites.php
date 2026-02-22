<?php
session_start();

// 1. التحقق من تسجيل الدخول (يجب أن يكون أول شيء)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. الاتصال بقاعدة البيانات
if (file_exists('db_connect.php')) {
    require 'db_connect.php';
} else {
    require '../db_connect.php';
}

// 3. الآن نستدعي الهيدر (بعد أن تأكدنا أن المستخدم مسموح له بالدخول)
include 'includes/header.php';

$user_id = $_SESSION['user_id'];
$products = [];

// 4. جلب المنتجات المفضلة
try {
    $sql = "SELECT p.*, w.created_at as wish_date 
            FROM products p 
            JOIN wishlist w ON p.id = w.product_id 
            WHERE w.user_id = ? 
            ORDER BY w.id DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // تحديث الجلسة
    $_SESSION['wishlist'] = array_column($products, 'id');

} catch(PDOException $e) {
    $products = [];
}
?>

<div class="min-h-screen bg-gray-50 pb-32 pt-6 px-4 font-['Cairo'] overflow-x-hidden">
    <div class="max-w-screen-xl mx-auto">
        
        <div class="text-center mb-8">
            <h1 class="text-xl font-black text-gray-900 flex items-center justify-center gap-2">قائمة مفضلاتي <span class="text-rose-500 text-2xl">❤️</span></h1>
            <p class="text-[10px] text-gray-400 font-bold tracking-widest uppercase mt-1">منتجاتك التي اخترتها بعناية</p>
        </div>

        <?php if (empty($products)): ?>
            <div class="h-[60vh] flex flex-col items-center justify-center text-center">
                <div class="w-24 h-24 bg-[#ffe4e6] flex items-center justify-center mb-6 shadow-sm rotate-3" style="border-radius: 50% !important;">
                    <span class="text-5xl grayscale opacity-30 -rotate-3">💔</span>
                </div>
                <h3 class="text-gray-800 text-lg font-black mb-2">مفضلاتك فارغة!</h3>
                <p class="text-xs text-gray-500 font-bold mb-6">لم تقم بإضافة أي منتج للمفضلة بعد.</p>
                <a href="index.php" class="bg-black text-white px-10 py-3 font-black text-sm shadow-xl rounded-xl hover:scale-95 transition-transform" style="border-radius: 12px !important;">ابدأ التسوق الآن</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-3 gap-y-5">
                <?php foreach($products as $p) { 
                    if(file_exists('card.php')) {
                        include 'card.php'; 
                    }
                } ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="favModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm opacity-0 invisible transition-all duration-300">
    <div class="bg-white p-8 w-[85%] max-w-sm text-center shadow-2xl transform scale-90 transition-all duration-300 relative rounded-3xl" id="favModalBox" style="border-radius: 24px !important;">
        <div class="mx-auto mb-4 w-12 h-12 text-rose-500">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-12 h-12 mx-auto">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
            </svg>
        </div>
        <h3 class="text-xl font-black text-gray-900 mb-2">حذف من المفضلة؟</h3>
        <p class="text-sm text-gray-500 font-bold mb-8 italic">سيتم إزالة هذا المنتج من قائمة رغباتك.</p>
        <div class="flex gap-3">
            <button onclick="hideFavModal()" class="flex-1 bg-gray-50 border border-gray-200 text-gray-700 py-3 font-black hover:bg-gray-100 transition rounded-xl" style="border-radius: 12px !important;">إلغاء</button>
            <button id="confirmFavBtn" class="flex-1 bg-rose-500 text-white py-3 font-black hover:bg-rose-600 transition shadow-lg shadow-rose-200 rounded-xl" style="border-radius: 12px !important;">تأكيد الحذف</button>
        </div>
    </div>
</div>

<div id="favToast" class="fixed top-24 left-1/2 -translate-x-1/2 z-[99999] flex items-center gap-3 bg-gray-900 text-white px-6 py-3 rounded-full shadow-2xl border border-gray-800 pointer-events-none transition-all duration-500 transform opacity-0 translate-y-[-20px]">
    <div class="bg-rose-500 rounded-full p-1 shadow-md">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
    </div>
    <span class="text-xs font-black tracking-wide whitespace-nowrap">تم الحذف بنجاح</span>
</div>

<div class="w-full fixed bottom-0 left-0 right-0 z-50">
    <?php include 'includes/navbar.php'; ?>
</div>

<script>
    let itemToFavDelete = null;

    function toggleWishlist(btn, itemId) {
        itemToFavDelete = itemId;
        const modal = document.getElementById('favModal');
        const box = document.getElementById('favModalBox');
        modal.classList.remove('opacity-0', 'invisible');
        box.classList.remove('scale-90');
        box.classList.add('scale-100');
    }

    function hideFavModal() {
        const modal = document.getElementById('favModal');
        const box = document.getElementById('favModalBox');
        modal.classList.add('opacity-0', 'invisible');
        box.classList.add('scale-90');
        box.classList.remove('scale-100');
        itemToFavDelete = null;
    }

    document.getElementById('confirmFavBtn').onclick = function() {
        if(itemToFavDelete) {
            const formData = new FormData();
            formData.append('item_id', itemToFavDelete);
            
            const btn = document.getElementById('confirmFavBtn');
            const originalText = btn.innerText;
            btn.innerText = 'جاري الحذف...';
            btn.disabled = true;

            fetch('wishlist_action.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success' || data.action === 'removed') {
                    window.location.href = 'favorites.php?status=removed';
                } else {
                    alert('حدث خطأ: ' + (data.message || 'غير معروف'));
                    btn.innerText = originalText;
                    btn.disabled = false;
                    hideFavModal();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ في الاتصال');
                btn.innerText = originalText;
                btn.disabled = false;
                hideFavModal();
            });
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('status') === 'removed') {
            const toast = document.getElementById('favToast');
            setTimeout(() => {
                toast.classList.remove('opacity-0', 'translate-y-[-20px]');
            }, 300);

            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-[-20px]');
                window.history.replaceState(null, null, window.location.pathname);
            }, 3000);
        }
    });

    function addToCartHome(btn, pId) {
        const original = btn.innerHTML;
        btn.innerHTML = '<span class="animate-pulse">⏳</span>'; 
        btn.disabled = true;
        
        const formData = new FormData(); 
        formData.append('product_id', pId); 
        formData.append('quantity', 1); 
        formData.append('ajax', 1);
        
        fetch('cart_action.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            const b = document.getElementById('cart-badge-count'); 
            if(b) { 
                b.innerText = data.count > 9 ? '+9' : data.count; 
                b.classList.add('show'); 
            }
            
            btn.innerHTML = '✔'; 
            btn.classList.remove('bg-black');
            btn.classList.add('bg-green-600');
            
            setTimeout(() => { 
                btn.innerHTML = original; 
                btn.classList.remove('bg-green-600'); 
                btn.classList.add('bg-black');
                btn.disabled = false; 
            }, 1000);
        });
    }
</script>
</body>
</html>
