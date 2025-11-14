// 게시글 상세보기 페이지 JavaScript
// post_id는 PHP 변수이므로 인라인 스크립트로 정의해야 함
// 이 파일은 post_id 없이 동작하는 함수들을 포함

// 댓글 글자 수 카운터 업데이트
function updateAdminCommentCharCount() {
    const commentContent = document.getElementById('adminCommentContent');
    const commentCharCount = document.getElementById('adminCommentCharCount');
    
    if (commentContent && commentCharCount) {
        commentCharCount.textContent = `${commentContent.value.length}/500`;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // 댓글 글자 수 카운터 이벤트 리스너 추가
    const adminCommentContent = document.getElementById('adminCommentContent');
    if (adminCommentContent) {
        adminCommentContent.addEventListener('input', updateAdminCommentCharCount);
        updateAdminCommentCharCount(); // 초기 카운트 설정
    }
    
    // 댓글 작성 폼 제출
    const commentForm = document.getElementById('adminCommentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const content = formData.get('content').trim();
            
            if (!content) {
                alert('댓글 내용을 입력해주세요.');
                return;
            }
            
            // post_id는 PHP에서 정의됨 (인라인 스크립트에서)
            // 여기서는 동적으로 가져옴
            const postId = formData.get('post_id') || document.querySelector('input[name="post_id"]')?.value;
            
            // 댓글 작성 요청
            fetch('admin_comment_write.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'post_id=' + encodeURIComponent(postId) + '&content=' + encodeURIComponent(content)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('댓글이 작성되었습니다.');
                    location.reload();
                } else {
                    alert('댓글 작성 중 오류가 발생했습니다: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('댓글 작성 중 오류가 발생했습니다.');
            });
        });
    }
    
    // 댓글 폼 초기화 버튼
    const clearCommentBtn = document.getElementById('clearCommentBtn');
    if (clearCommentBtn) {
        clearCommentBtn.addEventListener('click', function() {
            const contentTextarea = document.getElementById('adminCommentContent');
            if (contentTextarea) {
                contentTextarea.value = '';
                updateAdminCommentCharCount(); // 카운터 업데이트
            }
        });
    }
    
    // 게시글 삭제 버튼
    const deletePostBtn = document.querySelector('.delete-btn');
    if (deletePostBtn) {
        deletePostBtn.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            if (postId) {
                deletePost(postId);
            }
        });
    }
    
    // 댓글 삭제 버튼들
    const deleteCommentBtns = document.querySelectorAll('.comment-delete-btn');
    deleteCommentBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const commentId = this.getAttribute('data-comment-id');
            if (commentId) {
                deleteComment(commentId);
            }
        });
    });
});

function deletePost(postId) {
    if (confirm('정말로 이 게시글을 삭제하시겠습니까?\n게시글과 관련된 모든 댓글도 함께 삭제됩니다.')) {
        fetch('admin_post_delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'post_id=' + postId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('게시글이 삭제되었습니다.');
                window.location.href = 'admin_posts.php';
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

function deleteComment(commentId) {
    if (confirm('정말로 이 댓글을 삭제하시겠습니까?')) {
        fetch('admin_comment_delete.php', {
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
                alert('삭제 중 오류가 발생했습니다: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('삭제 중 오류가 발생했습니다.');
        });
    }
}

