<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_login.php');
    exit;
}

$admin_id = $_POST['admin_id'] ?? '';
$admin_pw = $_POST['admin_pw'] ?? '';

if (empty($admin_id) || empty($admin_pw)) {
    header('Location: admin_login.php?error=1');
    exit;
}

try {
    require_once 'DAO/mysqli_con.php';
    
    $conn = getConnection();
    
    // admin_account 테이블에서 관리자 정보 확인
    $stmt = mysqli_prepare($conn, "SELECT id, pw FROM admin_account WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "s", $admin_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // 비밀번호 확인 (평문 비교)
        if ($admin_pw === $row['pw']) {
            // 로그인 성공
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin_id;
            
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            
            header('Location: admin_main.php');
            exit;
        } else {
            // 비밀번호 불일치
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            
            header('Location: admin_login.php?error=1');
            exit;
        }
    } else {
        // 아이디 없음
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        
        header('Location: admin_login.php?error=1');
        exit;
    }
    
} catch (Exception $e) {
    error_log("Admin login error: " . $e->getMessage());
    header('Location: admin_login.php?error=1');
    exit;
}
?>















