<?php
session_start();
require_once 'admin_functions.php';
checkAdminSession();

require_once 'DAO/mysqli_con.php';

try {
    $conn = getConnection();
    
    // 모든 테이블 목록 확인
    $tables_query = "SHOW TABLES";
    $tables_result = mysqli_query($conn, $tables_query);
    $all_tables = [];
    if ($tables_result) {
        while ($row = mysqli_fetch_array($tables_result)) {
            $all_tables[] = $row[0];
        }
    }

    
    require_once 'DAO/CycleDAO.php';
    
    // 테이블 존재 확인
    $table_exists = CycleDAO::tableExists();
    
    $cycle_data = [];
    
    if ($table_exists) {
        // 검색어 처리
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $search_condition = '';
        if (!empty($search)) {
            $search_escaped = mysqli_real_escape_string($conn, $search);
            $search_condition = "WHERE (name LIKE '%$search_escaped%' OR exercise_time LIKE '%$search_escaped%' OR average_velocity LIKE '%$search_escaped%' OR distance LIKE '%$search_escaped%')";
        }
        
        // 페이지네이션 설정
        $items_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($current_page - 1) * $items_per_page;
        
        // 전체 데이터 개수 조회 (검색 조건 포함)
        $total_items = CycleDAO::getTotalCycles($search_condition);
        
        // 총 페이지 수 계산
        $total_pages = ceil($total_items / $items_per_page);
        
        // wintech_cycle 데이터 조회 (검색 조건 및 페이지네이션 적용)
        $cycle_data = CycleDAO::getCycles($search_condition, $items_per_page, $offset);
    } else {
        $total_items = 0;
        $total_pages = 0;
        $current_page = 1;
        $items_per_page = 10;
    }
    
    
    // 통계 데이터 계산
    $stats = [
        'total_sessions' => 0,
        'total_users' => 0,
        'avg_cycle_time' => 0,
        'total_distance' => 0
    ];
    
    // 총 세션 수
    $count_query = "SELECT COUNT(*) as total FROM wintech_cycle";
    $count_result = mysqli_query($conn, $count_query);
    if ($count_result) {
        $count_row = mysqli_fetch_assoc($count_result);
        $stats['total_sessions'] = $count_row['total'];
    }
    
    // 총 사용자 수 (고유 사용자)
    $user_query = "SELECT COUNT(DISTINCT account) as total FROM wintech_cycle";
    $user_result = mysqli_query($conn, $user_query);
    if ($user_result) {
        $user_row = mysqli_fetch_assoc($user_result);
        $stats['total_users'] = $user_row['total'];
    }
    
    // 평균 사이클 시간
    $avg_query = "SELECT AVG(cycle_time) as avg_time FROM wintech_cycle WHERE cycle_time > 0";
    $avg_result = mysqli_query($conn, $avg_query);
    if ($avg_result) {
        $avg_row = mysqli_fetch_assoc($avg_result);
        $stats['avg_cycle_time'] = round($avg_row['avg_time'], 1);
    }
    
    // 총 거리
    $distance_query = "SELECT SUM(cycle_distance) as total_distance FROM wintech_cycle WHERE cycle_distance > 0";
    $distance_result = mysqli_query($conn, $distance_query);
    if ($distance_result) {
        $distance_row = mysqli_fetch_assoc($distance_result);
        $stats['total_distance'] = round($distance_row['total_distance'], 2);
    }
    
    mysqli_close($conn);
    
} catch (Exception $e) {
    $cycle_data = [];
    $stats = [
        'total_sessions' => 0,
        'total_users' => 0,
        'avg_cycle_time' => 0,
        'total_distance' => 0
    ];
}

$page_title = 'VR 데이터관리 - 행복운동센터';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin_sidebar.css">
    <link rel="stylesheet" href="css/admin-common.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>
        <div class="admin-main">
            <div class="admin-header">
                <h1 class="admin-title">VR 데이터관리</h1>
            </div>
            
            <!-- 사이클 데이터 테이블 -->
            <div class="data-card">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                    <h3 style="color:#2c3e50;margin:0;">
                        <i class="fas fa-table"></i> 사이클 데이터
                    </h3>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <label style="font-size:14px;color:#666;">페이지당 표시:</label>
                        <select id="perPageSelect" style="padding:5px 10px;border:1px solid #ddd;border-radius:4px;">
                            <option value="5" <?php echo $items_per_page == 5 ? 'selected' : ''; ?>>5개</option>
                            <option value="10" <?php echo $items_per_page == 10 ? 'selected' : ''; ?>>10개</option>
                            <option value="20" <?php echo $items_per_page == 20 ? 'selected' : ''; ?>>20개</option>
                            <option value="50" <?php echo $items_per_page == 50 ? 'selected' : ''; ?>>50개</option>
                            <option value="100" <?php echo $items_per_page == 100 ? 'selected' : ''; ?>>100개</option>
                        </select>
                    </div>
                </div>
                
                <!-- 검색 섹션 -->
                <div style="margin-bottom:20px;padding:15px;background:#f8f9fa;border-radius:8px;">
                    <form method="GET" style="display:flex;gap:10px;align-items:center;">
                        <input type="hidden" name="per_page" value="<?php echo $items_per_page; ?>">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="아이디, 운동시간, 평균속도, 거리로 검색..." 
                               style="flex:1;padding:10px 15px;border:2px solid #ddd;border-radius:5px;font-size:14px;">
                        <button type="submit" style="background:#3498db;color:#fff;padding:10px 20px;border:none;border-radius:5px;cursor:pointer;font-size:14px;">
                            <i class="fas fa-search"></i> 검색
                        </button>
                        <?php if (!empty($search)): ?>
                            <a href="?per_page=<?php echo $items_per_page; ?>" style="background:#6c757d;color:#fff;padding:10px 20px;border:none;border-radius:5px;text-decoration:none;font-size:14px;">
                                <i class="fas fa-times"></i> 초기화
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- 데이터 정보 -->
                <div style="margin-bottom:15px;color:#666;font-size:14px;">
                    <?php if (!empty($search)): ?>
                        '<?php echo htmlspecialchars($search); ?>' 검색 결과: 총 <?php echo number_format($total_items); ?>개 중 <?php echo $offset + 1; ?>-<?php echo min($offset + $items_per_page, $total_items); ?>개 표시
                    <?php else: ?>
                        총 <?php echo number_format($total_items); ?>개 중 <?php echo $offset + 1; ?>-<?php echo min($offset + $items_per_page, $total_items); ?>개 표시
                    <?php endif; ?>
                    (페이지 <?php echo $current_page; ?>/<?php echo $total_pages; ?>)
                </div>
                <?php if (!empty($cycle_data)): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>no</th>
                                <th>저장시간</th>
                                <th>아이디</th>
                                <th>운동시간</th>
                                <th>평균속도</th>
                                <th>거리</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cycle_data as $cycle): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cycle['no']); ?></td>
                                    <td><?php echo htmlspecialchars($cycle['SaveTime']); ?></td>
                                    <td><?php echo htmlspecialchars($cycle['name']); ?></td>
                                    <td><?php echo htmlspecialchars($cycle['exercise_time']); ?></td>
                                    <td>
                                        <?php 
                                        $velocity = $cycle['average_velocity'];
                                        // 숫자만 추출하여 단위 추가
                                        $velocity_num = preg_replace('/[^0-9.]/', '', $velocity);
                                        echo htmlspecialchars($velocity_num) . 'm/s';
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $distance = $cycle['distance'];
                                        // 숫자만 추출하여 단위 추가
                                        $distance_num = preg_replace('/[^0-9.]/', '', $distance);
                                        echo htmlspecialchars($distance_num) . 'M';
                                        ?>
                                    </td>
                                    <td>
                                        <button class="delete-btn" onclick="deleteCycleData(<?php echo $cycle['no']; ?>)">
                                            <i class="fas fa-trash"></i> 삭제
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    
                    <!-- 페이지네이션 -->
                    <?php if ($total_pages > 1): ?>
                        <div style="margin-top: 20px; display: flex; justify-content: center; align-items: center; gap: 5px;">
                            <!-- 이전 페이지 -->
                            <?php if ($current_page > 1): ?>
                                <a href="?page=<?php echo $current_page - 1; ?>&per_page=<?php echo $items_per_page; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                                   style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #333; background: white;">
                                    <i class="fas fa-chevron-left"></i> 이전
                                </a>
                            <?php else: ?>
                                <span style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; color: #ccc; background: #f8f9fa;">
                                    <i class="fas fa-chevron-left"></i> 이전
                                </span>
                            <?php endif; ?>
                            
                            <!-- 페이지 번호들 -->
                            <?php
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);
                            
                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <?php if ($i == $current_page): ?>
                                    <span style="padding: 8px 12px; border: 1px solid #3498db; border-radius: 4px; color: white; background: #3498db; font-weight: bold;">
                                        <?php echo $i; ?>
                                    </span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?>&per_page=<?php echo $items_per_page; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                                       style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #333; background: white;">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <!-- 다음 페이지 -->
                            <?php if ($current_page < $total_pages): ?>
                                <a href="?page=<?php echo $current_page + 1; ?>&per_page=<?php echo $items_per_page; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                                   style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #333; background: white;">
                                    다음 <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php else: ?>
                                <span style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; color: #ccc; background: #f8f9fa;">
                                    다음 <i class="fas fa-chevron-right"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-bicycle"></i>
                        <?php if (!empty($search)): ?>
                            <h3>'<?php echo htmlspecialchars($search); ?>'에 대한 검색 결과가 없습니다</h3>
                            <p>다른 검색어로 다시 시도해보세요.</p>
                        <?php else: ?>
                            <h3>사이클 데이터가 없습니다</h3>
                            <p>사이클 운동을 시작하면 여기에 데이터가 표시됩니다.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="js/admin-vr-data.js"></script>
</body>
</html>
