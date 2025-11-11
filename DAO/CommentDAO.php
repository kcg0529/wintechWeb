<?php
require_once 'mysqli_con.php';

class CommentDAO {
    
    /**
     * 전체 댓글 수 조회 (검색 조건 포함)
     */
    public static function getTotalComments($search_condition = '') {
        $conn = getConnection();
        $count_query = "SELECT COUNT(*) as total FROM wintech_comment $search_condition";
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
     * 댓글 목록 조회 (페이지네이션 적용)
     */
    public static function getComments($search_condition = '', $limit = 20, $offset = 0) {
        $conn = getConnection();
        $comment_query = "SELECT no, name, content, post_no, date FROM wintech_comment $search_condition ORDER BY no DESC LIMIT $limit OFFSET $offset";
        $comment_result = mysqli_query($conn, $comment_query);
        $comments = [];
        if ($comment_result) {
            while ($row = mysqli_fetch_assoc($comment_result)) {
                $comments[] = $row;
            }
        }
        mysqli_close($conn);
        return $comments;
    }
    
    /**
     * 특정 게시글의 댓글 조회
     */
    public static function getCommentsByPostId($post_id) {
        $conn = getConnection();
        $query = "SELECT * FROM wintech_comment WHERE post_no = ? ORDER BY date ASC";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $post_id);
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
     * 댓글 작성
     */
    public static function createComment($post_id, $name, $content) {
        $conn = getConnection();
        $query = "INSERT INTO wintech_comment (post_no, name, content, date) VALUES (?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "iss", $post_id, $name, $content);
        $result = mysqli_stmt_execute($stmt);
        $comment_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result ? $comment_id : false;
    }
    
    /**
     * 댓글 삭제
     */
    public static function deleteComment($comment_id) {
        $conn = getConnection();
        $query = "DELETE FROM wintech_comment WHERE no = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $comment_id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result;
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
}
?>