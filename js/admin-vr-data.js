// VR 데이터 관리 페이지 JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const perPageSelect = document.getElementById('perPageSelect');
    
    // 페이지당 항목 수 변경 시 페이지 이동
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            const url = new URL(window.location);
            url.searchParams.set('per_page', this.value);
            url.searchParams.set('page', '1'); // 첫 페이지로 이동
            window.location.href = url.toString();
        });
    }
});

// 사이클 데이터 삭제 함수
function deleteCycleData(cycleId) {
    if (confirm('정말로 이 사이클 데이터를 삭제하시겠습니까?')) {
        fetch('admin_cycle_delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'cycle_id=' + cycleId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('사이클 데이터가 삭제되었습니다.');
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




