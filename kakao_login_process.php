<?php
session_start();
require_once 'DAO/mysqli_con.php';

// 카카오 액세스 토큰 받기
$access_token = isset($_POST['access_token']) ? $_POST['access_token'] : '';

if (empty($access_token)) {
    header('Location: login.php?error=kakao_login_failed');
    exit;
}

// 카카오 사용자 정보 가져오기
$userInfoUrl = 'https://kapi.kakao.com/v2/user/me';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $userInfoUrl);
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $access_token
));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    header('Location: login.php?error=kakao_api_failed');
    exit;
}

$userInfo = json_decode($response, true);

if (!$userInfo || !isset($userInfo['id'])) {
    header('Location: login.php?error=invalid_user_info');
    exit;
}

// 카카오 사용자 정보 추출 (이메일만)
$kakao_id = $userInfo['id'];
$email = isset($userInfo['kakao_account']['email']) ? $userInfo['kakao_account']['email'] : '';

// 이메일이 없으면 카카오 ID로 이메일 생성
if (empty($email)) {
    $email = 'kakao_' . $kakao_id . '@kakao.com';
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
    $_SESSION['login_type'] = 'kakao';
    
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
        $_SESSION['login_type'] = 'kakao';
        
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

