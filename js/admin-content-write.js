// 문자 수 카운트
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('title');
    const titleCount = document.getElementById('titleCount');
    
    if (titleInput && titleCount) {
        titleInput.addEventListener('input', function() {
            const length = this.value.length;
            titleCount.textContent = length;
        });
    }
});









