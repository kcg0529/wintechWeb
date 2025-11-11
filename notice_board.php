<?php
$page_title = "공지사항 게시판 - 행복운동센터";
include 'header.php';
require_once 'DAO/NoticeDAO.php';
?>

<link rel="stylesheet" href="css/notice-board.css">

<?php

// 페이지네이션 및 검색 파라미터 처리
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$perPage = 10; // 페이지당 10개

// 검색 조건 생성
$search_condition = '';
if (!empty($search)) {
    $search_escaped = mysqli_real_escape_string(getConnection(), $search);
    $search_condition = "WHERE (title LIKE '%$search_escaped%' OR content LIKE '%$search_escaped%')";
}

// 공지사항 데이터 가져오기
$offset = ($page - 1) * $perPage;
$notices = NoticeDAO::getNoticesWithPagination($search_condition, $perPage, $offset);
$totalCount = NoticeDAO::getSearchNoticeCount($search_condition);
$totalPages = ceil($totalCount / $perPage);

// 페이지 범위 계산
$startPage = max(1, $page - 2);
$endPage = min($totalPages, $page + 2);
?>

<main>
    <div class="notice-board-page">
        <div class="notice-board-container">
            <div class="notice-board-header">
                <button class="back-btn" id="backBtn">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="notice-board-title">
                    <i class="fas fa-bullhorn"></i>
                    <h1>공지사항</h1>
                </div>
                <div class="notice-board-stats">
                    <span class="stats-number"><?php echo number_format($totalCount); ?></span>
                    <i class="fas fa-bell"></i>
                </div>
            </div>
            
            <div class="notice-board-content">
                <table class="notice-table">
                    <thead>
                        <tr>
                            <th class="col-number">번호</th>
                            <th class="col-title">제목</th>
                            <th class="col-author">작성자</th>
                            <th class="col-date">등록날짜</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($notices)): ?>
                            <?php 
                            $startNumber = $totalCount - (($page - 1) * $perPage);
                            foreach ($notices as $notice): 
                            ?>
                                <tr>
                                    <td class="col-number"><?php echo $startNumber--; ?></td>
                                    <td class="col-title">
                                        <?php
                                        $parsed_title = NoticeDAO::parseNoticeTitle($notice['title']);
                                        $tag = $parsed_title['tag'];
                                        $title = $parsed_title['title'];
                                        
                                        // NEW 딱지 기준: 3일 이내 작성된 공지사항
                                        $noticeTime = strtotime($notice['date']);
                                        $isNew = (time() - $noticeTime) <= (3 * 24 * 60 * 60); // 3일 = 3 * 24시간 * 60분 * 60초
                                        ?>
                                        <a href="notice_detail.php?id=<?php echo $notice['no']; ?>" class="notice-title-link">
                                            <?php if ($tag): ?>
                                                <span class="notice-category"><?php echo htmlspecialchars($tag); ?></span>
                                            <?php endif; ?>
                                            <span class="notice-title-text"><?php echo htmlspecialchars($title); ?></span>
                                            <?php if ($isNew): ?>
                                                <span class="notice-badge">NEW</span>
                                            <?php endif; ?>
                                        </a>
                                    </td>
                                    <td class="col-author">관리자</td>
                                    <td class="col-date"><?php echo NoticeDAO::formatDate($notice['date']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="no-data">등록된 공지사항이 없습니다.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="notice-board-footer">
                <div class="pagination">
                    <?php if ($totalPages > 1): ?>
                        <!-- 첫 페이지 -->
                        <?php if ($page > 1): ?>
                            <a href="?page=1<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="page-btn first">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                            <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="page-btn prev">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <!-- 페이지 번호 -->
                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                               class="page-btn <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <!-- 마지막 페이지 -->
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="page-btn next">
                                <i class="fas fa-angle-right"></i>
                            </a>
                            <a href="?page=<?php echo $totalPages; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="page-btn last">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <div class="search-box">
                    <form method="GET" class="search-form">
                        <input type="text" name="search" placeholder="검색어를 입력하세요" 
                               value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="js/notice-board.js"></script>

<?php 
include 'footer.php'; 
?>