<?php
session_start();
require_once 'DAO/MainImgDAO.php';

// 관리자 권한 확인
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// POST 요청 확인
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_mainimages.php");
    exit();
}

// 입력값 받기
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$tag = isset($_POST['tag']) ? trim($_POST['tag']) : '';
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$text = isset($_POST['text']) ? trim($_POST['text']) : '';
$img = isset($_POST['img']) ? trim($_POST['img']) : '';

// 슬라이더 태그일 경우 title을 'slider'로 자동 설정
if ($tag === 'slider' && empty($title)) {
    $title = 'slider';
}

// 유효성 검사
$errors = [];

if ($id <= 0) {
    $errors[] = "잘못된 이미지 ID입니다.";
}

if (empty($tag)) {
    $errors[] = "태그를 선택해주세요.";
}

// shop 태그일 때만 title 필수
if ($tag === 'shop' && empty($title)) {
    $errors[] = "제목을 입력해주세요.";
}

// slider 태그일 때는 text 필수
if ($tag === 'slider' && empty($text)) {
    $errors[] = "슬라이더 오버레이 텍스트를 입력해주세요.";
}

if (empty($img)) {
    $errors[] = "이미지 파일명을 입력해주세요.";
}

// 에러가 있으면 이전 페이지로
if (!empty($errors)) {
    $error_message = implode("\\n", $errors);
    echo "<script>alert('" . addslashes($error_message) . "'); history.back();</script>";
    exit();
}

// 이미지 수정
try {
    $result = MainImgDAO::updateImage($id, $tag, $title, $text, $img);
    
    if ($result) {
        echo "<script>
            alert('메인이미지가 성공적으로 수정되었습니다.');
            location.href='admin_mainimages.php';
        </script>";
    } else {
        echo "<script>
            alert('메인이미지 수정에 실패했습니다.');
            history.back();
        </script>";
    }
} catch (Exception $e) {
    echo "<script>
        alert('오류가 발생했습니다: " . addslashes($e->getMessage()) . "');
        history.back();
    </script>";
}
?>

