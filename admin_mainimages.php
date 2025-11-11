<?php
session_start();
require_once 'DAO/MainImgDAO.php';

// 관리자 권한 확인
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// 페이지네이션
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// 검색 기능
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tag_filter = isset($_GET['tag']) ? trim($_GET['tag']) : '';

// 전체 이미지 수 가져오기
$allImages = MainImgDAO::getAllImages();
$filteredImages = $allImages;

// 검색 및 필터링
if (!empty($search) || !empty($tag_filter)) {
    $filteredImages = array_filter($allImages, function($image) use ($search, $tag_filter) {
        $matchSearch = empty($search) || 
                      (stripos($image['title'], $search) !== false) || 
                      (stripos($image['text'], $search) !== false) ||
                      (stripos($image['img'], $search) !== false);
        $matchTag = empty($tag_filter) || ($image['tag'] === $tag_filter);
        return $matchSearch && $matchTag;
    });
}

$totalImages = count($filteredImages);
$totalPages = ceil($totalImages / $limit);

// 현재 페이지 이미지 가져오기
$currentImages = array_slice($filteredImages, $offset, $limit);

$page_title = "메인이미지 관리";
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - 관리자</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin_sidebar.css">
    <link rel="stylesheet" href="css/admin-common.css">
    <link rel="stylesheet" href="css/admin-content-write.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>
        
        <div class="admin-main">
            <div class="admin-header">
                <div>
                    <h1 class="admin-title">메인이미지 관리</h1>
                    <p class="admin-subtitle">총 <?php echo number_format($totalImages); ?>개의 이미지</p>
                </div>
                <div class="admin-header-actions">
                    <a href="admin_mainimage_write.php" class="btn-primary">
                        <i class="fas fa-plus"></i> 이미지 추가
                    </a>
                </div>
            </div>
            
            <!-- 검색 섹션 -->
            <div class="search-section">
                <form method="GET" class="search-form">
                    <select name="tag" class="form-select" style="padding:8px 12px;border:1px solid #ddd;border-radius:4px;font-size:14px;margin-right:10px;">
                        <option value="">전체 태그</option>
                        <option value="slider" <?php echo $tag_filter === 'slider' ? 'selected' : ''; ?>>Slider</option>
                        <option value="shop" <?php echo $tag_filter === 'shop' ? 'selected' : ''; ?>>Shop</option>
                    </select>
                    <input type="text" name="search" placeholder="제목, 설명, 이미지명 검색..." 
                           value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> 검색
                    </button>
                    <?php if (!empty($search) || !empty($tag_filter)): ?>
                        <a href="admin_mainimages.php" class="reset-btn">
                            <i class="fas fa-times"></i> 초기화
                        </a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- 이미지 목록 -->
            <div class="data-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">NO</th>
                            <th style="width: 100px;">태그</th>
                            <th style="width: 120px;">이미지</th>
                            <th style="width: 200px;">제목</th>
                            <th>텍스트/가격</th>
                            <th style="width: 150px;">이미지 파일명</th>
                            <th style="width: 150px;">관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($currentImages) > 0): ?>
                            <?php foreach ($currentImages as $index => $image): ?>
                                <tr>
                                    <td><?php echo $image['no']; ?></td>
                                    <td>
                                        <span class="tag-badge tag-<?php echo htmlspecialchars($image['tag']); ?>">
                                            <?php echo htmlspecialchars($image['tag']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($image['img'])): ?>
                                            <img src="images/<?php echo htmlspecialchars($image['img']); ?>" 
                                                 alt="<?php echo htmlspecialchars($image['title']); ?>" 
                                                 class="thumbnail-image"
                                                 onclick="showImageModal('images/<?php echo htmlspecialchars($image['img']); ?>')">
                                        <?php else: ?>
                                            <span class="no-image">이미지 없음</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($image['title']); ?></td>
                                    <td class="text-truncate"><?php echo htmlspecialchars($image['text']); ?></td>
                                    <td class="text-truncate"><?php echo htmlspecialchars($image['img']); ?></td>
                                    <td>
                                        <a href="admin_mainimage_edit.php?id=<?php echo $image['no']; ?>" class="edit-btn" style="margin-right: 5px;">
                                            <i class="fas fa-edit"></i> 수정
                                        </a>
                                        <button class="delete-btn" onclick="deleteImage(<?php echo $image['no']; ?>)">
                                            <i class="fas fa-trash"></i> 삭제
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-data">
                                    <i class="fas fa-inbox"></i>
                                    <p>등록된 이미지가 없습니다.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- 페이지네이션 -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&tag=<?php echo urlencode($tag_filter); ?>" 
                           class="page-link">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&tag=<?php echo urlencode($tag_filter); ?>" 
                           class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&tag=<?php echo urlencode($tag_filter); ?>" 
                           class="page-link">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- 이미지 모달 -->
    <div id="imageModal" class="image-modal" onclick="closeImageModal()">
        <span class="modal-close">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>
    
    <script src="js/admin-mainimages.js"></script>
</body>
</html>

