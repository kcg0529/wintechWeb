<?php
session_start();

// 로그인 확인
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

$required = isset($_GET['required']) && $_GET['required'] === 'true';
$message = '';
$success = false;

if ($_POST) {
    $birthday = $_POST['birthday'] ?? '';
    $gender = $_POST['gender'] ?? '';
    
    if (empty($birthday) || empty($gender)) {
        $message = "모든 필드를 입력해주세요.";
    } else {
        try {
            require_once 'DAO/AccountDAO.php';
            $accountDAO = new AccountDAO();
            
            // 생년월일과 성별 업데이트
            $conn = getConnection();
            $stmt = mysqli_prepare($conn, "UPDATE wintech_account SET birthday = ?, gender = ? WHERE account = ?");
            mysqli_stmt_bind_param($stmt, "sss", $birthday, $gender, $_SESSION['email']);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = true;
                $message = "정보가 성공적으로 저장되었습니다.";
            } else {
                $message = "정보 저장에 실패했습니다: " . mysqli_error($conn);
            }
            
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            
        } catch (Exception $e) {
            $message = "오류가 발생했습니다: " . $e->getMessage();
        }
    }
}

$page_title = '정보 수정 - 행복운동센터';
include 'header.php';
?>

<link rel="stylesheet" href="css/edit-profile.css">
<script src="js/edit-profile.js"></script>

<main class="edit-profile-main">
    <div class="edit-profile-container">
        <!-- 진행 단계 표시 -->
        <div class="progress-steps">
            <div class="step active">
                <div class="step-number">1</div>
                <div class="step-label">정보입력</div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-label">건강설문</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-label">완료</div>
            </div>
        </div>

        <!-- 정보 수정 폼 -->
        <div class="edit-profile-form-container">
            <?php if ($required): ?>
                <div class="required-notice">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>서비스 이용을 위해 추가 정보를 입력해주세요.</p>
                </div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Step 1: 기본 정보 -->
            <div class="step-content active" id="step1">
                <h1 class="edit-profile-title">기본 정보</h1>
                <form class="edit-profile-form" method="POST">
                <div class="form-group">
                    <label for="gender" class="form-label">성별</label>
                    <div class="gender-buttons">
                        <input type="radio" id="male" name="gender" value="1" required>
                        <label for="male" class="gender-button">
                            <i class="fas fa-male"></i>
                            남자
                        </label>
                        
                        <input type="radio" id="female" name="gender" value="2" required>
                        <label for="female" class="gender-button">
                            <i class="fas fa-female"></i>
                            여자
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="birthday" class="form-label">생년월일</label>
                    <div class="birthday-selects">
                        <select id="year" name="year" required>
                            <option value="">년</option>
                            <?php for ($i = 2024; $i >= 1900; $i--): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?>년</option>
                            <?php endfor; ?>
                        </select>
                        
                        <select id="month" name="month" required>
                            <option value="">월</option>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?>월</option>
                            <?php endfor; ?>
                        </select>
                        
                        <select id="day" name="day" required>
                            <option value="">일</option>
                            <?php for ($i = 1; $i <= 31; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?>일</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="height" class="form-label">신장</label>
                    <select id="height" name="height" required>
                        <option value="">신장을 선택하세요</option>
                        <?php for ($i = 100; $i <= 250; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?>cm</option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="weight" class="form-label">체중</label>
                    <select id="weight" name="weight" required>
                        <option value="">체중을 선택하세요</option>
                        <?php for ($i = 30; $i <= 200; $i += 0.5): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?>kg</option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="form-buttons">
                    <button type="button" class="btn-prev" data-step="1">이전으로</button>
                    <button type="button" class="btn-next" id="step1-next" data-step="2" disabled>다음으로</button>
                </div>
                </form>
            </div>

            <!-- Step 2: 건강 설문지 -->
            <div class="step-content" id="step2">
                <h1 class="edit-profile-title">건강 설문지</h1>
                <form class="edit-profile-form" method="POST">
                    <div class="health-survey">
                        <div class="health-category">
                            <h3>심혈관계 질환</h3>
                            <div class="health-options">
                                <button type="button" class="health-option" data-value="고혈압">고혈압</button>
                                <button type="button" class="health-option" data-value="협심증">협심증</button>
                                <button type="button" class="health-option" data-value="부정맥">부정맥</button>
                                <button type="button" class="health-option" data-value="뇌졸중">뇌졸중</button>
                            </div>
                        </div>
                        
                        <div class="health-category">
                            <h3>내분비/대사 질환</h3>
                            <div class="health-options">
                                <button type="button" class="health-option" data-value="당뇨병">당뇨병</button>
                                <button type="button" class="health-option" data-value="고지혈증">고지혈증</button>
                                <button type="button" class="health-option" data-value="갑상선 질환">갑상선 질환</button>
                            </div>
                        </div>
                        
                        <div class="health-category">
                            <h3>호흡기 질환</h3>
                            <div class="health-options">
                                <button type="button" class="health-option" data-value="천식">천식</button>
                                <button type="button" class="health-option" data-value="만성 폐쇄성 폐질환">만성 폐쇄성 폐질환</button>
                            </div>
                        </div>
                        
                        <div class="health-category">
                            <h3>근골격계 / 신체 기능</h3>
                            <div class="health-options">
                                <button type="button" class="health-option" data-value="관절염">관절염</button>
                                <button type="button" class="health-option" data-value="골다공증">골다공증</button>
                                <button type="button" class="health-option" data-value="척추질환">척추질환</button>
                                <button type="button" class="health-option" data-value="뇌성마비">뇌성마비</button>
                            </div>
                        </div>
                        
                        <div class="health-category">
                            <h3>신경 / 정신 건강</h3>
                            <div class="health-options">
                                <button type="button" class="health-option" data-value="치매">치매</button>
                                <button type="button" class="health-option" data-value="파킨슨병">파킨슨병</button>
                                <button type="button" class="health-option" data-value="우울증">우울증</button>
                            </div>
                        </div>
                        
                        <div class="health-category">
                            <h3>기타</h3>
                            <div class="health-options">
                                <button type="button" class="health-option" data-value="암병력">암병력</button>
                                <button type="button" class="health-option" data-value="시력문제">시력문제</button>
                                <button type="button" class="health-option" data-value="청각문제">청각문제</button>
                                <button type="button" class="health-option" data-value="이상없음">이상없음</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 선택된 값들을 저장할 숨겨진 필드 -->
                    <input type="hidden" id="selected-health-conditions" name="health_conditions" value="">
                    
                    <div class="form-buttons">
                        <button type="button" class="btn-prev" data-step="1">이전으로</button>
                        <button type="button" class="btn-next" id="step2-next" data-step="3">다음으로</button>
                    </div>
                </form>
            </div>

            <!-- Step 3: 완료 -->
            <div class="step-content" id="step3">
                <h1 class="edit-profile-title">완료</h1>
                <div class="completion-message">
                    <i class="fas fa-check-circle"></i>
                    <p>정보 입력이 완료되었습니다.</p>
                    <p>서비스를 이용하실 수 있습니다.</p>
                </div>
                
                <div class="form-buttons">
                    <button type="button" class="btn-prev" data-step="2">이전으로</button>
                    <button type="button" class="btn-next" id="submitBtn">완료</button>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
