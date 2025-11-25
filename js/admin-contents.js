// 페이지당 표시 개수 변경
document.addEventListener('DOMContentLoaded', function() {
    const perPageSelect = document.getElementById('perPageSelect');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            const perPage = this.value;
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', perPage);
            url.searchParams.set('page', '1'); // 페이지를 1로 리셋
            window.location.href = url.toString();
        });
    }
});

// 콘텐츠 삭제
function deleteContent(id) {
    if (confirm('정말로 이 콘텐츠를 삭제하시겠습니까?')) {
        fetch('admin_content_delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('콘텐츠가 삭제되었습니다.');
                location.reload();
            } else {
                alert('삭제 중 오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('삭제 중 오류가 발생했습니다.');
        });
    }
}









