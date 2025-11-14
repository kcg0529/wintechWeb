<?php
session_start();
require_once 'admin_functions.php';
checkAdminSession();

require_once 'DAO/mysqli_con.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '잘못된 요청 방법입니다.']);
    exit;
}

if (!isset($_POST['post_id']) || empty($_POST['post_id']) || !isset($_POST['content']) || empty(trim($_POST['content']))) {
    echo json_encode(['success' => false, 'message' => '게시글 ID와 댓글 내용이 필요합니다.']);
    exit;
}

$post_id = (int)$_POST['post_id'];
$content = trim($_POST['content']);

// 내용 길이 검증
if (mb_strlen($content) > 500) {
    echo json_encode(['success' => false, 'message' => '댓글은 500자 이하로 작성해주세요.']);
    exit;
}

// 관리자 이름 고정
$admin_name = '관리자';

try {
    $conn = getConnection();
    
    // 게시글 존재 확인
    $check_query = "SELECT no FROM wintech_community WHERE no = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "i", $post_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) === 0) {
        echo json_encode(['success' => false, 'message' => '존재하지 않는 게시글입니다.']);
        exit;
    }
    
    // 댓글 작성
    $insert_query = "INSERT INTO wintech_comment (post_no, name, content, date) VALUES (?, ?, ?, NOW())";
    $insert_stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($insert_stmt, "iss", $post_id, $admin_name, $content);
    
    if (mysqli_stmt_execute($insert_stmt)) {
        echo json_encode(['success' => true, 'message' => '댓글이 성공적으로 작성되었습니다.']);
    } else {
        echo json_encode(['success' => false, 'message' => '댓글 작성 중 오류가 발생했습니다.']);
    }
    
    mysqli_stmt_close($insert_stmt);
    mysqli_stmt_close($check_stmt);
    mysqli_close($conn);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '데이터베이스 오류: ' . $e->getMessage()]);
}
?>
