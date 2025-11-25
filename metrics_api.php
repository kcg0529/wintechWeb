<?php
session_start();

// 기본 설정 (실제 DB 데이터와 일치)
if (!isset($_SESSION['email'])) {
    $_SESSION['email'] = 'ksares@aasd.com';
}

header('Content-Type: application/json; charset=utf-8');

/**
 * "분 : 초" 형식을 초로 변환
 */
function convertTimeFormatToSeconds($timeFormat) {
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

try {
    $email = $_SESSION['email'];
    $period = $_GET['period'] ?? 'day';
    $date = $_GET['date'] ?? date('Y-m-d');
    
    // DB 연결 (getConnection 함수 사용)
    require_once 'DAO/mysqli_con.php';
    $conn = getConnection();
    
    if (!$conn) {
        $error_msg = mysqli_connect_error() ?: "Unknown database connection error";
        error_log("metrics_api.php connection error: " . $error_msg);
        throw new Exception("데이터베이스 연결에 실패했습니다. 관리자에게 문의하세요.");
    }
    
    // 목표 설정
    $targets = [
        'exercise_hours' => 100, // 100분
        'average_velocity' => 6, // 6km/h
        'distance' => 10000 // 10km
    ];
    
    $data = [];
    $achievementRates = [];
    
    if ($period === 'day') {
        $sql = "SELECT exercise_hours, average_velocity, distance 
                FROM BedBike 
                WHERE email = ? AND DATE(SaveTime) = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $email, $date);
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);
        
        $totalTime = 0;
        $totalDistance = 0;
        $velocitySum = 0;
        $recordCount = 0;
        
        while ($row = mysqli_fetch_assoc($result)) {
            $totalTime += convertTimeFormatToSeconds($row['exercise_hours']); // "분 : 초"를 초로 변환
            $totalDistance += $row['distance'];
            $velocitySum += $row['average_velocity'];
            $recordCount++;
        }
        
        if ($recordCount > 0) {
            $data = [
                'exercise_hours' => round($totalTime / 60, 1), // 초를 분으로 변환
                'average_velocity' => round($velocitySum / $recordCount, 1),
                'distance' => round($totalDistance / 1000, 2) // 미터를 km로 변환 (소수점 두자리)
            ];
            
            // 목표값 단위 변환: 6km/h = 1.67m/s
            $targetVelocityMs = $targets['average_velocity'] * 1000 / 3600;
            
            $achievementRates = [
                'exercise_hours' => min(100, round(($data['exercise_hours'] / $targets['exercise_hours']) * 100)),
                'average_velocity' => min(100, round(($data['average_velocity'] / $targetVelocityMs) * 100)),
                'distance' => min(100, round(($data['distance'] / ($targets['distance'] / 1000)) * 100))
            ];
        } else {
            $data = [
                'exercise_hours' => 0,
                'average_velocity' => 0,
                'distance' => 0
            ];
            
            $achievementRates = [
                'exercise_hours' => 0,
                'average_velocity' => 0,
                'distance' => 0
            ];
        }
        
        mysqli_stmt_close($stmt);
    } elseif ($period === 'week') {
        // 주간 데이터 (일요일~토요일)
        $dateObj = new DateTime($date);
        $dayOfWeek = $dateObj->format('w'); // 0(일요일) ~ 6(토요일)
        
        // 해당 주의 일요일로 이동
        $weekStart = clone $dateObj;
        $weekStart->modify("-{$dayOfWeek} days");
        
        // 토요일로 이동
        $weekEnd = clone $weekStart;
        $weekEnd->modify("+6 days");
        
        $weekStartStr = $weekStart->format('Y-m-d');
        $weekEndStr = $weekEnd->format('Y-m-d');
        
        $sql = "SELECT DATE(SaveTime) as log_date, exercise_hours, average_velocity, distance 
                FROM BedBike 
                WHERE email = ? AND DATE(SaveTime) BETWEEN ? AND ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $email, $weekStartStr, $weekEndStr);
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);
        
        $totalTime = 0;
        $totalDistance = 0;
        $velocitySum = 0;
        $velocityCount = 0;
        $dailyTime = array_fill(0, 7, 0);
        $dailyDistance = array_fill(0, 7, 0);
        $dailyVelocitySum = array_fill(0, 7, 0);
        $dailyVelocityCount = array_fill(0, 7, 0);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $timeInSeconds = convertTimeFormatToSeconds($row['exercise_hours']); // "분 : 초"를 초로 변환
            $totalTime += $timeInSeconds;
            $totalDistance += $row['distance'];
            
            if ($row['average_velocity'] > 0) {
                $velocitySum += $row['average_velocity'];
                $velocityCount++;
            }

            if (!empty($row['log_date'])) {
                $logDate = new DateTime($row['log_date']);
                $dayDiff = $weekStart->diff($logDate)->days;
                if ($dayDiff >= 0 && $dayDiff < 7) {
                    $dailyTime[$dayDiff] += $timeInSeconds;
                    $dailyDistance[$dayDiff] += $row['distance'];
                    if ($row['average_velocity'] > 0) {
                        $dailyVelocitySum[$dayDiff] += $row['average_velocity'];
                        $dailyVelocityCount[$dayDiff]++;
                    }
                }
            }
        }
        
        if ($velocityCount > 0 || $totalTime > 0 || $totalDistance > 0) {
            $dailyData = [
                'distance' => [],
                'exercise_hours' => [],
                'average_velocity' => []
            ];
            for ($i = 0; $i < 7; $i++) {
                $dailyData['distance'][] = round($dailyDistance[$i] / 1000, 2);
                $dailyData['exercise_hours'][] = round($dailyTime[$i] / 60, 1);
                $dailyData['average_velocity'][] = $dailyVelocityCount[$i] > 0 ? round($dailyVelocitySum[$i] / $dailyVelocityCount[$i], 1) : 0;
            }

            $data = [
                'exercise_hours' => round($totalTime / 60, 1), // 초를 분으로 변환
                'average_velocity' => $velocityCount > 0 ? round($velocitySum / $velocityCount, 1) : 0,
                'distance' => round($totalDistance / 1000, 2), // 미터를 km로 변환 (소수점 두자리)
                'daily' => $dailyData
            ];
            
            // 주간 목표: 일일 목표 * 7
            $weekTimeTarget = $targets['exercise_hours'] * 7; // 700분
            $weekVelocityTarget = $targets['average_velocity']; // 6km/h (평균)
            $weekDistanceTarget = ($targets['distance'] / 1000) * 7; // 70km
            
            // 주간 목표값 단위 변환
            $weekTargetVelocityMs = $weekVelocityTarget * 1000 / 3600; // 6km/h = 1.67m/s
            
            $achievementRates = [
                'exercise_hours' => min(100, round(($data['exercise_hours'] / $weekTimeTarget) * 100)),
                'average_velocity' => min(100, round(($data['average_velocity'] / $weekTargetVelocityMs) * 100)),
                'distance' => min(100, round(($data['distance'] / $weekDistanceTarget) * 100))
            ];
        } else {
            $data = [
                'exercise_hours' => 0,
                'average_velocity' => 0,
                'distance' => 0,
                'daily' => [
                    'distance' => array_fill(0, 7, 0),
                    'exercise_hours' => array_fill(0, 7, 0),
                    'average_velocity' => array_fill(0, 7, 0)
                ]
            ];
            
            $achievementRates = [
                'exercise_hours' => 0,
                'average_velocity' => 0,
                'distance' => 0
            ];
        }
        
        mysqli_stmt_close($stmt);
    } elseif ($period === 'month') {
        // 월간 데이터 (주별로 4주 데이터 반환)
        $dateObj = new DateTime($date);
        $year = $dateObj->format('Y');
        $month = $dateObj->format('m');
        
        // 해당 월의 첫날과 마지막날
        $firstDay = new DateTime("$year-$month-01");
        $lastDay = clone $firstDay;
        $lastDay->modify('last day of this month');
        
        $firstDayStr = $firstDay->format('Y-m-d');
        $lastDayStr = $lastDay->format('Y-m-d');
        
        // 월간 전체 데이터 조회
        $sql = "SELECT DATE(SaveTime) as date, exercise_hours, average_velocity, distance 
                FROM BedBike 
                WHERE email = ? AND DATE(SaveTime) BETWEEN ? AND ?
                ORDER BY SaveTime";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $email, $firstDayStr, $lastDayStr);
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);
        
        // 주별로 데이터 그룹화 (4주)
        $weeklyData = [
            ['time' => 0, 'distance' => 0, 'velocity_sum' => 0, 'velocity_count' => 0],
            ['time' => 0, 'distance' => 0, 'velocity_sum' => 0, 'velocity_count' => 0],
            ['time' => 0, 'distance' => 0, 'velocity_sum' => 0, 'velocity_count' => 0],
            ['time' => 0, 'distance' => 0, 'velocity_sum' => 0, 'velocity_count' => 0]
        ];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $rowDate = new DateTime($row['date']);
            $dayOfMonth = (int)$rowDate->format('d');
            
            // 주차 계산 (1~7일: 1주차, 8~14일: 2주차, 15~21일: 3주차, 22~31일: 4주차)
            $weekIndex = min(3, floor(($dayOfMonth - 1) / 7));
            
            $timeInSeconds = convertTimeFormatToSeconds($row['exercise_hours']); // "분 : 초"를 초로 변환
            $weeklyData[$weekIndex]['time'] += $timeInSeconds;
            $weeklyData[$weekIndex]['distance'] += $row['distance'];
            
            if ($row['average_velocity'] > 0) {
                $weeklyData[$weekIndex]['velocity_sum'] += $row['average_velocity'];
                $weeklyData[$weekIndex]['velocity_count']++;
            }
        }
        
        // 주별 데이터를 변환 (4주 데이터)
        $data = [
            'distance' => [],
            'exercise_hours' => [],
            'average_velocity' => []
        ];
        
        foreach ($weeklyData as $week) {
            $data['distance'][] = round($week['distance'] / 1000, 2); // km (소수점 두자리)
            $data['exercise_hours'][] = round($week['time'] / 60, 1); // 분
            $data['average_velocity'][] = $week['velocity_count'] > 0 
                ? round($week['velocity_sum'] / $week['velocity_count'], 1) 
                : 0;
        }
        
        // 월간 달성률은 전체 합산 기준
        $totalTime = array_sum($data['exercise_hours']);
        $totalDistance = array_sum($data['distance']);
        $avgVelocity = 0;
        $velocityCount = 0;
        
        foreach ($data['average_velocity'] as $vel) {
            if ($vel > 0) {
                $avgVelocity += $vel;
                $velocityCount++;
            }
        }
        $avgVelocity = $velocityCount > 0 ? $avgVelocity / $velocityCount : 0;
        
        // 월간 목표 (약 30일)
        $monthTimeTarget = $targets['exercise_hours'] * 30; // 3000분
        $monthVelocityTarget = $targets['average_velocity']; // 6km/h
        $monthDistanceTarget = ($targets['distance'] / 1000) * 30; // 300km
        
        $achievementRates = [
            'exercise_hours' => min(100, round(($totalTime / $monthTimeTarget) * 100)),
            'average_velocity' => min(100, round(($avgVelocity / $monthVelocityTarget) * 100)),
            'distance' => min(100, round(($totalDistance / $monthDistanceTarget) * 100))
        ];
        
        mysqli_stmt_close($stmt);
    } elseif ($period === 'year') {
        // 년간 데이터 (12개월 데이터 반환)
        $dateObj = new DateTime($date);
        $year = $dateObj->format('Y');
        
        // 해당 년도의 1월 1일과 12월 31일
        $firstDay = new DateTime("$year-01-01");
        $lastDay = new DateTime("$year-12-31");
        
        $firstDayStr = $firstDay->format('Y-m-d');
        $lastDayStr = $lastDay->format('Y-m-d');
        
        // 년간 전체 데이터 조회
        $sql = "SELECT MONTH(SaveTime) as month, exercise_hours, average_velocity, distance
                FROM BedBike 
                WHERE email = ? AND DATE(SaveTime) BETWEEN ? AND ?
                ORDER BY MONTH(SaveTime)";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $email, $firstDayStr, $lastDayStr);
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);
        
        // 월별로 데이터 집계
        $monthlyAggregate = array_fill(0, 12, [
            'time' => 0,
            'distance' => 0,
            'velocity_sum' => 0,
            'velocity_count' => 0
        ]);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $month = $row['month'] - 1; // 배열 인덱스는 0부터 시작
            $timeInSeconds = convertTimeFormatToSeconds($row['exercise_hours']); // "분 : 초"를 초로 변환
            
            $monthlyAggregate[$month]['time'] += $timeInSeconds;
            $monthlyAggregate[$month]['distance'] += $row['distance'];
            
            if ($row['average_velocity'] > 0) {
                $monthlyAggregate[$month]['velocity_sum'] += $row['average_velocity'];
                $monthlyAggregate[$month]['velocity_count']++;
            }
        }
        
        // 최종 월별 데이터 생성
        $monthlyData = [
            'distance' => array_fill(0, 12, 0),
            'exercise_hours' => array_fill(0, 12, 0),
            'average_velocity' => array_fill(0, 12, 0)
        ];
        
        for ($i = 0; $i < 12; $i++) {
            $monthlyData['exercise_hours'][$i] = round($monthlyAggregate[$i]['time'] / 60, 1); // 초를 분으로 변환
            $monthlyData['distance'][$i] = round($monthlyAggregate[$i]['distance'] / 1000, 2); // 미터를 km로 변환 (소수점 두자리)
            $monthlyData['average_velocity'][$i] = $monthlyAggregate[$i]['velocity_count'] > 0
                ? round($monthlyAggregate[$i]['velocity_sum'] / $monthlyAggregate[$i]['velocity_count'], 1)
                : 0;
        }
        
        $data = $monthlyData;
        
        // 년간 달성률은 전체 합산 기준
        $totalTime = array_sum($monthlyData['exercise_hours']);
        $totalDistance = array_sum($monthlyData['distance']);
        $avgVelocity = 0;
        $velocityCount = 0;
        
        foreach ($monthlyData['average_velocity'] as $vel) {
            if ($vel > 0) {
                $avgVelocity += $vel;
                $velocityCount++;
            }
        }
        $avgVelocity = $velocityCount > 0 ? $avgVelocity / $velocityCount : 0;
        
        // 년간 목표 (365일)
        $yearTimeTarget = $targets['exercise_hours'] * 365; // 36500분
        $yearVelocityTarget = $targets['average_velocity']; // 6km/h
        $yearDistanceTarget = ($targets['distance'] / 1000) * 365; // 3650km
        
        // 년간 목표값 단위 변환
        $yearTargetVelocityMs = $yearVelocityTarget * 1000 / 3600; // 6km/h = 1.67m/s
        
        $achievementRates = [
            'exercise_hours' => min(100, round(($totalTime / $yearTimeTarget) * 100)),
            'average_velocity' => min(100, round(($avgVelocity / $yearTargetVelocityMs) * 100)),
            'distance' => min(100, round(($totalDistance / $yearDistanceTarget) * 100))
        ];
        
        mysqli_stmt_close($stmt);
    } elseif ($period === '10year') {
        // 최근 10년 데이터 (연도별 10개 데이터 반환)
        $dateObj = new DateTime($date);
        $endYear = (int)$dateObj->format('Y');
        $startYear = $endYear - 9;

        $firstDay = new DateTime("$startYear-01-01");
        $lastDay = new DateTime("$endYear-12-31");

        $firstDayStr = $firstDay->format('Y-m-d');
        $lastDayStr = $lastDay->format('Y-m-d');

        $sql = "SELECT YEAR(SaveTime) as log_year, exercise_hours, average_velocity, distance
                FROM BedBike
                WHERE email = ? AND DATE(SaveTime) BETWEEN ? AND ?
                ORDER BY YEAR(SaveTime)";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $email, $firstDayStr, $lastDayStr);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        // 연도별로 데이터 집계
        $yearlyAggregate = array_fill(0, 10, [
            'time' => 0,
            'distance' => 0,
            'velocity_sum' => 0,
            'velocity_count' => 0
        ]);

        while ($row = mysqli_fetch_assoc($result)) {
            $yearIndex = (int)$row['log_year'] - $startYear;
            if ($yearIndex < 0 || $yearIndex >= 10) {
                continue;
            }
            
            $timeInSeconds = convertTimeFormatToSeconds($row['exercise_hours']); // "분 : 초"를 초로 변환
            $yearlyAggregate[$yearIndex]['time'] += $timeInSeconds;
            $yearlyAggregate[$yearIndex]['distance'] += $row['distance'];
            
            if ($row['average_velocity'] > 0) {
                $yearlyAggregate[$yearIndex]['velocity_sum'] += $row['average_velocity'];
                $yearlyAggregate[$yearIndex]['velocity_count']++;
            }
        }
        
        // 최종 데이터 생성
        $decadeData = [
            'distance' => array_fill(0, 10, 0),
            'exercise_hours' => array_fill(0, 10, 0),
            'average_velocity' => array_fill(0, 10, 0)
        ];
        
        for ($i = 0; $i < 10; $i++) {
            $decadeData['exercise_hours'][$i] = round($yearlyAggregate[$i]['time'] / 60, 1); // 초를 분으로 변환
            $decadeData['distance'][$i] = round($yearlyAggregate[$i]['distance'] / 1000, 2); // 미터를 km로 변환 (소수점 두자리)
            $decadeData['average_velocity'][$i] = $yearlyAggregate[$i]['velocity_count'] > 0
                ? round($yearlyAggregate[$i]['velocity_sum'] / $yearlyAggregate[$i]['velocity_count'], 1)
                : 0;
        }

        $data = $decadeData;

        // 10년 전체 합산
        $totalTime = array_sum($decadeData['exercise_hours']); // 분
        $totalDistance = array_sum($decadeData['distance']); // km
        $avgVelocity = 0;
        $velocityCount = 0;

        foreach ($decadeData['average_velocity'] as $vel) {
            if ($vel > 0) {
                $avgVelocity += $vel;
                $velocityCount++;
            }
        }
        $avgVelocity = $velocityCount > 0 ? $avgVelocity / $velocityCount : 0;

        // 10년 목표 (365일 × 10)
        $decadeTimeTarget = $targets['exercise_hours'] * 365 * 10; // 분
        $decadeDistanceTarget = ($targets['distance'] / 1000) * 365 * 10; // km
        $decadeVelocityTargetMs = $targets['average_velocity'] * 1000 / 3600; // m/s

        $achievementRates = [
            'exercise_hours' => $decadeTimeTarget > 0 ? min(100, round(($totalTime / $decadeTimeTarget) * 100)) : 0,
            'average_velocity' => $decadeVelocityTargetMs > 0 ? min(100, round(($avgVelocity / $decadeVelocityTargetMs) * 100)) : 0,
            'distance' => $decadeDistanceTarget > 0 ? min(100, round(($totalDistance / $decadeDistanceTarget) * 100)) : 0
        ];

        $data['total_time'] = round($totalTime, 1);
        $data['total_distance'] = round($totalDistance, 2); // 소수점 두자리
        $data['avg_velocity_total'] = round($avgVelocity, 1);

        mysqli_stmt_close($stmt);
    } elseif ($period === '30year') {
        // 최근 30년 데이터 (연도별 30개 데이터 반환)
        $dateObj = new DateTime($date);
        $endYear = (int)$dateObj->format('Y');
        $startYear = $endYear - 29;

        $firstDay = new DateTime("$startYear-01-01");
        $lastDay = new DateTime("$endYear-12-31");

        $firstDayStr = $firstDay->format('Y-m-d');
        $lastDayStr = $lastDay->format('Y-m-d');

        $sql = "SELECT YEAR(SaveTime) as log_year, exercise_hours, average_velocity, distance
                FROM BedBike
                WHERE email = ? AND DATE(SaveTime) BETWEEN ? AND ?
                ORDER BY YEAR(SaveTime)";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $email, $firstDayStr, $lastDayStr);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        // 연도별로 데이터 집계
        $yearlyAggregate = array_fill(0, 30, [
            'time' => 0,
            'distance' => 0,
            'velocity_sum' => 0,
            'velocity_count' => 0
        ]);

        while ($row = mysqli_fetch_assoc($result)) {
            $yearIndex = (int)$row['log_year'] - $startYear;
            if ($yearIndex < 0 || $yearIndex >= 30) {
                continue;
            }
            
            $timeInSeconds = convertTimeFormatToSeconds($row['exercise_hours']); // "분 : 초"를 초로 변환
            $yearlyAggregate[$yearIndex]['time'] += $timeInSeconds;
            $yearlyAggregate[$yearIndex]['distance'] += $row['distance'];
            
            if ($row['average_velocity'] > 0) {
                $yearlyAggregate[$yearIndex]['velocity_sum'] += $row['average_velocity'];
                $yearlyAggregate[$yearIndex]['velocity_count']++;
            }
        }
        
        // 최종 데이터 생성
        $thirtyYearData = [
            'distance' => array_fill(0, 30, 0),
            'exercise_hours' => array_fill(0, 30, 0),
            'average_velocity' => array_fill(0, 30, 0)
        ];
        
        for ($i = 0; $i < 30; $i++) {
            $thirtyYearData['exercise_hours'][$i] = round($yearlyAggregate[$i]['time'] / 60, 1); // 초를 분으로 변환
            $thirtyYearData['distance'][$i] = round($yearlyAggregate[$i]['distance'] / 1000, 2); // 미터를 km로 변환 (소수점 두자리)
            $thirtyYearData['average_velocity'][$i] = $yearlyAggregate[$i]['velocity_count'] > 0
                ? round($yearlyAggregate[$i]['velocity_sum'] / $yearlyAggregate[$i]['velocity_count'], 1)
                : 0;
        }

        $data = $thirtyYearData;

        // 30년 전체 합산
        $totalTime = array_sum($thirtyYearData['exercise_hours']); // 분
        $totalDistance = array_sum($thirtyYearData['distance']); // km
        $avgVelocity = 0;
        $velocityCount = 0;

        foreach ($thirtyYearData['average_velocity'] as $vel) {
            if ($vel > 0) {
                $avgVelocity += $vel;
                $velocityCount++;
            }
        }
        $avgVelocity = $velocityCount > 0 ? $avgVelocity / $velocityCount : 0;

        // 30년 목표 (365일 × 30)
        $thirtyYearTimeTarget = $targets['exercise_hours'] * 365 * 30; // 분
        $thirtyYearDistanceTarget = ($targets['distance'] / 1000) * 365 * 30; // km
        $thirtyYearVelocityTargetMs = $targets['average_velocity'] * 1000 / 3600; // m/s

        $achievementRates = [
            'exercise_hours' => $thirtyYearTimeTarget > 0 ? min(100, round(($totalTime / $thirtyYearTimeTarget) * 100)) : 0,
            'average_velocity' => $thirtyYearVelocityTargetMs > 0 ? min(100, round(($avgVelocity / $thirtyYearVelocityTargetMs) * 100)) : 0,
            'distance' => $thirtyYearDistanceTarget > 0 ? min(100, round(($totalDistance / $thirtyYearDistanceTarget) * 100)) : 0
        ];

        $data['total_time'] = round($totalTime, 1);
        $data['total_distance'] = round($totalDistance, 2); // 소수점 두자리
        $data['avg_velocity_total'] = round($avgVelocity, 1);

        mysqli_stmt_close($stmt);
    } elseif ($period === '100year') {
        // 최근 100년 데이터 (10년 단위로 10개 데이터 반환)
        $dateObj = new DateTime($date);
        $endYear = (int)$dateObj->format('Y');
        $startYear = $endYear - 99;

        $firstDay = new DateTime("$startYear-01-01");
        $lastDay = new DateTime("$endYear-12-31");

        $firstDayStr = $firstDay->format('Y-m-d');
        $lastDayStr = $lastDay->format('Y-m-d');

        // 10년 단위로 데이터 조회
        $hundredYearData = [
            'distance' => array_fill(0, 10, 0),
            'exercise_hours' => array_fill(0, 10, 0),
            'average_velocity' => array_fill(0, 10, 0)
        ];

        for ($i = 0; $i < 10; $i++) {
            $decadeStart = $startYear + ($i * 10);
            $decadeEnd = $decadeStart + 9;
            
            $decadeStartStr = "$decadeStart-01-01";
            $decadeEndStr = "$decadeEnd-12-31";

            $sql = "SELECT exercise_hours, average_velocity, distance
                    FROM BedBike
                    WHERE email = ? AND DATE(SaveTime) BETWEEN ? AND ?";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sss", $email, $decadeStartStr, $decadeEndStr);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            
            $decadeTime = 0;
            $decadeDistance = 0;
            $decadeVelocitySum = 0;
            $decadeVelocityCount = 0;
            
            while ($row = mysqli_fetch_assoc($result)) {
                $timeInSeconds = convertTimeFormatToSeconds($row['exercise_hours']); // "분 : 초"를 초로 변환
                $decadeTime += $timeInSeconds;
                $decadeDistance += $row['distance'];
                
                if ($row['average_velocity'] > 0) {
                    $decadeVelocitySum += $row['average_velocity'];
                    $decadeVelocityCount++;
                }
            }
            
            $hundredYearData['exercise_hours'][$i] = round($decadeTime / 60, 1); // 초를 분으로 변환
            $hundredYearData['distance'][$i] = round($decadeDistance / 1000, 2); // 미터를 km로 변환 (소수점 두자리)
            $hundredYearData['average_velocity'][$i] = $decadeVelocityCount > 0
                ? round($decadeVelocitySum / $decadeVelocityCount, 1)
                : 0;

            mysqli_stmt_close($stmt);
        }

        $data = $hundredYearData;

        // 100년 전체 합산
        $totalTime = array_sum($hundredYearData['exercise_hours']); // 분
        $totalDistance = array_sum($hundredYearData['distance']); // km
        $avgVelocity = 0;
        $velocityCount = 0;

        foreach ($hundredYearData['average_velocity'] as $vel) {
            if ($vel > 0) {
                $avgVelocity += $vel;
                $velocityCount++;
            }
        }
        $avgVelocity = $velocityCount > 0 ? $avgVelocity / $velocityCount : 0;

        // 100년 목표 (365일 × 100)
        $hundredYearTimeTarget = $targets['exercise_hours'] * 365 * 100; // 분
        $hundredYearDistanceTarget = ($targets['distance'] / 1000) * 365 * 100; // km
        $hundredYearVelocityTargetMs = $targets['average_velocity'] * 1000 / 3600; // m/s

        $achievementRates = [
            'exercise_hours' => $hundredYearTimeTarget > 0 ? min(100, round(($totalTime / $hundredYearTimeTarget) * 100)) : 0,
            'average_velocity' => $hundredYearVelocityTargetMs > 0 ? min(100, round(($avgVelocity / $hundredYearVelocityTargetMs) * 100)) : 0,
            'distance' => $hundredYearDistanceTarget > 0 ? min(100, round(($totalDistance / $hundredYearDistanceTarget) * 100)) : 0
        ];

        $data['total_time'] = round($totalTime, 1);
        $data['total_distance'] = round($totalDistance, 2); // 소수점 두자리
        $data['avg_velocity_total'] = round($avgVelocity, 1);
    } else {
        // 다른 기간은 기본값 사용
        $data = [
            'exercise_hours' => 25,
            'average_velocity' => 2.9,
            'distance' => 1.2
        ];
        
        $achievementRates = [
            'exercise_hours' => 25,
            'average_velocity' => 48,
            'distance' => 12
        ];
    }
    
    mysqli_close($conn);
    
    $response = [
        'success' => true,
        'data' => $data,
        'achievementRates' => $achievementRates,
        'period' => $period,
        'date' => $date
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}
?>
