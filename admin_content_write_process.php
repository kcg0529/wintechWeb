<?php
session_start();
require_once 'admin_functions.php';
checkAdminSession();

require_once 'DAO/ContentDAO.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tag = isset($_POST['tag']) ? trim($_POST['tag']) : '';
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $path = isset($_POST['path']) ? trim($_POST['path']) : '';
    $img = isset($_POST['img']) ? trim($_POST['img']) : '';
    
    // 유효성 검사
    if (empty($tag) || empty($title) || empty($path)) {
        $_SESSION['error'] = '태그, 제목, 경로는 필수 입력 항목입니다.';
        header('Location: admin_content_write.php');
        exit;
    }
    
    // 콘텐츠 생성
    $result = ContentDAO::createContent($tag, $title, $path, $img);
    
    if ($result) {
        $_SESSION['success'] = '콘텐츠가 성공적으로 추가되었습니다.';
        header('Location: admin_contents.php');
    } else {
        $_SESSION['error'] = '콘텐츠 추가 중 오류가 발생했습니다.';
        header('Location: admin_content_write.php');
    }
} else {
    header('Location: admin_contents.php');
}
exit;
?>




