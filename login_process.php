<?php
session_start();
require_once 'DAO/AccountDAO.php';

if ($_POST) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // 기본 유효성 검사
    if (empty($email) || empty($password)) {
        $error = "이메일과 비밀번호를 입력해주세요.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "올바른 이메일 형식이 아닙니다.";
    } elseif (strlen($password) < 6) {
        $error = "비밀번호는 6자 이상이어야 합니다.";
    } else {
        try {
            $accountDAO = new AccountDAO();
            
            // 이메일 존재 확인
            $emailExists = $accountDAO->checkEmailExists($email);
            if (!$emailExists) {
                $error = "등록되지 않은 이메일입니다.";
            } else {
            // 로그인 검증
            if ($accountDAO->validateLogin($email, $password)) {
                // 로그인 성공 처리
                $_SESSION['email'] = $email;
                
                // 생년월일 정보 확인
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
                $error = "비밀번호가 올바르지 않습니다.";
            }
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = "데이터베이스 오류가 발생했습니다: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그인 처리 - 행복운동센터</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-process-container">
        <div class="login-process-content">
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
                <a href="login.php" class="btn-back">로그인 페이지로 돌아가기</a>
            <?php else: ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    로그인이 완료되었습니다!
                </div>
                <a href="index.php" class="btn-continue">메인 페이지로 이동</a>
            <?php endif; ?>
        </div>
    </div>

    <style>
    .login-process-container {
        min-height: 100vh;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .login-process-content {
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        padding: 50px;
        text-align: center;
        max-width: 400px;
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
