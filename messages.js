/**
 * messages.js — نظام الرسائل - كُرّة
 * التحديث التلقائي لعداد الرسائل في جميع الصفحات
 */
(function () {
    'use strict';

    function updateUnreadBadge() {
        fetch('/messages/get_unread_count.php')
            .then(r => r.json())
            .then(data => {
                const count = parseInt(data.count) || 0;
                document.querySelectorAll('.msg-badge').forEach(el => {
                    el.textContent = count > 9 ? '9+' : count;
                    count > 0 ? el.classList.remove('hidden') : el.classList.add('hidden');
                });
                const base = document.title.replace(/^\(\d+\) /, '');
                document.title = count > 0 ? `(${count}) ${base}` : base;
            }).catch(() => {});
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateUnreadBadge();
        setInterval(updateUnreadBadge, 10000);
    });

    window.openChat = function (userId) {
        window.location.href = `/dashboard/chat.php?user_id=${userId}`;
    };
})();
