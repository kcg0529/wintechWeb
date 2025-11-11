// 공지사항 작성 페이지 JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('title');
    const contentTextarea = document.getElementById('content');
    const titleCount = document.getElementById('titleCount');
    const contentCount = document.getElementById('contentCount');
    
    // 취소 버튼
    const cancelBtn = document.getElementById('cancelBtn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            history.back();
        });
    }
    
    // 초기화 버튼
    const resetBtn = document.getElementById('resetBtn');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            resetForm();
        });
    }
    
    // 제목 글자수 카운트
    if (titleInput && titleCount) {
        titleInput.addEventListener('input', function() {
            const length = this.value.length;
            titleCount.textContent = length;
            
            if (length > 180) {
                titleCount.style.color = '#e74c3c';
            } else if (length > 150) {
                titleCount.style.color = '#f39c12';
            } else {
                titleCount.style.color = '#666';
            }
        });
    }
    
    // 내용 글자수 카운트
    if (contentTextarea && contentCount) {
        contentTextarea.addEventListener('input', function() {
            const length = this.value.length;
            contentCount.textContent = length;
            
            if (length > 4500) {
                contentCount.style.color = '#e74c3c';
            } else if (length > 4000) {
                contentCount.style.color = '#f39c12';
            } else {
                contentCount.style.color = '#666';
            }
        });
    }
    
    // 폼 제출 전 유효성 검사
    const noticeForm = document.getElementById('noticeForm');
    if (noticeForm) {
        noticeForm.addEventListener('submit', function(e) {
            e.preventDefault(); // 기본 제출 동작 중지
            
            const selectedTag = document.querySelector('input[name="tag"]:checked');
            let title = titleInput.value.trim();
            const content = contentTextarea.value.trim();
            
            if (!selectedTag) {
                alert('태그를 선택해주세요.');
                return false;
            }
            
            // 태그가 이미 포함되어 있는지 확인하고 제거
            // [태그] 형식으로 시작하면 제거
            if (title.match(/^\[[^\]]+\]\s*/)) {
                title = title.replace(/^\[[^\]]+\]\s*/, '');
            }
            
            if (!title) {
                alert('제목을 입력해주세요.');
                titleInput.focus();
                return false;
            }
            
            if (!content) {
                alert('내용을 입력해주세요.');
                contentTextarea.focus();
                return false;
            }
            
            // 태그를 포함한 전체 제목 길이 체크
            const tagValue = selectedTag.value;
            const combinedTitle = tagValue + ' ' + title;
            
            if (combinedTitle.length > 200) {
                alert('제목(태그 포함)은 200자 이하로 입력해주세요.');
                titleInput.focus();
                return false;
            }
            
            if (content.length > 5000) {
                alert('내용은 5000자 이하로 입력해주세요.');
                contentTextarea.focus();
                return false;
            }
            
            // 확인 대화상자
            if (!confirm('공지사항을 작성하시겠습니까?')) {
                return false;
            }
            
            // 태그와 제목을 합쳐서 title 필드에 저장
            titleInput.value = combinedTitle;
            
            // 폼 제출
            this.submit();
        });
    }
});

function resetForm() {
    if (confirm('입력한 내용을 모두 지우시겠습니까?')) {
        document.getElementById('noticeForm').reset();
        document.getElementById('titleCount').textContent = '0';
        document.getElementById('contentCount').textContent = '0';
        document.getElementById('titleCount').style.color = '#666';
        document.getElementById('contentCount').style.color = '#666';
    }
}

