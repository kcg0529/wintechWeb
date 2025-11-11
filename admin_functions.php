<?php
// 관리자 세션 체크 함수
function checkAdminSession() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: admin_login.php');
        exit;
    }
}

// 관리자 로그아웃 함수
function adminLogout() {
    session_start();
    session_destroy();
    header('Location: admin_login.php');
    exit;
}
?>










