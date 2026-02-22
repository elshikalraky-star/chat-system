<?php
// ⭐ مسحنا session_start من هنا عشان الهيدر يقوم بالواجب ويأمنها ⭐
require 'db_connect.php';
include 'includes/header.php'; // ده دلوقتي بيستدعي الحماية وبيبدأ الجلسة بأمان

$cart_items = $_SESSION['cart'] ?? [];
$products = [];
$total_price = 0;

if (!empty($cart_items)) {
    $ids = implode(',', array_keys($cart_items));
    if (preg_match('/^[0-9,]+$/', $ids)) {
        $stmt = $conn->query("SELECT * FROM products WHERE id IN ($ids)");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<div class="min-h-screen bg-gray-50 pb-40 pt-4 px-4 font-['Cairo'] overflow-x-hidden">
    
    <h1 class="text-xl font-black mb-5 text-center text-gray-800">سلة المشتريات 🛒</h1>

    <?php if (empty($products)): ?>
        <div class="h-[60vh] flex flex-col items-center justify-center text-center">
            <span class="text-6xl mb-4 grayscale opacity-20">🛍️</span>
            <p class="text-gray-400 text-lg mb-6 font-bold">السلة فارغة!</p>
            <a href="index.php" class="bg-black text-white px-8 py-3 font-black shadow-lg hover:shadow-xl transition-all rounded-none" style="border-radius: 0 !important;">تسوق الآن</a>
        </div>
    <?php else: ?>
        
        <div class="space-y-3 mb-6">
            <?php foreach ($products as $p): 
                $qty = $cart_items[$p['id']];
                $subtotal = $p['price'] * $qty;
                $total_price += $subtotal;
            ?>
            <div class="bg-white p-3 border border-gray-100 shadow-sm flex gap-4 items-center relative overflow-hidden rounded-none" style="border-radius: 0 !important;">
                <img src="uploads/<?= $p['image'] ?>" class="w-20 h-20 object-contain bg-gray-50 p-1 rounded-none" style="border-radius: 0 !important;">
                
                <div class="flex-1">
                    <h3 class="font-black text-xs text-gray-900 mb-1"><?= $p['name'] ?></h3>
                    <p class="text-[10px] text-gray-400 font-bold"><?= $qty ?> قطعة × <?= $p['price'] ?> ر.س</p>
                    <p class="font-black text-rose-600 mt-1"><?= $subtotal ?> ر.س</p>
                </div>

                <button onclick="confirmDelete(<?= $p['id'] ?>)" class="text-gray-300 hover:text-red-500 transition-colors absolute top-3 left-3 p-2 active:scale-90">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="bg-white p-5 border-t border-gray-100 shadow-[0_-5px_20px_rgba(0,0,0,0.03)] fixed bottom-0 left-0 w-full z-30 rounded-t-3xl pb-24"> 
            <div class="flex justify-between items-end mb-4 px-1">
                <span class="text-lg font-black text-gray-900">الإجمالي:</span>
                <div class="flex items-end gap-1">
                    <span class="text-2xl font-black text-gray-900"><?= $total_price ?></span>
                    <span class="text-sm font-bold text-gray-500 mb-1">ر.س</span>
                </div>
            </div>

            <button onclick="checkout()" class="w-full bg-black text-white h-12 font-black text-sm flex items-center justify-center gap-2 shadow-lg active:scale-95 transition-all mb-4 rounded-none" style="border-radius: 0 !important;">
                <span>إتمام الطلب (واتساب)</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
            
            <div class="border-t border-dashed border-gray-200 pt-3 text-center">
                <p class="text-[9px] text-gray-400 font-bold mb-2">طرق الدفع المدعومة</p>
                <div class="flex justify-center items-center gap-4 opacity-70 hover:opacity-100 transition-all duration-300">
                     <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mada_Logo.svg/100px-Mada_Logo.svg.png" class="h-5 object-contain" alt="Mada">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/b0/Apple_Pay_logo.svg/100px-Apple_Pay_logo.svg.png" class="h-6 object-contain" alt="Apple Pay">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Visa_Inc._logo.svg/100px-Visa_Inc._logo.svg.png" class="h-3 object-contain" alt="Visa">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/100px-Mastercard-logo.svg.png" class="h-5 object-contain" alt="Mastercard">
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div id="deleteModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm opacity-0 invisible transition-all duration-300">
    <div class="bg-white rounded-[2rem] p-8 w-[85%] max-w-sm text-center shadow-2xl transform scale-90 transition-all duration-300 relative" id="modalBox">
        <div class="mx-auto mb-4 w-12 h-12 text-blue-400">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
            </svg>
        </div>
        <h3 class="text-xl font-black text-gray-900 mb-2">حذف المنتج نهائياً؟</h3>
        <p class="text-sm text-gray-500 font-bold mb-8">هل أنت متأكد من رغبتك في إزالة هذا المنتج من السلة؟</p>
        <div class="flex gap-3">
            <button onclick="hideModal()" class="flex-1 bg-white border border-gray-200 text-gray-700 py-3 rounded-xl font-black hover:bg-gray-50 transition shadow-sm">إلغاء</button>
            <button id="confirmDeleteBtn" class="flex-1 bg-red-600 text-white py-3 rounded-xl font-black hover:bg-red-700 transition shadow-lg shadow-red-200">حذف</button>
        </div>
    </div>
</div>

<div id="deleteToast" class="fixed top-36 right-4 z-[99999] flex items-center gap-3 bg-[#1e1e1e] text-white px-5 py-3 rounded-full shadow-xl border border-gray-800 pointer-events-none transition-all duration-500 transform translate-x-[150%] opacity-0">
    <div class="bg-red-500 rounded-full p-1.5 shadow-md shadow-red-900/50">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
    </div>
    <span class="text-xs font-black tracking-wide whitespace-nowrap">تم حذف المنتج بنجاح</span>
</div>

<div class="w-full fixed bottom-0 left-0 right-0 z-50">
    <?php include 'includes/navbar.php'; ?>
</div>

<script>
    let productToDelete = null;

    function confirmDelete(id) {
        productToDelete = id;
        const modal = document.getElementById('deleteModal');
        const box = document.getElementById('modalBox');
        modal.classList.remove('opacity-0', 'invisible');
        box.classList.remove('scale-90');
        box.classList.add('scale-100');
    }

    function hideModal() {
        const modal = document.getElementById('deleteModal');
        const box = document.getElementById('modalBox');
        modal.classList.add('opacity-0', 'invisible');
        box.classList.add('scale-90');
        box.classList.remove('scale-100');
        productToDelete = null;
    }

    document.getElementById('confirmDeleteBtn').onclick = function() {
        if(productToDelete) {
            window.location.href = 'cart_action.php?action=remove&id=' + productToDelete;
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        
        if (urlParams.get('status') === 'deleted') {
            const toast = document.getElementById('deleteToast');
            
            // ⭐ الحركة (Animation): يدخل من اليمين ⭐
            setTimeout(() => {
                toast.classList.remove('translate-x-[150%]', 'opacity-0'); // يظهر في مكانه
            }, 300);

            // يخرج تاني لليمين
            setTimeout(() => {
                toast.classList.add('translate-x-[150%]', 'opacity-0'); // يرجع يستخبى يمين
            }, 3000);

            const newUrl = window.location.pathname;
            window.history.replaceState(null, null, newUrl);
        }
    });

    function checkout() {
        let msg = "مرحباً، أرغب في إتمام الطلب:%0a";
        <?php foreach($products as $p): ?>
        msg += "- <?= $p['name'] ?> (<?= $cart_items[$p['id']] ?>)%0a";
        <?php endforeach; ?>
        msg += "الإجمالي: <?= $total_price ?> ر.س";
        window.open("https://wa.me/966500000000?text=" + msg, "_blank");
    }
</script>

</body>
</html>