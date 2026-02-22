<?php
/**
 * tailor_card_style.php — كارت الخياط مع زر تواصل ذكي
 * المتغير المتوقع: $p (صف بيانات الخياط من قاعدة البيانات)
 */

// التحقق الآمن من الجلسة (لا نستدعي session_start هنا؛ الصفحة الرئيسية استدعتها)
$_card_logged_in = isset($_SESSION['user_id']);
$_card_pid       = (int)($p['id'] ?? 0);

// رابط التواصل: مباشر إن كان مسجلاً، أو عبر login إن كان زائراً
$_card_chat_url = $_card_logged_in
    ? "/dashboard/chat.php?user_id={$_card_pid}"
    : "/login.php?chat_with={$_card_pid}";
?>

<div class="bg-white rounded-[24px] border border-gray-100 shadow-sm overflow-hidden flex-shrink-0"
     style="width:210px; min-width:210px;">

    <!-- صورة الغلاف -->
    <div class="h-28 bg-blue-50 flex items-center justify-center relative overflow-hidden">
        <?php if (!empty($p['work_image']) && file_exists("uploads/" . $p['work_image'])): ?>
            <img src="uploads/<?= htmlspecialchars($p['work_image']) ?>"
                 class="w-full h-full object-cover" alt="">
        <?php else: ?>
            <span class="text-6xl opacity-20">✂️</span>
        <?php endif; ?>

        <!-- أفاتار الملف الشخصي -->
        <div class="absolute -bottom-5 right-3 w-12 h-12 rounded-full border-2 border-white bg-gray-100 overflow-hidden shadow-md">
            <?php if (!empty($p['provider_image']) && file_exists("uploads/" . $p['provider_image'])): ?>
                <img src="uploads/<?= htmlspecialchars($p['provider_image']) ?>"
                     class="w-full h-full object-cover">
            <?php else: ?>
                <div class="w-full h-full flex items-center justify-center text-xl bg-emerald-50">👤</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="p-4 pt-7">

        <!-- الاسم -->
        <h3 class="font-black text-gray-900 text-sm truncate mb-2">
            <?= htmlspecialchars($p['provider_name'] ?? $p['username'] ?? 'خياط') ?>
        </h3>

        <!-- التخصص والسعر -->
        <div class="flex gap-1.5 mb-2 flex-wrap">
            <span class="text-[10px] font-bold bg-gray-100 text-gray-600 px-2 py-0.5 rounded-lg">
                ✨ <?= htmlspecialchars($p['specialty'] ?? 'عام') ?>
            </span>
            <span class="text-[10px] font-bold bg-amber-50 text-amber-700 px-2 py-0.5 rounded-lg">
                💰 <?= number_format((float)($p['starting_price'] ?? 0)) ?> ر.س
            </span>
        </div>

        <!-- الموقع -->
        <p class="text-[10px] text-gray-400 font-bold mb-3">
            📍 <?= htmlspecialchars($p['location'] ?? 'غير محدد') ?>
        </p>

        <!-- الأزرار -->
        <div class="flex gap-2">
            <?php if (!empty($p['phone'])): ?>
            <a href="tel:<?= htmlspecialchars($p['phone']) ?>"
               class="flex-1 bg-gray-900 text-white text-[11px] font-black py-2.5 rounded-xl flex items-center justify-center gap-1 active:scale-95 transition-transform">
                📞 اتصل
            </a>
            <?php endif; ?>

            <a href="<?= $_card_chat_url ?>"
               class="flex-1 bg-emerald-500 text-white text-[11px] font-black py-2.5 rounded-xl flex items-center justify-center gap-1 hover:bg-emerald-600 active:scale-95 transition-all">
                💬 تواصل
            </a>
        </div>

    </div>
</div>
