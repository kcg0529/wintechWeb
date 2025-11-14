<?php
session_start();
require_once 'admin_functions.php';
checkAdminSession();

require_once 'DAO/CycleDAO.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '잘못된 요청 방법입니다.']);
    exit;
}

if (!isset($_POST['save_time']) || empty($_POST['save_time']) || !isset($_POST['name']) || empty($_POST['name'])) {
    echo json_encode(['success' => false, 'message' => '필수 정보가 누락되었습니다.']);
    exit;
}

$save_time = $_POST['save_time'];
$name = $_POST['name'];

try {
    // 사이클 데이터 존재 확인
    $cycle = CycleDAO::getCycleByIdentifier($save_time, $name);
    if (!$cycle) {
        echo json_encode(['success' => false, 'message' => '존재하지 않는 사이클 데이터입니다.']);
        exit;
    }
    
    // 사이클 데이터 삭제
    if (CycleDAO::deleteCycle($save_time, $name)) {
        echo json_encode(['success' => true, 'message' => '사이클 데이터가 성공적으로 삭제되었습니다.']);
    } else {
        echo json_encode(['success' => false, 'message' => '사이클 데이터 삭제 중 오류가 발생했습니다.']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '데이터베이스 오류: ' . $e->getMessage()]);
}
?>
