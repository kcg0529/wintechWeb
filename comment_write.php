<?php
session_start();

// 로그인 확인
if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
    exit;
}

// POST 데이터 확인
if (!isset($_POST['post_id']) || !isset($_POST['content'])) {
    echo json_encode(['success' => false, 'message' => '필수 데이터가 누락되었습니다.']);
    exit;
}

$post_id = (int)$_POST['post_id'];
$content = trim($_POST['content']);
$name = $_SESSION['email']; // 이메일 전체 사용

// 내용 검증
if (empty($content)) {
    echo json_encode(['success' => false, 'message' => '댓글 내용을 입력해주세요.']);
    exit;
}

if (mb_strlen($content) > 500) {
    echo json_encode(['success' => false, 'message' => '댓글은 500자 이하로 작성해주세요.']);
    exit;
}

try {
    require_once 'DAO/CommentDAO.php';
    
    // DAO를 사용하여 댓글 작성
    if (CommentDAO::createComment($post_id, $name, $content)) {
        echo json_encode(['success' => true, 'message' => '댓글이 작성되었습니다.']);
    } else {
        echo json_encode(['success' => false, 'message' => '댓글 작성에 실패했습니다.']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '데이터베이스 오류가 발생했습니다.']);
}
?>
