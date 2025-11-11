<?php
// Set content type to JSON
header('Content-Type: application/json; charset=utf-8');

// Enable CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get form data
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$company = isset($_POST['company']) ? trim($_POST['company']) : '';
$service = isset($_POST['service']) ? trim($_POST['service']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validate input
$errors = [];

if (empty($name)) {
    $errors[] = '이름을 입력해주세요.';
}

if (empty($email)) {
    $errors[] = '이메일을 입력해주세요.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = '올바른 이메일 주소를 입력해주세요.';
}

if (empty($message)) {
    $errors[] = '메시지를 입력해주세요.';
}

if (empty($service)) {
    $errors[] = '관심 서비스를 선택해주세요.';
}

// If there are validation errors, return them
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// Sanitize input
$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
$company = htmlspecialchars($company, ENT_QUOTES, 'UTF-8');
$service = htmlspecialchars($service, ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

// Service mapping
$serviceNames = [
    'cloud' => '클라우드 솔루션',
    'ai' => 'AI/ML 서비스',
    'security' => '사이버 보안',
    'mobile' => '모바일 앱 개발',
    'data' => '데이터 분석',
    'integration' => '시스템 통합',
    'consulting' => 'IT 컨설팅'
];

$serviceName = isset($serviceNames[$service]) ? $serviceNames[$service] : $service;

// Email configuration
$to = 'contact@wintech.co.kr'; // 실제 이메일 주소로 변경
$subject = '새로운 프로젝트 문의 - ' . $name;
$headers = [
    'From: ' . $email,
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . phpversion(),
    'Content-Type: text/html; charset=UTF-8'
];

// Email body
$emailBody = "
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: 'Noto Sans KR', Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px 10px 0 0; }
        .content { background: #f8fafc; padding: 30px; border-radius: 0 0 10px 10px; }
        .field { margin-bottom: 20px; }
        .label { font-weight: bold; color: #2563eb; margin-bottom: 5px; }
        .value { background: white; padding: 10px; border-radius: 5px; border-left: 4px solid #2563eb; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>새로운 문의 메시지</h2>
        </div>
        <div class='content'>
            <div class='field'>
                <div class='label'>이름:</div>
                <div class='value'>" . $name . "</div>
            </div>
            <div class='field'>
                <div class='label'>이메일:</div>
                <div class='value'>" . $email . "</div>
            </div>
            <div class='field'>
                <div class='label'>회사명:</div>
                <div class='value'>" . ($company ?: '미입력') . "</div>
            </div>
            <div class='field'>
                <div class='label'>관심 서비스:</div>
                <div class='value'>" . $serviceName . "</div>
            </div>
            <div class='field'>
                <div class='label'>메시지:</div>
                <div class='value'>" . nl2br($message) . "</div>
            </div>
            <div class='field'>
                <div class='label'>전송 시간:</div>
                <div class='value'>" . date('Y-m-d H:i:s') . "</div>
            </div>
        </div>
    </div>
</body>
</html>
";

// Send email
$mailSent = mail($to, $subject, $emailBody, implode("\r\n", $headers));

// Log to file (optional)
$logData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'name' => $name,
    'email' => $email,
    'company' => $company,
    'service' => $serviceName,
    'message' => $message,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
];

$logFile = 'contact_log.txt';
file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);

// Return response
if ($mailSent) {
    echo json_encode([
        'success' => true, 
        'message' => '메시지가 성공적으로 전송되었습니다. 빠른 시일 내에 연락드리겠습니다.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => '메시지 전송에 실패했습니다. 다시 시도해주세요.'
    ]);
}
?>
