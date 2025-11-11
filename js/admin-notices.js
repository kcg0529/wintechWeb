// 공지사항 관리 페이지 JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // 삭제 버튼 이벤트
    const deleteBtns = document.querySelectorAll('.delete-btn[data-notice-id]');
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const noticeId = this.getAttribute('data-notice-id');
            if (noticeId) {
                deleteNotice(noticeId);
            }
        });
    });
    
    // 페이지당 표시 개수 변경
    const perPageSelect = document.getElementById('perPageSelect');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            const url = new URL(window.location);
            url.searchParams.set('per_page', this.value);
            url.searchParams.set('page', '1'); // 첫 페이지로 이동
            window.location.href = url.toString();
        });
    }
});

function deleteNotice(noticeId) {
    if (confirm('정말로 이 공지사항을 삭제하시겠습니까?')) {
        fetch('admin_notice_delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'notice_id=' + noticeId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('공지사항이 삭제되었습니다.');
                location.reload();
            } else {
                alert('삭제 중 오류가 발생했습니다: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('삭제 중 오류가 발생했습니다.');
        });
    }
}

