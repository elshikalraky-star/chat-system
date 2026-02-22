<?php 
// pending_approval.php - صفحة الانتظار بعد التسجيل لأصحاب المهن
include 'includes/header.php'; 
?>

<main class="min-h-screen bg-gray-50 flex items-center justify-center px-4 font-['Cairo']">
    <div class="max-w-md w-full bg-white rounded-[35px] shadow-xl border border-gray-100 p-10 text-center relative overflow-hidden">
        
        <div class="relative z-10">
            <div class="w-24 h-24 bg-amber-50 rounded-full flex items-center justify-center text-5xl mx-auto mb-6 animate-pulse">
                ⏳
            </div>
            
            <h1 class="text-2xl font-black text-gray-900 mb-4">طلبك قيد المراجعة! ✨</h1>
            
            <p class="text-gray-500 font-bold leading-relaxed mb-8">
                أهلاً بك في عائلة <span class="text-black">كُرّة</span>. <br>
                لقد استلمنا بياناتك وصور أعمالك بنجاح. يقوم فريقنا حالياً بمراجعة الحساب للتأكد من الجودة وتفعيل كارته في الصفحة الرئيسية.
            </p>

            <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 mb-8">
                <p class="text-blue-700 text-xs font-black">
                    📢 سيتم تفعيل حسابك خلال 24 ساعة كحد أقصى.
                </p>
            </div>

            <div class="space-y-3">
                <a href="index.php" class="block w-full bg-black text-white py-4 rounded-2xl font-black shadow-lg hover:scale-[0.98] transition-transform">
                    العودة للرئيسية
                </a>
                <a href="contact.php" class="block w-full text-gray-400 text-xs font-bold hover:text-gray-600 transition-colors">
                    هل لديك استفسار؟ تواصل معنا
                </a>
            </div>
        </div>

        <div class="absolute -top-10 -left-10 w-40 h-40 bg-amber-100 rounded-full opacity-30 filter blur-3xl"></div>
        <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-blue-100 rounded-full opacity-30 filter blur-3xl"></div>
    </div>
</main>

<?php include 'includes/navbar.php'; ?>