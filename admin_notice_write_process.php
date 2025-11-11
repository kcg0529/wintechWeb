<?php
session_start();
require_once 'admin_functions.php';
checkAdminSession();

require_once 'DAO/NoticeDAO.php';

// POST 데이터 확인
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_notice_write.php');
    exit;
}

$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$content = isset($_POST['content']) ? trim($_POST['content']) : '';

// 입력값 검증
$errors = [];

if (empty($title)) {
    $errors[] = '제목을 입력해주세요.';
} elseif (strlen($title) > 200) {
    $errors[] = '제목은 200자 이하로 입력해주세요.';
}

if (empty($content)) {
    $errors[] = '내용을 입력해주세요.';
} elseif (strlen($content) > 5000) {
    $errors[] = '내용은 5000자 이하로 입력해주세요.';
}

// HTML 태그 제거 (보안을 위해)
$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
$content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

// 줄바꿈을 <br> 태그로 변환
$content = nl2br($content);

// 에러가 있으면 작성 페이지로 리다이렉트
if (!empty($errors)) {
    $error_message = implode('<br>', $errors);
    echo "<script>
        alert('" . addslashes($error_message) . "');
        history.back();
    </script>";
    exit;
}

try {
    // 공지사항 작성
    $notice_id = NoticeDAO::createNotice($title, $content);
    
    if ($notice_id) {
        // 성공 시 공지사항 목록으로 리다이렉트
        echo "<script>
            alert('공지사항이 성공적으로 작성되었습니다.');
            location.href = 'admin_notices.php';
        </script>";
    } else {
        echo "<script>
            alert('공지사항 작성 중 오류가 발생했습니다.');
            history.back();
        </script>";
    }
    
} catch (Exception $e) {
    error_log("Notice write error: " . $e->getMessage());
    echo "<script>
        alert('데이터베이스 오류가 발생했습니다.');
        history.back();
    </script>";
}
?>

