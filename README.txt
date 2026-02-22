╔══════════════════════════════════════════════════════════════╗
║        نظام الرسائل - كُرّة | دليل التثبيت                ║
╚══════════════════════════════════════════════════════════════╝

📁 بنية الملفات (المسارات النهائية على الاستضافة)
═══════════════════════════════════════════════════════════

messages/
   ├── send_message.php
   ├── get_messages.php
   ├── get_conversations.php
   ├── mark_as_read.php
   ├── get_unread_count.php
   ├── block_user.php
   ├── unblock_user.php
   └── delete_conversation.php

dashboard/
   ├── messages_inbox.php       ← صفحة صندوق الرسائل
   ├── chat.php                 ← صفحة الدردشة
   ├── client_dashboard.php     ← استبدال الملف الحالي
   ├── tailor_dashboard.php     ← استبدال الملف الحالي
   ├── designer_dashboard.php   ← استبدال الملف الحالي
   └── packaging_dashboard.php  ← استبدال الملف الحالي

js/
   └── messages.js

css/
   └── messages.css


🚀 خطوات التثبيت
═══════════════════════════════════════════════════════════

الخطوة 1: قاعدة البيانات
─────────────────────────
تأكد أنك نفّذت ملف messaging_system_01_DATABASE.sql
مسبقاً في phpMyAdmin (هذا مكتمل بالفعل حسب ما ذكرت).

الخطوة 2: رفع مجلد messages/
──────────────────────────────
ارفع مجلد messages/ إلى جذر موقعك (public_html/)
بحيث يصبح المسار: public_html/messages/

الخطوة 3: رفع صفحات dashboard/
──────────────────────────────────
ارفع الملفات التالية إلى public_html/dashboard/:
  - messages_inbox.php  (جديد)
  - chat.php            (جديد)
  - client_dashboard.php    (يستبدل القديم)
  - tailor_dashboard.php    (يستبدل القديم)
  - designer_dashboard.php  (يستبدل القديم)
  - packaging_dashboard.php (يستبدل القديم)

الخطوة 4: رفع JS و CSS
────────────────────────
ارفع js/messages.js  إلى public_html/js/
ارفع css/messages.css إلى public_html/css/


✅ التحقق من التثبيت
═══════════════════════════════════════════════════════════

1. افتح لوحة تحكم أي حساب
2. يجب أن يظهر زر 📬 الرسائل في الهيدر وشريط التنقل
3. افتح messages_inbox.php - يجب أن تعمل الصفحة
4. جرب الدردشة بين حسابين مختلفين

⚠️ ملاحظات مهمة
═══════════════════════════════════════════════════════════

- جميع ملفات messages/ تتوقع أن db_connect.php
  موجود في المجلد الأب (public_html/db_connect.php)
  وهو كذلك حسب بنية موقعك.

- لتفعيل زر "تواصل" على بطاقات الخياطين/المصممين
  في الصفحة الرئيسية، أضف هذا الكود على كل بطاقة:
  
  <a href="/dashboard/chat.php?user_id=<?= $provider['id'] ?>">
      💬 تواصل
  </a>

- التحديث التلقائي للرسائل: كل 2 ثانية في صفحة الدردشة
- التحديث التلقائي للعداد: كل 10 ثواني في الداشبوردات
