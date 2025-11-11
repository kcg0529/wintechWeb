// 공지사항 상세보기 페이지 JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const deleteBtn = document.querySelector('.btn-danger[data-notice-id]');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            const noticeId = this.getAttribute('data-notice-id');
            if (noticeId) {
                deleteNotice(noticeId);
            }
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
                location.href = 'admin_notices.php';
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

