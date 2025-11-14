<?php
// 중복 선언 방지
if (!function_exists('getConnection')) {
function getConnection() {
    $conn = mysqli_connect("localhost", "wintech30", "wintech1304", "wintech30");
    
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    mysqli_set_charset($conn, "utf8");
    return $conn;
}
} // function_exists 체크 종료
?>