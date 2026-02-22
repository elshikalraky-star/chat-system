<?php
$services = [
    [
        'title' => 'اكتشف قسم خدمات التغليف',
        'subtitle' => 'تغليف هدايا ومنتجات',
        'icon' => '📦',
        'link' => 'packaging.php',
        'rating' => '4.9'
    ],
    [
        'title' => 'اكتشف قسم الأقمشة',
        'subtitle' => 'خامات مختارة بعناية',
        'icon' => '👕',
        'link' => 'fabrics.php', /* ✅ تم التعديل هنا: يفتح صفحة الأقمشة مباشرة */
        'rating' => '5.0'
    ],
    [
        'title' => 'اكتشف قسم خياطين مميزين',
        'subtitle' => 'تفصيل حسب الطلب',
        'icon' => '✂️',
        'link' => 'tailors.php',
        'rating' => '4.8'
    ],
    [
        'title' => 'اكتشف قسم تصميم وجرافيك',
        'subtitle' => 'تصميم وتطريز العبايات',
        'icon' => '🎨',
        'link' => 'design.php',
        'rating' => '4.9'
    ]
];
?>

<style>
    .banner-fixed-frame {
        position: relative;
        height: 80px;
        background-color: #ffe4e6;
        border: 1px solid #ffe4e6;
        border-radius: 1rem;
        box-shadow: 0 2px 4px rgba(255, 228, 230, 0.5); 
        overflow: hidden;
        margin-bottom: 1rem; 
        direction: ltr; 
    }

    .banner-content {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        padding: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        direction: rtl; 
        background-color: #ffe4e6; 
        transition: transform 0.6s cubic-bezier(0.45, 0.05, 0.55, 0.95); 
    }

    /* حالات الحركة */
    .banner-content.active { transform: translateX(0); z-index: 10; }
    .banner-content.waiting { transform: translateX(-100%); z-index: 1; transition: none; }
    .banner-content.exit { transform: translateX(100%); z-index: 5; }
</style>

<div class="flex items-center justify-between mb-4 px-1">
    <h3 class="font-black text-base text-gray-800 flex items-center gap-1">
        كل خدماتك بمكان واحد اختر وش يناسبك؟✨🧶
    </h3>
    <a href="#" class="text-[10px] font-bold text-rose-500 hover:text-rose-600 transition-colors"></a>
</div>

<div class="banner-fixed-frame">
    <?php foreach ($services as $index => $service): ?>
        <a href="<?php echo $service['link']; ?>" class="banner-content <?php echo $index === 0 ? 'active' : 'waiting'; ?>">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center text-2xl shadow-sm border border-white">
                    <?php echo $service['icon']; ?>
                </div>
                <div class="flex flex-col">
                    <h4 class="font-black text-xs text-gray-900 mb-1"><?php echo $service['title']; ?></h4>
                    <p class="text-[10px] text-gray-600 font-bold"><?php echo $service['subtitle']; ?></p>
                </div>
            </div>
            <div class="bg-white text-amber-500 px-2 py-1 rounded-lg text-[10px] font-black whitespace-nowrap shadow-sm">
                ⭐ <?php echo $service['rating']; ?>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const slides = document.querySelectorAll('.banner-content');
        let currentIndex = 0;
        const intervalTime = 3000;

        function cycleSlides() {
            const currentSlide = slides[currentIndex];
            const nextIndex = (currentIndex + 1) % slides.length;
            const nextSlide = slides[nextIndex];

            // تحريك الشريحة الحالية للخروج
            currentSlide.classList.remove('active');
            currentSlide.classList.add('exit');

            // تحريك الشريحة التالية للدخول
            nextSlide.classList.remove('waiting');
            nextSlide.classList.add('active');

            // إعادة تهيئة الشريحة التي خرجت لتكون في الانتظار
            setTimeout(() => {
                currentSlide.classList.remove('exit');
                currentSlide.classList.add('waiting');
            }, 600); 

            currentIndex = nextIndex;
        }

        if(slides.length > 1) {
            setInterval(cycleSlides, intervalTime);
        }
    });
</script>