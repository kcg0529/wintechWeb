<?php
// 에러 리포팅 활성화
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = "커뮤니티 - 행복운동센터";
include 'header.php';
require_once 'profile_check.php';

// 프로필 정보 완성도 확인
checkProfileCompletion();
?>

<link rel="stylesheet" href="css/community.css">

<?php
// 페이지네이션 설정
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // 한 페이지당 게시글 수
$offset = ($page - 1) * $limit;

// 검색 기능
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
$search_params = [];

if (!empty($search)) {
    $search_condition = "WHERE title LIKE ? OR content LIKE ?";
    $search_params = ["%$search%", "%$search%"];
}

// 커뮤니티 DAO 로드
require_once 'DAO/CommunityDAO.php';

// DAO 인스턴스 생성
try {
    $communityDAO = new CommunityDAO();
    
    // 게시글 목록 가져오기
    $posts = $communityDAO->getPosts($limit, $offset, $search_condition, $search_params);
    $total_posts = $communityDAO->getTotalPosts($search_condition, $search_params);
    $total_pages = ceil($total_posts / $limit);
} catch (Exception $e) {
    // 테이블이 없거나 오류가 발생한 경우
    $posts = [];
    $total_posts = 0;
    $total_pages = 0;
    $error_message = "데이터베이스 오류가 발생했습니다. 관리자에게 문의하세요.";
}
?>

<main>
    <div class="community-page">
        <div class="community-container">
            <div class="community-header">
                <div class="community-actions">
                    <div class="search-box">
                        <form method="GET" class="search-form">
                            <input type="text" name="search" placeholder="제목, 내용으로 검색..." value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    
                    <?php if (isset($_SESSION['email'])): ?>
                        <button class="write-btn" onclick="openWriteModal()">
                            <i class="fas fa-pen"></i>
                            글쓰기
                        </button>
                    <?php else: ?>
                        <button class="write-btn disabled" onclick="if(confirm('로그인이 필요합니다. 로그인 페이지로 이동하시겠습니까?')) { window.location.href='login.php'; }">
                            <i class="fas fa-lock"></i>
                            로그인 후 글쓰기
                        </button>
                    <?php endif; ?>
                </div>
                <div class="community-title">
                    <i class="fas fa-comments"></i>
                    <h1>커뮤니티</h1>
                </div>
            </div>

            <!-- 게시글 목록 -->
            <div class="community-content">
                <?php if (isset($error_message)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>오류가 발생했습니다</h3>
                        <p><?php echo $error_message; ?></p>
                    </div>
                <?php elseif (!empty($posts)): ?>
                    <?php foreach ($posts as $post): ?>
                        <?php
                        // NEW 딱지 기준: 3일 이내 작성된 게시글
                        $postTime = strtotime($post['time']);
                        $isNew = (time() - $postTime) <= (3 * 24 * 60 * 60); // 3일 = 3 * 24시간 * 60분 * 60초
                        ?>
                        <div class="community-item">
                            <div class="community-info">
                                <span class="community-date"><?php echo date('Y.m.d', $postTime); ?></span>
                                <span class="community-author"><?php echo htmlspecialchars($post['name']); ?></span>
                                <a href="community_detail.php?id=<?php echo $post['no']; ?>" class="community-title-link">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </div>
                            <?php if ($isNew): ?>
                                <div class="community-badge">NEW</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="community-item">
                        <div class="community-info">
                            <span class="community-title-text">등록된 게시글이 없습니다.</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

        <!-- 페이지네이션 -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="page-btn prev">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>
                
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                    <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                       class="page-btn <?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="page-btn next">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- 글쓰기 모달 -->
<div id="writeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>
                <i class="fas fa-pen"></i>
                새 게시글 작성
            </h3>
            <button class="close-btn" onclick="closeWriteModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="writeForm" class="write-form">
            <div class="form-group">
                <label for="postTitle">제목 <span class="char-count" id="titleCount">0/100</span></label>
                <input type="text" id="postTitle" name="title" placeholder="제목을 입력하세요" maxlength="100" required>
            </div>
            
            <div class="form-group">
                <label for="postContent">내용 <span class="char-count" id="contentCount">0/1000</span></label>
                <textarea id="postContent" name="content" placeholder="내용을 입력하세요" rows="10" maxlength="1000" required></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="cancel-btn" onclick="closeWriteModal()">취소</button>
                <button type="submit" class="submit-btn">작성하기</button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="js/community.js"></script>