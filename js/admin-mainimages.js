// 이미지 삭제
function deleteImage(id) {
    if (!confirm('정말로 이 이미지를 삭제하시겠습니까?')) {
        return;
    }
    
    fetch('admin_mainimage_delete.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || '삭제에 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('오류가 발생했습니다.');
    });
}

// 이미지 모달 표시
function showImageModal(imgSrc) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    
    if (modal && modalImg) {
        modal.style.display = 'block';
        modalImg.src = imgSrc;
    }
}

// 이미지 모달 닫기
function closeImageModal() {
    const modal = document.getElementById('imageModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// ESC 키로 모달 닫기
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeImageModal();
    }
});

// 썸네일 이미지 스타일
document.addEventListener('DOMContentLoaded', function() {
    const thumbnails = document.querySelectorAll('.thumbnail-image');
    thumbnails.forEach(function(thumb) {
        thumb.style.maxWidth = '80px';
        thumb.style.maxHeight = '60px';
        thumb.style.objectFit = 'cover';
        thumb.style.cursor = 'pointer';
        thumb.style.borderRadius = '4px';
        thumb.style.border = '1px solid #ddd';
    });
});






