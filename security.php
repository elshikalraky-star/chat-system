<?php
// includes/security.php

// 1. منع الوصول المباشر للملف (حماية السيرفر) 🚫
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header("HTTP/1.1 403 Forbidden");
    exit("Access denied.");
}

// 2. إعدادات الجلسة الآمنة (Session Security) 🔒
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_trans_sid', 0);
    
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => $cookieParams["lifetime"],
        'path' => $cookieParams["path"],
        'domain' => $cookieParams["domain"],
        'secure' => isset($_SERVER['HTTPS']), // يشتغل فقط مع SSL
        'httponly' => true, // منع سرقة الكوكيز عبر الجافاسكريبت
        'samesite' => 'Strict'
    ]);
    
    session_start();
    
    // تجديد الجلسة لمنع هجمات التثبيت
    if (!isset($_SESSION['CREATED'])) {
        $_SESSION['CREATED'] = time();
    } else if (time() - $_SESSION['CREATED'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['CREATED'] = time();
    }
}

// 3. حماية رؤوس الصفحة (Security Headers)  Headers 🛡️
header("X-Frame-Options: DENY"); // منع وضع الموقع في Iframe
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

// 4. دالة تنظيف المدخلات (Input Cleaning) 🧹
if (!function_exists('clean_input')) {
    function clean_input($data) {
        if (is_array($data)) {
            return array_map('clean_input', $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

// تطبيق التنظيف على جميع المدخلات تلقائياً
if (!empty($_GET)) $_GET = clean_input($_GET);
if (!empty($_POST)) {
    foreach ($_POST as $key => $value) {
        if (strpos(strtolower($key), 'password') !== false) continue; 
        $_POST[$key] = clean_input($value);
    }
}

// نهاية كود السيرفر وبداية كود الواجهة
?>

<style>
    /* منع تحديد النصوص وسحب الصور */
    body {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
    /* استثناء الخانات للسماح للمستخدم بالكتابة */
    input, textarea {
        -webkit-user-select: text !important;
        -moz-user-select: text !important;
        -ms-user-select: text !important;
        user-select: text !important;
    }
    img { pointer-events: none; }
</style>

<script>
    // منع الزر الأيمن للفأرة
    document.addEventListener('contextmenu', event => event.preventDefault());

    // منع اختصارات لوحة المفاتيح (Ctrl+C, Ctrl+U, F12)
    document.onkeydown = function(e) {
        if (e.keyCode == 123 || (e.ctrlKey && (e.keyCode == 'U'.charCodeAt(0) || e.keyCode == 'C'.charCodeAt(0)))) {
            return false;
        }
    };
</script>