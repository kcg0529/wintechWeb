<?php
require_once 'mysqli_con.php';

class NoticeDAO {
    
    /**
     * 전체 공지사항 수 조회 (검색 조건 포함)
     */
    public static function getTotalNotices($search_condition = '') {
        $conn = getConnection();
        $count_query = "SELECT COUNT(*) as total FROM wintech_notice $search_condition";
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
     * 공지사항 목록 조회 (페이지네이션 적용)
     */
    public static function getNotices($search_condition = '', $limit = 20, $offset = 0) {
        $conn = getConnection();
        $query = "SELECT no, title, content, date, view_count FROM wintech_notice $search_condition ORDER BY no DESC LIMIT $limit OFFSET $offset";
        $result = mysqli_query($conn, $query);
        $notices = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $notices[] = $row;
            }
        }
        mysqli_close($conn);
        return $notices;
    }
    
    /**
     * 특정 공지사항 조회
     */
    public static function getNoticeById($notice_id) {
        $conn = getConnection();
        $query = "SELECT * FROM wintech_notice WHERE no = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $notice_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $notice = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $notice;
    }
    
    /**
     * 공지사항 작성
     */
    public static function createNotice($title, $content) {
        $conn = getConnection();
        $query = "INSERT INTO wintech_notice (title, content, date, view_count) VALUES (?, ?, NOW(), 0)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $title, $content);
        $result = mysqli_stmt_execute($stmt);
        $notice_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result ? $notice_id : false;
    }
    
    /**
     * 공지사항 삭제
     */
    public static function deleteNotice($notice_id) {
        $conn = getConnection();
        $query = "DELETE FROM wintech_notice WHERE no = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $notice_id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result;
    }
    
    /**
     * 조회수 증가
     */
    public static function incrementViewCount($notice_id) {
        $conn = getConnection();
        $query = "UPDATE wintech_notice SET view_count = view_count + 1 WHERE no = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $notice_id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result;
    }
    
    /**
     * 최근 공지사항 조회 (관리자 메인용)
     */
    public static function getRecentNotices($limit = 5) {
        $conn = getConnection();
        $query = "SELECT no, title, date, view_count FROM wintech_notice ORDER BY no DESC LIMIT ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $notices = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $notices[] = $row;
        }
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $notices;
    }
    
    /**
     * 페이지네이션을 포함한 공지사항 조회 (기존 페이지용)
     */
    public static function getNoticesWithPagination($search_condition = '', $limit = 10, $offset = 0) {
        $conn = getConnection();
        $query = "SELECT no, title, content, date, view_count FROM wintech_notice $search_condition ORDER BY no DESC LIMIT $limit OFFSET $offset";
        $result = mysqli_query($conn, $query);
        $notices = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $notices[] = $row;
            }
        }
        mysqli_close($conn);
        return $notices;
    }
    
    /**
     * 검색 조건에 따른 공지사항 수 조회 (기존 페이지용)
     */
    public static function getSearchNoticeCount($search_condition = '') {
        $conn = getConnection();
        $count_query = "SELECT COUNT(*) as total FROM wintech_notice $search_condition";
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
     * 공지사항 제목에서 태그 파싱 (기존 페이지용)
     */
    public static function parseNoticeTitle($title) {
        if (preg_match('/^\[([^\]]+)\]\s*(.+)/', $title, $matches)) {
            return [
                'tag' => '[' . $matches[1] . ']',
                'title' => $matches[2]
            ];
        }
        return [
            'tag' => '',
            'title' => $title
        ];
    }
    
    /**
     * 날짜 포맷팅 (기존 페이지용)
     */
    public static function formatDate($date) {
        return date('Y-m-d', strtotime($date));
    }
    
    /**
     * 이전 공지사항 조회
     */
    public static function getPreviousNotice($current_no, $current_date) {
        $conn = getConnection();
        $query = "SELECT * FROM wintech_notice WHERE (date < ? OR (date = ? AND no < ?)) ORDER BY date DESC, no DESC LIMIT 1";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssi", $current_date, $current_date, $current_no);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $notice = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $notice;
    }
    
    /**
     * 다음 공지사항 조회
     */
    public static function getNextNotice($current_no, $current_date) {
        $conn = getConnection();
        $query = "SELECT * FROM wintech_notice WHERE (date > ? OR (date = ? AND no > ?)) ORDER BY date ASC, no ASC LIMIT 1";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssi", $current_date, $current_date, $current_no);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $notice = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $notice;
    }
}
?>