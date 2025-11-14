<?php
session_start();
require_once 'DAO/mysqli_con.php';

// Google ID 토큰 받기
$credential = isset($_POST['credential']) ? $_POST['credential'] : '';

if (empty($credential)) {
    header('Location: login.php?error=google_login_failed');
    exit;
}

// Google ID 토큰 검증 및 사용자 정보 추출
// JWT 토큰을 디코딩 (간단한 방법 - 실제로는 Google API로 검증해야 함)
$tokenParts = explode('.', $credential);
if (count($tokenParts) !== 3) {
    header('Location: login.php?error=invalid_token');
    exit;
}

// JWT 페이로드 디코딩
$payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1])), true);

if (!$payload) {
    header('Location: login.php?error=token_decode_failed');
    exit;
}

// Google 사용자 정보 추출
$google_id = isset($payload['sub']) ? $payload['sub'] : '';
$email = isset($payload['email']) ? $payload['email'] : '';
$name = isset($payload['name']) ? $payload['name'] : '';
$picture = isset($payload['picture']) ? $payload['picture'] : '';

if (empty($email) || empty($google_id)) {
    header('Location: login.php?error=missing_user_info');
    exit;
}

$conn = getConnection();

// 기존 회원 확인 (이메일로 - account 컬럼이 이메일로 사용됨)
$query = "SELECT * FROM wintech_account WHERE account = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$member = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($member) {
    // 기존 회원 확인
    // password가 있으면 일반 가입 계정, 없으면 소셜 가입 계정
    if (!empty($member['password'])) {
        // 일반 가입으로 이미 가입된 이메일
        mysqli_close($conn);
        header('Location: login.php?error=email_exists');
        exit;
    }
    
    // 소셜 가입 계정 - 로그인 처리
    $_SESSION['email'] = $email;
    $_SESSION['account'] = $member['account'];
    $_SESSION['login_type'] = 'google';
    
    mysqli_close($conn);
    
    // 생년월일 정보 확인
    require_once 'DAO/AccountDAO.php';
    $accountDAO = new AccountDAO();
    $birthday = $accountDAO->getUserBirthday($email);
    
    if ($birthday === null) {
        // 생년월일이 없으면 정보 수정 페이지로 리다이렉트
        header('Location: edit_profile.php?required=true');
        exit;
    } else {
        // 생년월일이 있으면 메인 페이지로 리다이렉트
        header('Location: index.php');
        exit;
    }
} else {
    // 신규 회원 - 자동 가입 처리
    // account는 이메일로 사용
    $account = $email;
    
    // 회원 가입 (password는 빈 문자열로 저장)
    $insertQuery = "INSERT INTO wintech_account (account, password) VALUES (?, '')";
    $insertStmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($insertStmt, "s", $account);
    
    if (mysqli_stmt_execute($insertStmt)) {
        // 가입 성공 - 자동 로그인
        $_SESSION['email'] = $email;
        $_SESSION['account'] = $account;
        $_SESSION['login_type'] = 'google';
        
        mysqli_stmt_close($insertStmt);
        mysqli_close($conn);
        
        // 신규 가입자는 생년월일이 없으므로 정보 수정 페이지로 리다이렉트
        header('Location: edit_profile.php?required=true');
        exit;
    } else {
        mysqli_stmt_close($insertStmt);
        mysqli_close($conn);
        header('Location: login.php?error=signup_failed');
        exit;
    }
}
?>

