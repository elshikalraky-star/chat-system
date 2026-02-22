<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../db_connect.php';

header('Content-Type: application/json; charset=utf-8');

$type = $_GET['type'] ?? '';
$id   = $_GET['id'] ?? 0;

if (!$type || !$id) {
    echo json_encode([], JSON_UNESCAPED_UNICODE);
    exit;
}

try {

    if ($type === 'regions') {
        $stmt = $conn->prepare("SELECT id, name_ar FROM regions WHERE country_id = ?");
        $stmt->execute([$id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($type === 'cities') {
        $stmt = $conn->prepare("SELECT id, name_ar FROM cities WHERE region_id = ?");
        $stmt->execute([$id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}