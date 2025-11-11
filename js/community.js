// 글쓰기 모달
function openWriteModal() {
    document.getElementById('writeModal').style.display = 'block';
}

function closeWriteModal() {
    document.getElementById('writeModal').style.display = 'none';
    document.getElementById('writeForm').reset();
}

// 모달 외부 클릭 시 닫기
window.onclick = function(event) {
    const modal = document.getElementById('writeModal');
    if (event.target == modal) {
        closeWriteModal();
    }
}

// 글쓰기 폼 제출
document.getElementById('writeForm').addEventListener('submit', function(e) {
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




