// 회원 관리 페이지 JavaScript
document.addEventListener('DOMContentLoaded', function() {
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

function editMember(account) {
    window.location.href = 'admin_member_edit.php?account=' + encodeURIComponent(account);
}

function deleteMember(account) {
    if (confirm('회원 "' + account + '"을 삭제하시겠습니까?\n\n이 작업은 되돌릴 수 없습니다.')) {
        // AJAX로 회원 삭제 요청
        fetch('admin_member_delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'account=' + encodeURIComponent(account)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('회원이 성공적으로 삭제되었습니다.');
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




