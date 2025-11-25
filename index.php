<?php
$page_title = "행복운동센터 - 홈";
include 'header.php';
require_once 'DAO/NoticeDAO.php';
require_once 'DAO/ContentDAO.php';
require_once 'DAO/MainImgDAO.php';
require_once 'profile_check.php';

// 프로필 정보 완성도 확인
checkProfileCompletion();

// 공지사항 데이터 가져오기 (최근 2개)
$notices = NoticeDAO::getRecentNotices(5);

// 콘텐츠 데이터 가져오기
$funContents = ContentDAO::getContentsByTag('운동');
$vrContents = ContentDAO::getContentsByTag('VR');
$arContents = ContentDAO::getContentsByTag('AR');

// 슬라이더 및 쇼핑 이미지 가져오기
$sliderImages = MainImgDAO::getSliderImages();
$shopImages = MainImgDAO::getShopImages();

// GET 파라미터로 콘텐츠 타입 확인
$content_type = isset($_GET['content']) ? $_GET['content'] : 'main';

// Unity 콘텐츠 정보 가져오기 (content 파라미터가 있고 main이 아닌 경우)
$unityContent = null;
if ($content_type !== 'main') {
    // 1순위: index.php?content= 형식으로 검색
    $contentPath = 'index.php?content=' . $content_type;
    $unityContent = ContentDAO::getContentByPath($contentPath);
    
    // 2순위: 데이터베이스에 없으면 content_type으로 직접 검색 시도
    if (!$unityContent) {
        $unityContent = ContentDAO::getContentByContentType($content_type);
    }
}
?>

    <main>
        <?php 
        // Unity 콘텐츠 요청 시 로그인 체크 (데이터베이스 조회 결과와 관계없이 체크)
        if ($content_type !== 'main') {
            if (!isset($_SESSION['email'])) {
                echo '<link rel="stylesheet" href="css/login-required.css">';
                echo '<div class="login-required-container">';
                echo '<div class="login-required-box">';
                echo '<i class="fas fa-lock login-required-icon"></i>';
                echo '<h2 class="login-required-title">로그인이 필요합니다</h2>';
                echo '<p class="login-required-text">컨텐츠를 이용하시려면 로그인해주세요.</p>';
                echo '<div class="login-required-buttons">';
                echo '<a href="login.php" class="login-required-btn login">로그인</a>';
                echo '<a href="signup.php" class="login-required-btn signup">회원가입</a>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                exit;
            }
        }
        
        // Unity 콘텐츠 로드 (데이터베이스에 있으면 사용, 없으면 기존 방식으로)
        if ($content_type !== 'main'): ?>
            <?php
            // 폴더명 결정: 데이터베이스 path에서 content 추출 > content_type 매핑 > content_type 직접 사용
            $unityFolder = null;
            
            // 폴더명 매핑 정의 (기존 게임 호환성)
            $folderMapping = [
                'unity_road' => 'road',
                'minigame' => 'minigame',
                'kid_quiz' => 'Kid_Quiz'
            ];
            
            // 폴더명 결정 우선순위:
            // 1순위: content_type 기반 매핑 (기존 방식, 가장 안정적)
            // 2순위: 데이터베이스 path 필드 (새 콘텐츠용)
            if (isset($folderMapping[$content_type])) {
                // 기존 게임은 항상 매핑 사용 (unity_road, minigame, kid_quiz)
                $unityFolder = $folderMapping[$content_type];
            } elseif ($unityContent && !empty($unityContent['path'])) {
                // 새 콘텐츠: 데이터베이스 path 필드 사용
                // path가 "index.php?content=xxx" 형식인 경우 content 파라미터 추출
                if (preg_match('/index\.php\?content=([^&]+)/', $unityContent['path'], $matches)) {
                    $contentFromPath = $matches[1];
                    // 추출한 content도 매핑 확인
                    if (isset($folderMapping[$contentFromPath])) {
                        $unityFolder = $folderMapping[$contentFromPath];
                    } else {
                        $unityFolder = $contentFromPath;
                    }
                } else {
                    // path가 직접 폴더명인 경우
                    $unityFolder = $unityContent['path'];
                }
            } else {
                // 매핑도 없고 데이터베이스도 없으면 content_type 직접 사용
                $unityFolder = $content_type;
            }
            
            $buildPath = $unityFolder . '/Build';
            $templatePath = $unityFolder . '/TemplateData';
            
            // 디버깅 정보 (개발 환경에서만)
            // echo '<div style="padding: 10px; background: #f0f0f0; margin: 10px;">';
            // echo 'Debug: content_type=' . htmlspecialchars($content_type) . ', unityFolder=' . htmlspecialchars($unityFolder) . ', buildPath=' . htmlspecialchars($buildPath);
            // echo '</div>';
            
            // Build 폴더가 없으면 에러 메시지 표시
            if (!is_dir($buildPath)) {
                echo '<div class="error-message" style="padding: 20px; text-align: center; color: red;">';
                echo '<h2>Unity Build 폴더를 찾을 수 없습니다</h2>';
                echo '<p>content_type: ' . htmlspecialchars($content_type) . '</p>';
                echo '<p>unityFolder: ' . htmlspecialchars($unityFolder) . '</p>';
                echo '<p>폴더 경로: ' . htmlspecialchars($buildPath) . '</p>';
                echo '<p>현재 디렉토리: ' . htmlspecialchars(getcwd()) . '</p>';
                echo '</div>';
            } else {
                // Build 폴더 내에서 첫 번째 JSON 파일 찾기
                $jsonPath = null;
                $files = scandir($buildPath);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                        $jsonPath = $buildPath . '/' . $file;
                        break;
                    }
                }
                
                // JSON 파일이 없으면 에러 메시지 표시
                if (!$jsonPath || !file_exists($jsonPath)) {
                    echo '<div class="error-message" style="padding: 20px; text-align: center; color: red;">';
                    echo '<h2>Unity JSON 파일을 찾을 수 없습니다</h2>';
                    echo '<p>폴더 경로: ' . htmlspecialchars($buildPath) . '</p>';
                    echo '</div>';
                } else {
                    // JSON 파일이 있으면 Unity 콘텐츠 로드
                    $loadUnity = true;
                }
            }
            
            // Unity 콘텐츠 로드 여부 확인
            if (isset($loadUnity) && $loadUnity): 
                // 제목 가져오기: 데이터베이스 title 우선, 없으면 기본값
                $unityTitle = 'Unity Game';
                
                // 디버깅: 데이터베이스에서 가져온 내용 확인
                // var_dump($unityContent); // 디버깅용
                
                if ($unityContent && !empty($unityContent['title'])) {
                    // 데이터베이스에서 title 값을 가져옴
                    $unityTitle = htmlspecialchars($unityContent['title']);
                } else {
                    // 데이터베이스에 title이 없는 경우 기존 Unity 게임들의 기본 제목 설정
                    if ($unityFolder === 'road') {
                        $unityTitle = 'cycle_Wintech';
                    } elseif ($unityFolder === 'minigame') {
                        $unityTitle = 'minigame';
                    } elseif ($unityFolder === 'Kid_Quiz') {
                        $unityTitle = 'Kid_Quiz';
                    }
                }
            ?>
            <!-- Unity WebGL 콘텐츠 -->
            <section class="unity-content">
                <div class="unity-container">
                    <div class="unity-content-area">
                        <div class="unity-webgl-container">
                            <div class="webgl-content">
                                <div id="unityContainer"></div>
                                <div class="footer <?php echo $unityFolder; ?>-footer">
                                    <div class="webgl-logo"></div>
                                    <div class="fullscreen" data-fullscreen-btn></div>
                                    <div class="title"><?php echo $unityTitle; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <link rel="stylesheet" href="css/unity-webgl.css">
            <?php if (file_exists($templatePath . '/style.css')): ?>
                <link rel="stylesheet" href="<?php echo $templatePath; ?>/style.css">
            <?php endif; ?>
            <?php if (file_exists($templatePath . '/UnityProgress.js')): ?>
                <script src="<?php echo $templatePath; ?>/UnityProgress.js"></script>
            <?php endif; ?>
            <?php if (file_exists($buildPath . '/UnityLoader.js')): ?>
                <script src="<?php echo $buildPath; ?>/UnityLoader.js"></script>
            <?php endif; ?>
            <script>
                <?php if (isset($jsonPath) && file_exists($jsonPath)): ?>
                    var unityInstance = UnityLoader.instantiate("unityContainer", "<?php echo $jsonPath; ?>", {onProgress: UnityProgress});
                <?php else: ?>
                    console.error('Unity JSON file not found');
                <?php endif; ?>
                // Unity 로드 후 스타일 강제 적용 및 전체화면 버튼 설정
                setTimeout(function() {
                    var webglContent = document.querySelector('.webgl-content');
                    var unityContainer = document.querySelector('#unityContainer');
                    var fullscreenBtn = document.querySelector('[data-fullscreen-btn]');
                    
                    if (webglContent) {
                        webglContent.style.position = 'relative';
                        webglContent.style.top = 'auto';
                        webglContent.style.left = 'auto';
                        webglContent.style.transform = 'none';
                        webglContent.style.webkitTransform = 'none';
                        webglContent.style.height = 'auto';
                        webglContent.style.minHeight = 'auto';
                    }
                    if (unityContainer) {
                        // 모바일 환경 감지
                        var isMobile = window.innerWidth <= 768;
                        var isSmallMobile = window.innerWidth <= 480;
                        
                        if (isSmallMobile) {
                            unityContainer.style.height = '200px';
                            unityContainer.style.minHeight = '200px';
                        } else if (isMobile) {
                            unityContainer.style.height = '250px';
                            unityContainer.style.minHeight = '250px';
                        } else {
                            unityContainer.style.height = 'auto';
                            unityContainer.style.minHeight = '600px';
                            // Unity 컨테이너의 실제 높이 확인 후 조정
                            setTimeout(function() {
                                var containerHeight = unityContainer.scrollHeight;
                                if (containerHeight > 600) {
                                    unityContainer.style.height = containerHeight + 'px';
                                }
                            }, 500);
                        }
                    }
                    
                    // 전체화면 버튼 이벤트 (모바일 터치 지원)
                    if (fullscreenBtn && typeof unityInstance !== 'undefined') {
                        function goFullscreen() {
                            if (unityInstance && unityInstance.SetFullscreen) {
                                unityInstance.SetFullscreen(1);
                            }
                        }
                        // 클릭과 터치 모두 지원
                        fullscreenBtn.addEventListener('click', goFullscreen);
                        fullscreenBtn.addEventListener('touchstart', function(e) {
                            e.preventDefault();
                            goFullscreen();
                        });
                    }
                }, 100);
            </script>
            <?php endif; // Unity 콘텐츠 로드 여부 ?>

        <?php else: ?>
            <!-- 기본 메인 페이지 콘텐츠 -->
            <!-- Hero Section -->
            <section class="hero">
                <div class="hero-slider">
                    <?php if (!empty($sliderImages)): ?>
                        <?php foreach ($sliderImages as $index => $slide): ?>
                            <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>">
                                <img src="images/<?php echo htmlspecialchars($slide['img']); ?>" alt="<?php echo htmlspecialchars($slide['text']); ?>" class="hero-image">
                                <div class="hero-overlay">
                                    <h2 class="hero-text"><?php echo htmlspecialchars($slide['text']); ?></h2>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- 기본 슬라이더 -->
                        <div class="slide active">
                            <img src="images/slide1.png" alt="행복운동센터 스트레칭" class="hero-image">
                            <div class="hero-overlay">
                                <h2 class="hero-text">오늘의 작은 움직임이 내일의 큰 활력이 됩니다.</h2>
                            </div>
                        </div>
                        <div class="slide">
                            <img src="images/slide2.png" alt="행복운동센터 운동" class="hero-image">
                            <div class="hero-overlay">
                                <h2 class="hero-text">가족의 미소는 당신의 건강에서 시작됩니다.</h2>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Navigation arrows -->
                    <button class="slide-nav prev" onclick="changeSlide(-1)">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="slide-nav next" onclick="changeSlide(1)">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    
                    <!-- Slide indicators -->
                    <div class="slide-indicators">
                        <?php 
                        $slideCount = !empty($sliderImages) ? count($sliderImages) : 2;
                        for ($i = 1; $i <= $slideCount; $i++): 
                        ?>
                            <span class="indicator <?php echo $i === 1 ? 'active' : ''; ?>" onclick="currentSlide(<?php echo $i; ?>)"></span>
                        <?php endfor; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($content_type === 'main'): ?>
            <!-- Navigation Cards Section -->
            <section class="nav-cards">
                <div class="nav-cards-wrapper">
                    <button class="nav-cards-nav prev" onclick="changeNavCards(-1)">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div class="nav-cards-container">
                        <div class="nav-cards-slider">
                            <a href="#notice-section" class="nav-card">
                                <div class="card-icon">
                                    <img src="images/notice-icon.png" alt="공지사항" class="card-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <i class="fas fa-bullhorn fallback-icon" style="display: none;"></i>
                                </div>
                                <span class="card-text">공지사항</span>
                            </a>
                            <a href="community.php" class="nav-card">
                                <div class="card-icon">
                                    <img src="images/community-icon.png" alt="커뮤니티" class="card-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <i class="fas fa-comments fallback-icon" style="display: none;"></i>
                                </div>
                                <span class="card-text">커뮤니티</span>
                            </a>
                            <a href="#fun-section" class="nav-card">
                                <div class="card-icon">
                                    <img src="images/game-icon.png" alt="운동" class="card-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <i class="fas fa-gamepad fallback-icon" style="display: none;"></i>
                                </div>
                                <span class="card-text">운동</span>
                            </a>
                            <a href="#shop-section" class="nav-card nav-card-shop">
                                <div class="card-icon">
                                    <img src="images/shop-icon.png" alt="쇼핑" class="card-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <i class="fas fa-shopping-cart fallback-icon" style="display: none;"></i>
                                </div>
                                <span class="card-text">쇼핑</span>
                            </a>
                            <a href="#ar-section" class="nav-card">
                                <div class="card-icon">
                                    <img src="images/ar-icon.png" alt="AR" class="card-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <i class="fas fa-mobile-alt fallback-icon" style="display: none;"></i>
                                </div>
                                <span class="card-text">AR</span>
                            </a>
                            <a href="#vr-section" class="nav-card">
                                <div class="card-icon">
                                    <img src="images/vr-icon.png" alt="VR" class="card-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <i class="fas fa-vr-cardboard fallback-icon" style="display: none;"></i>
                                </div>
                                <span class="card-text">VR</span>
                            </a>
                        </div>
                    </div>
                    <button class="nav-cards-nav next" onclick="changeNavCards(1)">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($content_type === 'main'): ?>
            <!-- Notice Section -->
            <section id="notice-section" class="notice-section">
                <div class="notice-page">
                    <div class="notice-container">
                        <div class="notice-header">
                            <a href="notice_board.php" class="view-details-btn">
                                <i class="fas fa-search"></i>
                                자세히보기
                            </a>
                            <div class="notice-title">
                                <img src="images/notice-icon.png" alt="공지사항" class="section-icon">
                                <div class="notice-title-text-wrapper">
                                    <h1>공지사항</h1>
                                    <p class="notice-subtitle">새로운 소식을 알려드립니다.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="notice-content">
                            <?php if (!empty($notices)): ?>
                                <?php foreach ($notices as $notice): ?>
                                    <?php
                                    // DAO 함수를 사용하여 날짜 포맷팅 및 제목 파싱
                                    $formatted_date = NoticeDAO::formatDate($notice['date']);
                                    $parsed_title = NoticeDAO::parseNoticeTitle($notice['title']);
                                    $tag = $parsed_title['tag'];
                                    $title = $parsed_title['title'];
                                    
                                    // NEW 딱지 기준: 3일 이내 작성된 공지사항
                                    $noticeTime = strtotime($notice['date']);
                                    $isNew = (time() - $noticeTime) <= (3 * 24 * 60 * 60); // 3일 = 3 * 24시간 * 60분 * 60초
                                    
                                    // 날짜를 일(day)과 년.월 형식으로 분리
                                    $dateObj = new DateTime($notice['date']);
                                    $day = $dateObj->format('d'); // 일 (30, 18 등)
                                    $yearMonth = $dateObj->format('Y.m'); // 년.월 (2025.09 등)
                                    ?>
                                    <div class="notice-card">
                                        <div class="notice-date-section">
                                            <div class="notice-day"><?php echo htmlspecialchars($day); ?></div>
                                            <div class="notice-year-month"><?php echo htmlspecialchars($yearMonth); ?></div>
                                        </div>
                                        <div class="notice-card-content">
                                            <a href="notice_detail.php?id=<?php echo $notice['no']; ?>" class="notice-card-link">
                                                <div class="notice-card-title">
                                                    <?php if ($tag): ?>
                                                        <?php 
                                                        // 태그에서 대괄호 제거 (이미 parseNoticeTitle에서 대괄호가 포함되어 있음)
                                                        $cleanTag = trim($tag, '[]');
                                                        ?>
                                                        <span class="notice-card-tag">[<?php echo htmlspecialchars($cleanTag); ?>]</span>
                                                    <?php endif; ?>
                                                    <?php echo htmlspecialchars($title); ?>
                                                </div>
                                            </a>
                                        </div>
                                        <?php if ($isNew): ?>
                                            <div class="notice-badge">NEW</div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="notice-card">
                                    <div class="notice-card-content">
                                        <div class="notice-card-title">등록된 공지사항이 없습니다.</div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($content_type === 'main'): ?>
            <!-- 운동 Section -->
            <section id="fun-section" class="fun-section">
                <div class="fun-container">
                    <div class="fun-header">
                        <div class="fun-title">
                            <img src="images/game-icon.png" alt="운동" class="section-icon">
                            <h2>운동</h2>
                        </div>
                        <div class="fun-divider"></div>
                    </div>
                    
                    <div class="fun-slider">
                        <?php if (!empty($funContents)): ?>
                            <?php 
                            $totalFunContents = count($funContents);
                            $initialActiveIndex = min(1, floor($totalFunContents / 2)); // 중간 인덱스 또는 1
                            ?>
                            <?php foreach ($funContents as $index => $content): ?>
                                <?php 
                                // 초기 활성 슬라이드는 중간 인덱스, 나머지는 동적으로 처리
                                $slideClass = ($index === $initialActiveIndex) ? 'fun-slide active' : 'fun-slide';
                                // 3개 이상일 때만 hidden 처리 (JavaScript에서 동적으로 처리)
                                if ($index > 4 && $totalFunContents > 5) {
                                    $slideClass .= ' hidden';
                                }
                                // path를 index.php?content= 형식으로 변환
                                $linkHref = '#';
                                if (!empty($content['path'])) {
                                    // path가 "index.php?content=xxx" 형식이면 그대로 사용
                                    if (strpos($content['path'], 'index.php?content=') !== false) {
                                        $linkHref = htmlspecialchars($content['path']);
                                    }
                                    // path가 폴더명 형식 (예: "road/", "minigame/", "새게임/")이면 변환
                                    elseif (preg_match('/^([^\/\?]+)\/?$/', $content['path'], $matches)) {
                                        $folderName = $matches[1];
                                        // 기존 게임 매핑 (특수 케이스)
                                        $contentMapping = [
                                            'road' => 'unity_road',
                                            'minigame' => 'minigame',
                                            'Kid_Quiz' => 'kid_quiz'
                                        ];
                                        // 매핑이 있으면 사용, 없으면 폴더명을 그대로 사용
                                        $contentParam = isset($contentMapping[$folderName]) ? $contentMapping[$folderName] : $folderName;
                                        $linkHref = 'index.php?content=' . $contentParam;
                                    }
                                    // 그 외는 그대로 사용
                                    else {
                                        $linkHref = htmlspecialchars($content['path']);
                                    }
                                }
                                $imgSrc = !empty($content['img']) ? 'images/' . htmlspecialchars($content['img']) : 'images/pic02.jpg';
                                $imgAlt = !empty($content['title']) ? htmlspecialchars($content['title']) : '운동';
                                ?>
                                <div class="<?php echo $slideClass; ?>" onclick="setActiveSlide(<?php echo $index; ?>)">
                                    <a href="<?php echo $linkHref; ?>" class="fun-slide-link">
                                        <img src="<?php echo $imgSrc; ?>" alt="<?php echo $imgAlt; ?>" class="fun-image">
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- 기본 콘텐츠 (데이터가 없을 경우) -->
                            <div class="fun-slide" onclick="setActiveSlide(0)">
                                <a href="index.php?content=minigame" class="fun-slide-link">
                                    <img src="images/minigame.png" alt="운동" class="fun-image">
                                </a>
                            </div>
                            <div class="fun-slide active" onclick="setActiveSlide(1)">
                                <a href="index.php?content=kid_quiz" class="fun-slide-link">
                                    <img src="images/Kid_Quiz.jpg" alt="퀴즈 운동" class="fun-image">
                                </a>
                            </div>
                        <?php endif; ?>
                        <!-- Navigation arrows -->
                        <button class="fun-nav prev" onclick="changeFunSlide(-1)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="fun-nav next" onclick="changeFunSlide(1)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </section>

            <!-- Shop Section -->
            <section id="shop-section" class="shop-section">
                <div class="shop-container">
                    <div class="shop-header">
                        <div class="shop-title">
                            <img src="images/shop-icon.png" alt="쇼핑" class="section-icon">
                            <h2>쇼핑</h2>
                        </div>
                        <div class="shop-divider"></div>
                    </div>
                    <div class="shop-slider">
                        <?php if (!empty($shopImages)): ?>
                            <?php foreach ($shopImages as $index => $shop): ?>
                                <?php 
                                $imgSrc = !empty($shop['img']) ? 'images/' . htmlspecialchars($shop['img']) : 'images/win01.jpg';
                                $imgAlt = !empty($shop['title']) ? htmlspecialchars($shop['title']) : 'Shop Item';
                                $title = !empty($shop['title']) ? htmlspecialchars($shop['title']) : '제품명';
                                $price = !empty($shop['text']) ? htmlspecialchars($shop['text']) : '가격문의';
                                ?>
                                <div class="shop-slide" onclick="setActiveShopSlide(<?php echo $index; ?>)">
                                    <a href="#" class="shop-item-link" data-shop-link>
                                        <div class="shop-item">
                                            <div class="shop-image">
                                                <img src="<?php echo $imgSrc; ?>" alt="<?php echo $imgAlt; ?>" class="product-image">
                                            </div>
                                            <div class="shop-info">
                                                <h3 class="product-name"><?php echo $title; ?></h3>
                                                <p class="product-price"><?php echo $price; ?></p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- 기본 쇼핑 아이템 (데이터가 없을 경우) -->
                            <div class="shop-slide" onclick="setActiveShopSlide(0)">
                                <a href="#" class="shop-item-link" data-shop-link>
                                    <div class="shop-item">
                                        <div class="shop-image">
                                            <img src="images/win01.jpg" alt="Win-01" class="product-image">
                                        </div>
                                        <div class="shop-info">
                                            <h3 class="product-name">Win-01</h3>
                                            <p class="product-price">가격문의</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="shop-slide" onclick="setActiveShopSlide(1)">
                                <a href="#" class="shop-item-link" data-shop-link>
                                    <div class="shop-item">
                                        <div class="shop-image">
                                            <img src="images/win02.jpg" alt="Win-02" class="product-image">
                                        </div>
                                        <div class="shop-info">
                                            <h3 class="product-name">Win-02</h3>
                                            <p class="product-price">가격문의</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="shop-slide" onclick="setActiveShopSlide(2)">
                                <a href="#" class="shop-item-link" data-shop-link>
                                    <div class="shop-item">
                                        <div class="shop-image">
                                            <img src="images/win03.jpg" alt="Win-03" class="product-image">
                                        </div>
                                        <div class="shop-info">
                                            <h3 class="product-name">Win-03</h3>
                                            <p class="product-price">가격문의</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="shop-slide" onclick="setActiveShopSlide(3)">
                                <a href="#" class="shop-item-link" data-shop-link>
                                    <div class="shop-item">
                                        <div class="shop-image">
                                            <img src="images/win04.jpg" alt="Win-04" class="product-image">
                                        </div>
                                        <div class="shop-info">
                                            <h3 class="product-name">Win-04</h3>
                                            <p class="product-price">가격문의</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endif; ?>
                        <button class="shop-nav prev" onclick="changeShopSlide(-1)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="shop-nav next" onclick="changeShopSlide(1)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </section>

            <!-- AR Section -->
            <section id="ar-section" class="ar-section">
                <div class="ar-container">
                    <div class="ar-header">
                        <div class="ar-title">
                            <img src="images/ar-icon.png" alt="AR" class="section-icon">
                            <h2>AR</h2>
                        </div>
                        <div class="ar-divider"></div>
                    </div>
                    <div class="ar-slider">
                        <?php if (!empty($arContents)): ?>
                            <?php foreach ($arContents as $index => $content): ?>
                                <?php 
                                // path를 index.php?content= 형식으로 변환
                                $linkHref = '#';
                                if (!empty($content['path'])) {
                                    // path가 "index.php?content=xxx" 형식이면 그대로 사용
                                    if (strpos($content['path'], 'index.php?content=') !== false) {
                                        $linkHref = htmlspecialchars($content['path']);
                                    }
                                    // path가 폴더명 형식 (예: "road/", "minigame/", "새게임/")이면 변환
                                    elseif (preg_match('/^([^\/\?]+)\/?$/', $content['path'], $matches)) {
                                        $folderName = $matches[1];
                                        // 기존 게임 매핑 (특수 케이스)
                                        $contentMapping = [
                                            'road' => 'unity_road',
                                            'minigame' => 'minigame',
                                            'Kid_Quiz' => 'kid_quiz'
                                        ];
                                        // 매핑이 있으면 사용, 없으면 폴더명을 그대로 사용
                                        $contentParam = isset($contentMapping[$folderName]) ? $contentMapping[$folderName] : $folderName;
                                        $linkHref = 'index.php?content=' . $contentParam;
                                    }
                                    // 그 외는 그대로 사용
                                    else {
                                        $linkHref = htmlspecialchars($content['path']);
                                    }
                                }
                                $imgSrc = !empty($content['img']) ? 'images/' . htmlspecialchars($content['img']) : 'images/pic01.jpg';
                                $imgAlt = !empty($content['title']) ? htmlspecialchars($content['title']) : 'AR Content';
                                ?>
                                <div class="ar-slide" onclick="setActiveArSlide(<?php echo $index; ?>)">
                                    <a href="<?php echo $linkHref; ?>" class="ar-slide-link">
                                        <img src="<?php echo $imgSrc; ?>" alt="<?php echo $imgAlt; ?>" class="ar-image">
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- 기본 콘텐츠 (데이터가 없을 경우) -->
                            <div class="ar-slide" onclick="setActiveArSlide(0)">
                                <a href="#" class="ar-slide-link">
                                    <img src="images/pic01.jpg" alt="AR Content 1" class="ar-image">
                                </a>
                            </div>
                        <?php endif; ?>
                        <button class="ar-nav prev" onclick="changeArSlide(-1)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="ar-nav next" onclick="changeArSlide(1)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </section>

            <!-- VR Section -->
            <section id="vr-section" class="vr-section">
                <div class="vr-container">
                    <div class="vr-header">
                        <div class="vr-title">
                            <img src="images/vr-icon.png" alt="VR" class="section-icon">
                            <h2>VR</h2>
                        </div>
                        <div class="vr-divider"></div>
                    </div>
                    <div class="vr-slider">
                        <?php if (!empty($vrContents)): ?>
                            <?php foreach ($vrContents as $index => $content): ?>
                                <?php 
                                // path를 index.php?content= 형식으로 변환
                                $linkHref = '#';
                                if (!empty($content['path'])) {
                                    // path가 "index.php?content=xxx" 형식이면 그대로 사용
                                    if (strpos($content['path'], 'index.php?content=') !== false) {
                                        $linkHref = htmlspecialchars($content['path']);
                                    }
                                    // path가 폴더명 형식 (예: "road/", "minigame/", "새게임/")이면 변환
                                    elseif (preg_match('/^([^\/\?]+)\/?$/', $content['path'], $matches)) {
                                        $folderName = $matches[1];
                                        // 기존 게임 매핑 (특수 케이스)
                                        $contentMapping = [
                                            'road' => 'unity_road',
                                            'minigame' => 'minigame',
                                            'Kid_Quiz' => 'kid_quiz'
                                        ];
                                        // 매핑이 있으면 사용, 없으면 폴더명을 그대로 사용
                                        $contentParam = isset($contentMapping[$folderName]) ? $contentMapping[$folderName] : $folderName;
                                        $linkHref = 'index.php?content=' . $contentParam;
                                    }
                                    // 그 외는 그대로 사용
                                    else {
                                        $linkHref = htmlspecialchars($content['path']);
                                    }
                                }
                                $imgSrc = !empty($content['img']) ? 'images/' . htmlspecialchars($content['img']) : 'images/pic01.jpg';
                                $imgAlt = !empty($content['title']) ? htmlspecialchars($content['title']) : 'VR Content';
                                ?>
                                <div class="vr-slide" onclick="setActiveVrSlide(<?php echo $index; ?>)">
                                    <a href="<?php echo $linkHref; ?>" class="vr-slide-link">
                                        <img src="<?php echo $imgSrc; ?>" alt="<?php echo $imgAlt; ?>" class="vr-image">
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- 기본 콘텐츠 (데이터가 없을 경우) -->
                            <div class="vr-slide" onclick="setActiveVrSlide(0)">
                                <a href="#" class="vr-slide-link">
                                    <img src="images/pic01.jpg" alt="VR Content 1" class="vr-image">
                                </a>
                            </div>
                            <div class="vr-slide" onclick="setActiveVrSlide(1)">
                                <a href="index.php?content=unity_road" class="vr-slide-link">
                                    <img src="images/road.jpg" alt="VR Content 2" class="vr-image">
                                </a>
                            </div>
                        <?php endif; ?>
                        <button class="vr-nav prev" onclick="changeVrSlide(-1)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="vr-nav next" onclick="changeVrSlide(1)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </main>

<?php include 'footer.php'; ?>