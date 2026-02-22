<?php
session_start();
require '../db_connect.php';
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }

include 'parts/header.php'; 
?>

<div class="max-w-md mx-auto px-4 mt-8">
    <div class="mb-8">
        <h2 class="text-2xl font-black text-gray-900 leading-tight">مركز القيادة 🚀</h2>
        <p class="text-gray-400 font-bold text-xs mt-1">أهلاً بك.. من هنا تتحكم في كل تفاصيل مشروعك.</p>
    </div>

    <div class="grid grid-cols-2 gap-4">
        
        <a href="manage_fabrics.php" class="btn-click bg-white p-6 rounded-[2.5rem] shadow-sm border border-gray-100 text-center flex flex-col items-center justify-center hover:border-rose-200 transition-all">
            <div class="text-3xl mb-3">🧵</div>
            <h3 class="font-black text-sm text-gray-800">الأقمشة</h3>
            <p class="text-[9px] text-gray-400 font-bold mt-1">المتجر الحالي</p>
        </a>

        <a href="manage_requests.php?role=tailor" class="btn-click bg-white p-6 rounded-[2.5rem] shadow-sm border border-gray-100 text-center flex flex-col items-center justify-center hover:border-blue-200 transition-all">
            <div class="text-3xl mb-3">✂️</div>
            <h3 class="font-black text-sm text-gray-800">الخياطين</h3>
            <span class="text-[9px] text-blue-600 font-bold mt-1">طلبات الانضمام</span>
        </a>

        <a href="manage_requests.php?role=designer" class="btn-click bg-white p-6 rounded-[2.5rem] shadow-sm border border-gray-100 text-center flex flex-col items-center justify-center hover:border-purple-200 transition-all">
            <div class="text-3xl mb-3">👗</div>
            <h3 class="font-black text-sm text-gray-800">المصممات</h3>
            <span class="text-[9px] text-purple-600 font-bold mt-1">التطريز والرسم</span>
        </a>

        <a href="manage_requests.php?role=packaging" class="btn-click bg-white p-6 rounded-[2.5rem] shadow-sm border border-gray-100 text-center flex flex-col items-center justify-center hover:border-amber-200 transition-all">
            <div class="text-3xl mb-3">🎁</div>
            <h3 class="font-black text-sm text-gray-800">التغليف</h3>
            <span class="text-[9px] text-amber-600 font-bold mt-1">هدايا وتجهيز</span>
        </a>

    </div>
</div>
</body>
</html>