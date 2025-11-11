<?php
session_start();
require_once 'DAO/MainImgDAO.php';

// 관리자 권한 확인
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit();
}

// POST 요청 확인
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
    exit();
}

// JSON 입력 받기
$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? (int)$input['id'] : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => '잘못된 이미지 ID입니다.']);
    exit();
}

try {
    $result = MainImgDAO::deleteImage($id);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => '메인이미지가 삭제되었습니다.']);
    } else {
        echo json_encode(['success' => false, 'message' => '메인이미지 삭제에 실패했습니다.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '오류가 발생했습니다: ' . $e->getMessage()]);
}
?>

