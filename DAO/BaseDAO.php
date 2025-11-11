<?php
require_once __DIR__ . '/mysqli_con.php';

/**
 * 기본 DAO 클래스
 * 모든 DAO 클래스의 부모 클래스
 */
abstract class BaseDAO {
    protected $db;
    
    public function __construct() {
        $this->db = getConnection();
    }
    
    public function __destruct() {
        if ($this->db) {
            mysqli_close($this->db);
        }
    }
    
    /**
     * SQL 쿼리를 안전하게 실행합니다 (Prepared Statement)
     * @param string $query SQL 쿼리
     * @param array $params 바인딩할 매개변수
     * @return mysqli_result|false 쿼리 결과
     */
    protected function executeQuery($query, $params = []) {
        $stmt = mysqli_prepare($this->db, $query);
        
        if (!$stmt) {
            return false;
        }
        
        if (!empty($params)) {
            // 매개변수 타입 자동 감지
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_double($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * 단일 행을 가져옵니다
     * @param string $query SQL 쿼리
     * @param array $params 바인딩할 매개변수
     * @return array|null 단일 행 데이터 또는 null
     */
    protected function fetchOne($query, $params = []) {
        $result = $this->executeQuery($query, $params);
        
        if (!$result) {
            return null;
        }
        
        return mysqli_fetch_assoc($result);
    }
    
    /**
     * 여러 행을 가져옵니다
     * @param string $query SQL 쿼리
     * @param array $params 바인딩할 매개변수
     * @return array 행 배열
     */
    protected function fetchAll($query, $params = []) {
        $result = $this->executeQuery($query, $params);
        
        if (!$result) {
            return [];
        }
        
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        
        return $rows;
    }
    
    /**
     * 쿼리 결과의 행 수를 반환합니다
     * @param string $query SQL 쿼리
     * @param array $params 바인딩할 매개변수
     * @return int 행 수
     */
    protected function getRowCount($query, $params = []) {
        $result = $this->executeQuery($query, $params);
        
        if (!$result) {
            return 0;
        }
        
        return mysqli_num_rows($result);
    }
}
?>
