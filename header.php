<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>مركز قيادة كُرّة</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; user-select: none; -webkit-user-select: none; }
        .btn-click:active { transform: scale(0.95); transition: 0.1s; }
    </style>
</head>
<body class="bg-gray-50 pb-10">
    <header class="bg-white p-4 shadow-sm border-b sticky top-0 z-50 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-black rounded-2xl flex items-center justify-center text-white font-black shadow-lg shadow-gray-200">K</div>
            <h1 class="text-sm font-black text-gray-800 leading-tight">نظام إدارة<br><span class="text-rose-600">كُرّة الشامل</span></h1>
        </div>
        <div class="flex items-center gap-4">
            <div class="relative bg-gray-50 p-2 rounded-xl border border-gray-100">
                <span class="text-xl">🔔</span>
                <?php 
                $total_pending = $conn->query("SELECT COUNT(*) FROM users WHERE status = 'pending' AND role != 'client'")->fetchColumn();
                if($total_pending > 0): 
                ?>
                    <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[8px] w-4 h-4 rounded-full flex items-center justify-center font-black animate-bounce"><?= $total_pending ?></span>
                <?php endif; ?>
            </div>
            <a href="logout.php" class="text-[10px] font-black bg-rose-50 text-rose-600 px-3 py-2 rounded-lg">خروج</a>
        </div>
    </header>