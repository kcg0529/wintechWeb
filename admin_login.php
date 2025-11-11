<?php
session_start();

// 이미 로그인된 경우 메인 페이지로 리다이렉트
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_main.php');
    exit;
}

$error_message = '';
if (isset($_GET['error'])) {
    $error_message = '로그인에 실패했습니다. 아이디와 비밀번호를 확인해주세요.';
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>관리자 로그인 - 행복운동센터</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans KR', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .admin-login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 50px;
            width: 100%;
            max-width: 400px;
            margin: 20px;
        }

        .admin-login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .admin-login-title {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .admin-login-subtitle {
            color: #666;
            font-size: 14px;
        }

        .admin-login-form {
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .login-button {
            width: 100%;
            padding: 15px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        .back-to-main {
            text-align: center;
            margin-top: 20px;
        }

        .back-to-main a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        .back-to-main a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .admin-login-container {
                padding: 30px;
                margin: 10px;
            }
            
            .admin-login-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-login-header">
            <h1 class="admin-login-title">관리자 로그인</h1>
            <p class="admin-login-subtitle">관리자 전용 페이지입니다</p>
        </div>

        <?php if ($error_message): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form class="admin-login-form" action="admin_login_process.php" method="POST">
            <div class="form-group">
                <label for="admin_id" class="form-label">관리자 ID</label>
                <input type="text" id="admin_id" name="admin_id" class="form-input" placeholder="관리자 ID를 입력하세요" required>
            </div>
            
            <div class="form-group">
                <label for="admin_pw" class="form-label">비밀번호</label>
                <input type="password" id="admin_pw" name="admin_pw" class="form-input" placeholder="비밀번호를 입력하세요" required>
            </div>
            
            <button type="submit" class="login-button">
                <i class="fas fa-sign-in-alt"></i> 로그인
            </button>
        </form>

        <div class="back-to-main">
            <a href="index.php">
                <i class="fas fa-arrow-left"></i> 메인 페이지로 돌아가기
            </a>
        </div>
    </div>
</body>
</html>










