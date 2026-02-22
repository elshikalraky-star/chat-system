<div class="relative z-40" style="direction: rtl;">
    <div id="location-trigger" onclick="toggleLocMenu()" 
         class="cursor-pointer inline-flex items-center gap-1.5 bg-white px-2.5 py-1 rounded-full border border-gray-100 shadow-sm hover:shadow-md transition-all">
        
        <div class="flex items-center justify-center text-red-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
            </svg>
        </div>
        
        <div class="flex flex-col leading-tight text-right">
            <span class="text-[8px] text-gray-400 font-bold">التوصيل إلى:</span>
            <span id="display-loc-text" class="text-[11px] font-black text-gray-800 truncate max-w-[120px]">
                جاري التحديد...
            </span>
        </div>
        <svg class="w-2.5 h-2.5 text-gray-300 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"></path></svg>
    </div>

    <div id="loc-dropdown" class="hidden absolute top-full right-0 mt-2 w-64 bg-white rounded-2xl shadow-2xl border border-gray-50 overflow-hidden z-50">
        <div class="p-4 border-b border-gray-50 bg-gray-50">
            <p class="text-[11px] text-gray-600 font-bold">هل الموقع غير دقيق؟</p>
        </div>
        <div class="p-2">
            <button onclick="detectGlobalLocation(true)" class="w-full text-right px-4 py-3 text-xs font-black text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg flex items-center gap-2">
                🔄 تحديث الموقع تلقائياً
            </button>
        </div>
        <div class="p-3 text-[10px] text-gray-400 text-center font-bold">
            يتم تحديد المنطقة بناءً على مزود الخدمة
        </div>
    </div>
</div>

<script>
    (function() {
        const KEY = 'global_user_location_v1';
        const disp = document.getElementById('display-loc-text');

        // 👈 التحقق من الموقع المحفوظ عند التحميل
        const saved = localStorage.getItem(KEY);
        if (saved) {
            disp.innerText = saved;
        } else {
            detectGlobalLocation(false);
        }
    })();

    function detectGlobalLocation(forceGPS = false) {
        const disp = document.getElementById('display-loc-text');
        disp.innerText = 'جاري التحديد...';

        // 👈 جلب البيانات من API الموقع
        fetch('https://api.bigdatacloud.net/data/reverse-geocode-client?localityLanguage=ar')
        .then(res => res.json())
        .then(data => {
            let country = data.countryName || 'غير معروف';
            let region = data.principalSubdivision || '';
            if (!region) region = data.city || '';

            // 👈 تنظيف النصوص الزائدة
            region = region.replace(/محافظة|منطقة|ولاية|Governorate|Region|Province|State/g, '').trim();

            let finalLoc = country;
            if (region && region !== country) {
                finalLoc = country + '، ' + region;
            }

            disp.innerText = finalLoc;
            localStorage.setItem('global_user_location_v1', finalLoc);
            
            if(forceGPS) toggleLocMenu();
        })
        .catch(() => {
            disp.innerText = 'مصر، القاهرة';
        });
    }

    // 👈 دالة إظهار وإخفاء القائمة
    function toggleLocMenu() {
        document.getElementById('loc-dropdown').classList.toggle('hidden');
    }

    // 👈 إغلاق القائمة عند النقر خارجها
    document.addEventListener('click', function(e) {
        const trigger = document.getElementById('location-trigger');
        const menu = document.getElementById('loc-dropdown');
        if (trigger && !trigger.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });
</script>