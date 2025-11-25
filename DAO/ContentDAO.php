<?php
require_once 'mysqli_con.php';

class ContentDAO {
    
    /**
     * 태그별 콘텐츠 목록 조회
     * @param string $tag 태그 (VR, AR 또는 운동)
     * @return array 콘텐츠 목록
     */
    public static function getContentsByTag($tag) {
        $conn = getConnection();
        
        // 운동 태그로 조회할 때는 게임, Fun, Work 태그도 함께 조회 (기존 데이터 호환성)
        if ($tag === '운동') {
            $query = "SELECT no, tag, title, path, img FROM wintech_content WHERE tag = ? OR tag = ? OR tag = ? OR tag = ? ORDER BY no ASC";
            $stmt = mysqli_prepare($conn, $query);
            $tagParam = '운동';
            $gameTagParam = '게임';
            $funTagParam = 'Fun';
            $workTagParam = 'Work';
            mysqli_stmt_bind_param($stmt, "ssss", $tagParam, $gameTagParam, $funTagParam, $workTagParam);
        } else {
            $query = "SELECT no, tag, title, path, img FROM wintech_content WHERE tag = ? ORDER BY no ASC";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "s", $tag);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $contents = [];
        while ($row = mysqli_fetch_assoc($result)) {
            // 게임, Fun, Work 태그를 운동으로 변환하여 반환
            if ($row['tag'] === '게임' || $row['tag'] === 'Fun' || $row['tag'] === 'Work') {
                $row['tag'] = '운동';
            }
            $contents[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $contents;
    }
    
    /**
     * 모든 콘텐츠 목록 조회
     * @return array 콘텐츠 목록
     */
    public static function getAllContents() {
        $conn = getConnection();
        $query = "SELECT no, tag, title, path, img FROM wintech_content ORDER BY tag ASC, no ASC";
        $result = mysqli_query($conn, $query);
        
        $contents = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                // 게임, Fun, Work 태그를 운동으로 변환
                if ($row['tag'] === '게임' || $row['tag'] === 'Fun' || $row['tag'] === 'Work') {
                    $row['tag'] = '운동';
                }
                $contents[] = $row;
            }
        }
        
        mysqli_close($conn);
        return $contents;
    }
    
    /**
     * path로 콘텐츠 조회
     * @param string $path 콘텐츠 경로 (예: index.php?content=unity_road)
     * @return array|null 콘텐츠 정보 또는 null
     */
    public static function getContentByPath($path) {
        $conn = getConnection();
        $query = "SELECT no, tag, title, path, img FROM wintech_content WHERE path = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $path);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $content = null;
        if ($row = mysqli_fetch_assoc($result)) {
            // 게임, Fun, Work 태그를 운동으로 변환
            if ($row['tag'] === '게임' || $row['tag'] === 'Fun' || $row['tag'] === 'Work') {
                $row['tag'] = '운동';
            }
            $content = $row;
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $content;
    }
    
    /**
     * content_type으로 콘텐츠 조회 (path가 다양한 형식일 수 있음)
     * @param string $contentType content 파라미터 값 (예: unity_road, minigame)
     * @return array|null 콘텐츠 정보 또는 null
     */
    public static function getContentByContentType($contentType) {
        $conn = getConnection();
        
        // 기존 게임 매핑 (content_type → 폴더명)
        $folderMapping = [
            'unity_road' => 'road',
            'minigame' => 'minigame',
            'kid_quiz' => 'Kid_Quiz'
        ];
        
        // content_type에 해당하는 폴더명 가져오기
        $folderName = isset($folderMapping[$contentType]) ? $folderMapping[$contentType] : $contentType;
        
        // path 필드에서 content 파라미터를 추출하여 검색
        // 예: path = 'index.php?content=unity_road' 또는 path = 'road/' 또는 path = 'unity_road' 또는 path = 'road'
        $query = "SELECT no, tag, title, path, img FROM wintech_content WHERE 
                  path LIKE ? OR 
                  path LIKE ? OR 
                  path LIKE ? OR
                  path = ? OR
                  path = ?
                  LIMIT 1";
        $pathPattern1 = '%index.php?content=' . $contentType . '%';
        $pathPattern2 = $contentType . '/%';
        $pathPattern3 = $folderName . '/%';
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssss", $pathPattern1, $pathPattern2, $pathPattern3, $contentType, $folderName);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $content = null;
        if ($row = mysqli_fetch_assoc($result)) {
            // 게임, Fun, Work 태그를 운동으로 변환
            if ($row['tag'] === '게임' || $row['tag'] === 'Fun' || $row['tag'] === 'Work') {
                $row['tag'] = '운동';
            }
            $content = $row;
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $content;
    }
    
    /**
     * 전체 콘텐츠 수 조회
     * @param string $searchCondition 검색 조건 (WHERE 절)
     * @return int 전체 콘텐츠 수
     */
    public static function getTotalContents($searchCondition = '') {
        $conn = getConnection();
        $query = "SELECT COUNT(*) as total FROM wintech_content " . $searchCondition;
        $result = mysqli_query($conn, $query);
        
        $total = 0;
        if ($result && $row = mysqli_fetch_assoc($result)) {
            $total = (int)$row['total'];
        }
        
        mysqli_close($conn);
        return $total;
    }
    
    /**
     * 콘텐츠 목록 조회 (페이지네이션 지원)
     * @param string $searchCondition 검색 조건 (WHERE 절)
     * @param int $limit 조회할 개수
     * @param int $offset 시작 위치
     * @return array 콘텐츠 목록
     */
    public static function getContentsWithPagination($searchCondition = '', $limit = 20, $offset = 0) {
        $conn = getConnection();
        $query = "SELECT no, tag, title, path, img FROM wintech_content " . $searchCondition . " ORDER BY no DESC LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $contents = [];
        while ($row = mysqli_fetch_assoc($result)) {
            // 게임, Fun, Work 태그를 운동으로 변환
            if ($row['tag'] === '게임' || $row['tag'] === 'Fun' || $row['tag'] === 'Work') {
                $row['tag'] = '운동';
            }
            $contents[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $contents;
    }
    
    /**
     * 콘텐츠 ID로 조회
     * @param int $id 콘텐츠 ID
     * @return array|null 콘텐츠 정보 또는 null
     */
    public static function getContentById($id) {
        $conn = getConnection();
        $query = "SELECT no, tag, title, path, img FROM wintech_content WHERE no = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $content = null;
        if ($row = mysqli_fetch_assoc($result)) {
            // 게임, Fun, Work 태그를 운동으로 변환
            if ($row['tag'] === '게임' || $row['tag'] === 'Fun' || $row['tag'] === 'Work') {
                $row['tag'] = '운동';
            }
            $content = $row;
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $content;
    }
    
    /**
     * 콘텐츠 생성
     * @param string $tag 태그
     * @param string $title 제목
     * @param string $path 경로
     * @param string $img 이미지 파일명
     * @return bool 성공 여부
     */
    public static function createContent($tag, $title, $path, $img) {
        $conn = getConnection();
        $query = "INSERT INTO wintech_content (tag, title, path, img) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $tag, $title, $path, $img);
        $result = mysqli_stmt_execute($stmt);
        
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result;
    }
    
    /**
     * 콘텐츠 수정
     * @param int $id 콘텐츠 ID
     * @param string $tag 태그
     * @param string $title 제목
     * @param string $path 경로
     * @param string $img 이미지 파일명
     * @return bool 성공 여부
     */
    public static function updateContent($id, $tag, $title, $path, $img) {
        $conn = getConnection();
        $query = "UPDATE wintech_content SET tag = ?, title = ?, path = ?, img = ? WHERE no = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssi", $tag, $title, $path, $img, $id);
        $result = mysqli_stmt_execute($stmt);
        
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result;
    }
    
    /**
     * 콘텐츠 삭제
     * @param int $id 콘텐츠 ID
     * @return bool 성공 여부
     */
    public static function deleteContent($id) {
        $conn = getConnection();
        $query = "DELETE FROM wintech_content WHERE no = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        $result = mysqli_stmt_execute($stmt);
        
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result;
    }
    
}
?>

