<?php
session_start();
require_once 'admin_functions.php';

// 관리자 세션 체크
checkAdminSession();

$account = isset($_GET['account']) ? trim($_GET['account']) : '';
$member = null;
$error_message = '';

if (empty($account)) {
    header('Location: admin_member.php');
    exit;
}

// 회원 정보 조회
try {
    require_once 'DAO/mysqli_con.php';
    $conn = getConnection();
    
    $query = "SELECT account, password, birthday, gender, height, weight, note FROM wintech_account WHERE account = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $account);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        header('Location: admin_member.php');
        exit;
    }
    
    $member = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    
} catch (Exception $e) {
    error_log("Admin member edit error: " . $e->getMessage());
    $error_message = "회원 정보를 불러오는 중 오류가 발생했습니다.";
}

$page_title = '회원 정보 수정 - 행복운동센터';
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
            <a href="admin_member.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> 회원 목록으로 돌아가기
            </a>
            
            <div class="admin-header">
                <h1 class="admin-title">회원 정보 수정</h1>
            </div>

            <?php if ($error_message): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($member): ?>
                <div class="member-edit-form">
                    <form id="memberEditForm" method="POST" action="admin_member_update.php">
                        <input type="hidden" name="account" value="<?php echo htmlspecialchars($member['account']); ?>">
                        
                        <div class="form-group">
                            <label for="account">회원 아이디</label>
                            <input type="text" id="account" name="account_display" value="<?php echo htmlspecialchars($member['account']); ?>" readonly style="background-color: #f8f9fa;">
                        </div>

                        <div class="form-group">
                            <label for="password">패스워드</label>
                            <input type="text" id="password" value="<?php echo htmlspecialchars($member['password']); ?>" readonly style="background-color: #f8f9fa;">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="birthday">생년월일</label>
                                <input type="date" id="birthday" name="birthday" value="<?php echo htmlspecialchars($member['birthday'] ?: ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="gender">성별</label>
                                <select id="gender" name="gender">
                                    <option value="">선택하세요</option>
                                    <option value="1" <?php echo $member['gender'] == '1' ? 'selected' : ''; ?>>남성</option>
                                    <option value="2" <?php echo $member['gender'] == '2' ? 'selected' : ''; ?>>여성</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="height">신장 (cm)</label>
                                <input type="text" id="height" name="height" value="<?php echo htmlspecialchars($member['height'] ?: ''); ?>" placeholder="예: 170">
                            </div>
                            
                            <div class="form-group">
                                <label for="weight">체중 (kg)</label>
                                <input type="text" id="weight" name="weight" value="<?php echo htmlspecialchars($member['weight'] ?: ''); ?>" placeholder="예: 65">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="note">건강상태</label>
                            <textarea id="note" name="note" placeholder="건강상태를 입력하세요"><?php echo htmlspecialchars($member['note'] ?: ''); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <a href="admin_member.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> 취소
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 수정 완료
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="js/admin-member-edit.js"></script>
</body>
</html>
