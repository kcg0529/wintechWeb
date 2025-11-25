<?php
session_start();
require_once 'admin_functions.php';
checkAdminSession();

require_once 'DAO/ContentDAO.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $tag = isset($_POST['tag']) ? trim($_POST['tag']) : '';
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $path = isset($_POST['path']) ? trim($_POST['path']) : '';
    $img = isset($_POST['img']) ? trim($_POST['img']) : '';
    
    // 태그 처리: 게임을 운동으로 변환, Fun을 Work로 변환
    if ($tag === '게임' || $tag === 'Fun') {
        $tag = '운동';
    }
    // DB 저장 시 Work로 저장
    if ($tag === '운동') {
        $tag = 'Work';
    }
    
    // 유효성 검사
    if ($id <= 0 || empty($tag) || empty($title) || empty($path)) {
        $_SESSION['error'] = '모든 필수 항목을 입력해주세요.';
        header('Location: admin_content_edit.php?id=' . $id);
        exit;
    }
    
    // 콘텐츠 수정
    $result = ContentDAO::updateContent($id, $tag, $title, $path, $img);
    
    if ($result) {
        $_SESSION['success'] = '콘텐츠가 성공적으로 수정되었습니다.';
        header('Location: admin_contents.php');
    } else {
        $_SESSION['error'] = '콘텐츠 수정 중 오류가 발생했습니다.';
        header('Location: admin_content_edit.php?id=' . $id);
    }
} else {
    header('Location: admin_contents.php');
}
exit;
?>









