<?php
session_start();
require_once 'admin_functions.php';

// 관리자 세션 체크
checkAdminSession();

// 데이터베이스 연결 및 데이터 가져오기
try {
    require_once 'DAO/AdminDAO.php';
require_once 'DAO/NoticeDAO.php';
    
    // 신규가입회원 5명 가져오기 (최근 가입순)
    $members = AdminDAO::getRecentMembers(5);
    
    // 총 회원수 계산
    $total_members = AdminDAO::getTotalMembers();
    
    // 최근 댓글 5개 가져오기 (최근 작성순)
    $comments = AdminDAO::getRecentComments(5);
    
    // 최근 게시글 5개 가져오기 (최근 작성순)
    $posts = AdminDAO::getRecentPosts(5);
    
    // 최근 공지사항 5개 가져오기 (최근 작성순)
    $notices = NoticeDAO::getRecentNotices(5);
    
} catch (Exception $e) {
    error_log("Admin main data fetch error: " . $e->getMessage());
    $members = [];
    $total_members = 0;
    $comments = [];
    $posts = [];
    $notices = [];
}

$page_title = '관리자 메인 - 행복운동센터';
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin_sidebar.css">
    <link rel="stylesheet" href="css/admin-main.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>

        <!-- 메인 콘텐츠 -->
        <div class="admin-main">
            <div class="admin-header">
                <h1 class="admin-title">관리자메인</h1>
            </div>

            <div class="dashboard-grid">
                <!-- 신규가입회원 섹션 -->
                <div class="dashboard-card">
                    <div class="card-header">
                        신규가입회원 5건 목록
                    </div>
                    <div class="card-content">
                        <div class="summary-stats">
                            총회원수 <?php echo $total_members; ?>명
                        </div>
                        
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>회원아이디</th>
                                    <th>생년월일</th>
                                    <th>성별</th>
                                    <th>신장</th>
                                    <th>체중</th>
                                    <th>건강상태</th>
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
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; color: #999;">등록된 회원이 없습니다.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        
                        <a href="admin_member.php" class="view-all-btn">회원 전체보기</a>
                    </div>
                </div>

                <!-- 최근게시글 섹션 -->
                <div class="dashboard-card">
                    <div class="card-header">
                        최근게시글
                    </div>
                    <div class="card-content">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>제목</th>
                                    <th>작성자</th>
                                    <th>조회수</th>
                                    <th>작성일시</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($posts)): ?>
                                    <?php foreach ($posts as $post): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars(mb_substr($post['title'], 0, 20) . (mb_strlen($post['title']) > 20 ? '...' : '')); ?></td>
                                            <td><?php echo htmlspecialchars($post['name']); ?></td>
                                            <td><?php echo htmlspecialchars($post['view_count']); ?></td>
                                            <td><?php echo htmlspecialchars($post['time']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; color: #999;">등록된 게시글이 없습니다.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        
                        <a href="admin_posts.php" class="view-all-btn">게시글 전체보기</a>
                    </div>
                </div>

                <!-- 최근댓글 섹션 -->
                <div class="dashboard-card">
                    <div class="card-header">
                        최근댓글
                    </div>
                    <div class="card-content">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>작성자</th>
                                    <th>댓글내용</th>
                                    <th>게시글번호</th>
                                    <th>작성일시</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($comments)): ?>
                                    <?php foreach ($comments as $comment): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($comment['name']); ?></td>
                                            <td><?php echo htmlspecialchars(mb_substr($comment['content'], 0, 30) . (mb_strlen($comment['content']) > 30 ? '...' : '')); ?></td>
                                            <td><?php echo htmlspecialchars($comment['post_no']); ?></td>
                                            <td><?php echo htmlspecialchars($comment['date']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; color: #999;">등록된 댓글이 없습니다.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        
                        <a href="admin_comments.php" class="view-all-btn">댓글 전체보기</a>
                    </div>
                </div>

                <!-- 최근공지사항 섹션 -->
                <div class="dashboard-card">
                    <div class="card-header">
                        최근공지사항
                    </div>
                    <div class="card-content">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>제목</th>
                                    <th>태그</th>
                                    <th>조회수</th>
                                    <th>작성일시</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($notices)): ?>
                                    <?php foreach ($notices as $notice): ?>
                                        <?php
                                        // title에서 태그 추출
                                        $title = $notice['title'];
                                        $tag = '';
                                        $displayTitle = $title;
                                        
                                        if (preg_match('/^\[([^\]]+)\]\s*(.+)/', $title, $matches)) {
                                            $tag = '[' . $matches[1] . ']';
                                            $displayTitle = $matches[2];
                                        }
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars(mb_substr($displayTitle, 0, 20) . (mb_strlen($displayTitle) > 20 ? '...' : '')); ?></td>
                                            <td>
                                                <?php if ($tag): ?>
                                                    <span class="tag-display" style="background:#3498db;color:#fff;padding:2px 6px;border-radius:8px;font-size:10px;font-weight:500;">
                                                        <?php echo htmlspecialchars($tag); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span style="color:#999;font-size:10px;">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($notice['view_count']); ?></td>
                                            <td><?php echo htmlspecialchars($notice['date']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; color: #999;">등록된 공지사항이 없습니다.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        
                        <a href="admin_notices.php" class="view-all-btn">공지사항 전체보기</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
