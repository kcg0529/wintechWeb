<?php
session_start();
require_once 'admin_functions.php';
checkAdminSession();

$page_title = '공지사항 작성 - 행복운동센터';
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
    <link rel="stylesheet" href="css/admin-notice-write.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>
        <div class="admin-main">
            <div class="admin-header">
                <h1 class="admin-title">공지사항 작성</h1>
                <a href="admin_notices.php" class="btn btn-secondary">
                    <i class="fas fa-list"></i> 목록으로
                </a>
            </div>
            
            <div class="write-form">
                <form id="noticeForm" method="POST" action="admin_notice_write_process.php">
                    <div class="form-group">
                        <label for="title" class="form-label">
                            제목 <span class="required">*</span>
                        </label>
                        <input type="text" id="title" name="title" class="form-input" 
                               placeholder="공지사항 제목을 입력하세요" maxlength="200" required>
                        <div class="char-count">
                            <span id="titleCount">0</span>/200
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            태그 <span class="required">*</span>
                        </label>
                        <div class="tag-selection">
                            <label class="tag-option">
                                <input type="radio" name="tag" value="[공지사항]" checked>
                                <span class="tag-label">[공지사항]</span>
                            </label>
                            <label class="tag-option">
                                <input type="radio" name="tag" value="[게임]">
                                <span class="tag-label">[게임]</span>
                            </label>
                            <label class="tag-option">
                                <input type="radio" name="tag" value="[AR]">
                                <span class="tag-label">[AR]</span>
                            </label>
                            <label class="tag-option">
                                <input type="radio" name="tag" value="[VR]">
                                <span class="tag-label">[VR]</span>
                            </label>
                            <label class="tag-option">
                                <input type="radio" name="tag" value="[커뮤니티]">
                                <span class="tag-label">[커뮤니티]</span>
                            </label>
                            <label class="tag-option">
                                <input type="radio" name="tag" value="[쇼핑]">
                                <span class="tag-label">[쇼핑]</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="content" class="form-label">
                            내용 <span class="required">*</span>
                        </label>
                        <textarea id="content" name="content" class="form-textarea" 
                                  placeholder="공지사항 내용을 입력하세요" required></textarea>
                        <div class="char-count">
                            <span id="contentCount">0</span>/5000
                        </div>
                        <div class="form-help">
                            <i class="fas fa-info-circle"></i> 
                            HTML 태그는 사용할 수 없습니다. 줄바꿈은 자동으로 적용됩니다.
                        </div>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="button" class="btn btn-secondary" id="cancelBtn">
                            <i class="fas fa-arrow-left"></i> 취소
                        </button>
                        <button type="button" class="btn btn-danger" id="resetBtn">
                            <i class="fas fa-undo"></i> 초기화
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> 작성완료
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="js/admin-notice-write.js"></script>
</body>
</html>


