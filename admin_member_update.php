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
$birthday = isset($_POST['birthday']) ? trim($_POST['birthday']) : '';
$gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
$height = isset($_POST['height']) ? trim($_POST['height']) : '';
$weight = isset($_POST['weight']) ? trim($_POST['weight']) : '';
$note = isset($_POST['note']) ? trim($_POST['note']) : '';

if (empty($account)) {
    echo json_encode(['success' => false, 'message' => '회원 아이디가 필요합니다.']);
    exit;
}

// 입력값 검증
$errors = [];

if (!empty($birthday) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthday)) {
    $errors[] = '생년월일 형식이 올바르지 않습니다.';
}

if (!empty($gender) && !in_array($gender, ['1', '2'])) {
    $errors[] = '성별은 남성(1) 또는 여성(2)만 선택할 수 있습니다.';
}

if (!empty($height) && (!is_numeric($height) || $height < 100 || $height > 250)) {
    $errors[] = '신장은 100-250cm 사이의 숫자여야 합니다.';
}

if (!empty($weight) && (!is_numeric($weight) || $weight < 20 || $weight > 200)) {
    $errors[] = '체중은 20-200kg 사이의 숫자여야 합니다.';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    require_once 'DAO/mysqli_con.php';
    $conn = getConnection();
    
    // 회원 존재 여부 확인
    $check_query = "SELECT account FROM wintech_account WHERE account = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "s", $account);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($result) === 0) {
        echo json_encode(['success' => false, 'message' => '해당 회원을 찾을 수 없습니다.']);
        exit;
    }
    
    // 회원 정보 업데이트
    $update_query = "UPDATE wintech_account SET birthday = ?, gender = ?, height = ?, weight = ?, note = ? WHERE account = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    
    // 빈 값은 NULL로 처리
    $birthday = empty($birthday) ? null : $birthday;
    $gender = empty($gender) ? null : $gender;
    $height = empty($height) ? null : $height;
    $weight = empty($weight) ? null : $weight;
    $note = empty($note) ? null : $note;
    
    mysqli_stmt_bind_param($update_stmt, "ssssss", $birthday, $gender, $height, $weight, $note, $account);
    
    if (mysqli_stmt_execute($update_stmt)) {
        echo json_encode(['success' => true, 'message' => '회원 정보가 성공적으로 수정되었습니다.']);
    } else {
        echo json_encode(['success' => false, 'message' => '회원 정보 수정 중 오류가 발생했습니다.']);
    }
    
    mysqli_stmt_close($update_stmt);
    mysqli_stmt_close($check_stmt);
    mysqli_close($conn);
    
} catch (Exception $e) {
    error_log("Admin member update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '서버 오류가 발생했습니다.']);
}
?>









