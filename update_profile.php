<?php
session_start();

// 로그인 확인
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

require_once 'DAO/AccountDAO.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $accountDAO = new AccountDAO();
        $email = $_SESSION['email'];
        
        // 받은 데이터
        $gender = $_POST['gender'] ?? '';
        $birthday = $_POST['birthday'] ?? '';
        $height = $_POST['height'] ?? '';
        $weight = $_POST['weight'] ?? '';
        $note = $_POST['note'] ?? '';
        
        // 데이터 검증
        if (empty($gender) || empty($birthday) || empty($height) || empty($weight) || empty($note)) {
            throw new Exception('모든 필드를 입력해주세요.');
        }
        
        // gender 값 검증 (1 또는 2)
        if (!in_array($gender, ['1', '2'])) {
            throw new Exception('올바른 성별을 선택해주세요.');
        }
        
        // 생년월일 형식 검증
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthday)) {
            throw new Exception('올바른 생년월일 형식이 아닙니다.');
        }
        
        // 신장/체중 범위 검증 (VARCHAR 타입이므로 문자열로 처리)
        if (!is_numeric($height) || !is_numeric($weight)) {
            throw new Exception('신장과 체중은 숫자여야 합니다.');
        }
        
        $heightNum = (float)$height;
        $weightNum = (float)$weight;
        
        if ($heightNum < 100 || $heightNum > 250) {
            throw new Exception('신장은 100cm 이상 250cm 이하여야 합니다.');
        }
        
        if ($weightNum < 30 || $weightNum > 200) {
            throw new Exception('체중은 30kg 이상 200kg 이하여야 합니다.');
        }
        
        // note 데이터 검증 (건강 설문지 결과)
        if (strlen($note) > 1000) {
            throw new Exception('건강 설문지 데이터가 너무 깁니다.');
        }
        
        // 데이터베이스 업데이트
        require_once 'DAO/mysqli_con.php';
        $conn = getConnection();
        
        $stmt = mysqli_prepare($conn, 
            "UPDATE wintech_account SET gender = ?, birthday = ?, height = ?, weight = ?, note = ? WHERE account = ?");
        
        if (!$stmt) {
            throw new Exception('데이터베이스 준비 실패: ' . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "isssss", $gender, $birthday, $height, $weight, $note, $email);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = true;
            $message = '정보가 성공적으로 업데이트되었습니다.';
            
            // 메인 페이지로 리다이렉트
            header('Location: index.php?success=1');
            exit;
        } else {
            throw new Exception('데이터베이스 업데이트 실패: ' . mysqli_stmt_error($stmt));
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        
    } catch (Exception $e) {
        $message = '오류가 발생했습니다: ' . $e->getMessage();
        error_log("Profile update error: " . $e->getMessage());
    }
} else {
    header('Location: edit_profile.php');
    exit;
}
?>
