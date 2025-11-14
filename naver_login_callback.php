<?php
session_start();

// 네이버 REST API 설정 (필요하다면 다른 곳에서 미리 define 하세요)
if (!defined('NAVER_CLIENT_ID')) {
    define('NAVER_CLIENT_ID', 'xKitqQAmY3k_HXU9KxEs');
}

if (!defined('NAVER_CLIENT_SECRET')) {
    define('NAVER_CLIENT_SECRET', 'jTz0gZLOqO');
}

if (!defined('NAVER_REDIRECT_URI')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    if ($basePath === '') {
        $basePath = '/';
    }
    define('NAVER_REDIRECT_URI', rtrim($scheme . '://' . $host . $basePath, '/') . '/naver_login_callback.php');
}

require_once __DIR__ . '/DAO/mysqli_con.php';

// 필수 설정 확인
if (empty(NAVER_CLIENT_ID) || empty(NAVER_CLIENT_SECRET)) {
    header('Location: login.php?error=naver_not_configured');
    exit;
}

// 요청 파라미터 확인
$code = isset($_GET['code']) ? $_GET['code'] : '';
$state = isset($_GET['state']) ? $_GET['state'] : '';

if (empty($code) || empty($state)) {
    header('Location: login.php?error=naver_login_failed');
    exit;
}

// state 검증
if (!isset($_SESSION['naver_oauth_state']) || $state !== $_SESSION['naver_oauth_state']) {
    unset($_SESSION['naver_oauth_state']);
    header('Location: login.php?error=naver_state_mismatch');
    exit;
}

unset($_SESSION['naver_oauth_state']);

// 리다이렉트 URI 계산
if (!empty(NAVER_REDIRECT_URI)) {
    $redirectUri = NAVER_REDIRECT_URI;
} else {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $redirectUri = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/naver_login_callback.php';
}

// 액세스 토큰 요청
$tokenUrl = 'https://nid.naver.com/oauth2.0/token';
$tokenParams = [
    'grant_type' => 'authorization_code',
    'client_id' => NAVER_CLIENT_ID,
    'client_secret' => NAVER_CLIENT_SECRET,
    'code' => $code,
    'state' => $state,
    'redirect_uri' => $redirectUri
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $tokenUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenParams));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$tokenResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$tokenResponse) {
    header('Location: login.php?error=naver_token_failed');
    exit;
}

$tokenData = json_decode($tokenResponse, true);

if (!isset($tokenData['access_token'])) {
    header('Location: login.php?error=naver_token_failed');
    exit;
}

$accessToken = $tokenData['access_token'];

// 사용자 정보 요청
$userInfoUrl = 'https://openapi.naver.com/v1/nid/me';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $userInfoUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken
]);

$userInfoResponse = curl_exec($ch);
$userInfoHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($userInfoHttpCode !== 200 || !$userInfoResponse) {
    header('Location: login.php?error=naver_userinfo_failed');
    exit;
}

$userInfo = json_decode($userInfoResponse, true);

if (!isset($userInfo['response']['id'])) {
    header('Location: login.php?error=naver_userinfo_failed');
    exit;
}

$naverId = $userInfo['response']['id'];
$email = isset($userInfo['response']['email']) ? $userInfo['response']['email'] : '';

if (empty($email)) {
    $email = $naverId . '@naver.com';
}

$conn = getConnection();

// 기존 회원 확인
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
    $_SESSION['login_type'] = 'naver';

    mysqli_close($conn);

    require_once __DIR__ . '/DAO/AccountDAO.php';
    $accountDAO = new AccountDAO();
    $birthday = $accountDAO->getUserBirthday($email);

    if ($birthday === null) {
        header('Location: edit_profile.php?required=true');
        exit;
    }

    header('Location: index.php');
    exit;
}

// 신규 회원 가입
$account = $email;
$insertQuery = "INSERT INTO wintech_account (account, password) VALUES (?, '')";
$insertStmt = mysqli_prepare($conn, $insertQuery);
mysqli_stmt_bind_param($insertStmt, "s", $account);

if (mysqli_stmt_execute($insertStmt)) {
    $_SESSION['email'] = $email;
    $_SESSION['account'] = $account;
    $_SESSION['login_type'] = 'naver';

    mysqli_stmt_close($insertStmt);
    mysqli_close($conn);

    header('Location: edit_profile.php?required=true');
    exit;
}

mysqli_stmt_close($insertStmt);
mysqli_close($conn);

header('Location: login.php?error=signup_failed');
exit;
