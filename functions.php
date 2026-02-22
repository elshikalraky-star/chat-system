<?php
// includes/functions.php - نسخة مستقرة وآمنة

function hashUserPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function registerUserSecurely($conn, $basicData, $profileData = null) {

    if (!$conn instanceof PDO) {
        die("Database connection is not PDO");
    }

    try {
        $conn->beginTransaction();

        $sqlUser = "INSERT INTO users 
        (username, phone, password, role, status, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())";

        $stmtUser = $conn->prepare($sqlUser);
        $hashedPass = hashUserPassword($basicData['password']);

        $stmtUser->execute([
            $basicData['username'], 
            $basicData['phone'],
            $hashedPass,
            $basicData['role'],
            $basicData['status'] ?? 'pending' // قيمة افتراضية إذا لم تُرسَل
        ]);

        $newUserId = $conn->lastInsertId();

        // إدخال البروفايل لمقدمي الخدمة
        if ($basicData['role'] !== 'client' && $profileData !== null) {

            $sqlProfile = "INSERT INTO provider_profiles (
                user_id, provider_type, provider_name, specialty, 
                starting_price, delivery_time, location, 
                country_id, region_id, city_id, provider_image, work_image
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmtProfile = $conn->prepare($sqlProfile);

            $stmtProfile->execute([
                $newUserId,
                $basicData['role'],
                $profileData['business_name'] ?? '',
                $profileData['specialty'] ?? '',
                $profileData['price'] ?? 0,
                $profileData['time'] ?? '',
                $profileData['location'] ?? '',
                $profileData['country_id'] ?? null,
                $profileData['region_id'] ?? null,
                $profileData['city_id'] ?? null,
                $profileData['provider_image'] ?? 'default_provider.png',
                $profileData['work_image'] ?? 'default_work.png'
            ]);
        }

        $conn->commit();
        return $newUserId; // ← إعادة ID بدلاً من true

    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Register Error: " . $e->getMessage());
        return false;
    }
}
?>
