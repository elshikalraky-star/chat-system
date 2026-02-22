<?php
/**
 * packaging_card_style.php — كارت التغليف مع زر تواصل ذكي
 * المتغير المتوقع: $p
 */
$_card_logged_in_pkg = isset($_SESSION['user_id']);
$_card_pid_pkg       = (int)($p['id'] ?? 0);

$_card_chat_url_pkg = $_card_logged_in_pkg
    ? "/dashboard/chat.php?user_id={$_card_pid_pkg}"
    : "/login.php?chat_with={$_card_pid_pkg}";
?>

<div class="bg-white rounded-[24px] border border-gray-100 shadow-sm overflow-hidden flex-shrink-0 p-4"
     style="width:180px; min-width:180px;">

    <!-- الأيقونة / الصورة -->
    <div class="w-16 h-16 bg-amber-50 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-3 overflow-hidden">
        <?php if (!empty($p['provider_image']) && file_exists("uploads/" . $p['provider_image'])): ?>
            <img src="uploads/<?= htmlspecialchars($p['provider_image']) ?>"
                 class="w-full h-full object-cover rounded-2xl">
        <?php else: ?>
            🎁
        <?php endif; ?>
    </div>

    <!-- الاسم -->
    <h3 class="font-black text-gray-900 text-sm text-center mb-1 truncate">
        <?= htmlspecialchars($p['provider_name'] ?? $p['username'] ?? 'خدمة تغليف') ?>
    </h3>

    <!-- السعر -->
    <p class="text-center font-black text-amber-600 text-xs mb-1">
        يبدأ من <?= number_format((float)($p['starting_price'] ?? 0)) ?> ر.س
    </p>

    <!-- الموقع -->
    <p class="text-[10px] text-gray-400 font-bold text-center mb-3">
        📍 <?= htmlspecialchars($p['location'] ?? 'غير محدد') ?>
    </p>

    <!-- زر التواصل -->
    <a href="<?= $_card_chat_url_pkg ?>"
       class="w-full bg-amber-50 text-amber-700 text-[11px] font-black py-2.5 rounded-xl flex items-center justify-center gap-1 hover:bg-amber-100 active:scale-95 transition-all border border-amber-100">
        💬 تواصل
    </a>

</div>
