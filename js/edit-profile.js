let currentStep = 1;
let formData = {};

// 단계 이동 함수
function goToStep(step) {
    // 현재 단계 숨기기
    document.getElementById('step' + currentStep).classList.remove('active');
    document.querySelector('.step:nth-child(' + currentStep + ')').classList.remove('active');
    
    // 새 단계 보이기
    currentStep = step;
    document.getElementById('step' + currentStep).classList.add('active');
    document.querySelector('.step:nth-child(' + currentStep + ')').classList.add('active');
    
    // 진행률 업데이트
    updateProgress();
}

// 진행률 업데이트
function updateProgress() {
    const steps = document.querySelectorAll('.step');
    steps.forEach((step, index) => {
        if (index + 1 <= currentStep) {
            step.classList.add('active');
        } else {
            step.classList.remove('active');
        }
    });
}

// 모든 데이터 제출
function submitAllData() {
    // 1단계 데이터 수집
    const gender = document.querySelector('input[name="gender"]:checked');
    const year = document.getElementById('year').value;
    const month = document.getElementById('month').value;
    const day = document.getElementById('day').value;
    
    // 1단계 추가 데이터 수집
    const height = document.getElementById('height').value;
    const weight = document.getElementById('weight').value;
    
    // 2단계 데이터 수집 (건강 설문지) - 선택사항
    const healthConditions = document.getElementById('selected-health-conditions').value;
    
    if (!gender || !year || !month || !day || !height || !weight) {
        alert('기본 정보를 모두 입력해주세요.');
        return;
    }
    
    // 생년월일 생성
    const birthday = year + '-' + month.padStart(2, '0') + '-' + day.padStart(2, '0');
    
    // 폼 생성 및 제출
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'update_profile.php';
    
    const fields = {
        'gender': gender.value,
        'birthday': birthday,
        'height': height,
        'weight': weight,
        'note': healthConditions
    };
    
    Object.keys(fields).forEach(key => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = fields[key];
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
}

document.addEventListener('DOMContentLoaded', function() {
    // 단계 이동 버튼 이벤트
    const stepButtons = document.querySelectorAll('.btn-prev[data-step], .btn-next[data-step]');
    stepButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const step = parseInt(this.getAttribute('data-step'));
            goToStep(step);
        });
    });
    
    // 완료 버튼 이벤트
    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            submitAllData();
        });
    }
    
    const yearSelect = document.getElementById('year');
    const monthSelect = document.getElementById('month');
    const daySelect = document.getElementById('day');
    const heightSelect = document.getElementById('height');
    const weightSelect = document.getElementById('weight');
    
    // 1단계 폼 검증
    function validateStep1() {
        const gender = document.querySelector('input[name="gender"]:checked');
        const year = yearSelect.value;
        const month = monthSelect.value;
        const day = daySelect.value;
        const height = heightSelect.value;
        const weight = weightSelect.value;
        
        const isValid = gender && year && month && day && height && weight;
        const nextBtn = document.getElementById('step1-next');
        
        if (nextBtn) {
            nextBtn.disabled = !isValid;
        }
    }
    
    // 2단계 폼 검증 (건강 설문지) - 선택하지 않아도 넘어갈 수 있음
    function validateStep2() {
        const selectedConditions = document.querySelectorAll('.health-option.selected');
        const isValid = true; // 항상 true로 설정하여 선택하지 않아도 넘어갈 수 있게 함
        const nextBtn = document.getElementById('step2-next');
        
        if (nextBtn) {
            nextBtn.disabled = !isValid;
        }
    }
    
    // 성별 선택 이벤트
    const genderInputs = document.querySelectorAll('input[name="gender"]');
    genderInputs.forEach(input => {
        input.addEventListener('change', validateStep1);
    });
    
    // 생년월일 선택 이벤트
    if (yearSelect) {
        yearSelect.addEventListener('change', function() {
            updateDays();
            validateStep1();
        });
    }
    
    if (monthSelect) {
        monthSelect.addEventListener('change', function() {
            updateDays();
            validateStep1();
        });
    }
    
    if (daySelect) {
        daySelect.addEventListener('change', validateStep1);
    }
    
    // 신장/체중 선택 이벤트
    if (heightSelect) {
        heightSelect.addEventListener('change', validateStep1);
    }
    
    if (weightSelect) {
        weightSelect.addEventListener('change', validateStep1);
    }
    
    // 건강 설문지 옵션 클릭 이벤트
    const healthOptions = document.querySelectorAll('.health-option');
    healthOptions.forEach(option => {
        option.addEventListener('click', function() {
            // 토글 선택/해제
            this.classList.toggle('selected');
            
            // 선택된 값들을 숨겨진 필드에 저장
            updateHealthConditions();
            
            // 2단계 검증
            validateStep2();
        });
    });
    
    function updateHealthConditions() {
        const selectedOptions = document.querySelectorAll('.health-option.selected');
        const selectedValues = Array.from(selectedOptions).map(option => option.dataset.value);
        const hiddenField = document.getElementById('selected-health-conditions');
        
        if (hiddenField) {
            // 아무것도 선택하지 않았으면 기본값으로 "이상없음" 설정
            if (selectedValues.length === 0) {
                hiddenField.value = '이상없음';
            } else {
                hiddenField.value = selectedValues.join(',');
            }
        }
    }
    
    // 초기 검증 및 기본값 설정
    validateStep1();
    validateStep2();
    updateHealthConditions(); // 기본값 "이상없음" 설정
    
    function updateDays() {
        const year = parseInt(yearSelect.value);
        const month = parseInt(monthSelect.value);
        const currentDay = parseInt(daySelect.value);
        
        if (year && month) {
            // 해당 월의 마지막 날 계산
            const lastDay = new Date(year, month, 0).getDate();
            
            // 현재 선택된 일이 마지막 날보다 크면 조정
            if (currentDay > lastDay) {
                daySelect.value = '';
            }
            
            // 일 옵션 업데이트
            const dayOptions = daySelect.querySelectorAll('option');
            dayOptions.forEach((option, index) => {
                if (index === 0) return; // 첫 번째는 "일" 옵션
                const dayValue = parseInt(option.value);
                if (dayValue > lastDay) {
                    option.style.display = 'none';
                } else {
                    option.style.display = 'block';
                }
            });
        }
    }
});

