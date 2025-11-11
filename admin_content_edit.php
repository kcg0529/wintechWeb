<?php
session_start();
require_once 'admin_functions.php';
checkAdminSession();

require_once 'DAO/ContentDAO.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: admin_contents.php');
    exit;
}

$content = ContentDAO::getContentById($id);

if (!$content) {
    header('Location: admin_contents.php');
    exit;
}

$page_title = '콘텐츠 수정 - 행복운동센터';
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
    <link rel="stylesheet" href="css/admin-content-write.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>
        <div class="admin-main">
            <div class="admin-header">
                <h1 class="admin-title">콘텐츠 수정</h1>
                <a href="admin_contents.php" class="btn btn-secondary">
                    <i class="fas fa-list"></i> 목록으로
                </a>
            </div>
            
            <div class="write-form">
                <form id="contentForm" method="POST" action="admin_content_update.php">
                    <input type="hidden" name="id" value="<?php echo $content['no']; ?>">
                    
                    <div class="form-group">
                        <label for="tag" class="form-label">
                            태그 <span class="required">*</span>
                        </label>
                        <select id="tag" name="tag" class="form-input" required>
                            <option value="">태그 선택</option>
                            <option value="VR" <?php echo $content['tag'] === 'VR' ? 'selected' : ''; ?>>VR</option>
                            <option value="AR" <?php echo $content['tag'] === 'AR' ? 'selected' : ''; ?>>AR</option>
                            <option value="게임" <?php echo $content['tag'] === '게임' ? 'selected' : ''; ?>>게임</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="title" class="form-label">
                            제목 <span class="required">*</span>
                        </label>
                        <input type="text" id="title" name="title" class="form-input" 
                               value="<?php echo htmlspecialchars($content['title']); ?>" 
                               placeholder="콘텐츠 제목을 입력하세요" maxlength="200" required>
                        <div class="char-count">
                            <span id="titleCount"><?php echo mb_strlen($content['title']); ?></span>/200
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="path" class="form-label">
                            경로 <span class="required">*</span>
                        </label>
                        <input type="text" id="path" name="path" class="form-input" 
                               value="<?php echo htmlspecialchars($content['path']); ?>" 
                               placeholder="예: index.php?content=unity_road 또는 road/" required>
                        <small class="form-help">폴더명 형식(예: road/) 또는 index.php?content= 형식으로 입력하세요</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="img" class="form-label">
                            이미지 파일명
                        </label>
                        <input type="text" id="img" name="img" class="form-input" 
                               value="<?php echo htmlspecialchars($content['img']); ?>" 
                               placeholder="예: road.jpg (images 폴더에 있는 파일명)">
                        <small class="form-help">images 폴더에 있는 이미지 파일명만 입력하세요</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> 저장
                        </button>
                        <a href="admin_contents.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> 취소
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="js/admin-content-write.js"></script>
</body>
</html>

