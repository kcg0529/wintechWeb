<?php
require_once 'mysqli_con.php';

class AdminDAO {
    
    /**
     * 신규가입회원 조회 (관리자 메인용)
     */
    public static function getRecentMembers($limit = 5) {
        $conn = getConnection();
        $query = "SELECT account, birthday, gender, height, weight, note FROM wintech_account ORDER BY no DESC LIMIT ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $members = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $members[] = $row;
        }
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $members;
    }
    
    /**
     * 총 회원 수 조회
     */
    public static function getTotalMembers() {
        $conn = getConnection();
        $query = "SELECT COUNT(*) as total FROM wintech_account";
        $result = mysqli_query($conn, $query);
        $total = 0;
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $total = $row['total'];
        }
        mysqli_close($conn);
        return $total;
    }
    
    /**
     * 최근 게시글 조회 (관리자 메인용)
     */
    public static function getRecentPosts($limit = 5) {
        $conn = getConnection();
        $query = "SELECT no, title, name, time, view_count FROM wintech_community ORDER BY no DESC LIMIT ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $posts = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $posts[] = $row;
        }
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $posts;
    }
    
    /**
     * 최근 댓글 조회 (관리자 메인용)
     */
    public static function getRecentComments($limit = 5) {
        $conn = getConnection();
        $query = "SELECT name, content, date, post_no FROM wintech_comment ORDER BY no DESC LIMIT ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $comments = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $comments[] = $row;
        }
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $comments;
    }
    
    /**
     * 관리자 로그인 확인
     */
    public static function checkAdminLogin($admin_id, $admin_pw) {
        $conn = getConnection();
        $query = "SELECT id FROM admin_account WHERE id = ? AND pw = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $admin_id, $admin_pw);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $admin = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $admin !== null;
    }
}
?>





