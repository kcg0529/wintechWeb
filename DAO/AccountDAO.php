<?php
require_once __DIR__ . '/mysqli_con.php';

class AccountDAO {
    private $conn;
    
    public function __construct() {
        $this->conn = getConnection();
    }
    
    // 이메일 중복 체크
    public function checkEmailExists($email) {
        $stmt = mysqli_prepare($this->conn, "SELECT COUNT(*) as count FROM wintech_account WHERE account = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return $row['count'] > 0;
    }
    
    // 계정 생성
    public function createAccount($email, $password) {
        // 평문으로 저장 (보안상 권장하지 않음)
        $stmt = mysqli_prepare($this->conn, "INSERT INTO wintech_account (account, password) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ss", $email, $password);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    // 기존 평문 비밀번호를 해싱으로 변환
    public function hashExistingPasswords() {
        $stmt = mysqli_prepare($this->conn, "SELECT account, password FROM wintech_account WHERE password NOT LIKE '$2y$%'");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $updated = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $email = $row['account'];
            $plainPassword = $row['password'];
            $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
            
            $updateStmt = mysqli_prepare($this->conn, "UPDATE wintech_account SET password = ? WHERE account = ?");
            mysqli_stmt_bind_param($updateStmt, "ss", $hashedPassword, $email);
            
            if (mysqli_stmt_execute($updateStmt)) {
                $updated++;
            }
            mysqli_stmt_close($updateStmt);
        }
        
        mysqli_stmt_close($stmt);
        return $updated;
    }
    
    // 로그인 검증
    public function validateLogin($email, $password) {
        $stmt = mysqli_prepare($this->conn, "SELECT password FROM wintech_account WHERE account = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            // 평문 비교 (보안상 권장하지 않음)
            $isMatch = $password === $row['password'];
            
            // 디버깅 정보 (나중에 제거)
            error_log("AccountDAO validateLogin - Email: $email");
            error_log("AccountDAO validateLogin - Input password: '$password'");
            error_log("AccountDAO validateLogin - DB password: '{$row['password']}'");
            error_log("AccountDAO validateLogin - Match: " . ($isMatch ? 'true' : 'false'));
            
            return $isMatch;
        }
        
        mysqli_stmt_close($stmt);
        error_log("AccountDAO validateLogin - No user found for email: $email");
        return false;
    }
    
    // 사용자 생년월일 정보 가져오기
    public function getUserBirthday($email) {
        $stmt = mysqli_prepare($this->conn, "SELECT birthday FROM wintech_account WHERE account = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['birthday'];
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    public function __destruct() {
        if ($this->conn) {
            mysqli_close($this->conn);
        }
    }
}
?>
