// 글쓰기 모달
function openWriteModal() {
    document.getElementById('writeModal').style.display = 'block';
    // 모달이 열릴 때 이벤트 리스너 다시 추가
    setTimeout(function() {
        attachCharCountListeners();
        updateCharCount();
    }, 100);
}

function closeWriteModal() {
    document.getElementById('writeModal').style.display = 'none';
    document.getElementById('writeForm').reset();
    updateCharCount();
}

// 글자 수 카운트 업데이트
function updateCharCount() {
    const titleInput = document.getElementById('postTitle');
    const contentTextarea = document.getElementById('postContent');
    const titleCount = document.getElementById('titleCount');
    const contentCount = document.getElementById('contentCount');
    
    if (titleInput && titleCount) {
        titleCount.textContent = `${titleInput.value.length}/100`;
    }
    
    if (contentTextarea && contentCount) {
        contentCount.textContent = `${contentTextarea.value.length}/1000`;
    }
}

// 입력 시 실시간 글자 수 업데이트
function attachCharCountListeners() {
    const titleInput = document.getElementById('postTitle');
    const contentTextarea = document.getElementById('postContent');
    
    if (titleInput) {
        titleInput.removeEventListener('input', updateCharCount);
        titleInput.addEventListener('input', updateCharCount);
    }
    
    if (contentTextarea) {
        contentTextarea.removeEventListener('input', updateCharCount);
        contentTextarea.addEventListener('input', updateCharCount);
    }
}

// 글쓰기 폼 제출
function initWriteForm() {
    const writeForm = document.getElementById('writeForm');
    if (!writeForm) return;
    
    writeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const title = formData.get('title');
        const content = formData.get('content');
        
        if (!title.trim() || !content.trim()) {
            alert('제목과 내용을 모두 입력해주세요.');
            return;
        }
        
        // AJAX로 글쓰기 처리
        fetch('community_write.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('게시글이 작성되었습니다.');
                closeWriteModal();
                location.reload();
            } else {
                alert('게시글 작성에 실패했습니다: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('오류가 발생했습니다.');
        });
    });
}

// 모달 외부 클릭 시 닫기
window.onclick = function(event) {
    const modal = document.getElementById('writeModal');
    if (event.target == modal) {
        closeWriteModal();
    }
}

// 페이지 로드 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    attachCharCountListeners();
    initWriteForm();
});




