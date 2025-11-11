<?php
session_start();
require_once 'admin_functions.php';

// 관리자 세션 체크
checkAdminSession();

// 페이지네이션 설정
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10; // 한 페이지당 표시할 회원 수
$offset = ($page - 1) * $limit;

// 검색 기능
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = "WHERE account LIKE '%$search%'";
}

// 데이터베이스 연결 및 데이터 가져오기
try {
    require_once 'DAO/MemberDAO.php';
    
    // 전체 회원 수 조회
    $total_members = MemberDAO::getTotalMembers($search_condition);
    $total_pages = ceil($total_members / $limit);
    
    // 회원 목록 조회
    $members = MemberDAO::getMembers($search_condition, $limit, $offset);
    
} catch (Exception $e) {
    error_log("Admin member management error: " . $e->getMessage());
    $members = [];
    $total_members = 0;
    $total_pages = 0;
}

$page_title = '회원 관리 - 행복운동센터';
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
    <link rel="stylesheet" href="css/admin-member.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>

        <!-- 메인 콘텐츠 -->
        <div class="admin-main">
            <div class="admin-header">
                <h1 class="admin-title">회원 관리</h1>
            </div>

            <div class="member-management">
                <div class="member-stats">
                    <div class="stats-info">
                        총 회원수: <strong><?php echo $total_members; ?>명</strong>
                    </div>
                    
                    <div class="member-stats-actions">
                        <form class="member-search-form" method="GET">
                            <input type="hidden" name="per_page" value="<?php echo $limit; ?>">
                            <input type="text" name="search" class="search-input" placeholder="회원 ID로 검색..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i> 검색
                            </button>
                            <?php if (!empty($search)): ?>
                                <a href="admin_member.php?per_page=<?php echo $limit; ?>" class="search-btn reset-btn">
                                    <i class="fas fa-times"></i> 초기화
                                </a>
                            <?php endif; ?>
                        </form>
                        
                        <div class="per-page-wrapper">
                            <label>페이지당 표시:</label>
                            <select id="perPageSelect" class="per-page-select">
                                <option value="5" <?php echo $limit == 5 ? 'selected' : ''; ?>>5개</option>
                                <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10개</option>
                                <option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20개</option>
                                <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50개</option>
                                <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100개</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <table class="member-table">
                            <thead>
                                <tr>
                                    <th>회원아이디</th>
                                    <th>생년월일</th>
                                    <th>성별</th>
                                    <th>신장</th>
                                    <th>체중</th>
                                    <th>건강상태</th>
                                    <th>관리</th>
                                </tr>
                            </thead>
                    <tbody>
                        <?php if (!empty($members)): ?>
                            <?php foreach ($members as $member): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($member['account']); ?></td>
                                    <td><?php echo htmlspecialchars($member['birthday'] ?: '-'); ?></td>
                                    <td><?php 
                                        if ($member['gender'] == '1') echo '남성';
                                        elseif ($member['gender'] == '2') echo '여성';
                                        else echo '-';
                                    ?></td>
                                    <td><?php echo htmlspecialchars($member['height'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($member['weight'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($member['note'] ?: '-'); ?></td>
                                    <td class="action-buttons">
                                        <button class="edit-btn" onclick="editMember('<?php echo htmlspecialchars($member['account']); ?>')">
                                            <i class="fas fa-edit"></i> 수정
                                        </button>
                                        <button class="delete-btn" onclick="deleteMember('<?php echo htmlspecialchars($member['account']); ?>')">
                                            <i class="fas fa-trash"></i> 삭제
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-data">
                                    <?php if (!empty($search)): ?>
                                        "<?php echo htmlspecialchars($search); ?>"에 대한 검색 결과가 없습니다.
                                    <?php else: ?>
                                        등록된 회원이 없습니다.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <!-- 페이지네이션 -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&per_page=<?php echo $limit; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                <i class="fas fa-chevron-left"></i> 이전
                            </a>
                        <?php else: ?>
                            <span class="disabled"><i class="fas fa-chevron-left"></i> 이전</span>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <?php if ($i == $page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&per_page=<?php echo $limit; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&per_page=<?php echo $limit; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
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

    <script src="js/admin-member.js"></script>
</body>
</html>
