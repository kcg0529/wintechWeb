<?php
require_once 'mysqli_con.php';

class PostDAO {
    
    /**
     * 전체 게시글 수 조회 (검색 조건 포함)
     */
    public static function getTotalPosts($search_condition = '') {
        $conn = getConnection();
        $count_query = "SELECT COUNT(*) as total FROM wintech_community $search_condition";
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
     * 게시글 목록 조회 (페이지네이션 적용)
     */
    public static function getPosts($search_condition = '', $limit = 20, $offset = 0) {
        $conn = getConnection();
        $post_query = "SELECT no, title, name, time, view_count FROM wintech_community $search_condition ORDER BY no DESC LIMIT $limit OFFSET $offset";
        $post_result = mysqli_query($conn, $post_query);
        $posts = [];
        if ($post_result) {
            while ($row = mysqli_fetch_assoc($post_result)) {
                $posts[] = $row;
            }
        }
        mysqli_close($conn);
        return $posts;
    }
    
    /**
     * 특정 게시글 조회
     */
    public static function getPostById($post_id) {
        $conn = getConnection();
        $query = "SELECT * FROM wintech_community WHERE no = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $post_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $post = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $post;
    }
    
    /**
     * 게시글 조회수 증가
     */
    public static function incrementViewCount($post_id) {
        $conn = getConnection();
        $query = "UPDATE wintech_community SET view_count = view_count + 1 WHERE no = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $post_id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result;
    }
    
    /**
     * 게시글 삭제 (댓글도 함께 삭제)
     */
    public static function deletePost($post_id) {
        $conn = getConnection();
        mysqli_autocommit($conn, false);
        
        try {
            // 먼저 댓글 삭제
            $comment_query = "DELETE FROM wintech_comment WHERE post_no = ?";
            $comment_stmt = mysqli_prepare($conn, $comment_query);
            mysqli_stmt_bind_param($comment_stmt, "i", $post_id);
            mysqli_stmt_execute($comment_stmt);
            mysqli_stmt_close($comment_stmt);
            
            // 게시글 삭제
            $post_query = "DELETE FROM wintech_community WHERE no = ?";
            $post_stmt = mysqli_prepare($conn, $post_query);
            mysqli_stmt_bind_param($post_stmt, "i", $post_id);
            $result = mysqli_stmt_execute($post_stmt);
            mysqli_stmt_close($post_stmt);
            
            if ($result) {
                mysqli_commit($conn);
            } else {
                mysqli_rollback($conn);
            }
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $result = false;
        }
        
        mysqli_close($conn);
        return $result;
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
}
?>





