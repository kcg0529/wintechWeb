<?php
/**
 * 프로필 정보 완성도 확인 및 리다이렉트 함수
 * birthday가 NULL이면 프로필 수정 페이지로 리다이렉트
 */
function checkProfileCompletion() {
    // 로그인 확인
    if (!isset($_SESSION['email'])) {
        return; // 로그인하지 않은 사용자는 체크하지 않음
    }
    
    include_once 'DAO/mysqli_con.php';
    
    try {
        $conn = getConnection();
        $email = $_SESSION['email'];
        
        // 사용자 정보 확인 (birthday가 NULL이면 미완성)
        $stmt = mysqli_prepare($conn, "SELECT birthday FROM wintech_account WHERE account = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            if ($row['birthday'] === null) {
                // 정보가 미완성이면 프로필 수정 페이지로 리다이렉트
                header('Location: edit_profile.php?required=true');
                exit;
            }
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        
    } catch (Exception $e) {
        // 오류 발생 시 로그만 남기고 계속 진행
        error_log("Profile check error: " . $e->getMessage());
    }
}
?>
