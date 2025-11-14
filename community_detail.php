<?php
$page_title = "게시글 상세보기 - 커뮤니티";
include 'header.php';

// 게시글 ID 확인
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$post_id) {
    header('Location: community.php');
    exit;
}

// DAO 로드
require_once 'DAO/CommunityDAO.php';
require_once 'DAO/CommentDAO.php';

$communityDAO = new CommunityDAO();
$commentDAO = new CommentDAO();
$post = $communityDAO->getPostById($post_id);

if (!$post) {
    header('Location: community.php');
    exit;
}

// 조회수 증가
$communityDAO->updateViewCount($post_id);

// 댓글 목록 가져오기
$comments = $commentDAO->getCommentsByPostId($post_id);
?>

<link rel="stylesheet" href="css/community-detail.css">

<main>
    <div class="community-detail-page">
        <div class="community-detail-container">
            <div class="community-detail-header">
                <button class="back-btn" onclick="history.back()">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="community-detail-title">
                    <i class="fas fa-comments"></i>
                    <h1>커뮤니티</h1>
                </div>
            </div>
            
            <div class="community-detail-content">
                <!-- 게시글 정보 박스 -->
                <div class="community-info-box">
                    <div class="info-row">
                        <span class="info-label">제목</span>
                        <span class="info-value"><?php echo htmlspecialchars($post['title']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">작성자</span>
                        <span class="info-value"><?php echo htmlspecialchars($post['name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">날짜</span>
                        <span class="info-value"><?php echo date('Y년 m월 d일 H:i', strtotime($post['time'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">조회수</span>
                        <span class="info-value"><?php echo isset($post['view_count']) ? $post['view_count'] : 0; ?></span>
                    </div>
                </div>
                
                <!-- 게시글 내용 -->
                <div class="community-body">
                    <div class="community-content-text">
                        <div class="content-indent">
                            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                        </div>
                    </div>
                </div>
                
                <!-- 게시글 액션 -->
                <div class="community-actions">
                    <a href="community.php" class="list-btn">
                        <i class="fas fa-list"></i>
                        목록
                    </a>
                    
                    <?php 
                    $current_user_name = isset($_SESSION['email']) ? $_SESSION['email'] : '';
                    if (isset($_SESSION['email']) && $current_user_name === $post['name']): ?>
                        <button class="edit-btn" onclick="editPost(<?php echo $post['no']; ?>)">
                            <i class="fas fa-edit"></i>
                            수정
                        </button>
                        <button class="delete-btn" onclick="deletePost(<?php echo $post['no']; ?>)">
                            <i class="fas fa-trash"></i>
                            삭제
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 댓글 섹션 -->
        <div class="comments-section">
            <div class="comments-container">
                <div class="comments-header">
                    <h3>
                        <i class="fas fa-comments"></i>
                        댓글 <span class="comment-count"><?php echo count($comments); ?></span>
                    </h3>
                </div>

            <!-- 댓글 작성 폼 -->
            <?php if (isset($_SESSION['email'])): ?>
                <div class="comment-form">
                    <form id="commentForm">
                        <div class="form-group">
                            <label>
                                댓글 <span class="char-count" id="commentCharCount">0/500</span>
                            </label>
                            <textarea id="commentContent" name="content" placeholder="댓글을 작성해주세요..." maxlength="500" required></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="comment-submit-btn">
                                <i class="fas fa-paper-plane"></i>
                                댓글 작성
                            </button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="comment-login-required">
                    <p>댓글을 작성하려면 <a href="login.php">로그인</a>해주세요.</p>
                </div>
            <?php endif; ?>

            <!-- 댓글 목록 -->
            <div class="comments-list">
                <?php if (!empty($comments)): ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-item" data-comment-id="<?php echo $comment['no']; ?>">
                            <div class="comment-header">
                                <div class="comment-author">
                                    <i class="fas fa-user"></i>
                                    <?php echo htmlspecialchars($comment['name']); ?>
                                </div>
                                <div class="comment-meta">
                                    <span class="comment-date">
                                        <?php echo date('Y.m.d H:i', strtotime($comment['date'])); ?>
                                    </span>
                                    <?php 
                                    $current_user_name = isset($_SESSION['email']) ? $_SESSION['email'] : '';
                                    if (isset($_SESSION['email']) && $current_user_name === $comment['name']): ?>
                                        <button class="comment-delete-btn" onclick="deleteComment(<?php echo $comment['no']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="comment-content">
                                <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-comments">
                        <i class="fas fa-comment-slash"></i>
                        <p>아직 댓글이 없습니다. 첫 번째 댓글을 작성해보세요!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
// PHP 변수 전달
const postId = <?php echo $post_id; ?>;
</script>
<script src="js/community-detail.js"></script>

<?php include 'footer.php'; ?>
