<?php
session_start();

// JSON 응답 헤더 설정
header('Content-Type: application/json');

// 로그인 체크
if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
    exit;
}

// POST 데이터 확인
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
    exit;
}

$post_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if (!$post_id) {
    echo json_encode(['success' => false, 'message' => '게시글 ID가 필요합니다.']);
    exit;
}

try {
    require_once 'DAO/CommunityDAO.php';
    
    // 사용자 이메일 전체 사용
    $current_user_name = $_SESSION['email'];
    
    // DAO를 사용하여 게시글 삭제
    $communityDAO = new CommunityDAO();
    
    if ($communityDAO->deletePost($post_id, $current_user_name)) {
        echo json_encode(['success' => true, 'message' => '게시글이 삭제되었습니다.']);
    } else {
        echo json_encode(['success' => false, 'message' => '게시글을 찾을 수 없거나 삭제 권한이 없습니다.']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '서버 오류가 발생했습니다.']);
}
?>
