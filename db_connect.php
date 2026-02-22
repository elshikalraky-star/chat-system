<?php
// db_connect.php - ??? ??????? ?????? ????????

$host     = 'sql307.infinityfree.com';
$dbname   = 'if0_40592393_sultana_db';
$username = 'if0_40592393';
$password = 'nasser1995pro';

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ]
    );

    // ????? ???????
    $conn->exec("SET NAMES utf8mb4");

} catch (PDOException $e) {
    die("??? ??? ?? ??????? ?????? ????????: " . $e->getMessage());
}
?>