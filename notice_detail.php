<?php
$page_title = "공지사항 상세 - 행복운동센터";
include 'header.php';
require_once 'DAO/NoticeDAO.php';

// 공지사항 번호 확인
$no = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($no <= 0) {
    header('Location: notice_board.php');
    exit;
}

// 공지사항 데이터 가져오기
$notice = NoticeDAO::getNoticeById($no);

if (!$notice) {
    header('Location: notice_board.php');
    exit;
}

// 조회수 증가
NoticeDAO::incrementViewCount($no);

// 제목 파싱
$parsed_title = NoticeDAO::parseNoticeTitle($notice['title']);
$tag = $parsed_title['tag'];
$title = $parsed_title['title'];

// 이전글/다음글 가져오기
$previousNotice = NoticeDAO::getPreviousNotice($notice['no'], $notice['date']);
$nextNotice = NoticeDAO::getNextNotice($notice['no'], $notice['date']);
?>

<link rel="stylesheet" href="css/notice-detail.css">

<main>
    <div class="notice-detail-page">
        <div class="notice-detail-container">
            <div class="notice-detail-header">
                <button class="back-btn" id="backBtn">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="notice-detail-title">
                    <i class="fas fa-bullhorn"></i>
                    <h1>공지사항</h1>
                </div>
            </div>
            
            <div class="notice-detail-content">
                <!-- 공지사항 정보 박스 -->
                <div class="notice-info-box">
                    <div class="info-row">
                        <span class="info-label">제목</span>
                        <span class="info-value">
                            <?php if ($tag): ?>
                                <span class="notice-category"><?php echo htmlspecialchars($tag); ?></span>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($title); ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">작성자</span>
                        <span class="info-value">관리자</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">날짜</span>
                        <span class="info-value"><?php echo NoticeDAO::formatDate($notice['date']); ?></span>
                    </div>
                </div>
                
                <!-- 공지사항 요약 -->
                <?php if (isset($notice['summary']) && !empty($notice['summary'])): ?>
                <div class="notice-summary">
                    <?php echo htmlspecialchars($notice['summary']); ?>
                </div>
                <?php endif; ?>
                
                <!-- 공지사항 내용 -->
                <?php if (isset($notice['content']) && !empty($notice['content'])): ?>
                <div class="notice-body">
                    <div class="notice-content-text">
                        <div class="content-indent">
                            <?php echo nl2br(htmlspecialchars($notice['content'])); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- 이전글/다음글 네비게이션 -->
                <div class="notice-navigation">
                    <div class="nav-section">
                        <div class="nav-item previous">
                            <span class="nav-label">이전글</span>
                            <?php if ($previousNotice): ?>
                                <?php 
                                $prev_parsed = NoticeDAO::parseNoticeTitle($previousNotice['title']);
                                $prev_tag = $prev_parsed['tag'];
                                $prev_title = $prev_parsed['title'];
                                ?>
                                <a href="notice_detail.php?id=<?php echo $previousNotice['no']; ?>" class="nav-link">
                                    <?php if ($prev_tag): ?>
                                        <span class="nav-category"><?php echo htmlspecialchars($prev_tag); ?></span>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($prev_title); ?>
                                </a>
                            <?php else: ?>
                                <span class="nav-empty">이전글이 없습니다.</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="nav-item next">
                            <span class="nav-label">다음글</span>
                            <?php if ($nextNotice): ?>
                                <?php 
                                $next_parsed = NoticeDAO::parseNoticeTitle($nextNotice['title']);
                                $next_tag = $next_parsed['tag'];
                                $next_title = $next_parsed['title'];
                                ?>
                                <a href="notice_detail.php?id=<?php echo $nextNotice['no']; ?>" class="nav-link">
                                    <?php if ($next_tag): ?>
                                        <span class="nav-category"><?php echo htmlspecialchars($next_tag); ?></span>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($next_title); ?>
                                </a>
                            <?php else: ?>
                                <span class="nav-empty">다음글이 없습니다.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="nav-actions">
                        <a href="notice_board.php" class="list-btn">
                            <i class="fas fa-list"></i>
                            목록
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="js/notice-detail.js"></script>

<?php 
include 'footer.php'; 
?>
