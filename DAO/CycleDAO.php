<?php
require_once 'mysqli_con.php';

class CycleDAO {
    
    /**
     * 전체 사이클 데이터 수 조회 (검색 조건 포함)
     */
    public static function getTotalCycles($search_condition = '') {
        $conn = getConnection();
        if (!$conn) {
            error_log("CycleDAO getTotalCycles: Database connection failed");
            return 0;
        }
        $count_query = "SELECT COUNT(*) as total FROM BedBike $search_condition";
        error_log("CycleDAO getTotalCycles: Query = " . $count_query);
        $count_result = mysqli_query($conn, $count_query);
        $total = 0;
        if ($count_result) {
            $row = mysqli_fetch_assoc($count_result);
            $total = $row['total'];
        } else {
            error_log("CycleDAO getTotalCycles: Query failed - " . mysqli_error($conn));
        }
        mysqli_close($conn);
        return $total;
    }
    
    /**
     * 사이클 데이터 목록 조회 (페이지네이션 적용)
     */
    public static function getCycles($search_condition = '', $limit = 10, $offset = 0) {
        $conn = getConnection();
        if (!$conn) {
            error_log("CycleDAO getCycles: Database connection failed");
            return [];
        }
        $query = "SELECT * FROM BedBike $search_condition ORDER BY SaveTime DESC LIMIT $limit OFFSET $offset";
        error_log("CycleDAO getCycles: Query = " . $query);
        $result = mysqli_query($conn, $query);
        $cycles = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $cycles[] = $row;
            }
        } else {
            error_log("CycleDAO getCycles: Query failed - " . mysqli_error($conn));
        }
        mysqli_close($conn);
        return $cycles;
    }
    
    /**
     * 특정 사이클 데이터 조회 (SaveTime과 email로 식별)
     */
    public static function getCycleByIdentifier($saveTime, $email) {
        $conn = getConnection();
        $query = "SELECT * FROM BedBike WHERE SaveTime = ? AND email = ?";
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            mysqli_close($conn);
            return null;
        }
        mysqli_stmt_bind_param($stmt, "ss", $saveTime, $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $cycle = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $cycle;
    }
    
    /**
     * 사이클 데이터 삭제 (SaveTime과 email로 식별)
     */
    public static function deleteCycle($saveTime, $email) {
        $conn = getConnection();
        $query = "DELETE FROM BedBike WHERE SaveTime = ? AND email = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            mysqli_close($conn);
            return false;
        }
        mysqli_stmt_bind_param($stmt, "ss", $saveTime, $email);
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
        if (!$conn) {
            return [
                'total_sessions' => 0,
                'total_users' => 0,
                'avg_exercise_time' => 0,
                'total_distance' => 0
            ];
        }
        
        $stats = [];
        
        // 총 세션 수
        $total_query = "SELECT COUNT(*) as total FROM BedBike";
        $total_result = mysqli_query($conn, $total_query);
        if ($total_result) {
            $stats['total_sessions'] = mysqli_fetch_assoc($total_result)['total'] ?? 0;
        } else {
            $stats['total_sessions'] = 0;
        }
        
        // 총 사용자 수 (email 컬럼 사용)
        $users_query = "SELECT COUNT(DISTINCT email) as total FROM BedBike";
        $users_result = mysqli_query($conn, $users_query);
        if ($users_result) {
            $stats['total_users'] = mysqli_fetch_assoc($users_result)['total'] ?? 0;
        } else {
            $stats['total_users'] = 0;
        }
        
        // 평균 운동 시간
        $avg_time_query = "SELECT AVG(exercise_hours) as avg_time FROM BedBike";
        $avg_time_result = mysqli_query($conn, $avg_time_query);
        if ($avg_time_result) {
            $avg_row = mysqli_fetch_assoc($avg_time_result);
            $stats['avg_exercise_time'] = $avg_row['avg_time'] ?? 0;
        } else {
            $stats['avg_exercise_time'] = 0;
        }
        
        // 총 거리 (distance > 0 조건 추가)
        $total_distance_query = "SELECT SUM(distance) as total_distance FROM BedBike WHERE distance > 0";
        $total_distance_result = mysqli_query($conn, $total_distance_query);
        if ($total_distance_result) {
            $distance_row = mysqli_fetch_assoc($total_distance_result);
            $stats['total_distance'] = $distance_row['total_distance'] ?? 0;
        } else {
            $stats['total_distance'] = 0;
        }
        
        mysqli_close($conn);
        return $stats;
    }
    
    /**
     * 테이블 존재 여부 확인
     */
    public static function tableExists() {
        $conn = getConnection();
        $query = "SHOW TABLES LIKE 'BedBike'";
        $result = mysqli_query($conn, $query);
        $exists = mysqli_num_rows($result) > 0;
        mysqli_close($conn);
        return $exists;
    }
}
?>





