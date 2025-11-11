<?php
session_start();
require_once 'admin_functions.php';
checkAdminSession();

require_once 'DAO/mysqli_con.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: admin_posts.php');
    exit;
}

$post_id = (int)$_GET['id'];

try {
    $conn = getConnection();
    
    // 게시글 정보 가져오기
    $post_query = "SELECT no, title, name, content, time, view_count FROM wintech_community WHERE no = ?";
    $post_stmt = mysqli_prepare($conn, $post_query);
    mysqli_stmt_bind_param($post_stmt, "i", $post_id);
    mysqli_stmt_execute($post_stmt);
    $post_result = mysqli_stmt_get_result($post_stmt);
    
    if (mysqli_num_rows($post_result) === 0) {
        header('Location: admin_posts.php');
        exit;
    }
    
    $post = mysqli_fetch_assoc($post_result);
    
    // 조회수 증가
    $view_query = "UPDATE wintech_community SET view_count = view_count + 1 WHERE no = ?";
    $view_stmt = mysqli_prepare($conn, $view_query);
    mysqli_stmt_bind_param($view_stmt, "i", $post_id);
    mysqli_stmt_execute($view_stmt);
    mysqli_stmt_close($view_stmt);
    
    // 댓글 목록 가져오기
    $comments_query = "SELECT no, name, content, date FROM wintech_comment WHERE post_no = ? ORDER BY date ASC";
    $comments_stmt = mysqli_prepare($conn, $comments_query);
    mysqli_stmt_bind_param($comments_stmt, "i", $post_id);
    mysqli_stmt_execute($comments_stmt);
    $comments_result = mysqli_stmt_get_result($comments_stmt);
    
    $comments = [];
    while ($row = mysqli_fetch_assoc($comments_result)) {
        $comments[] = $row;
    }
    
    mysqli_stmt_close($comments_stmt);
    mysqli_stmt_close($post_stmt);
    mysqli_close($conn);
    
} catch (Exception $e) {
    header('Location: admin_posts.php');
    exit;
}

$page_title = '게시글 상세보기 - 행복운동센터';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin_sidebar.css">
    <link rel="stylesheet" href="css/admin-common.css">
    <link rel="stylesheet" href="css/admin-post-detail.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>
        <div class="admin-main">
            <div class="admin-header">
                <h1 class="admin-title">게시글 상세보기</h1>
            </div>
            
            <a href="admin_posts.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                게시글 목록으로 돌아가기
            </a>
            
            <!-- 게시글 내용 -->
            <div class="post-card">
                <div class="post-header">
                    <h2 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                    <div class="post-meta">
                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($post['name']); ?></span>
                        <span><i class="fas fa-calendar"></i> <?php echo date('Y.m.d H:i', strtotime($post['time'])); ?></span>
                        <span><i class="fas fa-eye"></i> 조회수 <?php echo number_format($post['view_count']); ?></span>
                    </div>
                </div>
                <div class="post-content"><?php echo htmlspecialchars($post['content']); ?></div>
                
                <div class="action-buttons">
                    <button class="delete-btn" data-post-id="<?php echo $post['no']; ?>">
                        <i class="fas fa-trash"></i> 게시글 삭제
                    </button>
                </div>
            </div>
            
            <!-- 댓글 섹션 -->
            <div class="comments-section">
                <div class="comments-header">
                    <h3 class="comments-title">
                        <i class="fas fa-comments"></i>
                        댓글
                    </h3>
                    <span class="comment-count"><?php echo count($comments); ?>개</span>
                </div>
                
                <!-- 댓글 작성 폼 -->
                <div class="comment-form-section">
                    <h4 class="comment-form-title">
                        <i class="fas fa-edit"></i> 댓글 작성
                    </h4>
                    <form id="adminCommentForm">
                        <input type="hidden" name="post_id" value="<?php echo $post['no']; ?>">
                        <div class="comment-form-field">
                            <textarea name="content" placeholder="댓글을 작성해주세요..." required class="comment-textarea"></textarea>
                        </div>
                        <div class="comment-form-buttons">
                            <button type="submit" class="btn-comment-submit">
                                <i class="fas fa-paper-plane"></i> 댓글 작성
                            </button>
                            <button type="button" id="clearCommentBtn" class="btn-comment-reset">
                                <i class="fas fa-eraser"></i> 초기화
                            </button>
                        </div>
                    </form>
                </div>
                
                <?php if (!empty($comments)): ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-item" data-comment-id="<?php echo $comment['no']; ?>">
                            <div class="comment-header">
                                <div class="comment-author">
                                    <i class="fas fa-user"></i>
                                    <?php echo htmlspecialchars($comment['name']); ?>
                                </div>
                                <div class="comment-actions">
                                    <span class="comment-date">
                                        <?php echo date('Y.m.d H:i', strtotime($comment['date'])); ?>
                                    </span>
                                    <button class="comment-delete-btn" data-comment-id="<?php echo $comment['no']; ?>">
                                        <i class="fas fa-trash"></i> 삭제
                                    </button>
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
                        <p>아직 댓글이 없습니다.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="js/admin-post-detail.js"></script>
</body>
</html>
