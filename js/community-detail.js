// 게시글 수정
function editPost(postId) {
    if (confirm('게시글을 수정하시겠습니까?')) {
        // 수정 페이지로 이동 (향후 구현)
        alert('수정 기능은 준비 중입니다.');
    }
}

// 게시글 삭제
function deletePost(postId) {
    if (confirm('게시글을 삭제하시겠습니까?\n삭제된 게시글은 복구할 수 없습니다.')) {
        fetch('community_delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + postId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('게시글이 삭제되었습니다.');
                window.location.href = 'community.php';
            } else {
                alert('게시글 삭제에 실패했습니다: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('오류가 발생했습니다.');
        });
    }
}

// 댓글 작성
document.getElementById('commentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('post_id', postId);
    
    // 버튼 비활성화
    const submitBtn = this.querySelector('.comment-submit-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 작성 중...';
    
    fetch('comment_write.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 성공 시 페이지 새로고침
            location.reload();
        } else {
            // 실패 시 버튼 복원하고 에러 표시
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            alert('댓글 작성에 실패했습니다: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // 에러 시 버튼 복원
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        alert('오류가 발생했습니다.');
    });
});

// 댓글 삭제
function deleteComment(commentId) {
    if (confirm('댓글을 삭제하시겠습니까?')) {
        fetch('comment_delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'comment_id=' + commentId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('댓글이 삭제되었습니다.');
                location.reload();
            } else {
                alert('댓글 삭제에 실패했습니다: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('오류가 발생했습니다.');
        });
    }
}

