<!-- 사이드바 -->
<div class="admin-sidebar">
    <div class="admin-profile">
        <div class="admin-avatar">
            <i class="fas fa-user-shield"></i>
        </div>
        <div class="admin-name"><?php echo htmlspecialchars($_SESSION['admin_id']); ?></div>
        <div class="admin-role">관리자</div>
    </div>
    
    <ul class="admin-menu">
        <li><a href="admin_main.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_main.php' ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i> 관리자 메인</a></li>
        <li><a href="admin_member.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_member.php' ? 'active' : ''; ?>"><i class="fas fa-users"></i> 회원관리 <i class="fas fa-chevron-right arrow"></i></a></li>
        <li class="has-submenu">
            <?php $isBoardActive = in_array(basename($_SERVER['PHP_SELF']), ['admin_posts.php','admin_comments.php','admin_notices.php']); ?>
            <a href="#" class="board-toggle <?php echo $isBoardActive ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-list"></i> 게시판관리 <i class="fas fa-chevron-right arrow"></i>
            </a>
            <ul class="submenu" style="display: <?php echo $isBoardActive ? 'block' : 'none'; ?>;">
                <li><a href="admin_posts.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_posts.php' ? 'active' : ''; ?>"><i class="fas fa-file-alt"></i> 게시글관리</a></li>
                <li><a href="admin_comments.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_comments.php' ? 'active' : ''; ?>"><i class="fas fa-comments"></i> 댓글관리</a></li>
                <li><a href="admin_notices.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_notices.php' ? 'active' : ''; ?>"><i class="fas fa-bullhorn"></i> 공지사항관리</a></li>
            </ul>
        </li>
        <li class="has-submenu">
            <?php $isContentActive = in_array(basename($_SERVER['PHP_SELF']), ['admin_contents.php','admin_content_write.php','admin_content_edit.php','admin_mainimages.php','admin_mainimage_write.php','admin_mainimage_edit.php']); ?>
            <a href="#" class="content-toggle <?php echo $isContentActive ? 'active' : ''; ?>">
                <i class="fas fa-folder-open"></i> 콘텐츠관리 <i class="fas fa-chevron-right arrow"></i>
            </a>
            <ul class="submenu" style="display: <?php echo $isContentActive ? 'block' : 'none'; ?>;">
                <li><a href="admin_contents.php" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['admin_contents.php','admin_content_write.php','admin_content_edit.php']) ? 'active' : ''; ?>"><i class="fas fa-cube"></i> 콘텐츠관리</a></li>
                <li><a href="admin_mainimages.php" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['admin_mainimages.php','admin_mainimage_write.php','admin_mainimage_edit.php']) ? 'active' : ''; ?>"><i class="fas fa-images"></i> 메인이미지관리</a></li>
            </ul>
        </li>
        <li class="has-submenu">
            <?php $isDataActive = in_array(basename($_SERVER['PHP_SELF']), ['admin_vr_data.php']); ?>
            <a href="#" class="data-toggle <?php echo $isDataActive ? 'active' : ''; ?>">
                <i class="fas fa-database"></i> 데이터관리 <i class="fas fa-chevron-right arrow"></i>
            </a>
            <ul class="submenu" style="display: <?php echo $isDataActive ? 'block' : 'none'; ?>;">
                <li><a href="admin_vr_data.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_vr_data.php' ? 'active' : ''; ?>"><i class="fas fa-vr-cardboard"></i> VR 데이터관리</a></li>
            </ul>
        </li>
    </ul>
    
    <!-- 로그아웃 버튼 -->
    <div class="admin-logout-section">
        <a href="admin_logout.php" class="admin-logout-btn">
            <i class="fas fa-sign-out-alt"></i> 로그아웃
        </a>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 게시판관리 토글
    var boardToggle = document.querySelector('.board-toggle');
    if (boardToggle) {
        boardToggle.addEventListener('click', function(e) {
            e.preventDefault();
            var submenu = this.parentElement.querySelector('.submenu');
            if (submenu) {
                var isOpen = submenu.style.display === 'block';
                submenu.style.display = isOpen ? 'none' : 'block';
            }
        });
    }
    
    // 콘텐츠관리 토글
    var contentToggle = document.querySelector('.content-toggle');
    if (contentToggle) {
        contentToggle.addEventListener('click', function(e) {
            e.preventDefault();
            var submenu = this.parentElement.querySelector('.submenu');
            if (submenu) {
                var isOpen = submenu.style.display === 'block';
                submenu.style.display = isOpen ? 'none' : 'block';
            }
        });
    }
    
    // 데이터관리 토글
    var dataToggle = document.querySelector('.data-toggle');
    if (dataToggle) {
        dataToggle.addEventListener('click', function(e) {
            e.preventDefault();
            var submenu = this.parentElement.querySelector('.submenu');
            if (submenu) {
                var isOpen = submenu.style.display === 'block';
                submenu.style.display = isOpen ? 'none' : 'block';
            }
        });
    }
});
</script>
