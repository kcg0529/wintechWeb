<?php
$page_title = '회원가입 - 행복운동센터';
include 'header.php';
?>

<link rel="stylesheet" href="css/signup.css">

<main class="signup-main">
    <div class="signup-container">

        <!-- 회원가입 폼 -->
        <div class="signup-form-container">
            <h1 class="signup-title">회원가입</h1>
            
            <form class="signup-form" action="signup_process.php" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="email" class="form-label">이메일</label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="이메일을 입력하세요" required>
                        <div id="email-message" class="email-message"></div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password" class="form-label">비밀번호</label>
                        <input type="password" id="password" name="password" class="form-input" placeholder="비밀번호를 입력하세요" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password_confirm" class="form-label">비밀번호 확인</label>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-input" placeholder="비밀번호를 다시 입력하세요" required>
                    </div>
                </div>
                
                
                
                <div class="form-options">
                    <label class="checkbox-container">
                        <input type="checkbox" name="terms" required>
                        <span class="checkmark"></span>
                        <a href="terms.php" target="_blank">이용약관</a> 및 <a href="privacy.php" target="_blank">개인정보처리방침</a>에 동의합니다.
                    </label>
                </div>
                
                
                <div class="form-buttons">
                    <button type="submit" class="btn-signup-submit" id="signupBtn" disabled>회원가입</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
<script src="js/signup.js"></script>
