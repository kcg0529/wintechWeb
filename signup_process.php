<?php
session_start();
require_once 'DAO/AccountDAO.php';

if ($_POST) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $terms = isset($_POST['terms']) ? true : false;
    
    // 기본 유효성 검사
    if (empty($email) || empty($password) || empty($password_confirm)) {
        $error = "모든 필수 항목을 입력해주세요.";
    } elseif ($password !== $password_confirm) {
        $error = "비밀번호가 일치하지 않습니다.";
    } elseif (strlen($password) < 6) {
        $error = "비밀번호는 6자 이상이어야 합니다.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "올바른 이메일 형식이 아닙니다.";
    } elseif (!$terms) {
        $error = "이용약관에 동의해주세요.";
    } else {
        try {
            $accountDAO = new AccountDAO();
            
            // 이메일 중복 재확인
            if ($accountDAO->checkEmailExists($email)) {
                $error = "이미 사용 중인 이메일입니다.";
            } else {
                // 계정 생성
                if ($accountDAO->createAccount($email, $password)) {
                    // 회원가입 성공 처리
                    $_SESSION['email'] = $email;
                    $success = true;
                } else {
                    $error = "회원가입 중 오류가 발생했습니다. 다시 시도해주세요.";
                }
            }
        } catch (Exception $e) {
            $error = "데이터베이스 오류가 발생했습니다. 다시 시도해주세요.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입 처리 - 행복운동센터</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="signup-process-container">
        <div class="signup-process-content">
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
                <a href="signup.php" class="btn-back">회원가입 페이지로 돌아가기</a>
            <?php elseif (isset($success)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    회원가입이 완료되었습니다!
                </div>
                <div class="welcome-message">
                    <h3>환영합니다!</h3>
                    <p>행복운동센터에 가입해주셔서 감사합니다.</p>
                </div>
                <a href="index.php" class="btn-continue">메인 페이지로 이동</a>
            <?php else: ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    잘못된 접근입니다.
                </div>
                <a href="signup.php" class="btn-back">회원가입 페이지로 이동</a>
            <?php endif; ?>
        </div>
    </div>

    <style>
    .signup-process-container {
        min-height: 100vh;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .signup-process-content {
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        padding: 50px;
        text-align: center;
        max-width: 500px;
        width: 100%;
    }

    .error-message,
    .success-message {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 30px;
        padding: 20px;
        border-radius: 10px;
    }

    .error-message {
        color: #e74c3c;
        background: #fdf2f2;
        border: 2px solid #fecaca;
    }

    .success-message {
        color: #27ae60;
        background: #f0f9f4;
        border: 2px solid #a7f3d0;
    }

    .welcome-message {
        margin-bottom: 30px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 10px;
    }

    .welcome-message h3 {
        color: #333;
        margin-bottom: 10px;
        font-size: 24px;
    }

    .welcome-message p {
        color: #666;
        font-size: 16px;
    }

    .btn-back,
    .btn-continue {
        display: inline-block;
        padding: 15px 30px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        font-size: 16px;
        transition: all 0.3s ease;
    }

    .btn-back {
        background: #f5f5f5;
        color: #666;
    }

    .btn-back:hover {
        background: #e0e0e0;
        transform: translateY(-2px);
    }

    .btn-continue {
        background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
        color: white;
    }

    .btn-continue:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
    }
    </style>
</body>
</html>
