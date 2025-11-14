<?php
require_once 'mysqli_con.php';

class CycleDAO {
    
    /**
     * 전체 사이클 데이터 수 조회 (검색 조건 포함)
     */
    public static function getTotalCycles($search_condition = '') {
        $conn = getConnection();
        $count_query = "SELECT COUNT(*) as total FROM cycle_distance $search_condition";
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
     * 사이클 데이터 목록 조회 (페이지네이션 적용)
     */
    public static function getCycles($search_condition = '', $limit = 10, $offset = 0) {
        $conn = getConnection();
        $query = "SELECT * FROM cycle_distance $search_condition ORDER BY SaveTime DESC LIMIT $limit OFFSET $offset";
        $result = mysqli_query($conn, $query);
        $cycles = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $cycles[] = $row;
            }
        }
        mysqli_close($conn);
        return $cycles;
    }
    
    /**
     * 특정 사이클 데이터 조회 (SaveTime과 name으로 식별)
     */
    public static function getCycleByIdentifier($save_time, $name) {
        $conn = getConnection();
        $query = "SELECT * FROM cycle_distance WHERE SaveTime = ? AND name = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $save_time, $name);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $cycle = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $cycle;
    }
    
    /**
     * 사이클 데이터 삭제 (SaveTime과 name으로 식별)
     */
    public static function deleteCycle($save_time, $name) {
        $conn = getConnection();
        $query = "DELETE FROM cycle_distance WHERE SaveTime = ? AND name = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $save_time, $name);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result;
    }
    
    /**
     * 사이클 데이터 통계 조회
     */
    public static function getCycleStats() {
        $conn = getConnection();
        $stats = [];
        
        // 총 세션 수
        $total_query = "SELECT COUNT(*) as total FROM cycle_distance";
        $total_result = mysqli_query($conn, $total_query);
        $stats['total_sessions'] = mysqli_fetch_assoc($total_result)['total'];
        
        // 총 사용자 수
        $users_query = "SELECT COUNT(DISTINCT name) as total FROM cycle_distance";
        $users_result = mysqli_query($conn, $users_query);
        $stats['total_users'] = mysqli_fetch_assoc($users_result)['total'];
        
        // 평균 운동 시간
        $avg_time_query = "SELECT AVG(exercise_time) as avg_time FROM cycle_distance";
        $avg_time_result = mysqli_query($conn, $avg_time_query);
        $stats['avg_exercise_time'] = mysqli_fetch_assoc($avg_time_result)['avg_time'];
        
        // 총 거리
        $total_distance_query = "SELECT SUM(distance) as total_distance FROM cycle_distance";
        $total_distance_result = mysqli_query($conn, $total_distance_query);
        $stats['total_distance'] = mysqli_fetch_assoc($total_distance_result)['total_distance'];
        
        mysqli_close($conn);
        return $stats;
    }
    
    /**
     * 테이블 존재 여부 확인
     */
    public static function tableExists() {
        $conn = getConnection();
        $query = "SHOW TABLES LIKE 'cycle_distance'";
        $result = mysqli_query($conn, $query);
        $exists = mysqli_num_rows($result) > 0;
        mysqli_close($conn);
        return $exists;
    }
}
?>





