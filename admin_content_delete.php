<?php
session_start();
require_once 'admin_functions.php';
checkAdminSession();

require_once 'DAO/ContentDAO.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    
    if ($id > 0) {
        $result = ContentDAO::deleteContent($id);
        
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => '삭제 실패']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => '유효하지 않은 ID']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '잘못된 요청']);
}
?>









