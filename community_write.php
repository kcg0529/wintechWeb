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

$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');

// 입력값 검증
if (empty($title) || empty($content)) {
    echo json_encode(['success' => false, 'message' => '제목과 내용을 모두 입력해주세요.']);
    exit;
}

if (mb_strlen($title) > 200) {
    echo json_encode(['success' => false, 'message' => '제목은 200자 이하로 입력해주세요.']);
    exit;
}

if (mb_strlen($content) > 5000) {
    echo json_encode(['success' => false, 'message' => '내용은 5000자 이하로 입력해주세요.']);
    exit;
}

try {
    require_once 'DAO/CommunityDAO.php';
    
    // 사용자 이메일 전체 사용
    $author_name = $_SESSION['email'];
    
    // DAO를 사용하여 게시글 작성
    $communityDAO = new CommunityDAO();
    
    if ($communityDAO->createPost($title, $content, $author_name)) {
        echo json_encode(['success' => true, 'message' => '게시글이 작성되었습니다.']);
    } else {
        echo json_encode(['success' => false, 'message' => '데이터베이스 오류가 발생했습니다.']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '서버 오류가 발생했습니다.']);
}
?>
