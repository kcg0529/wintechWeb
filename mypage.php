<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 로그인 확인
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

// 프로필 정보 완성도 확인
require_once 'profile_check.php';
checkProfileCompletion();

$page_title = '마이페이지 - 행복운동센터';

// 사용자 정보 가져오기
include_once 'DAO/mysqli_con.php';
$user_info = [];

try {
    $conn = getConnection();
    $email = $_SESSION['email'];
    
    $stmt = mysqli_prepare($conn, "SELECT gender, birthday, height, weight, note FROM wintech_account WHERE account = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $user_info = $row;
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    
} catch (Exception $e) {
    error_log("User info fetch error: " . $e->getMessage());
}

// 목표 설정
$targets = [
    'exercise_hours' => 100, // 100분
    'average_velocity' => 6, // 6km/h
    'distance' => 10000 // 10km (100분 * 6km/h = 600분 = 10시간... 수정 필요)
];

// 6km/h = 1.67m/s, 100분 = 6000초
// 목표 거리 = 6000초 * 1.67m/s = 10000m = 10km
$targets['distance'] = 10000; // 10km

// 현재 날짜 설정
$currentDate = date('Y-m-d');
$currentWeekStart = date('Y-m-d', strtotime('last sunday', strtotime($currentDate)));
$currentWeekEnd = date('Y-m-d', strtotime('saturday', strtotime($currentWeekStart)));
$currentMonth = date('n');
$currentYear = date('Y');

// 현재일 데이터 로드 시도
$todayStats = [
    'exercise_hours' => 0,
    'average_velocity' => 0,
    'distance' => 0
];

$achievementRates = [
    'exercise_hours' => 0,
    'average_velocity' => 0,
    'distance' => 0
];

// 실제 데이터 로드 시도
try {
    if (isset($_SESSION['email'])) {
        // CycleDAO 파일이 존재하는지 확인
        if (!file_exists('DAO/cycle_dao.php')) {
            throw new Exception('CycleDAO 파일이 존재하지 않습니다.');
        }
        
        // 이미 include되었는지 확인
        if (!class_exists('CycleDAO')) {
            include 'DAO/cycle_dao.php';
        }
        
        $userEmail = $_SESSION['email'];
        error_log("mypage.php: Loading data for email: " . $userEmail . ", date: " . $currentDate);
        
        $todayDataArray = CycleDAO::getDailyData($userEmail, $currentDate);
        
        error_log("mypage.php: getDailyData returned " . count($todayDataArray) . " records");
        
        if ($todayDataArray && is_array($todayDataArray) && count($todayDataArray) > 0) {
            
            // 모든 데이터 합산
            $totalExerciseTime = 0;
            $totalDistance = 0;
            $velocitySum = 0;
            $velocityCount = 0;
            
            foreach ($todayDataArray as $index => $data) {
                $exerciseTime = isset($data['exercise_hours']) ? (float)$data['exercise_hours'] : 0;
                $averageVelocity = isset($data['average_velocity']) ? (float)$data['average_velocity'] : 0;
                $distance = isset($data['distance']) ? (float)$data['distance'] : 0;
                
                $totalExerciseTime += $exerciseTime;
                $totalDistance += $distance;
                
                if ($averageVelocity > 0) {
                    $velocitySum += $averageVelocity;
                    $velocityCount++;
                }
            }
            
            // 평균 속도 계산 (0이 아닌 값들의 평균)
            $averageVelocity = $velocityCount > 0 ? $velocitySum / $velocityCount : 0;
            
            // 최종 변환
            $exerciseTime = $totalExerciseTime;
            $distance = $totalDistance;
            
            $todayStats = [
                'exercise_hours' => round($exerciseTime / 60, 1), // 초를 분으로 변환
                'average_velocity' => round($averageVelocity, 1), // m/s
                'distance' => round($distance / 1000, 2) // 미터를 km로 변환
            ];
            
            // 달성률 계산
            // 목표값: 운동시간 100분, 평균속도 6km/h, 거리 10km
            // DB 데이터: 운동시간(분), 평균속도(m/s), 거리(km)
            $targetVelocityMs = $targets['average_velocity'] * 1000 / 3600; // 6km/h = 1.67m/s
            
            $achievementRates = [
                'exercise_hours' => min(100, round(($todayStats['exercise_hours'] / $targets['exercise_hours']) * 100)),
                'average_velocity' => min(100, round(($todayStats['average_velocity'] / $targetVelocityMs) * 100)),
                'distance' => min(100, round(($todayStats['distance'] / ($targets['distance'] / 1000)) * 100))
            ];
        }
    }
} catch (Exception $e) {
    // 오류 시 기본값 유지
    error_log("데이터 로드 오류: " . $e->getMessage());
}

include 'header.php';
?>

<main class="mypage-main">
    <div class="mypage-container">
        <!-- 사용자 정보 섹션 -->
        <div class="user-profile-section">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-details">
                    <h2 class="username"><?php echo htmlspecialchars($email); ?></h2>
                    <div class="user-stats">
                        <div class="stat-item">• 성별: <?php echo ($user_info['gender'] == 1) ? '남성' : '여성'; ?></div>
                        <div class="stat-item">• 생년월일: <?php echo htmlspecialchars($user_info['birthday']); ?></div>
                        <div class="stat-item">• 신장: <?php echo htmlspecialchars($user_info['height']); ?> cm</div>
                        <div class="stat-item">• 체중: <?php echo htmlspecialchars($user_info['weight']); ?> kg</div>
                    </div>
                </div>
            </div>
            
            <!-- 기간 선택 -->
            <div class="period-selector">
                <div class="period-buttons">
                    <button class="period-btn active" data-period="day">일</button>
                    <button class="period-btn" data-period="week">주</button>
                    <button class="period-btn" data-period="month">월</button>
                    <button class="period-btn" data-period="year">년</button>
                    <button class="period-btn" data-period="10year">10년</button>
                    <button class="period-btn" data-period="30year">30년</button>
                    <button class="period-btn" data-period="100year">100년</button>
                </div>
                <div class="date-navigation">
                    <button class="nav-arrow left">‹</button>
                    <span class="date-display"><?php echo date('Y.m.d'); ?></span>
                    <button class="nav-arrow right">›</button>
                </div>
            </div>
        </div>

        <!-- 운동 데이터 섹션 -->
        <div class="fitness-metrics-section">
            <div class="metrics-container">
                <!-- 운동거리 -->
                <div class="metric-card">
                    <div class="metric-icon distance">
                        <i class="fas fa-running"></i>
                    </div>
                        <div class="metric-value" id="distance-value"><?php echo number_format($todayStats['distance'], 2); ?> Km</div>
                    <div class="metric-label">운동거리</div>
                    <div class="progress-container" id="distance-progress">
                        <div style="width: 40px; height: 120px; background: #f0f0f0; border-radius: 20px; position: relative; margin: 0 auto; border: 2px solid #ddd;">
                            <div style="width: 100%; height: <?php echo $achievementRates['distance']; ?>%; background: #28a745; border-radius: 20px; position: absolute; bottom: 0; left: 0; min-height: 10px;"></div>
                        </div>
                    </div>
                    <div class="achievement-rate">목표 달성률 <?php echo $achievementRates['distance']; ?>%</div>
                </div>

                <!-- 운동시간 -->
                <div class="metric-card">
                    <div class="metric-icon time">
                        <i class="fas fa-clock"></i>
                    </div>
                        <div class="metric-value" id="time-value"><?php echo $todayStats['exercise_hours']; ?> Min</div>
                    <div class="metric-label">운동시간</div>
                    <div class="progress-container" id="time-progress">
                        <div style="width: 40px; height: 120px; background: #f0f0f0; border-radius: 20px; position: relative; margin: 0 auto; border: 2px solid #ddd;">
                            <div style="width: 100%; height: <?php echo $achievementRates['exercise_hours']; ?>%; background: #007bff; border-radius: 20px; position: absolute; bottom: 0; left: 0; min-height: 10px;"></div>
                        </div>
                    </div>
                    <div class="achievement-rate">목표 달성률 <?php echo $achievementRates['exercise_hours']; ?>%</div>
                </div>

                <!-- 평균속도 -->
                <div class="metric-card">
                    <div class="metric-icon speed">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                        <div class="metric-value" id="speed-value"><?php echo $todayStats['average_velocity']; ?> m/s</div>
                    <div class="metric-label">평균속도</div>
                    <div class="progress-container" id="speed-progress">
                        <div style="width: 40px; height: 120px; background: #f0f0f0; border-radius: 20px; position: relative; margin: 0 auto; border: 2px solid #ddd;">
                            <div style="width: 100%; height: <?php echo $achievementRates['average_velocity']; ?>%; background: #ffc107; border-radius: 20px; position: absolute; bottom: 0; left: 0; min-height: 10px;"></div>
                        </div>
                    </div>
                    <div class="achievement-rate">목표 달성률 <?php echo $achievementRates['average_velocity']; ?>%</div>
                </div>
            </div>
            
            <!-- 통합 선 그래프 영역 (월간/년간용) -->
            <div class="integrated-chart-section" id="integrated-chart" style="display: none;">
                <div class="chart-container">
                    <svg width="100%" height="300" viewBox="0 0 1200 300" style="border: 1px solid #ddd; border-radius: 8px;">
                        <!-- 여기에 통합 선 그래프가 표시됩니다 -->
                    </svg>
                </div>
            </div>
            
            <!-- 레이더 그래프 영역 -->
            <div class="radar-chart-section">
                <h3 style="text-align: center; margin-bottom: 20px; color: #333;">종합 능력 분석</h3>
                <div class="radar-container">
                    <svg id="radar-chart" width="100%" height="400" viewBox="0 0 500 500">
                        <!-- 레이더 차트가 여기에 그려집니다 -->
                    </svg>
                </div>
            </div>
            
        </div>
    </div>
</main>
<link rel="stylesheet" href="css/mypage.css">

<script>
// PHP에서 전달된 데이터 (PHP 변수는 인라인으로 유지)
const userEmail = '<?php echo isset($email) ? $email : ''; ?>';
const todayStats = <?php echo json_encode($todayStats); ?>;
const achievementRates = <?php echo json_encode($achievementRates); ?>;
const targets = <?php echo json_encode($targets); ?>;

// 현재 선택된 기간과 날짜
let currentPeriod = 'day';
let currentDate = '<?php echo date('Y-m-d'); ?>';
let isNavigating = false;
let currentPeriodLabel = '';
    
    // AJAX로 데이터 가져오기
    async function fetchMetricsData(period, date) {
        try {
            const url = `metrics_api.php?period=${period}&date=${date}`;
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const text = await response.text();
            
            if (!text.trim()) {
                throw new Error('Empty response received');
            }
            
            const result = JSON.parse(text);
            
            if (result.success) {
                return result;
            } else {
                console.error('데이터 가져오기 실패:', result.error);
                return null;
            }
        } catch (error) {
            console.error('AJAX 오류:', error);
            console.error('Error stack:', error.stack);
            return null;
        }
    }
    
    // 날짜 계산 함수들
    function getNextDate(date, period) {
        const d = new Date(date);
        
        switch (period) {
            case 'day':
                d.setDate(d.getDate() + 1);
                break;
            case 'week':
                // 주간: 7일 후로 이동
                d.setDate(d.getDate() + 7);
                break;
            case 'month':
                // 월간: 다음 달로 이동 (같은 일자 유지)
                d.setMonth(d.getMonth() + 1);
                break;
            case 'year':
                // 년간: 다음 년도로 이동 (같은 월일 유지)
                d.setFullYear(d.getFullYear() + 1);
                break;
            case '10year':
                d.setFullYear(d.getFullYear() + 10);
                break;
            case '30year':
                d.setFullYear(d.getFullYear() + 30);
                break;
            case '100year':
                d.setFullYear(d.getFullYear() + 100);
                break;
        }
        
        return d.toISOString().split('T')[0];
    }
    
    function getPrevDate(date, period) {
        const d = new Date(date);
        
        switch (period) {
            case 'day':
                d.setDate(d.getDate() - 1);
                break;
            case 'week':
                // 주간: 7일 전으로 이동
                d.setDate(d.getDate() - 7);
                break;
            case 'month':
                // 월간: 이전 달로 이동 (같은 일자 유지)
                d.setMonth(d.getMonth() - 1);
                break;
            case 'year':
                // 년간: 이전 년도로 이동 (같은 월일 유지)
                d.setFullYear(d.getFullYear() - 1);
                break;
            case '10year':
                d.setFullYear(d.getFullYear() - 10);
                break;
            case '30year':
                d.setFullYear(d.getFullYear() - 30);
                break;
            case '100year':
                d.setFullYear(d.getFullYear() - 100);
                break;
        }
        
        return d.toISOString().split('T')[0];
    }
    
    // 날짜와 함께 데이터 업데이트
    async function updateMetricsDataWithDate(period, date) {
        // 중복 호출 방지
        if (isNavigating) {
            console.log('이미 데이터 로딩 중입니다.');
            return;
        }
        
        isNavigating = true;
        currentPeriod = period;
        currentDate = date;
        
        // 로딩 표시
        showLoading();
        const periodLabel = getPeriodLabel(period, date);
        
        // 날짜 표시 업데이트
        updateDateDisplay(period, date, periodLabel);
        
        try {
            const result = await fetchMetricsData(period, date);
            
            if (result && result.success) {
                updateMetricsData(period, result.data, result.achievementRates, periodLabel);
            } else {
                // 오류 시 기본 데이터 사용
                updateMetricsData(period, todayStats, achievementRates, periodLabel);
            }
        } catch (error) {
            console.error('❌ Error in updateMetricsDataWithDate:', error);
            updateMetricsData(period, todayStats, achievementRates, periodLabel);
        } finally {
            hideLoading();
            isNavigating = false;
        }
    }
    
    // 로딩 표시
    function showLoading() {
        const metricsContainer = document.querySelector('.metrics-container');
        if (metricsContainer) {
            metricsContainer.style.opacity = '0.5';
            metricsContainer.style.pointerEvents = 'none';
        }
    }
    
    function hideLoading() {
        const metricsContainer = document.querySelector('.metrics-container');
        if (metricsContainer) {
            metricsContainer.style.opacity = '1';
            metricsContainer.style.pointerEvents = 'auto';
        }
    }

    function padNumber(num) {
        return String(num).padStart(2, '0');
    }

    function parseDateParts(dateStr) {
        if (!dateStr) {
            return null;
        }
        const match = /^([0-9]{4})-([0-9]{2})-([0-9]{2})$/.exec(dateStr);
        if (!match) {
            return null;
        }
        const year = parseInt(match[1], 10);
        const month = parseInt(match[2], 10);
        const day = parseInt(match[3], 10);
        if (Number.isNaN(year) || Number.isNaN(month) || Number.isNaN(day)) {
            return null;
        }
        return {
            year,
            month,
            day,
            dateObj: new Date(year, month - 1, day)
        };
    }

    function getPeriodLabel(period, date) {
        try {
            const parsed = parseDateParts(date);
            const d = parsed ? parsed.dateObj : new Date(date);
            let displayText = '';
            
            console.log('getPeriodLabel 호출:', { period, date, parsed, d: d.toString() });
            
            switch (period) {
                case 'day': {
                    const year = parsed ? parsed.year : d.getFullYear();
                    const month = parsed ? parsed.month : (d.getMonth() + 1);
                    const day = parsed ? parsed.day : d.getDate();
                    displayText = `${year}.${padNumber(month)}.${padNumber(day)}`;
                    break;
                }
                case 'week': {
                    const baseDate = parsed ? new Date(parsed.year, parsed.month - 1, parsed.day) : new Date(d);
                    const weekStart = new Date(baseDate);
                    weekStart.setDate(baseDate.getDate() - baseDate.getDay());
                    const weekEnd = new Date(weekStart);
                    weekEnd.setDate(weekStart.getDate() + 6);
                    
                    const startYear = weekStart.getFullYear();
                    const startMonth = padNumber(weekStart.getMonth() + 1);
                    const startDay = padNumber(weekStart.getDate());
                    const endYear = weekEnd.getFullYear();
                    const endMonth = padNumber(weekEnd.getMonth() + 1);
                    const endDay = padNumber(weekEnd.getDate());
                    
                    displayText = `${startYear}.${startMonth}.${startDay} - ${endYear}.${endMonth}.${endDay}`;
                    break;
                }
                case 'month': {
                    const monthYear = parsed ? parsed.year : d.getFullYear();
                    const monthNum = parsed ? parsed.month : (d.getMonth() + 1);
                    displayText = `${monthYear}.${padNumber(monthNum)}`;
                    break;
                }
                case 'year':
                    displayText = (parsed ? parsed.year : d.getFullYear()) + '년';
                    break;
                case '10year': {
                    const endYear = parsed ? parsed.year : d.getFullYear();
                    const startYearDecade = endYear - 9;
                    displayText = `${startYearDecade} ~ ${endYear}`;
                    console.log('10year 레이블 생성:', { endYear, startYearDecade, displayText });
                    break;
                }
                case '30year': {
                    const endYear30 = parsed ? parsed.year : d.getFullYear();
                    const startYear30 = endYear30 - 29;
                    displayText = `${startYear30} ~ ${endYear30}`;
                    console.log('30year 레이블 생성:', { endYear30, startYear30, displayText });
                    break;
                }
                case '100year': {
                    const endYear100 = parsed ? parsed.year : d.getFullYear();
                    const startYear100 = endYear100 - 99;
                    displayText = `${startYear100} ~ ${endYear100}`;
                    console.log('100year 레이블 생성:', { endYear100, startYear100, displayText });
                    break;
                }
                default:
                    displayText = '';
                    console.warn('알 수 없는 기간:', period);
            }
            
            console.log('getPeriodLabel 결과:', displayText);
            return displayText;
        } catch (error) {
            console.error('getPeriodLabel 오류:', error);
            return '';
        }
    }
    
    // 날짜 표시 업데이트
    function updateDateDisplay(period, date, labelText = '') {
        const dateDisplay = document.querySelector('.date-display');
        if (!dateDisplay) {
            console.warn('날짜 표시 요소를 찾을 수 없습니다.');
            return;
        }
        
        const displayText = labelText || getPeriodLabel(period, date);
        if (!displayText) {
            console.warn('표시할 날짜 텍스트가 없습니다.', { period, date, labelText });
            return;
        }
        
        // 업데이트
        dateDisplay.textContent = displayText;
        currentPeriodLabel = displayText;
    }
    
    function updateMetricsData(period, data = null, achievementRates = null, periodLabel = '') {
        const distanceValue = document.getElementById('distance-value');
        const timeValue = document.getElementById('time-value');
        const speedValue = document.getElementById('speed-value');
        
        // 진행률 바 컨테이너 가져오기
        const progressContainers = Array.from(document.querySelectorAll('.progress-container')).filter(container => container !== null);
        
        // 데이터가 없으면 0으로 설정
        if (!data) {
            data = {
                'exercise_hours': 0,
                'average_velocity': 0,
                'distance': 0
            };
        }
        if (!achievementRates) {
            achievementRates = {
                'exercise_hours': 0,
                'average_velocity': 0,
                'distance': 0
            };
        }
        
        // DOM 요소 존재 확인
        if (!distanceValue || !timeValue || !speedValue) {
            console.error('Required DOM elements not found:', {
                distanceValue: !!distanceValue,
                timeValue: !!timeValue,
                speedValue: !!speedValue
            });
            console.error('Available elements with similar IDs:');
            console.error('distance-value:', document.getElementById('distance-value'));
            console.error('time-value:', document.getElementById('time-value'));
            console.error('speed-value:', document.getElementById('speed-value'));
            return;
        }
        
        switch(period) {
            case 'day':
                // 일별 데이터 (실제 DB 데이터 또는 전달받은 데이터)
                distanceValue.textContent = parseFloat(data.distance).toFixed(2) + ' Km';
                timeValue.textContent = data.exercise_hours + ' Min';
                speedValue.textContent = data.average_velocity + ' m/s';
                
                
                // 통합 그래프 숨김
                const integratedChart = document.getElementById('integrated-chart');
                if (integratedChart) {
                    integratedChart.style.display = 'none';
                }
                
                // 개별 진행률 컨테이너 표시 (실제 달성률 사용)
                progressContainers.forEach((container, index) => {
                    if (!container) {
                        console.error(`Container ${index} is null`);
                        return;
                    }
                    try {
                        container.style.display = 'block';
                    } catch (error) {
                        console.error(`Error setting display for container ${index}:`, error);
                        return;
                    }
                    const heights = [
                        achievementRates.distance + '%',
                        achievementRates.exercise_hours + '%', 
                        achievementRates.average_velocity + '%'
                    ];
                    const classes = ['distance', 'time', 'speed'];
                    const colors = ['#28a745', '#007bff', '#ffc107'];
                    
                    container.innerHTML = `
                        <div style="width: 40px; height: 120px; background: #f0f0f0; border-radius: 20px; position: relative; margin: 0 auto; border: 2px solid #ddd;">
                            <div style="width: 100%; height: ${heights[index]}; background: ${colors[index]}; border-radius: 20px; position: absolute; bottom: 0; left: 0; transition: height 0.3s ease; min-height: 10px;"></div>
                        </div>
                    `;
                });
                
                // 목표 달성률 업데이트 (실제 달성률 사용)
                document.querySelectorAll('.achievement-rate')[0].textContent = '목표 달성률 ' + achievementRates.distance + '%';
                document.querySelectorAll('.achievement-rate')[1].textContent = '목표 달성률 ' + achievementRates.exercise_hours + '%';
                document.querySelectorAll('.achievement-rate')[2].textContent = '목표 달성률 ' + achievementRates.average_velocity + '%';
                
                // 일간 레이더 그래프 다시 그리기
                setTimeout(() => {
                    drawRadarChart('day', data);
                }, 100);
                break;
                
            case 'week':
                // 주별 데이터 (실제 DB 데이터 또는 전달받은 데이터)
                distanceValue.textContent = parseFloat(data.distance).toFixed(2) + ' Km';
                timeValue.textContent = data.exercise_hours + ' Min';
                speedValue.textContent = data.average_velocity + ' m/s';
                
                // 개별 진행률 컨테이너 숨김
                progressContainers.forEach(container => {
                    if (!container) {
                        console.error('Container is null');
                        return;
                    }
                    try {
                        container.style.display = 'none';
                    } catch (error) {
                        console.error('Error setting display to none:', error);
                    }
                });
                
                // 통합 선 그래프 표시
                const integratedChartWeek = document.getElementById('integrated-chart');
                if (integratedChartWeek) {
                    integratedChartWeek.style.display = 'block';
                }
                
                const chartWeekSvg = integratedChartWeek ? integratedChartWeek.querySelector('svg') : null;
                if (chartWeekSvg) {
                    const weekDailyData = (data && data.daily) ? data.daily : {
                        distance: Array(7).fill(0),
                        exercise_hours: Array(7).fill(0),
                        average_velocity: Array(7).fill(0)
                    };
                    
                    // 일일 목표값
                    const dayTargets = {
                        distance: 10, // km
                        exercise_hours: 100, // 분
                        average_velocity: (6 * 1000) / 3600 // m/s
                    };
                    
                    // 달성률로 변환
                    const weekLineData = [
                        weekDailyData.distance.map(val => Math.min(100, Math.round((val / dayTargets.distance) * 100))),
                        weekDailyData.exercise_hours.map(val => Math.min(100, Math.round((val / dayTargets.exercise_hours) * 100))),
                        weekDailyData.average_velocity.map(val => Math.min(100, Math.round((val / dayTargets.average_velocity) * 100)))
                    ];
                    const weekColors = ['#28a745', '#007bff', '#ffc107'];
                    const weekScale = 2.4;
                    const xStart = 100;
                    const xStep = 100;
                    
                    const currentDateObj = new Date(currentDate);
                    const weekStartDate = new Date(currentDateObj);
                    weekStartDate.setDate(currentDateObj.getDate() - currentDateObj.getDay());
                    const dayLabels = [];
                    for (let i = 0; i < 7; i++) {
                        const labelDate = new Date(weekStartDate);
                        labelDate.setDate(weekStartDate.getDate() + i);
                        const month = String(labelDate.getMonth() + 1).padStart(2, '0');
                        const day = String(labelDate.getDate()).padStart(2, '0');
                        dayLabels.push(`${month}.${day}`);
                    }
                    const weekLabelText = periodLabel || currentPeriodLabel || '';
                    
                    chartWeekSvg.innerHTML = `
                        <defs>
                            <pattern id="week-grid" width="100" height="60" patternUnits="userSpaceOnUse">
                                <path d="M 100 0 L 0 0 0 60" fill="none" stroke="#f0f0f0" stroke-width="1"/>
                            </pattern>
                        </defs>
                        <rect width="800" height="300" fill="url(#week-grid)"/>
                        ${weekLabelText ? `<text x="400" y="30" font-size="18" fill="#333" font-weight="600" text-anchor="middle">${weekLabelText}</text>` : ''}
                        
                        <!-- Y축 레이블 (달성률) -->
                        <text x="30" y="40" font-size="14" fill="#666">100</text>
                        <text x="30" y="100" font-size="14" fill="#666">80</text>
                        <text x="30" y="160" font-size="14" fill="#666">60</text>
                        <text x="30" y="220" font-size="14" fill="#666">40</text>
                        <text x="30" y="280" font-size="14" fill="#666">20</text>
                        
                        <!-- X축 레이블 (요일) -->
                        ${dayLabels.map((label, index) => `
                            <text x="${xStart + index * xStep}" y="290" font-size="14" fill="#666" text-anchor="middle">${label}</text>
                        `).join('')}
                        
                        ${weekLineData.map((dataArray, index) => {
                            const points = dataArray.map((value, i) => 
                                `${xStart + i * xStep},${300 - value * weekScale}`
                            ).join(' ');
                            const circles = dataArray.map((value, i) => 
                                `<circle cx="${xStart + i * xStep}" cy="${300 - value * weekScale}" r="6" fill="${weekColors[index]}"/>`
                            ).join('');
                            return `
                                <polyline points="${points}" fill="none" stroke="${weekColors[index]}" stroke-width="4"/>
                                ${circles}
                            `;
                        }).join('')}
                    `;
                }
                
                // 목표 달성률 업데이트
                const achievementRateElements = document.querySelectorAll('.achievement-rate');
                if (achievementRates) {
                    if (achievementRateElements[0]) achievementRateElements[0].textContent = `목표 달성률 ${achievementRates.distance}%`;
                    if (achievementRateElements[1]) achievementRateElements[1].textContent = `목표 달성률 ${achievementRates.exercise_hours}%`;
                    if (achievementRateElements[2]) achievementRateElements[2].textContent = `목표 달성률 ${achievementRates.average_velocity}%`;
                }
                
                // 주간 레이더 그래프 다시 그리기
                setTimeout(() => {
                    drawRadarChart('week', data);
                }, 100);
                
                break;
                
            case 'month':
                // 월별 데이터는 배열 형태로 받음 (4주 데이터)
                if (data && typeof data === 'object') {
                    // 합계 계산 (상단 표시용)
                    let totalDistance = 0;
                    let totalTime = 0;
                    let avgVelocity = 0;
                    let velocityCount = 0;
                    
                    if (Array.isArray(data.distance)) {
                        totalDistance = data.distance.reduce((sum, val) => sum + val, 0);
                        totalTime = data.exercise_hours.reduce((sum, val) => sum + val, 0);
                        
                        data.average_velocity.forEach(vel => {
                            if (vel > 0) {
                                avgVelocity += vel;
                                velocityCount++;
                            }
                        });
                        avgVelocity = velocityCount > 0 ? (avgVelocity / velocityCount) : 0;
                    }
                    
                    distanceValue.textContent = totalDistance.toFixed(2) + ' Km';
                    timeValue.textContent = totalTime.toFixed(1) + ' Min';
                    speedValue.textContent = avgVelocity.toFixed(1) + ' m/s';
                }
                
                // 개별 진행률 컨테이너 숨김
                progressContainers.forEach(container => {
                    if (!container) {
                        console.error('Container is null');
                        return;
                    }
                    try {
                        container.style.display = 'none';
                    } catch (error) {
                        console.error('Error setting display to none:', error);
                    }
                });
                
                // 통합 선 그래프 표시
                const integratedChartMonth = document.getElementById('integrated-chart');
                if (integratedChartMonth) {
                    integratedChartMonth.style.display = 'block';
                }
                
                // DB 데이터 사용 (4주 데이터)
                const rawData = {
                    distance: data.distance || [0, 0, 0, 0],
                    exercise_hours: data.exercise_hours || [0, 0, 0, 0],
                    average_velocity: data.average_velocity || [0, 0, 0, 0]
                };
                
                
                // 주간 목표 (7일 기준)
                const weekTargets = {
                    distance: 70,  // 10km * 7일 = 70km
                    exercise_hours: 700, // 100분 * 7일 = 700분
                    average_velocity: 6 // 6km/h (평균)
                };
                
                // 달성률로 변환 (0~100%)
                const lineData = [
                    rawData.distance.map(val => Math.min(100, Math.round((val / weekTargets.distance) * 100))),
                    rawData.exercise_hours.map(val => Math.min(100, Math.round((val / weekTargets.exercise_hours) * 100))),
                    rawData.average_velocity.map(val => Math.min(100, Math.round((val / weekTargets.average_velocity) * 100)))
                ];
                const colors = ['#28a745', '#007bff', '#ffc107'];
                
                
                // Y축 스케일: 100% = 240px
                const monthScale = 2.4; // 100%일 때 240px
                
                const chartSvg = integratedChartMonth.querySelector('svg');
                const monthLabelText = periodLabel || currentPeriodLabel || '';
                chartSvg.innerHTML = `
                    <!-- 격자 배경 -->
                    <defs>
                        <pattern id="month-grid" width="200" height="60" patternUnits="userSpaceOnUse">
                            <path d="M 200 0 L 0 0 0 60" fill="none" stroke="#f0f0f0" stroke-width="1"/>
                        </pattern>
                    </defs>
                    <rect width="800" height="300" fill="url(#month-grid)"/>
                    ${monthLabelText ? `<text x="400" y="30" font-size="18" fill="#333" font-weight="600" text-anchor="middle">${monthLabelText}</text>` : ''}
                    
                    <!-- Y축 레이블 (달성률) -->
                    <text x="30" y="40" font-size="14" fill="#666">100</text>
                    <text x="30" y="100" font-size="14" fill="#666">80</text>
                    <text x="30" y="160" font-size="14" fill="#666">60</text>
                    <text x="30" y="220" font-size="14" fill="#666">40</text>
                    <text x="30" y="280" font-size="14" fill="#666">20</text>
                    
                    <!-- X축 레이블 -->
                    <text x="200" y="290" font-size="14" fill="#666" text-anchor="middle">1주차</text>
                    <text x="400" y="290" font-size="14" fill="#666" text-anchor="middle">2주차</text>
                    <text x="600" y="290" font-size="14" fill="#666" text-anchor="middle">3주차</text>
                    <text x="800" y="290" font-size="14" fill="#666" text-anchor="middle">4주차</text>
                    
                    <!-- 3개 선 그래프 -->
                    ${lineData.map((dataArray, index) => {
                        const points = dataArray.map((value, i) => 
                            `${200 + i * 200},${300 - value * monthScale}`
                        ).join(' ');
                        const circles = dataArray.map((value, i) => 
                            `<circle cx="${200 + i * 200}" cy="${300 - value * monthScale}" r="6" fill="${colors[index]}"/>`
                        ).join('');
                        return `
                            <polyline points="${points}" fill="none" stroke="${colors[index]}" stroke-width="4"/>
                            ${circles}
                        `;
                    }).join('')}
                `;
                
                // 목표 달성률 업데이트 (실제 달성률 사용)
                if (achievementRates) {
                    document.querySelectorAll('.achievement-rate')[0].textContent = '목표 달성률 ' + achievementRates.distance + '%';
                    document.querySelectorAll('.achievement-rate')[1].textContent = '목표 달성률 ' + achievementRates.exercise_hours + '%';
                    document.querySelectorAll('.achievement-rate')[2].textContent = '목표 달성률 ' + achievementRates.average_velocity + '%';
                }
                
                // 월간 레이더 그래프 다시 그리기
                setTimeout(() => {
                    drawRadarChart('month', data);
                }, 100);
                
                break;
                
            case 'year':
                // 년별 데이터는 배열 형태로 받음 (12개월 데이터)
                if (data && typeof data === 'object') {
                    // 연간 평균 달성률 계산 (상단 표시용)
                    let totalDistance = 0;
                    let totalTime = 0;
                    let avgVelocity = 0;
                    let velocityCount = 0;
                    
                    if (Array.isArray(data.distance)) {
                        totalDistance = data.distance.reduce((sum, val) => sum + val, 0);
                        totalTime = data.exercise_hours.reduce((sum, val) => sum + val, 0);
                        
                        data.average_velocity.forEach(vel => {
                            if (vel > 0) {
                                avgVelocity += vel;
                                velocityCount++;
                            }
                        });
                        avgVelocity = velocityCount > 0 ? (avgVelocity / velocityCount) : 0;
                    }
                    
                    distanceValue.textContent = totalDistance.toFixed(2) + ' Km';
                    timeValue.textContent = totalTime.toFixed(1) + ' Min';
                    speedValue.textContent = avgVelocity.toFixed(1) + ' m/s';
                }
                
                // 개별 진행률 컨테이너 숨김
                progressContainers.forEach(container => {
                    if (!container) {
                        console.error('Container is null');
                        return;
                    }
                    try {
                        container.style.display = 'none';
                    } catch (error) {
                        console.error('Error setting display to none:', error);
                    }
                });
                
                // 통합 선 그래프 표시 (12개월 데이터)
                const yearIntegratedChart = document.getElementById('integrated-chart');
                if (yearIntegratedChart) {
                    yearIntegratedChart.style.display = 'block';
                }
                
                // DB 데이터 사용 (12개월 데이터)
                const rawYearData = {
                    distance: data.distance || [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    exercise_hours: data.exercise_hours || [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    average_velocity: data.average_velocity || [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                };
                
                // 월간 목표 (30일 기준)
                const monthTargets = {
                    distance: 300,  // 10km * 30일 = 300km
                    exercise_hours: 3000, // 100분 * 30일 = 3000분
                    average_velocity: 6 // 6km/h (평균)
                };
                
                // 달성률로 변환 (0~100%)
                const yearLineData = [
                    rawYearData.distance.map(val => Math.min(100, Math.round((val / monthTargets.distance) * 100))),
                    rawYearData.exercise_hours.map(val => Math.min(100, Math.round((val / monthTargets.exercise_hours) * 100))),
                    rawYearData.average_velocity.map(val => Math.min(100, Math.round((val / monthTargets.average_velocity) * 100)))
                ];
                
                // Y축 스케일: 100% = 240px
                const yearScale = 2.4; // 100%일 때 240px
                const yearColors = ['#28a745', '#007bff', '#ffc107'];
                const monthLabels = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
                
                const yearChartSvg = yearIntegratedChart.querySelector('svg');
                const yearLabelText = periodLabel || currentPeriodLabel || '';
                yearChartSvg.innerHTML = `
                    <!-- 격자 배경 -->
                    <defs>
                        <pattern id="year-grid" width="90" height="60" patternUnits="userSpaceOnUse">
                            <path d="M 90 0 L 0 0 0 60" fill="none" stroke="#f0f0f0" stroke-width="1"/>
                        </pattern>
                    </defs>
                    <rect width="1200" height="300" fill="url(#year-grid)"/>
                    ${yearLabelText ? `<text x="600" y="30" font-size="18" fill="#333" font-weight="600" text-anchor="middle">${yearLabelText}</text>` : ''}
                    
                    <!-- Y축 레이블 (달성률) -->
                    <text x="30" y="40" font-size="14" fill="#666">100</text>
                    <text x="30" y="88" font-size="14" fill="#666">80</text>
                    <text x="30" y="136" font-size="14" fill="#666">60</text>
                    <text x="30" y="184" font-size="14" fill="#666">40</text>
                    <text x="30" y="232" font-size="14" fill="#666">20</text>
                    
                    <!-- X축 레이블 (12개월) -->
                    ${monthLabels.map((month, index) => `
                        <text x="${80 + index * 90}" y="290" font-size="14" fill="#666" text-anchor="middle">${month}</text>
                    `).join('')}
                    
                    <!-- 3개 선 그래프 -->
                    ${yearLineData.map((dataArray, index) => {
                        const points = dataArray.map((value, i) => 
                            `${80 + i * 90},${300 - value * yearScale}`
                        ).join(' ');
                        const circles = dataArray.map((value, i) => 
                            `<circle cx="${80 + i * 90}" cy="${300 - value * yearScale}" r="6" fill="${yearColors[index]}"/>`
                        ).join('');
                        return `
                            <polyline points="${points}" fill="none" stroke="${yearColors[index]}" stroke-width="4"/>
                            ${circles}
                        `;
                    }).join('')}
                `;
                
                // 목표 달성률 업데이트 (실제 달성률 사용 또는 기본값)
                if (achievementRates && achievementRates.distance !== undefined) {
                    document.querySelectorAll('.achievement-rate')[0].textContent = '목표 달성률 ' + achievementRates.distance + '%';
                    document.querySelectorAll('.achievement-rate')[1].textContent = '목표 달성률 ' + achievementRates.exercise_hours + '%';
                    document.querySelectorAll('.achievement-rate')[2].textContent = '목표 달성률 ' + achievementRates.average_velocity + '%';
                } else {
                    document.querySelectorAll('.achievement-rate')[0].textContent = '목표 달성률 42%';
                    document.querySelectorAll('.achievement-rate')[1].textContent = '목표 달성률 48%';
                    document.querySelectorAll('.achievement-rate')[2].textContent = '목표 달성률 42%';
                }
                
                // 년간 레이더 그래프 다시 그리기
                setTimeout(() => {
                    drawRadarChart('year', data);
                }, 100);
                
                break;
            case '10year':
                if (data && typeof data === 'object') {
                    let totalDistanceDecade = 0;
                    let totalTimeDecade = 0;
                    let avgVelocityDecade = 0;
                    let velocityCountDecade = 0;

                    const rawDecadeData = {
                        distance: Array.isArray(data.distance) ? data.distance : Array(10).fill(0),
                        exercise_hours: Array.isArray(data.exercise_hours) ? data.exercise_hours : Array(10).fill(0),
                        average_velocity: Array.isArray(data.average_velocity) ? data.average_velocity : Array(10).fill(0)
                    };

                    if (typeof data.total_distance === 'number') {
                        totalDistanceDecade = data.total_distance;
                    } else {
                        totalDistanceDecade = rawDecadeData.distance.reduce((sum, val) => sum + val, 0);
                    }

                    if (typeof data.total_time === 'number') {
                        totalTimeDecade = data.total_time;
                    } else {
                        totalTimeDecade = rawDecadeData.exercise_hours.reduce((sum, val) => sum + val, 0);
                    }

                    if (typeof data.avg_velocity_total === 'number') {
                        avgVelocityDecade = data.avg_velocity_total;
                    } else {
                        rawDecadeData.average_velocity.forEach(vel => {
                            if (vel > 0) {
                                avgVelocityDecade += vel;
                                velocityCountDecade++;
                            }
                        });
                        avgVelocityDecade = velocityCountDecade > 0 ? (avgVelocityDecade / velocityCountDecade) : 0;
                    }

                    distanceValue.textContent = totalDistanceDecade.toFixed(2) + ' Km';
                    timeValue.textContent = totalTimeDecade.toFixed(1) + ' Min';
                    speedValue.textContent = avgVelocityDecade.toFixed(1) + ' m/s';

                    // 개별 진행률 컨테이너 숨김
                    progressContainers.forEach(container => {
                        if (!container) {
                            console.error('Container is null');
                            return;
                        }
                        try {
                            container.style.display = 'none';
                        } catch (error) {
                            console.error('Error setting display to none:', error);
                        }
                    });

                    // 통합 선 그래프 표시 (10년)
                    const decadeIntegratedChart = document.getElementById('integrated-chart');
                    if (decadeIntegratedChart) {
                        decadeIntegratedChart.style.display = 'block';
                    }

                    const decadeSvg = decadeIntegratedChart ? decadeIntegratedChart.querySelector('svg') : null;
                    if (decadeSvg) {
                        const decadeTargets = {
                            distance: 10 * 365, // 연간 3650km
                            exercise_hours: 100 * 365, // 연간 36500분
                            average_velocity: (6 * 1000) / 3600 // m/s
                        };

                        const decadeLineData = [
                            rawDecadeData.distance.map(val => Math.min(100, Math.round((val / decadeTargets.distance) * 100))),
                            rawDecadeData.exercise_hours.map(val => Math.min(100, Math.round((val / decadeTargets.exercise_hours) * 100))),
                            rawDecadeData.average_velocity.map(val => Math.min(100, Math.round((val / decadeTargets.average_velocity) * 100)))
                        ];

                        const decadeColors = ['#28a745', '#007bff', '#ffc107'];
                        const decadeScale = 2.4;
                        const xStartDecade = 90;
                        const xStepDecade = 90;

                        const endYear = new Date(currentDate).getFullYear();
                        const startYearDecade = endYear - 9;
                        const yearLabels = Array.from({ length: 10 }, (_, i) => startYearDecade + i);
                        const decadeLabelText = periodLabel || currentPeriodLabel || '';

                        decadeSvg.innerHTML = `
                            <defs>
                                <pattern id="decade-grid" width="90" height="60" patternUnits="userSpaceOnUse">
                                    <path d="M 90 0 L 0 0 0 60" fill="none" stroke="#f0f0f0" stroke-width="1"/>
                                </pattern>
                            </defs>
                            <rect width="1000" height="300" fill="url(#decade-grid)"/>
                            ${decadeLabelText ? `<text x="500" y="30" font-size="18" fill="#333" font-weight="600" text-anchor="middle">${decadeLabelText}</text>` : ''}
                            <text x="30" y="40" font-size="14" fill="#666">100</text>
                            <text x="30" y="100" font-size="14" fill="#666">80</text>
                            <text x="30" y="160" font-size="14" fill="#666">60</text>
                            <text x="30" y="220" font-size="14" fill="#666">40</text>
                            <text x="30" y="280" font-size="14" fill="#666">20</text>
                            ${yearLabels.map((label, index) => `
                                <text x="${xStartDecade + index * xStepDecade}" y="290" font-size="14" fill="#666" text-anchor="middle">${label}</text>
                            `).join('')}
                            ${decadeLineData.map((dataArray, index) => {
                                const points = dataArray.map((value, i) => 
                                    `${xStartDecade + i * xStepDecade},${300 - value * decadeScale}`
                                ).join(' ');
                                const circles = dataArray.map((value, i) => 
                                    `<circle cx="${xStartDecade + i * xStepDecade}" cy="${300 - value * decadeScale}" r="6" fill="${decadeColors[index]}"/>`
                                ).join('');
                                return `
                                    <polyline points="${points}" fill="none" stroke="${decadeColors[index]}" stroke-width="4"/>
                                    ${circles}
                                `;
                            }).join('')}
                        `;
                    }

                    const decadeAchievementElements = document.querySelectorAll('.achievement-rate');
                    if (achievementRates) {
                        if (decadeAchievementElements[0]) decadeAchievementElements[0].textContent = `목표 달성률 ${achievementRates.distance}%`;
                        if (decadeAchievementElements[1]) decadeAchievementElements[1].textContent = `목표 달성률 ${achievementRates.exercise_hours}%`;
                        if (decadeAchievementElements[2]) decadeAchievementElements[2].textContent = `목표 달성률 ${achievementRates.average_velocity}%`;
                    }
                }

                setTimeout(() => {
                    drawRadarChart('10year', data);
                }, 100);

                break;
            case '30year':
                if (data && typeof data === 'object') {
                    let totalDistance30 = 0;
                    let totalTime30 = 0;
                    let avgVelocity30 = 0;
                    let velocityCount30 = 0;

                    const raw30Data = {
                        distance: Array.isArray(data.distance) ? data.distance : Array(30).fill(0),
                        exercise_hours: Array.isArray(data.exercise_hours) ? data.exercise_hours : Array(30).fill(0),
                        average_velocity: Array.isArray(data.average_velocity) ? data.average_velocity : Array(30).fill(0)
                    };

                    if (typeof data.total_distance === 'number') {
                        totalDistance30 = data.total_distance;
                    } else {
                        totalDistance30 = raw30Data.distance.reduce((sum, val) => sum + val, 0);
                    }

                    if (typeof data.total_time === 'number') {
                        totalTime30 = data.total_time;
                    } else {
                        totalTime30 = raw30Data.exercise_hours.reduce((sum, val) => sum + val, 0);
                    }

                    const validVelocities30 = raw30Data.average_velocity.filter(v => v > 0);
                    if (validVelocities30.length > 0) {
                        avgVelocity30 = validVelocities30.reduce((sum, val) => sum + val, 0) / validVelocities30.length;
                    } else {
                        avgVelocity30 = 0;
                    }

                    distanceValue.textContent = totalDistance30.toFixed(2) + ' Km';
                    timeValue.textContent = totalTime30.toFixed(0) + ' Min';
                    speedValue.textContent = avgVelocity30.toFixed(2) + ' m/s';

                    progressContainers.forEach(container => {
                        if (container) container.style.display = 'none';
                    });

                    const integratedChart30 = document.getElementById('integrated-chart');
                    if (integratedChart30) {
                        integratedChart30.style.display = 'block';
                        const svg30 = integratedChart30.querySelector('svg');

                        const thirtyYearScale = 2.4;
                        const xStart30 = 70;
                        const xStep30 = 38;

                        const endYear30 = new Date(currentDate).getFullYear();
                        const startYear30 = endYear30 - 29;
                        const yearLabels30 = Array.from({ length: 30 }, (_, i) => startYear30 + i);
                        const thirtyLabelText = periodLabel || currentPeriodLabel || '';

                        // 30년 목표값 설정
                        const thirtyTargets = {
                            distance: 10 * 365, // 연간 3650km
                            exercise_hours: 100 * 365, // 연간 36500분
                            average_velocity: (6 * 1000) / 3600 // m/s
                        };

                        svg30.innerHTML = `
                            <defs>
                                <pattern id="thirty-grid" width="38" height="60" patternUnits="userSpaceOnUse">
                                    <path d="M 38 0 L 0 0 0 60" fill="none" stroke="#f0f0f0" stroke-width="1"/>
                                </pattern>
                            </defs>
                            <rect width="1200" height="300" fill="url(#thirty-grid)"/>
                            ${thirtyLabelText ? `<text x="600" y="30" font-size="18" fill="#333" font-weight="600" text-anchor="middle">${thirtyLabelText}</text>` : ''}
                            <text x="30" y="40" font-size="14" fill="#666">100</text>
                            <text x="30" y="100" font-size="14" fill="#666">80</text>
                            <text x="30" y="160" font-size="14" fill="#666">60</text>
                            <text x="30" y="220" font-size="14" fill="#666">40</text>
                            <text x="30" y="280" font-size="14" fill="#666">20</text>
                            ${yearLabels30.map((label, index) => {
                                // 5년마다 레이블 표시
                                if (index % 5 === 0) {
                                    return `<text x="${xStart30 + index * xStep30}" y="290" font-size="12" fill="#666" text-anchor="middle">${label}</text>`;
                                }
                                return '';
                            }).join('')}
                        `;

                        // 달성률로 변환 (목표 대비 %)
                        const thirtyLineData = [
                            raw30Data.distance.map(val => Math.min(100, Math.round((val / thirtyTargets.distance) * 100))),
                            raw30Data.exercise_hours.map(val => Math.min(100, Math.round((val / thirtyTargets.exercise_hours) * 100))),
                            raw30Data.average_velocity.map(val => Math.min(100, Math.round((val / thirtyTargets.average_velocity) * 100)))
                        ];
                        const thirtyColors = ['#28a745', '#007bff', '#ffc107'];

                        thirtyLineData.forEach((dataArray, index) => {
                            const points = dataArray.map((value, i) => 
                                `${xStart30 + i * xStep30},${300 - value * thirtyYearScale}`
                            ).join(' ');
                            const circles = dataArray.map((value, i) => 
                                `<circle cx="${xStart30 + i * xStep30}" cy="${300 - value * thirtyYearScale}" r="4" fill="${thirtyColors[index]}"/>`
                            ).join('');
                            
                            svg30.innerHTML += `
                                <polyline points="${points}" fill="none" stroke="${thirtyColors[index]}" stroke-width="3"/>
                                ${circles}
                            `;
                        });
                    }

                    const achievement30Elements = document.querySelectorAll('.achievement-rate');
                    if (achievementRates) {
                        if (achievement30Elements[0]) achievement30Elements[0].textContent = `목표 달성률 ${achievementRates.distance}%`;
                        if (achievement30Elements[1]) achievement30Elements[1].textContent = `목표 달성률 ${achievementRates.exercise_hours}%`;
                        if (achievement30Elements[2]) achievement30Elements[2].textContent = `목표 달성률 ${achievementRates.average_velocity}%`;
                    }
                }

                setTimeout(() => {
                    drawRadarChart('30year', data);
                }, 100);

                break;
            case '100year':
                if (data && typeof data === 'object') {
                    let totalDistance100 = 0;
                    let totalTime100 = 0;
                    let avgVelocity100 = 0;

                    const raw100Data = {
                        distance: Array.isArray(data.distance) ? data.distance : Array(10).fill(0),
                        exercise_hours: Array.isArray(data.exercise_hours) ? data.exercise_hours : Array(10).fill(0),
                        average_velocity: Array.isArray(data.average_velocity) ? data.average_velocity : Array(10).fill(0)
                    };

                    if (typeof data.total_distance === 'number') {
                        totalDistance100 = data.total_distance;
                    } else {
                        totalDistance100 = raw100Data.distance.reduce((sum, val) => sum + val, 0);
                    }

                    if (typeof data.total_time === 'number') {
                        totalTime100 = data.total_time;
                    } else {
                        totalTime100 = raw100Data.exercise_hours.reduce((sum, val) => sum + val, 0);
                    }

                    const validVelocities100 = raw100Data.average_velocity.filter(v => v > 0);
                    if (validVelocities100.length > 0) {
                        avgVelocity100 = validVelocities100.reduce((sum, val) => sum + val, 0) / validVelocities100.length;
                    } else {
                        avgVelocity100 = 0;
                    }

                    distanceValue.textContent = totalDistance100.toFixed(2) + ' Km';
                    timeValue.textContent = totalTime100.toFixed(0) + ' Min';
                    speedValue.textContent = avgVelocity100.toFixed(2) + ' m/s';

                    progressContainers.forEach(container => {
                        if (container) container.style.display = 'none';
                    });

                    const integratedChart100 = document.getElementById('integrated-chart');
                    if (integratedChart100) {
                        integratedChart100.style.display = 'block';
                        const svg100 = integratedChart100.querySelector('svg');

                        const hundredYearScale = 2.4;
                        const xStart100 = 90;
                        const xStep100 = 90;

                        const endYear100 = new Date(currentDate).getFullYear();
                        const startYear100 = endYear100 - 99;
                        // X축: 10년 단위로 10개 포인트 (0년째, 10년째, 20년째, ..., 90년째)
                        const decadeLabels100 = Array.from({ length: 10 }, (_, i) => startYear100 + (i * 10));
                        const hundredLabelText = periodLabel || currentPeriodLabel || '';

                        // 100년 목표값 설정
                        const hundredTargets = {
                            distance: 10 * 365 * 10, // 10년치 거리 × 10
                            exercise_hours: 100 * 365 * 10, // 10년치 시간 × 10
                            average_velocity: (6 * 1000) / 3600 // m/s
                        };

                        svg100.innerHTML = `
                            <defs>
                                <pattern id="hundred-grid" width="90" height="60" patternUnits="userSpaceOnUse">
                                    <path d="M 90 0 L 0 0 0 60" fill="none" stroke="#f0f0f0" stroke-width="1"/>
                                </pattern>
                            </defs>
                            <rect width="1000" height="300" fill="url(#hundred-grid)"/>
                            ${hundredLabelText ? `<text x="500" y="30" font-size="18" fill="#333" font-weight="600" text-anchor="middle">${hundredLabelText}</text>` : ''}
                            <text x="30" y="40" font-size="14" fill="#666">100</text>
                            <text x="30" y="100" font-size="14" fill="#666">80</text>
                            <text x="30" y="160" font-size="14" fill="#666">60</text>
                            <text x="30" y="220" font-size="14" fill="#666">40</text>
                            <text x="30" y="280" font-size="14" fill="#666">20</text>
                            ${decadeLabels100.map((label, index) => `
                                <text x="${xStart100 + index * xStep100}" y="290" font-size="12" fill="#666" text-anchor="middle">${label}</text>
                            `).join('')}
                        `;

                        // 달성률로 변환 (목표 대비 %)
                        const hundredLineData = [
                            raw100Data.distance.map(val => Math.min(100, Math.round((val / hundredTargets.distance) * 100))),
                            raw100Data.exercise_hours.map(val => Math.min(100, Math.round((val / hundredTargets.exercise_hours) * 100))),
                            raw100Data.average_velocity.map(val => Math.min(100, Math.round((val / hundredTargets.average_velocity) * 100)))
                        ];
                        const hundredColors = ['#28a745', '#007bff', '#ffc107'];

                        hundredLineData.forEach((dataArray, index) => {
                            const points = dataArray.map((value, i) => 
                                `${xStart100 + i * xStep100},${300 - value * hundredYearScale}`
                            ).join(' ');
                            const circles = dataArray.map((value, i) => 
                                `<circle cx="${xStart100 + i * xStep100}" cy="${300 - value * hundredYearScale}" r="6" fill="${hundredColors[index]}"/>`
                            ).join('');
                            
                            svg100.innerHTML += `
                                <polyline points="${points}" fill="none" stroke="${hundredColors[index]}" stroke-width="4"/>
                                ${circles}
                            `;
                        });
                    }

                    const achievement100Elements = document.querySelectorAll('.achievement-rate');
                    if (achievementRates) {
                        if (achievement100Elements[0]) achievement100Elements[0].textContent = `목표 달성률 ${achievementRates.distance}%`;
                        if (achievement100Elements[1]) achievement100Elements[1].textContent = `목표 달성률 ${achievementRates.exercise_hours}%`;
                        if (achievement100Elements[2]) achievement100Elements[2].textContent = `목표 달성률 ${achievementRates.average_velocity}%`;
                    }
                }

                setTimeout(() => {
                    drawRadarChart('100year', data);
                }, 100);

                break;
        }
    }
    
    
    // 초기 로드 시 현재일 데이터 자동 로드
    document.addEventListener('DOMContentLoaded', async () => {
        // 현재 날짜로 초기화
        currentDate = '<?php echo date('Y-m-d'); ?>';
        currentPeriod = 'day';
        
        // 모든 기간 버튼 비활성화
        document.querySelectorAll('.period-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // 일간 버튼 활성화
        const dayButton = document.querySelector('.period-btn[data-period="day"]');
        if (dayButton) {
            dayButton.classList.add('active');
        }
        
        // 기간 선택 버튼 이벤트 (중복 클릭 방지)
        if (!window.periodButtonHandler) {
            window.periodButtonHandler = async function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (isNavigating) return; // 중복 클릭 방지
                
                // 모든 버튼 비활성화
                document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
                // 현재 버튼 활성화
                this.classList.add('active');
                
                // 기간에 따른 날짜 범위 업데이트
                const period = this.dataset.period;
                currentPeriod = period;
                currentDate = '<?php echo date('Y-m-d'); ?>'; // 현재 날짜로 초기화
                
                // 데이터 로드 (내부에서 날짜 표시 업데이트됨)
                await updateMetricsDataWithDate(period, currentDate);
            };
        }
        
        document.querySelectorAll('.period-btn:not(.disabled)').forEach(btn => {
            btn.removeEventListener('click', window.periodButtonHandler);
            btn.addEventListener('click', window.periodButtonHandler);
        });
        
        // DOM 요소 직접 조작으로 즉시 그래프 그리기
        const distanceValue = document.getElementById('distance-value');
        const timeValue = document.getElementById('time-value');
        const speedValue = document.getElementById('speed-value');
        const progressContainers = document.querySelectorAll('.progress-container');
        
        // 값 즉시 업데이트 (실제 DB 데이터)
        if (distanceValue) {
            distanceValue.textContent = parseFloat(todayStats.distance).toFixed(2) + ' Km';
        }
        if (timeValue) {
            timeValue.textContent = todayStats.exercise_hours + ' Min';
        }
        if (speedValue) {
            speedValue.textContent = todayStats.average_velocity + ' m/s';
        }
        
        // 그래프 바 즉시 생성 (실제 달성률) - 초기 로딩이므로 transition 없음
        
        progressContainers.forEach((container, index) => {
            if (!container) {
                console.error(`컨테이너 ${index}가 null입니다`);
                return;
            }
            
            const heights = [
                achievementRates.distance + '%',
                achievementRates.exercise_hours + '%',
                achievementRates.average_velocity + '%'
            ];
            const colors = ['#28a745', '#007bff', '#ffc107'];
            const labels = ['운동거리', '운동시간', '평균속도'];
            
            
            container.style.display = 'block';
            // 초기 로딩: transition 없이 즉시 표시
            container.innerHTML = `
                <div style="width: 40px; height: 120px; background: #f0f0f0; border-radius: 20px; position: relative; margin: 0 auto; border: 2px solid #ddd;">
                    <div class="progress-bar-fill" style="width: 100%; height: ${heights[index]}; background: ${colors[index]}; border-radius: 20px; position: absolute; bottom: 0; left: 0; min-height: 10px;"></div>
                </div>
            `;
            
        });
        
        // 달성률 텍스트 즉시 업데이트
        const achievementRateElements = document.querySelectorAll('.achievement-rate');
        if (achievementRateElements[0]) {
            achievementRateElements[0].textContent = '목표 달성률 ' + achievementRates.distance + '%';
        }
        if (achievementRateElements[1]) {
            achievementRateElements[1].textContent = '목표 달성률 ' + achievementRates.exercise_hours + '%';
        }
        if (achievementRateElements[2]) {
            achievementRateElements[2].textContent = '목표 달성률 ' + achievementRates.average_velocity + '%';
        }
        
        // 날짜 표시 업데이트
        updateDateDisplay('day', currentDate);
        
        // 레이더 그래프 그리기
        setTimeout(() => {
            drawRadarChart();
        }, 100);
        
        // 네비게이션 화살표 이벤트 (클릭 중복 방지)
        const leftArrow = document.querySelector('.nav-arrow.left');
        const rightArrow = document.querySelector('.nav-arrow.right');
        
        // 이벤트 핸들러 함수 정의 (전역 스코프에 저장하여 중복 방지)
        if (!window.navArrowHandlers) {
            window.navArrowHandlers = {
                left: async function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (isNavigating) return; // 중복 클릭 방지
                    
                    const newDate = getPrevDate(currentDate, currentPeriod);
                    await updateMetricsDataWithDate(currentPeriod, newDate);
                },
                right: async function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (isNavigating) return; // 중복 클릭 방지
                    
                    const newDate = getNextDate(currentDate, currentPeriod);
                    await updateMetricsDataWithDate(currentPeriod, newDate);
                }
            };
        }
        
        // 기존 이벤트 리스너 제거 후 새로 등록
        if (leftArrow) {
            leftArrow.removeEventListener('click', window.navArrowHandlers.left);
            leftArrow.addEventListener('click', window.navArrowHandlers.left);
        }
        
        if (rightArrow) {
            rightArrow.removeEventListener('click', window.navArrowHandlers.right);
            rightArrow.addEventListener('click', window.navArrowHandlers.right);
        }
    });
    
    // 레이더 그래프 그리기 함수 (전역 스코프로 이동)
    window.drawRadarChart = function(period = 'day', data = null) {
        
        // 기간에 따른 데이터 선택
        let exerciseTime, avgSpeed;
        
        if (period === 'day' && data && data.exercise_hours !== undefined && data.average_velocity !== undefined) {
            // 일간: 전달받은 날짜별 데이터 사용
            exerciseTime = data.exercise_hours; // 분
            avgSpeed = data.average_velocity; // m/s
        } else if (period === 'day') {
            // 일간 데이터가 없으면 todayStats 사용 (초기 로드 시)
            exerciseTime = todayStats.exercise_hours; // 분
            avgSpeed = todayStats.average_velocity; // m/s
        } else if (data && data.exercise_hours !== undefined && data.average_velocity !== undefined) {
            if (period === 'week') {
                // 주간: 총 운동시간과 평균속도 사용
                exerciseTime = data.exercise_hours; // 주간 총 운동시간 (분)
                avgSpeed = data.average_velocity; // 주간 평균속도 (m/s)
            } else if (period === 'month') {
                // 월간: 4주 데이터의 총합 사용
                if (Array.isArray(data.exercise_hours)) {
                    exerciseTime = data.exercise_hours.reduce((sum, val) => sum + val, 0); // 4주 총합
                    // 평균속도는 각 주의 평균속도의 평균
                    const validVelocities = data.average_velocity.filter(v => v > 0);
                    avgSpeed = validVelocities.length > 0 
                        ? validVelocities.reduce((sum, val) => sum + val, 0) / validVelocities.length 
                        : 0;
                } else {
                    exerciseTime = data.exercise_hours; // 월간 총합
                    avgSpeed = data.average_velocity; // 월간 평균속도
                }
            } else if (period === 'year') {
                // 년간: 12개월 데이터의 총합 사용
                if (Array.isArray(data.exercise_hours)) {
                    exerciseTime = data.exercise_hours.reduce((sum, val) => sum + val, 0); // 12개월 총합
                    // 평균속도는 각 월의 평균속도의 평균
                    const validVelocities = data.average_velocity.filter(v => v > 0);
                    avgSpeed = validVelocities.length > 0 
                        ? validVelocities.reduce((sum, val) => sum + val, 0) / validVelocities.length 
                        : 0;
                } else {
                    exerciseTime = data.exercise_hours; // 년간 총합
                    avgSpeed = data.average_velocity; // 년간 평균속도
                }
            } else if (period === '10year') {
                if (data && Array.isArray(data.exercise_hours)) {
                    exerciseTime = data.exercise_hours.reduce((sum, val) => sum + val, 0);
                } else if (data && typeof data.total_time === 'number') {
                    exerciseTime = data.total_time;
                } else {
                    exerciseTime = todayStats.exercise_hours * 3650; // 기본값
                }

                if (data && Array.isArray(data.average_velocity)) {
                    const validDecadeVelocities = data.average_velocity.filter(v => v > 0);
                    avgSpeed = validDecadeVelocities.length > 0
                        ? validDecadeVelocities.reduce((sum, val) => sum + val, 0) / validDecadeVelocities.length
                        : 0;
                } else if (data && typeof data.avg_velocity_total === 'number') {
                    avgSpeed = data.avg_velocity_total;
                } else {
                    avgSpeed = todayStats.average_velocity;
                }
            } else if (period === '30year') {
                if (data && Array.isArray(data.exercise_hours)) {
                    exerciseTime = data.exercise_hours.reduce((sum, val) => sum + val, 0);
                } else if (data && typeof data.total_time === 'number') {
                    exerciseTime = data.total_time;
                } else {
                    exerciseTime = todayStats.exercise_hours * 10950; // 기본값 (30년)
                }

                if (data && Array.isArray(data.average_velocity)) {
                    const valid30Velocities = data.average_velocity.filter(v => v > 0);
                    avgSpeed = valid30Velocities.length > 0
                        ? valid30Velocities.reduce((sum, val) => sum + val, 0) / valid30Velocities.length
                        : 0;
                } else if (data && typeof data.avg_velocity_total === 'number') {
                    avgSpeed = data.avg_velocity_total;
                } else {
                    avgSpeed = todayStats.average_velocity;
                }
            } else if (period === '100year') {
                if (data && Array.isArray(data.exercise_hours)) {
                    exerciseTime = data.exercise_hours.reduce((sum, val) => sum + val, 0);
                } else if (data && typeof data.total_time === 'number') {
                    exerciseTime = data.total_time;
                } else {
                    exerciseTime = todayStats.exercise_hours * 36500; // 기본값 (100년)
                }

                if (data && Array.isArray(data.average_velocity)) {
                    const valid100Velocities = data.average_velocity.filter(v => v > 0);
                    avgSpeed = valid100Velocities.length > 0
                        ? valid100Velocities.reduce((sum, val) => sum + val, 0) / valid100Velocities.length
                        : 0;
                } else if (data && typeof data.avg_velocity_total === 'number') {
                    avgSpeed = data.avg_velocity_total;
                } else {
                    avgSpeed = todayStats.average_velocity;
                }
            }
        } else {
            // 기본값 사용
            exerciseTime = todayStats.exercise_hours;
            avgSpeed = todayStats.average_velocity;
        }
        
        
        // 기간별 목표값 설정
        let timeTarget, speedTarget;
        
        switch (period) {
            case 'day':
                timeTarget = 100; // 일일 목표: 100분
                speedTarget = 6 * 1000 / 3600; // 6km/h = 1.67m/s
                break;
            case 'week':
                timeTarget = 700; // 주간 목표: 100분 × 7일 = 700분
                speedTarget = 6 * 1000 / 3600; // 6km/h = 1.67m/s
                break;
            case 'month':
                timeTarget = 2800; // 월간 목표: 100분 × 28일 = 2800분
                speedTarget = 6 * 1000 / 3600; // 6km/h = 1.67m/s
                break;
            case 'year':
                timeTarget = 36500; // 년간 목표: 100분 × 365일 = 36500분
                speedTarget = 6 * 1000 / 3600; // 6km/h = 1.67m/s
                break;
            case '10year':
                timeTarget = 36500 * 10; // 10년 목표
                speedTarget = 6 * 1000 / 3600;
                break;
            case '30year':
                timeTarget = 36500 * 30; // 30년 목표
                speedTarget = 6 * 1000 / 3600;
                break;
            case '100year':
                timeTarget = 36500 * 100; // 100년 목표
                speedTarget = 6 * 1000 / 3600;
                break;
            default:
                timeTarget = 100;
                speedTarget = 6 * 1000 / 3600;
        }
        
        // 운동시간 점수 (기간별 목표 이상 = 100점)
        const timeScore = Math.min(100, (exerciseTime / timeTarget) * 100);
        
        // 속도 점수 (6km/h = 1.67m/s 이상 = 100점)
        const speedScore = Math.min(100, (avgSpeed / speedTarget) * 100);
        
        // VR 최종 점수 (두 점수의 평균)
        const vrScore = Math.min(100, Math.round((timeScore + speedScore) / 2));
        
        
        // 레이더 차트 데이터
        const radarData = [
            { label: '순발력', score: 75 },
            { label: '균형감각', score: 75 },
            { label: '수리능력', score: 75 },
            { label: 'AR', score: 75 },
            { label: 'VR', score: vrScore }
        ];
        
        const svg = document.getElementById('radar-chart');
        if (!svg) {
            return;
        }
        const centerX = 250;
        const centerY = 250;
        const maxRadius = 150;
        const levels = 5; // 5단계 (0, 20, 40, 60, 80, 100)
        
        let svgContent = '';
        
        // 배경 동심원 그리기
        for (let i = 1; i <= levels; i++) {
            const radius = (maxRadius / levels) * i;
            svgContent += `<circle cx="${centerX}" cy="${centerY}" r="${radius}" fill="none" stroke="#e0e0e0" stroke-width="1"/>`;
        }
        
        // 축 그리기
        const angleStep = (2 * Math.PI) / radarData.length;
        radarData.forEach((item, index) => {
            const angle = angleStep * index - Math.PI / 2; // -90도부터 시작
            const x = centerX + maxRadius * Math.cos(angle);
            const y = centerY + maxRadius * Math.sin(angle);
            
            svgContent += `<line x1="${centerX}" y1="${centerY}" x2="${x}" y2="${y}" stroke="#ccc" stroke-width="1"/>`;
            
            // 레이블
            const labelX = centerX + (maxRadius + 30) * Math.cos(angle);
            const labelY = centerY + (maxRadius + 30) * Math.sin(angle);
            svgContent += `<text x="${labelX}" y="${labelY}" text-anchor="middle" dominant-baseline="middle" font-size="14" font-weight="bold" fill="#333">${item.label}</text>`;
            
            // 점수 표시 (항목별로 다른 위치 조정)
            let scoreDistance = 50; // 기본 거리
            
            // VR과 균형감각은 더 멀리 배치하여 겹치지 않도록 함
            if (item.label === 'VR') {
                scoreDistance = 70; // VR은 더 멀리
            } else if (item.label === '균형감각') {
                scoreDistance = 65; // 균형감각은 VR과 적절한 간격
            }
            
            const scoreX = centerX + (maxRadius + scoreDistance) * Math.cos(angle);
            const scoreY = centerY + (maxRadius + scoreDistance) * Math.sin(angle);
            svgContent += `<text x="${scoreX}" y="${scoreY}" text-anchor="middle" dominant-baseline="middle" font-size="12" fill="#666">${item.score}점</text>`;
        });
        
        // 데이터 영역 그리기
        let polygonPoints = '';
        radarData.forEach((item, index) => {
            const angle = angleStep * index - Math.PI / 2;
            const radius = (item.score / 100) * maxRadius;
            const x = centerX + radius * Math.cos(angle);
            const y = centerY + radius * Math.sin(angle);
            polygonPoints += `${x},${y} `;
        });
        
        svgContent += `<polygon points="${polygonPoints}" fill="rgba(102, 126, 234, 0.3)" stroke="#667eea" stroke-width="2"/>`;
        
        // 데이터 포인트 그리기
        radarData.forEach((item, index) => {
            const angle = angleStep * index - Math.PI / 2;
            const radius = (item.score / 100) * maxRadius;
            const x = centerX + radius * Math.cos(angle);
            const y = centerY + radius * Math.sin(angle);
            
            svgContent += `<circle cx="${x}" cy="${y}" r="5" fill="#667eea" stroke="white" stroke-width="2"/>`;
        });
        
        svg.innerHTML = svgContent;
    };
</script>
<script src="js/mypage.js"></script>

<?php include 'footer.php'; ?>
