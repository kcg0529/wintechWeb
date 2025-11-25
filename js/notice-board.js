// 공지사항 게시판 페이지 JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // 뒤로가기 버튼
    const backBtn = document.getElementById('backBtn');
    if (backBtn) {
        backBtn.addEventListener('click', function() {
            history.back();
        });
    }
});









