<?php
session_start();
$count = isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0;
echo json_encode(['count' => $count]);
?>