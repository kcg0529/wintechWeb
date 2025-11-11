<?php
session_start();
require_once 'admin_functions.php';
checkAdminSession();

require_once 'DAO/CommentDAO.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '잘못된 요청 방법입니다.']);
    exit;
}

if (!isset($_POST['comment_id']) || empty($_POST['comment_id'])) {
    echo json_encode(['success' => false, 'message' => '댓글 ID가 필요합니다.']);
    exit;
}

$comment_id = (int)$_POST['comment_id'];

try {
    // 댓글 삭제
    if (CommentDAO::deleteComment($comment_id)) {
        echo json_encode(['success' => true, 'message' => '댓글이 성공적으로 삭제되었습니다.']);
    } else {
        echo json_encode(['success' => false, 'message' => '댓글 삭제 중 오류가 발생했습니다.']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '데이터베이스 오류: ' . $e->getMessage()]);
}
?>
