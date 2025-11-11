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

// 네이버 클라이언트 ID 확인
if (empty(NAVER_CLIENT_ID)) {
    header('Location: login.php?error=naver_not_configured');
    exit;
}

// 리다이렉트 URI 결정 (설정 값이 없으면 현재 도메인을 기준으로 계산)
if (!empty(NAVER_REDIRECT_URI)) {
    $redirectUri = NAVER_REDIRECT_URI;
} else {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $redirectUri = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/naver_login_callback.php';
}

// CSRF 방지를 위한 state 생성
$state = bin2hex(random_bytes(16));
$_SESSION['naver_oauth_state'] = $state;

$authorizeUrl = 'https://nid.naver.com/oauth2.0/authorize';
$queryParams = http_build_query([
    'response_type' => 'code',
    'client_id' => NAVER_CLIENT_ID,
    'redirect_uri' => $redirectUri,
    'state' => $state,
    'scope' => 'email'
]);

header('Location: ' . $authorizeUrl . '?' . $queryParams);
exit;
