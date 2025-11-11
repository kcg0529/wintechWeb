<?php
session_start();

// 로그인 확인
if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
    exit;
}

// POST 데이터 확인
if (!isset($_POST['comment_id'])) {
    echo json_encode(['success' => false, 'message' => '댓글 ID가 필요합니다.']);
    exit;
}

$comment_id = (int)$_POST['comment_id'];
$current_user_name = $_SESSION['email']; // 이메일 전체 사용

try {
    require_once 'DAO/CommentDAO.php';
    
    // DAO를 사용하여 댓글 삭제
    $commentDAO = new CommentDAO();
    
    if ($commentDAO->deleteComment($comment_id, $current_user_name)) {
        echo json_encode(['success' => true, 'message' => '댓글이 삭제되었습니다.']);
    } else {
        echo json_encode(['success' => false, 'message' => '댓글을 찾을 수 없거나 삭제 권한이 없습니다.']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '데이터베이스 오류가 발생했습니다.']);
}
?>
