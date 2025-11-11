<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : '행복운동센터'; ?></title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="header-container">
            <div class="logo-section">
                <a href="index.php" class="logo-link">
                    <div class="logo-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h1 class="logo-text">행복운동센터</h1>
                </a>
            </div>
            <div class="header-buttons">
                
                <?php if (isset($_SESSION['email'])): ?>
                    <!-- 로그인된 상태 -->
                    <a href="logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        로그아웃
                    </a>
                    <a href="mypage.php" class="btn-mypage">
                        <i class="fas fa-user"></i>
                        마이페이지
                    </a>
                <?php else: ?>
                    <!-- 로그인되지 않은 상태 -->
                    <a href="login.php" class="btn-login">
                        <i class="fas fa-lock"></i>
                        로그인
                    </a>
                    <a href="signup.php" class="btn-signup">
                        <i class="fas fa-user"></i>
                        회원가입
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>
