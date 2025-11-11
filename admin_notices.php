<?php
session_start();
require_once 'admin_functions.php';
checkAdminSession();

require_once 'DAO/NoticeDAO.php';

// 검색어 처리
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search_escaped = mysqli_real_escape_string(getConnection(), $search);
    $search_condition = "WHERE (title LIKE '%$search_escaped%' OR content LIKE '%$search_escaped%')";
}

// 페이지네이션 설정
$notices_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $notices_per_page;

// 전체 공지사항 수 계산
$total_notices = NoticeDAO::getTotalNotices($search_condition);
$total_pages = ceil($total_notices / $notices_per_page);

// 공지사항 목록 조회
$notices = NoticeDAO::getNotices($search_condition, $notices_per_page, $offset);

$page_title = '공지사항관리 - 행복운동센터';
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
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>
        <div class="admin-main">
            <div class="admin-header">
                <div>
                    <h1 class="admin-title">공지사항관리</h1>
                    <p class="admin-subtitle">총 <?php echo number_format($total_notices); ?>개의 공지사항</p>
                </div>
                <div class="admin-header-actions">
                    <a href="admin_notice_write.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> 공지사항 작성
                    </a>
                    <div class="per-page-selector">
                        <label>페이지당 표시:</label>
                        <select id="perPageSelect" class="per-page-select">
                            <option value="5" <?php echo $notices_per_page == 5 ? 'selected' : ''; ?>>5개</option>
                            <option value="10" <?php echo $notices_per_page == 10 ? 'selected' : ''; ?>>10개</option>
                            <option value="20" <?php echo $notices_per_page == 20 ? 'selected' : ''; ?>>20개</option>
                            <option value="50" <?php echo $notices_per_page == 50 ? 'selected' : ''; ?>>50개</option>
                            <option value="100" <?php echo $notices_per_page == 100 ? 'selected' : ''; ?>>100개</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- 검색 섹션 -->
            <div class="search-section">
                <form class="search-form" method="GET">
                    <input type="hidden" name="per_page" value="<?php echo $notices_per_page; ?>">
                    <input type="text" name="search" class="search-input" placeholder="제목 또는 내용으로 검색..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> 검색
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="admin_notices.php?per_page=<?php echo $notices_per_page; ?>" class="reset-btn">
                            <i class="fas fa-times"></i> 초기화
                        </a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="data-card">
                <?php if (!empty($notices)): ?>
                    <table class="data-table">
                         <thead>
                             <tr>
                                 <th>번호</th>
                                 <th>제목</th>
                                 <th>태그</th>
                                 <th>조회수</th>
                                 <th>작성일시</th>
                                 <th>관리</th>
                             </tr>
                         </thead>
                        <tbody>
                            <?php foreach ($notices as $notice): ?>
                                <?php
                                // title에서 태그 추출
                                $title = $notice['title'];
                                $tag = '';
                                $displayTitle = $title;
                                
                                if (preg_match('/^\[([^\]]+)\]\s*(.+)/', $title, $matches)) {
                                    $tag = '[' . $matches[1] . ']';
                                    $displayTitle = $matches[2]; // 태그를 제외한 제목
                                }
                                ?>
                                <tr>
                                    <td><?php echo $notice['no']; ?></td>
                                    <td>
                                        <a href="admin_notice_detail.php?no=<?php echo $notice['no']; ?>" class="notice-title-link">
                                            <?php 
                                            if (mb_strlen($displayTitle) > 50) {
                                                $displayTitle = mb_substr($displayTitle, 0, 50) . '...';
                                            }
                                            echo htmlspecialchars($displayTitle); 
                                            ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($tag): ?>
                                            <span class="tag-display">
                                                <?php echo htmlspecialchars($tag); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="no-tag">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $notice['view_count']; ?></td>
                                    <td><?php echo $notice['date']; ?></td>
                                    <td>
                                        <button class="delete-btn" data-notice-id="<?php echo $notice['no']; ?>">
                                            <i class="fas fa-trash"></i> 삭제
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- 페이지네이션 -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($current_page > 1): ?>
                                <a href="?page=<?php echo $current_page - 1; ?>&per_page=<?php echo $notices_per_page; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                    <i class="fas fa-chevron-left"></i> 이전
                                </a>
                            <?php else: ?>
                                <span class="disabled"><i class="fas fa-chevron-left"></i> 이전</span>
                            <?php endif; ?>
                            
                            <?php
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);
                            
                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <?php if ($i == $current_page): ?>
                                    <span class="current"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?>&per_page=<?php echo $notices_per_page; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($current_page < $total_pages): ?>
                                <a href="?page=<?php echo $current_page + 1; ?>&per_page=<?php echo $notices_per_page; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                    다음 <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php else: ?>
                                <span class="disabled">다음 <i class="fas fa-chevron-right"></i></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-bullhorn"></i>
                        <?php if (!empty($search)): ?>
                            <h3>'<?php echo htmlspecialchars($search); ?>'에 대한 검색 결과가 없습니다</h3>
                            <p>다른 검색어로 다시 시도해보세요.</p>
                        <?php else: ?>
                            <h3>등록된 공지사항이 없습니다</h3>
                            <p>새로운 공지사항을 작성해보세요.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="js/admin-notices.js"></script>
</body>
</html>
