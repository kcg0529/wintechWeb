<?php
require_once 'mysqli_con.php';

class CommunityDAO {
    private $conn;
    
    public function __construct() {
        $this->conn = getConnection();
    }
    
    public function getPosts($limit, $offset, $search_condition = '', $search_params = []) {
        $sql = "SELECT * FROM wintech_community $search_condition ORDER BY time DESC LIMIT ? OFFSET ?";
        $params = array_merge($search_params, [$limit, $offset]);
        
        $stmt = $this->conn->prepare($sql);
        if (!empty($search_params)) {
            $types = str_repeat('s', count($search_params)) . 'ii';
            $stmt->bind_param($types, ...$params);
        } else {
            $stmt->bind_param('ii', $limit, $offset);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getTotalPosts($search_condition = '', $search_params = []) {
        $sql = "SELECT COUNT(*) as total FROM wintech_community $search_condition";
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($search_params)) {
            $types = str_repeat('s', count($search_params));
            $stmt->bind_param($types, ...$search_params);
        }
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'];
    }
    
    public function getPostById($id) {
        $sql = "SELECT * FROM wintech_community WHERE no = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function createPost($title, $content, $author_name) {
        $sql = "INSERT INTO wintech_community (title, content, name) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sss', $title, $content, $author_name);
        return $stmt->execute();
    }
    
    public function updateViewCount($id) {
        $sql = "UPDATE wintech_community SET view_count = view_count + 1 WHERE no = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
    
    public function deletePost($id, $author_name) {
        // 작성자 확인 후 삭제
        $sql = "DELETE FROM wintech_community WHERE no = ? AND name = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('is', $id, $author_name);
        return $stmt->execute();
    }
}
?>






