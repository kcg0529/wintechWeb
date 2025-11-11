<?php
require_once 'mysqli_con.php';

class MemberDAO {
    
    /**
     * 전체 회원 수 조회 (검색 조건 포함)
     */
    public static function getTotalMembers($search_condition = '') {
        $conn = getConnection();
        $count_query = "SELECT COUNT(*) as total FROM wintech_account $search_condition";
        $count_result = mysqli_query($conn, $count_query);
        $total = 0;
        if ($count_result) {
            $row = mysqli_fetch_assoc($count_result);
            $total = $row['total'];
        }
        mysqli_close($conn);
        return $total;
    }
    
    /**
     * 회원 목록 조회 (페이지네이션 적용)
     */
    public static function getMembers($search_condition = '', $limit = 10, $offset = 0) {
        $conn = getConnection();
        $member_query = "SELECT account, birthday, gender, height, weight, note FROM wintech_account $search_condition ORDER BY no DESC LIMIT $limit OFFSET $offset";
        $member_result = mysqli_query($conn, $member_query);
        $members = [];
        if ($member_result) {
            while ($row = mysqli_fetch_assoc($member_result)) {
                $members[] = $row;
            }
        }
        mysqli_close($conn);
        return $members;
    }
    
    /**
     * 특정 회원 정보 조회
     */
    public static function getMemberByAccount($account) {
        $conn = getConnection();
        $query = "SELECT account, birthday, gender, height, weight, note FROM wintech_account WHERE account = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $account);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $member = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $member;
    }
    
    /**
     * 회원 정보 업데이트
     */
    public static function updateMember($account, $birthday, $gender, $height, $weight, $note) {
        $conn = getConnection();
        $query = "UPDATE wintech_account SET birthday = ?, gender = ?, height = ?, weight = ?, note = ? WHERE account = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssss", $birthday, $gender, $height, $weight, $note, $account);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result;
    }
    
    /**
     * 회원 삭제
     */
    public static function deleteMember($account) {
        $conn = getConnection();
        $query = "DELETE FROM wintech_account WHERE account = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $account);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result;
    }
}
?>





