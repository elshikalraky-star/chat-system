<?php
// دالة تشفير كلمة المرور 🔒
function hashUserPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// دالة التسجيل الآمن المتوافقة مع PDO 🛡️
function registerUserSecurely($conn, $data) {
    try {
        $sql = "INSERT INTO users (username, phone, password, role, provider_name, location, provider_image, work_image, starting_price, specialty, delivery_time, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        
        $stmt = $conn->prepare($sql);
        
        // تشفير كلمة المرور قبل الحفظ
        $data['password'] = hashUserPassword($data['password']);

        // في PDO نمرر البيانات مباشرة في مصفوفة داخل execute
        return $stmt->execute([
            $data['username'], $data['phone'], $data['password'], $data['role'],
            $data['provider_name'], $data['location'], $data['provider_image'],
            $data['work_image'], $data['starting_price'], $data['specialty'], $data['delivery_time']
        ]);
    } catch (PDOException $e) {
        return false;
    }
}