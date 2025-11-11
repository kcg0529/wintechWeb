<?php
$page_title = "공지사항 - 행복운동센터";
include 'header.php';
require_once 'DAO/NoticeDAO.php';

// 공지사항 데이터 가져오기 (최근 4개)
$notices = NoticeDAO::getRecentNotices(4);
?>

<link rel="stylesheet" href="css/notice.css">

    <main>
        <div class="notice-page">
            <div class="notice-container">
                <div class="notice-header">
                    <a href="notice_board.php" class="view-details-btn">
                        <i class="fas fa-search"></i>
                        자세히보기
                    </a>
                    <div class="notice-title">
                        <i class="fas fa-bullhorn"></i>
                        <h1>공지사항</h1>
                    </div>
                </div>
                
                <div class="notice-content">
                    <?php if (!empty($notices)): ?>
                        <?php foreach ($notices as $notice): ?>
                            <?php
                            // DAO 함수를 사용하여 날짜 포맷팅 및 제목 파싱
                            $formatted_date = NoticeDAO::formatDate($notice['date']);
                            $parsed_title = NoticeDAO::parseNoticeTitle($notice['title']);
                            $tag = $parsed_title['tag'];
                            $title = $parsed_title['title'];
                            
                            // NEW 딱지 기준: 3일 이내 작성된 공지사항
                            $noticeTime = strtotime($notice['date']);
                            $isNew = (time() - $noticeTime) <= (3 * 24 * 60 * 60); // 3일 = 3 * 24시간 * 60분 * 60초
                            ?>
                            <div class="notice-item">
                                <div class="notice-info">
                                    <span class="notice-date"><?php echo htmlspecialchars($formatted_date); ?></span>
                                    <?php if ($tag): ?>
                                        <span class="notice-category"><?php echo htmlspecialchars($tag); ?></span>
                                    <?php endif; ?>
                                    <a href="notice_detail.php?id=<?php echo $notice['no']; ?>" class="notice-title-link">
                                        <?php echo htmlspecialchars($title); ?>
                                    </a>
                                </div>
                                <?php if ($isNew): ?>
                                    <div class="notice-badge">NEW</div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="notice-item">
                            <div class="notice-info">
                                <span class="notice-title-text">등록된 공지사항이 없습니다.</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

<?php 
include 'footer.php'; 
?>