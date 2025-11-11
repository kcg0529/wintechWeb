<?php
session_start();
require_once 'admin_functions.php';
checkAdminSession();

// 페이지네이션 설정
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20; // 한 페이지당 표시할 댓글 수
$offset = ($page - 1) * $limit;

// 검색 기능
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = "WHERE name LIKE '%$search%' OR content LIKE '%$search%'";
}

require_once 'DAO/CommentDAO.php';

// 전체 댓글 수 조회
$total_comments = CommentDAO::getTotalComments($search_condition);
$total_pages = ceil($total_comments / $limit);

// 댓글 목록 조회
$comments = CommentDAO::getComments($search_condition, $limit, $offset);

$page_title = '댓글관리 - 행복운동센터';
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
					<h1 class="admin-title">댓글관리</h1>
					<p style="margin-top:10px;color:#666;font-size:14px">총 <?php echo number_format($total_comments); ?>개의 댓글</p>
				</div>
				<div style="display:flex;align-items:center;gap:10px;">
					<label style="font-size:14px;color:#666;">페이지당 표시:</label>
					<select id="perPageSelect" style="padding:8px 12px;border:1px solid #ddd;border-radius:4px;font-size:14px;">
						<option value="5" <?php echo $limit == 5 ? 'selected' : ''; ?>>5개</option>
						<option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10개</option>
						<option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20개</option>
						<option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50개</option>
						<option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100개</option>
					</select>
				</div>
			</div>
			
			<div class="search-section">
				<form class="search-form" method="GET">
					<input type="hidden" name="per_page" value="<?php echo $limit; ?>">
					<input type="text" name="search" class="search-input" placeholder="작성자 또는 내용으로 검색..." value="<?php echo htmlspecialchars($search); ?>">
					<button type="submit" class="search-btn">
						<i class="fas fa-search"></i> 검색
					</button>
					<?php if (!empty($search)): ?>
						<a href="admin_comments.php?per_page=<?php echo $limit; ?>" class="reset-btn">
							<i class="fas fa-times"></i> 초기화
						</a>
					<?php endif; ?>
				</form>
			</div>
			
			<div class="data-card">
				<div style="margin-bottom: 15px; color: #666;">
					총 댓글수: <strong><?php echo $total_comments; ?>개</strong>
				</div>
				<table class="data-table">
					<thead>
						<tr><th>번호</th><th>작성자</th><th>내용</th><th>게시글번호</th><th>작성일시</th><th>관리</th></tr>
					</thead>
					<tbody>
						<?php if ($comments): foreach ($comments as $c): ?>
						<tr>
							<td><?php echo htmlspecialchars($c['no']); ?></td>
							<td><?php echo htmlspecialchars($c['name']); ?></td>
							<td><?php echo htmlspecialchars(mb_substr($c['content'],0,40) . (mb_strlen($c['content'])>40?'...':'')); ?></td>
							<td><?php echo htmlspecialchars($c['post_no']); ?></td>
							<td><?php echo htmlspecialchars($c['date']); ?></td>
							<td>
								<button class="delete-btn" onclick="deleteComment(<?php echo $c['no']; ?>)">
									<i class="fas fa-trash"></i> 삭제
								</button>
							</td>
						</tr>
						<?php endforeach; else: ?>
						<tr><td colspan="6" style="text-align:center;color:#999">
							<?php if (!empty($search)): ?>
								"<?php echo htmlspecialchars($search); ?>"에 대한 검색 결과가 없습니다.
							<?php else: ?>
								댓글이 없습니다.
							<?php endif; ?>
						</td></tr>
						<?php endif; ?>
					</tbody>
				</table>
				
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
	
	<script src="js/admin-comments.js"></script>
</body>
</html>
