<?php
session_start();
require_once 'admin_functions.php';
checkAdminSession();

require_once 'DAO/ContentDAO.php';

// 검색어 처리
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search_escaped = mysqli_real_escape_string(getConnection(), $search);
    $search_condition = "WHERE (title LIKE '%$search_escaped%' OR tag LIKE '%$search_escaped%' OR path LIKE '%$search_escaped%')";
}

// 페이지네이션 설정
$contents_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $contents_per_page;

// 전체 콘텐츠 수 계산
$total_contents = ContentDAO::getTotalContents($search_condition);
$total_pages = ceil($total_contents / $contents_per_page);

// 콘텐츠 목록 조회
$contents = ContentDAO::getContentsWithPagination($search_condition, $contents_per_page, $offset);

$page_title = '콘텐츠관리 - 행복운동센터';
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
                    <h1 class="admin-title">콘텐츠관리</h1>
                    <p class="admin-subtitle">총 <?php echo number_format($total_contents); ?>개의 콘텐츠</p>
                </div>
                <div class="admin-header-actions">
                    <label style="font-size:14px;color:#666;">페이지당 표시:</label>
                    <select id="perPageSelect" style="padding:8px 12px;border:1px solid #ddd;border-radius:4px;font-size:14px;">
                        <option value="5" <?php echo $contents_per_page == 5 ? 'selected' : ''; ?>>5개</option>
                        <option value="10" <?php echo $contents_per_page == 10 ? 'selected' : ''; ?>>10개</option>
                        <option value="20" <?php echo $contents_per_page == 20 ? 'selected' : ''; ?>>20개</option>
                        <option value="50" <?php echo $contents_per_page == 50 ? 'selected' : ''; ?>>50개</option>
                        <option value="100" <?php echo $contents_per_page == 100 ? 'selected' : ''; ?>>100개</option>
                    </select>
                    <a href="admin_content_write.php" class="btn-primary" style="margin-left: 10px;">
                        <i class="fas fa-plus"></i> 콘텐츠 추가
                    </a>
                </div>
            </div>
            
            <!-- 검색 섹션 -->
            <div class="search-section">
                <form method="GET" class="search-form">
                    <input type="hidden" name="per_page" value="<?php echo $contents_per_page; ?>">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="제목, 태그, 경로로 검색..." class="search-input">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> 검색
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="admin_contents.php?per_page=<?php echo $contents_per_page; ?>" class="reset-btn">
                            <i class="fas fa-times"></i> 초기화
                        </a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="data-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>번호</th>
                            <th>태그</th>
                            <th>제목</th>
                            <th>경로</th>
                            <th>이미지</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($contents): foreach ($contents as $content): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($content['no']); ?></td>
                            <td>
                                <span class="tag-display"><?php echo htmlspecialchars($content['tag']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($content['title']); ?></td>
                            <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?php echo htmlspecialchars($content['path']); ?>
                            </td>
                            <td>
                                <?php if (!empty($content['img'])): ?>
                                    <img src="images/<?php echo htmlspecialchars($content['img']); ?>" alt="<?php echo htmlspecialchars($content['title']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    <span style="color: #999;">이미지 없음</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="admin_content_edit.php?id=<?php echo $content['no']; ?>" class="edit-btn" style="margin-right: 5px;">
                                    <i class="fas fa-edit"></i> 수정
                                </a>
                                <button class="delete-btn" onclick="deleteContent(<?php echo $content['no']; ?>)">
                                    <i class="fas fa-trash"></i> 삭제
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center;color:#999;padding:40px;">
                                <?php if (!empty($search)): ?>
                                    <i class="fas fa-search" style="font-size:24px;margin-bottom:10px;display:block;"></i>
                                    '<?php echo htmlspecialchars($search); ?>'에 대한 검색 결과가 없습니다.
                                <?php else: ?>
                                    <i class="fas fa-folder-open" style="font-size:24px;margin-bottom:10px;display:block;"></i>
                                    콘텐츠가 없습니다.
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <!-- 페이지네이션 -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($current_page > 1): ?>
                            <a href="?page=<?php echo $current_page - 1; ?>&per_page=<?php echo $contents_per_page; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
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
                                <a href="?page=<?php echo $i; ?>&per_page=<?php echo $contents_per_page; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?php echo $current_page + 1; ?>&per_page=<?php echo $contents_per_page; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                다음 <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <span class="disabled">다음 <i class="fas fa-chevron-right"></i></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="js/admin-contents.js"></script>
</body>
</html>




