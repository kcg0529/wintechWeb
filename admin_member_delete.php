<?php
session_start();
require_once 'admin_functions.php';

// 관리자 세션 체크
checkAdminSession();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '잘못된 요청 방법입니다.']);
    exit;
}

$account = isset($_POST['account']) ? trim($_POST['account']) : '';

if (empty($account)) {
    echo json_encode(['success' => false, 'message' => '회원 아이디가 필요합니다.']);
    exit;
}

try {
    require_once 'DAO/MemberDAO.php';
    
    // 회원 존재 여부 확인
    $member = MemberDAO::getMemberByAccount($account);
    if (!$member) {
        echo json_encode(['success' => false, 'message' => '해당 회원을 찾을 수 없습니다.']);
        exit;
    }
    
    // 회원 삭제
    if (MemberDAO::deleteMember($account)) {
        echo json_encode(['success' => true, 'message' => '회원이 성공적으로 삭제되었습니다.']);
    } else {
        echo json_encode(['success' => false, 'message' => '회원 삭제 중 오류가 발생했습니다.']);
    }
    
} catch (Exception $e) {
    error_log("Admin member delete error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '서버 오류가 발생했습니다.']);
}
?>

