<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

switch ($_SESSION['role']) {
    case 'tailor':
        header("Location: /dashboard/tailor_dashboard.php");
        break;
    case 'designer':
        header("Location: /dashboard/designer_dashboard.php");
        break;
    case 'packaging':
        header("Location: /dashboard/packaging_dashboard.php");
        break;
    case 'client':
        header("Location: /dashboard/client_dashboard.php");
        break;
    default:
        session_destroy();
        header("Location: login.php");
        break;
}
exit();