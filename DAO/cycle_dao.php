<?php
require_once 'mysqli_con.php';

class CycleDAO {
    
    /**
     * "분 : 초" 형식을 초로 변환
     */
    public static function convertTimeFormatToSeconds($timeFormat) {
        // "60 : 30" -> 3630초
        if (empty($timeFormat)) return 0;
        
        // "분 : 초" 형식 파싱
        if (preg_match('/^(\d+)\s*:\s*(\d+)$/', $timeFormat, $matches)) {
            $minutes = (int)$matches[1];
            $seconds = (int)$matches[2];
            return ($minutes * 60) + $seconds;
        }
        
        // 숫자만 있는 경우 (기존 데이터 호환)
        if (is_numeric($timeFormat)) {
            return (int)$timeFormat;
        }
        
        return 0;
    }
    
    /**
     * 초를 "분 : 초" 형식으로 변환
     */
    public static function convertSecondsToTimeFormat($seconds) {
        // 3630초 -> "60 : 30"
        $seconds = (int)$seconds;
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        return sprintf("%d : %02d", $minutes, $remainingSeconds);
    }
    
    // 일별 데이터 가져오기
    public static function getDailyData($email, $date) {
        try {
            $conn = getConnection();
            $stmt = mysqli_prepare($conn, 
                "SELECT exercise_time, average_velocity, distance 
                 FROM cycle_distance 
                 WHERE name = ? AND DATE(SaveTime) = ?"
            );
            mysqli_stmt_bind_param($stmt, "ss", $email, $date);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = [
                    'exercise_time' => self::convertTimeFormatToSeconds($row['exercise_time']), // "분 : 초"를 초로 변환
                    'average_velocity' => $row['average_velocity'], // m/s 단위
                    'distance' => $row['distance'] // 미터 단위
                ];
            }
            
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            
            return $data;
        } catch (Exception $e) {
            error_log("CycleDAO getDailyData error: " . $e->getMessage());
            return [];
        }
    }
    
    // 주별 데이터 가져오기 (일요일~토요일)
    public static function getWeeklyData($email, $startDate, $endDate) {
        try {
            $conn = getConnection();
            $stmt = mysqli_prepare($conn, 
                "SELECT exercise_time, average_velocity, distance 
                 FROM cycle_distance 
                 WHERE name = ? AND DATE(SaveTime) BETWEEN ? AND ?"
            );
            mysqli_stmt_bind_param($stmt, "sss", $email, $startDate, $endDate);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = [
                    'exercise_time' => self::convertTimeFormatToSeconds($row['exercise_time']), // "분 : 초"를 초로 변환
                    'average_velocity' => $row['average_velocity'], // m/s 단위
                    'distance' => $row['distance'] // 미터 단위
                ];
            }
            
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            
            return $data;
        } catch (Exception $e) {
            error_log("CycleDAO getWeeklyData error: " . $e->getMessage());
            return [];
        }
    }
    
    // 월별 데이터 가져오기
    public static function getMonthlyData($email, $year, $month) {
        try {
            $conn = getConnection();
            $stmt = mysqli_prepare($conn, 
                "SELECT exercise_time, average_velocity, distance 
                 FROM cycle_distance 
                 WHERE name = ? AND YEAR(SaveTime) = ? AND MONTH(SaveTime) = ?"
            );
            mysqli_stmt_bind_param($stmt, "sii", $email, $year, $month);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = [
                    'exercise_time' => self::convertTimeFormatToSeconds($row['exercise_time']), // "분 : 초"를 초로 변환
                    'average_velocity' => $row['average_velocity'], // m/s 단위
                    'distance' => $row['distance'] // 미터 단위
                ];
            }
            
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            
            return $data;
        } catch (Exception $e) {
            error_log("CycleDAO getMonthlyData error: " . $e->getMessage());
            return [];
        }
    }
    
    // 년별 데이터 가져오기
    public static function getYearlyData($email, $year) {
        try {
            $conn = getConnection();
            $stmt = mysqli_prepare($conn, 
                "SELECT MONTH(SaveTime) as month, exercise_time, average_velocity, distance
                 FROM cycle_distance 
                 WHERE name = ? AND YEAR(SaveTime) = ? 
                 ORDER BY MONTH(SaveTime)"
            );
            mysqli_stmt_bind_param($stmt, "si", $email, $year);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            // 월별로 데이터 집계
            $monthlyData = array_fill(1, 12, [
                'total_time' => 0,
                'velocity_sum' => 0,
                'velocity_count' => 0,
                'total_distance' => 0
            ]);
            
            while ($row = mysqli_fetch_assoc($result)) {
                $month = (int)$row['month'];
                $timeInSeconds = self::convertTimeFormatToSeconds($row['exercise_time']);
                
                $monthlyData[$month]['total_time'] += $timeInSeconds;
                $monthlyData[$month]['velocity_sum'] += $row['average_velocity'];
                $monthlyData[$month]['velocity_count']++;
                $monthlyData[$month]['total_distance'] += $row['distance'];
            }
            
            // 최종 데이터 생성
            $data = [];
            foreach ($monthlyData as $month => $values) {
                if ($values['velocity_count'] > 0) {
                    $data[] = [
                        'month' => $month,
                        'total_time' => $values['total_time'],
                        'avg_velocity' => $values['velocity_sum'] / $values['velocity_count'],
                        'total_distance' => $values['total_distance']
                    ];
                }
            }
            
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            
            return $data;
        } catch (Exception $e) {
            error_log("CycleDAO getYearlyData error: " . $e->getMessage());
            return [];
        }
    }
    
    // 목표 달성률 계산
    public static function calculateAchievementRate($actual, $target) {
        if ($target == 0) return 0;
        return min(100, round(($actual / $target) * 100));
    }
    
    // 거리 계산 (운동시간 * 평균속도)
    public static function calculateDistance($exerciseTime, $averageVelocity) {
        // exercise_time은 초 단위, average_velocity는 m/s 단위
        // 거리 = 시간(초) * 속도(m/s) = 미터
        return $exerciseTime * $averageVelocity;
    }
    
    // km/h를 m/s로 변환
    public static function kmhToMs($kmh) {
        return $kmh * 1000 / 3600; // km/h * 1000m/km / 3600s/h
    }
    
    // m/s를 km/h로 변환
    public static function msToKmh($ms) {
        return $ms * 3600 / 1000; // m/s * 3600s/h / 1000m/km
    }
}
?>
