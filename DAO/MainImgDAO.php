<?php
require_once 'mysqli_con.php';

class MainImgDAO {
    
    /**
     * 슬라이더 이미지 목록 조회
     * @return array 슬라이더 이미지 목록
     */
    public static function getSliderImages() {
        $conn = getConnection();
        $query = "SELECT no, tag, title, text, img FROM wintech_mainimg WHERE tag = 'slider' ORDER BY no ASC";
        $result = mysqli_query($conn, $query);
        
        $images = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $images[] = $row;
            }
        }
        
        mysqli_close($conn);
        return $images;
    }
    
    /**
     * 쇼핑 이미지 목록 조회
     * @return array 쇼핑 이미지 목록
     */
    public static function getShopImages() {
        $conn = getConnection();
        $query = "SELECT no, tag, title, text, img FROM wintech_mainimg WHERE tag = 'shop' ORDER BY no ASC";
        $result = mysqli_query($conn, $query);
        
        $images = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $images[] = $row;
            }
        }
        
        mysqli_close($conn);
        return $images;
    }
    
    /**
     * 태그별 이미지 목록 조회
     * @param string $tag 태그 (slider, shop 등)
     * @return array 이미지 목록
     */
    public static function getImagesByTag($tag) {
        $conn = getConnection();
        $query = "SELECT no, tag, title, text, img FROM wintech_mainimg WHERE tag = ? ORDER BY no ASC";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $tag);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $images = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $images[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $images;
    }
    
    /**
     * 모든 이미지 목록 조회
     * @return array 이미지 목록
     */
    public static function getAllImages() {
        $conn = getConnection();
        $query = "SELECT no, tag, title, text, img FROM wintech_mainimg ORDER BY tag ASC, no ASC";
        $result = mysqli_query($conn, $query);
        
        $images = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $images[] = $row;
            }
        }
        
        mysqli_close($conn);
        return $images;
    }
    
    /**
     * 이미지 ID로 조회
     * @param int $id 이미지 ID
     * @return array|null 이미지 정보 또는 null
     */
    public static function getImageById($id) {
        $conn = getConnection();
        $query = "SELECT no, tag, title, text, img FROM wintech_mainimg WHERE no = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $image = null;
        if ($row = mysqli_fetch_assoc($result)) {
            $image = $row;
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $image;
    }
    
    /**
     * 이미지 생성
     * @param string $tag 태그
     * @param string $title 제목
     * @param string $text 설명 텍스트
     * @param string $img 이미지 파일명
     * @return bool 성공 여부
     */
    public static function createImage($tag, $title, $text, $img) {
        $conn = getConnection();
        $query = "INSERT INTO wintech_mainimg (tag, title, text, img) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $tag, $title, $text, $img);
        $result = mysqli_stmt_execute($stmt);
        
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result;
    }
    
    /**
     * 이미지 수정
     * @param int $id 이미지 ID
     * @param string $tag 태그
     * @param string $title 제목
     * @param string $text 설명 텍스트
     * @param string $img 이미지 파일명
     * @return bool 성공 여부
     */
    public static function updateImage($id, $tag, $title, $text, $img) {
        $conn = getConnection();
        $query = "UPDATE wintech_mainimg SET tag = ?, title = ?, text = ?, img = ? WHERE no = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssi", $tag, $title, $text, $img, $id);
        $result = mysqli_stmt_execute($stmt);
        
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result;
    }
    
    /**
     * 이미지 삭제
     * @param int $id 이미지 ID
     * @return bool 성공 여부
     */
    public static function deleteImage($id) {
        $conn = getConnection();
        $query = "DELETE FROM wintech_mainimg WHERE no = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        $result = mysqli_stmt_execute($stmt);
        
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result;
    }
}
?>

