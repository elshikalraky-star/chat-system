<div id="deleteModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] hidden flex items-center justify-center px-6">
        <div class="bg-white w-full max-w-sm p-6 rounded-[2.5rem] text-center shadow-2xl scale-95 transition-transform duration-300">
            <div class="text-4xl mb-3">🗑️</div>
            <h3 class="text-xl font-black text-gray-900 mb-2">تأكيد الحذف نهائياً؟</h3>
            <p class="text-xs text-gray-400 font-bold mb-6">احذر، هذا الإجراء لا يمكن التراجع عنه.</p>
            <div class="flex gap-3">
                <a id="confirmDeleteBtn" href="#" class="btn-click flex-1 bg-rose-600 text-white py-3 rounded-xl font-black shadow-lg shadow-rose-100">حذف</a>
                <button onclick="closeModal()" class="btn-click flex-1 bg-gray-100 text-gray-700 py-3 rounded-xl font-black">إلغاء</button>
            </div>
        </div>
    </div>

    <script>
        // 1. التحكم في مودال الحذف
        const modal = document.getElementById('deleteModal');
        const confirmBtn = document.getElementById('confirmDeleteBtn');

        function confirmDelete(id) {
            // نمرر الـ id لملف الحذف المخصص (سواء كان حذف منتج أو طلب)
            // ملاحظة: يمكنك تعديل الرابط هنا ليكون مرناً إذا لزم الأمر
            confirmBtn.href = "delete_product.php?id=" + id; 
            modal.classList.remove('hidden');
            setTimeout(() => { modal.firstElementChild.classList.remove('scale-95'); }, 10);
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.firstElementChild.classList.add('scale-95');
        }

        // 2. إخفاء رسائل النجاح تلقائياً بعد ثانيتين
        const alertBox = document.getElementById('success-alert');
        if(alertBox) {
            setTimeout(() => {
                alertBox.style.transition = "all 0.5s ease";
                alertBox.style.opacity = "0";
                alertBox.style.transform = "translate(-50%, -20px)";
                setTimeout(() => alertBox.remove(), 500);
            }, 2000);
        }

        // 3. تحسين استجابة الأزرار عند اللمس (لمنع التعليق)
        document.querySelectorAll('.btn-click').forEach(button => {
            button.addEventListener('touchend', function() {
                setTimeout(() => { this.blur(); }, 50);
            });
            button.addEventListener('click', function() {
                this.blur();
            });
        });

        // 4. تنظيف الصفحة عند الرجوع بالخلف في المتصفح
        window.onpageshow = function(event) {
            if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
                if (document.activeElement) document.activeElement.blur();
                closeModal();
            }
        };
    </script>
</body>
</html>