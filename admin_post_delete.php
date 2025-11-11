<?php
session_start();
require_once 'admin_functions.php';
checkAdminSession();

require_once 'DAO/PostDAO.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '잘못된 요청 방법입니다.']);
    exit;
}

if (!isset($_POST['post_id']) || empty($_POST['post_id'])) {
    echo json_encode(['success' => false, 'message' => '게시글 ID가 필요합니다.']);
    exit;
}

$post_id = (int)$_POST['post_id'];

try {
    // 게시글 존재 확인
    $post = PostDAO::getPostById($post_id);
    if (!$post) {
        echo json_encode(['success' => false, 'message' => '존재하지 않는 게시글입니다.']);
        exit;
    }
    
    // 게시글 삭제 (댓글도 함께 삭제)
    if (PostDAO::deletePost($post_id)) {
        echo json_encode(['success' => true, 'message' => '게시글과 관련 댓글이 성공적으로 삭제되었습니다.']);
    } else {
        echo json_encode(['success' => false, 'message' => '게시글 삭제 중 오류가 발생했습니다.']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '데이터베이스 오류: ' . $e->getMessage()]);
}
?>
