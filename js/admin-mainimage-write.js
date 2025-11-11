document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('mainimageForm');
    const tagSelect = document.getElementById('tag');
    const titleGroup = document.querySelector('[for="title"]').closest('.form-group');
    const titleInput = document.getElementById('title');
    const textInput = document.getElementById('text');
    
    // 태그 변경시 제목 필드 토글
    function toggleTitleField() {
        const value = tagSelect.value;
        const textRequired = document.getElementById('textRequired');
        const textLabel = document.getElementById('textLabel');
        const textHelp = document.getElementById('textHelp');
        
        if (value === 'slider') {
            // 슬라이더 선택시 제목 필드 숨김
            titleGroup.style.display = 'none';
            titleInput.required = false;
            titleInput.value = 'slider'; // 기본값 설정
            
            if (textLabel) {
                textLabel.textContent = '설명';
            }
            if (textInput) {
                textInput.placeholder = '슬라이더에 표시될 오버레이 텍스트를 입력하세요';
                textInput.required = true;
            }
            if (textRequired) {
                textRequired.style.display = 'inline';
            }
            if (textHelp) {
                textHelp.textContent = 'slider 태그: 슬라이더 오버레이 텍스트로 사용됩니다.';
            }
        } else if (value === 'shop') {
            // 쇼핑 선택시 제목 필드 표시
            titleGroup.style.display = 'block';
            titleInput.required = true;
            titleInput.value = '';
            titleInput.placeholder = '제품명을 입력하세요';
            
            if (textLabel) {
                textLabel.textContent = '가격';
            }
            if (textInput) {
                textInput.placeholder = '가격을 입력하세요 (예: 990,000원)';
                textInput.required = false;
            }
            if (textRequired) {
                textRequired.style.display = 'none';
            }
            if (textHelp) {
                textHelp.textContent = 'shop 태그: 상품 가격으로 사용됩니다.';
            }
        } else {
            // 선택안함
            titleGroup.style.display = 'block';
            titleInput.required = true;
            titleInput.value = '';
            if (textLabel) {
                textLabel.textContent = '설명';
            }
            if (textInput) {
                textInput.placeholder = '이미지 설명을 입력하세요';
            }
            if (textRequired) {
                textRequired.style.display = 'none';
            }
            if (textHelp) {
                textHelp.textContent = '';
            }
        }
    }
    
    if (tagSelect) {
        tagSelect.addEventListener('change', toggleTitleField);
        // 페이지 로드시에도 실행
        toggleTitleField();
    }
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // 유효성 검사
            const tag = document.getElementById('tag').value.trim();
            const title = document.getElementById('title').value.trim();
            const text = document.getElementById('text').value.trim();
            const img = document.getElementById('img').value.trim();
            
            const errors = [];
            
            if (!tag) {
                errors.push('태그를 선택해주세요.');
            }
            
            // 슬라이더가 아닐 때만 제목 체크
            if (tag !== 'slider' && !title) {
                errors.push('제목을 입력해주세요.');
            }
            
            // 슬라이더일 때는 설명 필수
            if (tag === 'slider' && !text) {
                errors.push('슬라이더 오버레이 텍스트를 입력해주세요.');
            }
            
            if (!img) {
                errors.push('이미지 파일명을 입력해주세요.');
            }
            
            if (errors.length > 0) {
                e.preventDefault();
                alert(errors.join('\n'));
                return false;
            }
        });
    }
});

