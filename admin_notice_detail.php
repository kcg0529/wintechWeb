<?php
session_start();
require_once 'admin_functions.php';
checkAdminSession();

require_once 'DAO/NoticeDAO.php';

// 공지사항 번호 확인
if (!isset($_GET['no']) || empty($_GET['no'])) {
    header('Location: admin_notices.php');
    exit;
}

$notice_id = (int)$_GET['no'];

try {
    // 공지사항 조회
    $notice = NoticeDAO::getNoticeById($notice_id);
    
    if (!$notice) {
        header('Location: admin_notices.php');
        exit;
    }
    
    // 관리자 페이지에서는 조회수 증가하지 않음
    
    // 태그와 제목 분리
    $title = $notice['title'];
    $tag = '';
    $displayTitle = $title;
    
    if (preg_match('/^\[([^\]]+)\]\s*(.+)/', $title, $matches)) {
        $tagName = $matches[1];
        // [게임]을 [운동]으로 변환
        if ($tagName === '게임') {
            $tagName = '운동';
        }
        $tag = '[' . $tagName . ']';
        $displayTitle = $matches[2];
    }
    
} catch (Exception $e) {
    error_log("Notice detail error: " . $e->getMessage());
    header('Location: admin_notices.php');
    exit;
}

$page_title = '공지사항 상세 - 행복운동센터';
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
    <link rel="stylesheet" href="css/admin-notice-detail.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>
        <div class="admin-main">
            <div class="admin-header">
                <h1 class="admin-title">공지사항 상세</h1>
                <a href="admin_notices.php" class="btn btn-secondary">
                    <i class="fas fa-list"></i> 목록으로
                </a>
            </div>
            
            <div class="notice-detail">
                <div class="notice-header">
                    <h2 class="notice-title"><?php echo htmlspecialchars($displayTitle); ?></h2>
                    <div class="notice-meta">
                        <?php if ($tag): ?>
                            <span class="notice-tag"><?php echo htmlspecialchars($tag); ?></span>
                        <?php else: ?>
                            <span class="no-tag">태그 없음</span>
                        <?php endif; ?>
                        <div class="notice-views">
                            <i class="fas fa-eye"></i>
                            <span><?php echo $notice['view_count']; ?>회</span>
                        </div>
                        <div class="notice-date">
                            <i class="fas fa-calendar"></i>
                            <span><?php echo $notice['date']; ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="notice-content">
                    <?php echo $notice['content']; ?>
                </div>
                
                <div class="notice-actions">
                    <button class="btn btn-danger" data-notice-id="<?php echo $notice['no']; ?>">
                        <i class="fas fa-trash"></i> 삭제
                    </button>
                    <a href="admin_notices.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> 목록으로
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="js/admin-notice-detail.js"></script>
</body>
</html>
