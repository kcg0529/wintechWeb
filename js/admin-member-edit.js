// 회원 정보 수정 페이지 JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const memberEditForm = document.getElementById('memberEditForm');
    
    if (memberEditForm) {
        memberEditForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('admin_member_update.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('회원 정보가 성공적으로 수정되었습니다.');
                    window.location.href = 'admin_member.php';
                } else {
                    alert('수정 중 오류가 발생했습니다: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('수정 중 오류가 발생했습니다.');
            });
        });
    }
});









