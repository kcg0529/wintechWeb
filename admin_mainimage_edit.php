<?php
session_start();
require_once 'DAO/MainImgDAO.php';

// 관리자 권한 확인
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// 이미지 ID 확인
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin_mainimages.php");
    exit();
}

$image_id = (int)$_GET['id'];
$image = MainImgDAO::getImageById($image_id);

if (!$image) {
    echo "<script>alert('이미지를 찾을 수 없습니다.'); location.href='admin_mainimages.php';</script>";
    exit();
}

$page_title = "메인이미지 수정";
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
                    <h1 class="admin-title">메인이미지 수정</h1>
                </div>
                <div class="admin-header-actions">
                    <button class="btn btn-secondary" onclick="location.href='admin_mainimages.php'">
                        <i class="fas fa-arrow-left"></i> 목록으로
                    </button>
                </div>
            </div>
            
            <div class="data-card">
                <form id="mainimageForm" method="POST" action="admin_mainimage_update.php" class="write-form">
                    <input type="hidden" name="id" value="<?php echo $image['no']; ?>">
                    
                    <div class="form-section">
                        <h2><i class="fas fa-info-circle"></i> 기본 정보</h2>
                        
                        <div class="form-group">
                            <label for="tag" class="form-label">태그 <span class="required">*</span></label>
                            <select id="tag" name="tag" required class="form-input">
                                <option value="">태그 선택</option>
                                <option value="slider" <?php echo $image['tag'] === 'slider' ? 'selected' : ''; ?>>slider (메인 슬라이더)</option>
                                <option value="shop" <?php echo $image['tag'] === 'shop' ? 'selected' : ''; ?>>shop (쇼핑 섹션)</option>
                            </select>
                            <small class="form-help">이미지가 표시될 위치를 선택하세요.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="title" class="form-label">제목 <span class="required">*</span></label>
                            <input type="text" id="title" name="title" required 
                                   value="<?php echo htmlspecialchars($image['title']); ?>"
                                   placeholder="이미지 제목을 입력하세요" class="form-input">
                            <small class="form-help">shop 태그: 제품명으로 사용됩니다.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="text" class="form-label"><span id="textLabel">설명</span> <span class="required" id="textRequired" style="display:none;">*</span></label>
                            <textarea id="text" name="text" rows="3" 
                                      placeholder="이미지 설명을 입력하세요" class="form-textarea"><?php echo htmlspecialchars($image['text']); ?></textarea>
                            <small class="form-help" id="textHelp">slider 태그: 슬라이더 오버레이 텍스트로 사용됩니다.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="img" class="form-label">이미지 파일명 <span class="required">*</span></label>
                            <input type="text" id="img" name="img" required 
                                   value="<?php echo htmlspecialchars($image['img']); ?>"
                                   placeholder="예: slide1.jpg 또는 win01.jpg" class="form-input">
                            <small class="form-help">images/ 폴더에 업로드된 이미지 파일명을 입력하세요.</small>
                        </div>
                        
                        <?php if (!empty($image['img'])): ?>
                            <div class="form-group">
                                <label class="form-label">현재 이미지 미리보기</label>
                                <div class="image-preview">
                                    <img src="images/<?php echo htmlspecialchars($image['img']); ?>" 
                                         alt="<?php echo htmlspecialchars($image['title']); ?>"
                                         style="max-width: 300px; max-height: 300px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> 수정 저장
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="location.href='admin_mainimages.php'">
                            <i class="fas fa-times"></i> 취소
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="js/admin-mainimage-write.js"></script>
</body>
</html>

