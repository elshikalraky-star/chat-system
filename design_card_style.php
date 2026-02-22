<?php
/**
 * design_card_style.php — كارت المصمم مع زر تواصل ذكي
 * المتغير المتوقع: $p
 */
$_card_logged_in_d = isset($_SESSION['user_id']);
$_card_pid_d       = (int)($p['id'] ?? 0);

$_card_chat_url_d = $_card_logged_in_d
    ? "/dashboard/chat.php?user_id={$_card_pid_d}"
    : "/login.php?chat_with={$_card_pid_d}";
?>

<div class="bg-white rounded-[24px] border border-gray-100 shadow-sm overflow-hidden flex-shrink-0 p-4"
     style="width:190px; min-width:190px;">

    <!-- الأيقونة / الصورة -->
    <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-3 overflow-hidden">
        <?php if (!empty($p['provider_image']) && file_exists("uploads/" . $p['provider_image'])): ?>
            <img src="uploads/<?= htmlspecialchars($p['provider_image']) ?>"
                 class="w-full h-full object-cover rounded-2xl">
        <?php else: ?>
            🎨
        <?php endif; ?>
    </div>

    <!-- نوع الخدمة -->
    <div class="text-center mb-2">
        <span class="text-[10px] font-bold bg-purple-50 text-purple-600 px-3 py-0.5 rounded-full">
            <?= htmlspecialchars($p['specialty'] ?? 'تصميم عام') ?>
        </span>
    </div>

    <!-- الاسم والموقع -->
    <h3 class="font-black text-gray-900 text-sm text-center mb-0.5 truncate">
        <?= htmlspecialchars($p['provider_name'] ?? $p['username'] ?? 'مصممة') ?>
    </h3>
    <p class="text-[10px] text-gray-400 font-bold text-center mb-1">
        📍 <?= htmlspecialchars($p['location'] ?? 'غير محدد') ?>
    </p>

    <!-- السعر -->
    <p class="text-center font-black text-purple-600 text-xs mb-3">
        يبدأ من <span><?= number_format((float)($p['starting_price'] ?? 0)) ?></span> ر.س
    </p>

    <!-- زر التواصل -->
    <a href="<?= $_card_chat_url_d ?>"
       class="w-full bg-purple-100 text-purple-700 text-[11px] font-black py-2.5 rounded-xl flex items-center justify-center gap-1 hover:bg-purple-200 active:scale-95 transition-all">
        💬 تواصل مع المصمم<?= (($p['gender'] ?? '') === 'female') ? 'ة' : '' ?>
    </a>

</div>
