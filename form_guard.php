<?php
// includes/form_guard.php
// هذا الملف يمنع إعادة إرسال البيانات عند تحديث الصفحة
// ويقوم بتعطيل زر الإرسال بعد الضغط عليه لمنع التكرار
?>
<script>
    // 1. منع رسالة "Confirm Form Resubmission" عند التحديث
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }

    // 2. تعطيل الزر عند الضغط عليه لمنع التكرار
    document.addEventListener("DOMContentLoaded", function() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const btn = form.querySelector('button[type="submit"]');
                if(btn) {
                    // نغير النص ونعطل الزر
                    const originalText = btn.innerText;
                    btn.innerText = 'جاري المعالجة... ⏳';
                    btn.style.opacity = '0.7';
                    btn.style.cursor = 'not-allowed';
                    
                    // منع الضغط مرة أخرى
                    setTimeout(() => {
                        btn.disabled = true; 
                    }, 50);
                }
            });
        });
    });
</script>