document.addEventListener('DOMContentLoaded', function() {
    const termsCheckbox = document.querySelector('input[name="terms"]');
    const signupBtn = document.getElementById('signupBtn');
    const emailInput = document.getElementById('email');
    const emailMessage = document.getElementById('email-message');
    
    let emailValid = false;
    let checkingTimeout = null;
    
    function updateButtonState() {
        if (termsCheckbox.checked && emailValid) {
            signupBtn.disabled = false;
            signupBtn.style.background = 'linear-gradient(135deg, #4CAF50 0%, #45a049 100%)';
        } else {
            signupBtn.disabled = true;
            signupBtn.style.background = '#ccc';
        }
    }
    
    function checkEmailAvailability(email) {
        if (!email || !email.includes('@')) {
            emailMessage.textContent = '';
            emailMessage.className = 'email-message';
            emailInput.className = 'form-input';
            emailValid = false;
            updateButtonState();
            return;
        }
        
        emailMessage.textContent = '이메일 확인 중...';
        emailMessage.className = 'email-message checking';
        emailInput.className = 'form-input';
        
        fetch('check_email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'email=' + encodeURIComponent(email)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                emailMessage.textContent = data.message;
                emailMessage.className = 'email-message success';
                emailInput.className = 'form-input success';
                emailValid = true;
            } else {
                emailMessage.textContent = data.message;
                emailMessage.className = 'email-message error';
                emailInput.className = 'form-input error';
                emailValid = false;
            }
            updateButtonState();
        })
        .catch(error => {
            emailMessage.textContent = '오류가 발생했습니다. 다시 시도해주세요.';
            emailMessage.className = 'email-message error';
            emailInput.className = 'form-input error';
            emailValid = false;
            updateButtonState();
        });
    }
    
    // 이메일 입력 시 중복체크 (디바운스 적용)
    emailInput.addEventListener('input', function() {
        clearTimeout(checkingTimeout);
        checkingTimeout = setTimeout(() => {
            checkEmailAvailability(this.value);
        }, 500); // 0.5초 후에 체크
    });
    
    // 초기 상태 설정
    updateButtonState();
    
    // 체크박스 변경 시 버튼 상태 업데이트
    termsCheckbox.addEventListener('change', updateButtonState);
});









