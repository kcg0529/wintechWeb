<?php
session_start();
require_once 'admin_functions.php';
checkAdminSession();

require_once 'DAO/NoticeDAO.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '잘못된 요청 방법입니다.']);
    exit;
}

if (!isset($_POST['notice_id']) || empty($_POST['notice_id'])) {
    echo json_encode(['success' => false, 'message' => '공지사항 ID가 필요합니다.']);
    exit;
}

$notice_id = (int)$_POST['notice_id'];

try {
    // 공지사항 존재 확인
    $notice = NoticeDAO::getNoticeById($notice_id);
    
    if (!$notice) {
        echo json_encode(['success' => false, 'message' => '존재하지 않는 공지사항입니다.']);
        exit;
    }
    
    // 공지사항 삭제
    if (NoticeDAO::deleteNotice($notice_id)) {
        echo json_encode(['success' => true, 'message' => '공지사항이 성공적으로 삭제되었습니다.']);
    } else {
        echo json_encode(['success' => false, 'message' => '공지사항 삭제 중 오류가 발생했습니다.']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '데이터베이스 오류: ' . $e->getMessage()]);
}
?>


