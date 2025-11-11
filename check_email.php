<?php
// 오류 보고 활성화 (개발 중에만 사용)
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/DAO/AccountDAO.php';
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '파일 로드 오류: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => '이메일을 입력해주세요.']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => '올바른 이메일 형식이 아닙니다.']);
        exit;
    }
    
    try {
        $accountDAO = new AccountDAO();
        $emailExists = $accountDAO->checkEmailExists($email);
        
        if ($emailExists) {
            echo json_encode(['success' => false, 'message' => '이미 사용 중인 이메일입니다.']);
        } else {
            echo json_encode(['success' => true, 'message' => '사용 가능한 이메일입니다.']);
        }
    } catch (Exception $e) {
        error_log("Email check error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => '데이터베이스 오류: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
}
?>
