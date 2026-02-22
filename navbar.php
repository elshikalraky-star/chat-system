<?php
// includes/navbar.php
// ملاحظة: session_start() يُستدعى بأمان فقط إن لم تكن الجلسة بدأت

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── رابط "حسابي / لوحتي" حسب الدور ────────────────────────────────────
if (isset($_SESSION['user_id'])) {
    $nav_account_href = match($_SESSION['role'] ?? 'client') {
        'tailor'    => '/dashboard/tailor_dashboard.php',
        'designer'  => '/dashboard/designer_dashboard.php',
        'packaging' => '/dashboard/packaging_dashboard.php',
        'admin'     => '/admin/dashboard.php',
        default     => '/dashboard/client_dashboard.php',
    };
    $nav_account_label = 'لوحتي';
    $nav_account_icon  = match($_SESSION['role'] ?? 'client') {
        'tailor'    => '✂️',
        'designer'  => '🎨',
        'packaging' => '🎁',
        default     => '👤',
    };
} else {
    $nav_account_href  = '/login.php';
    $nav_account_label = 'دخول';
    $nav_account_icon  = null; // سيستخدم SVG
}

// ── عدد الرسائل غير المقروءة ─────────────────────────────────────────────
$nav_unread_msgs = 0;
if (isset($_SESSION['user_id']) && isset($conn)) {
    try {
        $ns = $conn->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
        $ns->execute([$_SESSION['user_id']]);
        $nav_unread_msgs = (int) $ns->fetchColumn();
    } catch (Throwable $e) {
        $nav_unread_msgs = 0;
    }
}
?>
<style>
    .wa-float-nav {
        position: fixed; bottom: 85px; right: 12px; z-index: 1000;
        display: flex; flex-direction: column; align-items: center; pointer-events: none;
    }
    .wa-text-box {
        background: #222; color: white; padding: 5px 12px; border-radius: 8px;
        font-size: 10px; font-weight: bold; margin-bottom: 8px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2); opacity: 0; visibility: hidden;
        animation: waMsgCycle 60s infinite; white-space: nowrap; position: relative;
    }
    .wa-text-box::after {
        content: ''; position: absolute; bottom: -4px; left: 50%; transform: translateX(-50%);
        border-width: 5px 5px 0; border-style: solid; border-color: #222 transparent;
    }
    .wa-btn-pulse {
        width: 50px; height: 50px; background-color: #25d366; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 0 0 0 rgba(37,211,102,0.7); animation: waPulseGlow 2.5s infinite;
        text-decoration: none; pointer-events: auto;
    }
    .wa-btn-pulse img { width: 28px; height: 28px; }

    .cart-badge, .wishlist-badge {
        position: absolute; top: -5px; right: -2px;
        color: white; width: 16px; height: 16px; border-radius: 50%;
        font-size: 9px; font-weight: 900;
        display: flex; align-items: center; justify-content: center;
        border: 2px solid white; opacity: 0; transform: scale(0);
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); z-index: 10;
    }
    .cart-badge    { background-color: #ef4444; }
    .wishlist-badge{ background-color: #f43f5e; }
    .cart-badge.show, .wishlist-badge.show { opacity: 1; transform: scale(1); }

    @keyframes waMsgCycle {
        0%,94% { opacity:0; visibility:hidden; }
        95%,98% { opacity:1; visibility:visible; }
        100% { opacity:0; visibility:hidden; }
    }
    @keyframes waPulseGlow {
        0%   { transform:scale(0.98); box-shadow:0 0 0 0 rgba(37,211,102,0.6); }
        70%  { transform:scale(1.02); box-shadow:0 0 0 15px rgba(37,211,102,0); }
        100% { transform:scale(0.98); }
    }
</style>

<!-- زر واتساب عائم -->
<div class="wa-float-nav">
    <div class="wa-text-box">تواصل بدعم كُرّة</div>
    <a href="https://wa.me/966500000000" target="_blank" class="wa-btn-pulse">
        <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp">
    </a>
</div>

<!-- شريط التنقل -->
<nav class="fixed bottom-0 left-0 right-0 w-full bg-white/95 backdrop-blur-md border-t border-gray-100 py-1.5 px-4 flex justify-around items-end shadow-sm z-50">

    <!-- الرئيسية -->
    <a href="/index.php" class="nav-btn flex flex-col items-center gap-0 w-14">
        <div class="icon-box p-0.5 rounded-xl transition-all hover:bg-gray-50">
            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                <path d="M11.47 3.84a.75.75 0 011.06 0l8.632 8.632a.75.75 0 01-1.06 1.06l-.353-.353V21a.75.75 0 01-.75.75H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a.75.75 0 01-.75-.75v-7.821l-.353.353a.75.75 0 11-1.06-1.06l8.632-8.632z"/>
            </svg>
        </div>
        <span class="label-text text-[9px] font-bold text-gray-400">الرئيسية</span>
    </a>

    <!-- المفضلة -->
    <a href="/favorites.php" class="nav-btn flex flex-col items-center gap-0 w-14 relative">
        <div class="icon-box p-0.5 rounded-xl transition-all hover:bg-gray-50 relative">
            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.691 2.25 5.357 4.868 3 8.01 3c1.5 0 2.92.748 3.99 1.954C13.08 3.748 14.5 3 16 3c3.142 0 5.76 2.643 5.76 5.691 0 3.483-2.438 6.67-4.743 8.815a25.18 25.18 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/>
            </svg>
            <span id="wishlist-badge-count" class="wishlist-badge">0</span>
        </div>
        <span class="label-text text-[9px] font-bold text-gray-400">المفضلة</span>
    </a>

    <!-- السلة -->
    <a href="/cart.php" class="nav-btn flex flex-col items-center gap-0 w-14 relative">
        <div class="icon-box p-0.5 rounded-xl transition-all hover:bg-gray-50 relative">
            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M7.5 6v.75H5.513c-.96 0-1.764.724-1.865 1.679l-1.263 12A1.875 1.875 0 004.25 22.5h15.5a1.875 1.875 0 001.865-2.071l-1.263-12a1.875 1.875 0 00-1.865-1.679H16.5V6a4.5 4.5 0 10-9 0zM12 3a3 3 0 00-3 3v.75h6V6a3 3 0 00-3-3zm-3 8.25a3 3 0 106 0v-.75a.75.75 0 011.5 0v.75a4.5 4.5 0 11-9 0v-.75a.75.75 0 011.5 0v.75z" clip-rule="evenodd"/>
            </svg>
            <span id="cart-badge-count" class="cart-badge">0</span>
        </div>
        <span class="label-text text-[9px] font-bold text-gray-400">السلة</span>
    </a>

    <!-- حسابي / لوحتي -->
    <a href="<?= htmlspecialchars($nav_account_href) ?>" class="nav-btn flex flex-col items-center gap-0 w-14 relative">
        <div class="icon-box p-0.5 rounded-xl transition-all hover:bg-gray-50 relative">
            <?php if (isset($_SESSION['user_id']) && $nav_account_icon): ?>
                <!-- أيقونة إيموجي حسب الدور -->
                <span class="text-[22px] leading-6 block"><?= $nav_account_icon ?></span>
                <?php if ($nav_unread_msgs > 0): ?>
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[8px] font-black w-4 h-4 rounded-full flex items-center justify-center border border-white">
                        <?= $nav_unread_msgs > 9 ? '9+' : $nav_unread_msgs ?>
                    </span>
                <?php endif; ?>
            <?php else: ?>
                <!-- أيقونة SVG للزائر -->
                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd"/>
                </svg>
            <?php endif; ?>
        </div>
        <span class="label-text text-[9px] font-bold text-gray-400"><?= $nav_account_label ?></span>
    </a>

</nav>

<script>
(function() {
    // تحديد الزر النشط
    const path = window.location.pathname;
    const map  = {
        'index.php':  { i: 0, c: 'text-violet-600', b: 'bg-violet-50' },
        'favorites':  { i: 1, c: 'text-rose-500',   b: 'bg-rose-50'   },
        'cart':       { i: 2, c: 'text-orange-500',  b: 'bg-orange-50' },
        'dashboard':  { i: 3, c: 'text-blue-500',    b: 'bg-blue-50'   },
        'login':      { i: 3, c: 'text-blue-500',    b: 'bg-blue-50'   },
    };
    for (const [key, val] of Object.entries(map)) {
        if (path.includes(key)) {
            const btn = document.querySelectorAll('.nav-btn')[val.i];
            if (!btn) continue;
            btn.querySelectorAll('svg').forEach(s => s.classList.replace('text-gray-400', val.c));
            const lbl = btn.querySelector('.label-text');
            if (lbl) lbl.classList.replace('text-gray-400', val.c);
            const box = btn.querySelector('.icon-box');
            if (box) box.classList.add(val.b, '-translate-y-1');
        }
    }

    // بادجات السلة والمفضلة
    function updateBadges() {
        [
            { url: '/get_cart_count.php',     id: 'cart-badge-count'     },
            { url: '/get_wishlist_count.php',  id: 'wishlist-badge-count' },
        ].forEach(({ url, id }) => {
            fetch(url).then(r => r.json()).then(d => {
                const el = document.getElementById(id);
                if (!el) return;
                if (d.count > 0) { el.innerText = d.count > 9 ? '+9' : d.count; el.classList.add('show'); }
                else el.classList.remove('show');
            }).catch(() => {});
        });
    }
    updateBadges();
    setInterval(updateBadges, 5000);
})();
</script>
