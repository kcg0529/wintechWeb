<?php
$page_title = '로그인 - 행복운동센터';
include 'header.php';
?>


<main class="login-main">
    <div class="login-container">

        <!-- 로그인 폼 -->
        <div class="login-form-container">
            <h1 class="login-title">로그인</h1>
            <?php
            $socialErrorMessages = [
                'naver_not_configured' => '네이버 소셜 로그인 설정이 완료되지 않았습니다. 관리자에게 문의해주세요.',
                'naver_state_mismatch' => '네이버 로그인 요청이 유효하지 않습니다. 다시 시도해주세요.',
                'naver_api_failed' => '네이버 로그인 처리 중 오류가 발생했습니다. 잠시 후 다시 시도해주세요.',
                'naver_token_failed' => '네이버 액세스 토큰 발급에 실패했습니다. 다시 시도해주세요.',
                'naver_userinfo_failed' => '네이버 사용자 정보 조회에 실패했습니다. 다시 시도해주세요.',
                'naver_login_failed' => '네이버 로그인에 실패했습니다. 다시 시도해주세요.',
                'kakao_login_failed' => '카카오 로그인에 실패했습니다. 다시 시도해주세요.',
                'kakao_api_failed' => '카카오 사용자 정보를 가져오지 못했습니다. 다시 시도해주세요.',
                'invalid_user_info' => '소셜 로그인 사용자 정보를 확인할 수 없습니다. 다시 시도해주세요.',
                'signup_failed' => '소셜 로그인 회원 가입 중 오류가 발생했습니다. 다시 시도해주세요.'
            ];
            if (isset($_GET['error'])) {
                $errorKey = $_GET['error'];
                $socialMessage = $socialErrorMessages[$errorKey] ?? '소셜 로그인 처리 중 오류가 발생했습니다. 다시 시도해주세요.';
                echo '<div class="social-error-message">' . htmlspecialchars($socialMessage, ENT_QUOTES, 'UTF-8') . '</div>';
            }
            ?>
            <form class="login-form" action="login_process.php" method="POST">
                <div class="form-group">
                    <label for="email" class="form-label">이메일</label>
                    <input type="email" id="email" name="email" class="form-input" placeholder="이메일을 입력하세요" required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">비밀번호</label>
                    <input type="password" id="password" name="password" class="form-input" placeholder="비밀번호를 입력하세요" required>
                </div>
                
                
                <div class="form-options">
                    <label class="checkbox-container">
                        <input type="checkbox" name="remember_me">
                        <span class="checkmark"></span>
                        로그인 상태 유지
                    </label>
                </div>
                
                <div class="form-buttons">
                    <button type="submit" class="btn-login-submit">로그인</button>
                </div>
            </form>
            
            <!-- 소셜 로그인 섹션 -->
            <div class="social-login-section">
                <div class="divider">
                    <span class="divider-text">또는</span>
                </div>
                
                <div class="social-buttons">
                    <!-- 네이버 로그인 버튼 -->
                    <a href="naver_login_start.php" class="social-btn naver-btn" id="naver-login-button">
                        <img src="images/btnG_완성형.png" alt="네이버 로그인" class="social-btn-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div style="display: none; width: 100%; height: 100%; background: #03C75A; border-radius: 8px; align-items: center; justify-content: center; color: #fff; font-size: 14px; font-weight: 500; box-sizing: border-box; flex-direction: row; padding: 0 12px;">
                            네이버 로그인
                        </div>
                    </a>
                    
                    <!-- 카카오 로그인 버튼 -->
                    <div id="kakao-login-button" class="social-btn kakao-btn" style="cursor: pointer;">
                        <img src="images/kakao_login_medium_narrow.png" alt="카카오 로그인" class="social-btn-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div style="display: none; width: 100%; height: 100%; background: #FEE500; border-radius: 8px; align-items: center; justify-content: center; color: #000; font-size: 14px; font-weight: 500; box-sizing: border-box; flex-direction: row; padding: 0 12px;">
                            <svg width="18" height="18" viewBox="0 0 18 18" style="margin-right: 8px;">
                                <path fill="#000" d="M9 0C4.03 0 0 3.58 0 8c0 2.85 1.75 5.38 4.42 6.93L3.5 18l4.5-2.5c1.17.33 2.4.5 3.67.5 4.97 0 9-3.58 9-8s-4.03-8-9-8z"/>
                            </svg>
                            카카오 로그인
                        </div>
                    </div>
                    
                    <!-- 구글 로그인 버튼 -->
                    <div id="google-signin-button" class="social-btn google-btn" style="cursor: pointer;">
                        <div style="width: 100%; height: 100%; background: white; border: 1px solid #dadce0; border-radius: 8px; align-items: center; justify-content: center; color: #3C4043; font-size: 14px; font-weight: 500; box-sizing: border-box; display: flex; flex-direction: row; padding: 0 12px;">
                            <svg width="18" height="18" viewBox="0 0 18 18" style="margin-right: 8px;">
                                <path fill="#4285F4" d="M16.51 8H8.98v3h4.3c-.18 1-.74 1.48-1.6 2.04v2.01h2.6a7.8 7.8 0 002.38-5.88c0-.57-.05-.66-.15-1.18z"/>
                                <path fill="#34A853" d="M8.98 17c2.16 0 3.97-.72 5.3-1.94l-2.6-2.04a4.8 4.8 0 01-7.18-2.53H1.83v2.07A8 8 0 008.98 17z"/>
                                <path fill="#FBBC05" d="M4.5 8.49a4.8 4.8 0 010-3.02V3.4H1.83a8 8 0 000 7.17l2.67-2.08z"/>
                                <path fill="#EA4335" d="M8.98 4.5c1.16 0 2.19.4 3.01 1.2l2.26-2.26A7.77 7.77 0 008.98 1a8 8 0 00-7.15 4.4l2.67 2.07c.64-1.88 2.4-3.12 4.48-3.12z"/>
                            </svg>
                            Google로 로그인
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</main>

<link rel="stylesheet" href="css/login.css">

<!-- Kakao JavaScript SDK -->
<script src="https://developers.kakao.com/sdk/js/kakao.js"></script>

<!-- Google OAuth 2.0 API -->
<script src="https://accounts.google.com/gsi/client" async defer></script>

<script>
// Google 로그인 초기화
function initGoogleSignIn() {
    if (typeof google !== 'undefined' && google.accounts) {
        google.accounts.id.initialize({
            client_id: '345531153425-c4iplbf8c7f4n74jgnum6s4528urhc6p.apps.googleusercontent.com',
            callback: handleGoogleSignIn
        });
        
        // 구글 로그인 버튼 클릭 이벤트
        const googleBtn = document.getElementById('google-signin-button');
        if (googleBtn) {
            googleBtn.addEventListener('click', function() {
                // Google One Tap 로그인 프롬프트 표시
                google.accounts.id.prompt((notification) => {
                    if (notification.isNotDisplayed() || notification.isSkippedMoment()) {
                        // One Tap이 표시되지 않으면 팝업으로 로그인
                        google.accounts.oauth2.initTokenClient({
                            client_id: '345531153425-c4iplbf8c7f4n74jgnum6s4528urhc6p.apps.googleusercontent.com',
                            scope: 'email profile',
                            callback: function(response) {
                                // Access token을 받았지만, ID token이 필요하므로 다시 시도
                                // 대신 직접 로그인 URL로 리다이렉트
                                window.location.href = 'https://accounts.google.com/o/oauth2/v2/auth?client_id=345531153425-c4iplbf8c7f4n74jgnum6s4528urhc6p.apps.googleusercontent.com&redirect_uri=' + encodeURIComponent(window.location.origin + '/google_login_process.php') + '&response_type=code&scope=openid%20email%20profile';
                            }
                        }).requestAccessToken();
                    }
                });
            });
        }
    } else {
        // Google API가 아직 로드되지 않았으면 재시도
        setTimeout(initGoogleSignIn, 100);
    }
}

// Google 로그인 콜백 처리
function handleGoogleSignIn(response) {
    // ID 토큰을 서버로 전송
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'google_login_process.php';
    
    const tokenInput = document.createElement('input');
    tokenInput.type = 'hidden';
    tokenInput.name = 'credential';
    tokenInput.value = response.credential;
    form.appendChild(tokenInput);
    
    document.body.appendChild(form);
    form.submit();
}

// Kakao 로그인 초기화
function initKakaoSignIn() {
    if (typeof Kakao !== 'undefined') {
        Kakao.init('f3bf7a6f7bb2cd581efc856366b48596');
        
        // 카카오 로그인 버튼 클릭 이벤트
        const kakaoBtn = document.getElementById('kakao-login-button');
        if (kakaoBtn) {
            kakaoBtn.addEventListener('click', function() {
                // 카카오 로그인 실행 (이메일 권한 포함)
                Kakao.Auth.login({
                    scope: 'account_email',
                    success: function(authObj) {
                        // 액세스 토큰을 서버로 전송
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'kakao_login_process.php';
                        
                        const tokenInput = document.createElement('input');
                        tokenInput.type = 'hidden';
                        tokenInput.name = 'access_token';
                        tokenInput.value = authObj.access_token;
                        form.appendChild(tokenInput);
                        
                        document.body.appendChild(form);
                        form.submit();
                    },
                    fail: function(err) {
                        console.error('카카오 로그인 실패:', err);
                        alert('카카오 로그인에 실패했습니다. 다시 시도해주세요.');
                    }
                });
            });
        }
    } else {
        // Kakao SDK가 아직 로드되지 않았으면 재시도
        setTimeout(initKakaoSignIn, 100);
    }
}

// 페이지 로드 시 초기화
window.onload = function() {
    initKakaoSignIn();
    initGoogleSignIn();
};
</script>

<?php include 'footer.php'; ?>
