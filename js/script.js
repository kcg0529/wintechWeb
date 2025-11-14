// Smooth scrolling for navigation
document.addEventListener('DOMContentLoaded', function() {
    // 슬라이더 텍스트 줄바꿈 및 글자 크기 동적 조정 (최대 2줄)
    const heroTexts = document.querySelectorAll('.hero-text');
    heroTexts.forEach(textElement => {
        const text = textElement.textContent.trim();
        const screenWidth = window.innerWidth;
        let fontSize, maxCharsPerLine;
        
        // 화면 크기에 따른 기본 설정
        if (screenWidth <= 480) {
            fontSize = 1.5;
            maxCharsPerLine = 11;
        } else if (screenWidth <= 768) {
            fontSize = 1.8;
            maxCharsPerLine = 11;
        } else {
            fontSize = 2.5;
            maxCharsPerLine = 11;
        }
        
        // 텍스트 길이에 따라 글자 크기 추가 조정
        if (text.length > 40) {
            fontSize *= 0.72;
        } else if (text.length > 30) {
            fontSize *= 0.8;
        } else if (text.length > 20) {
            fontSize *= 0.88;
        }
        
        textElement.style.fontSize = fontSize + 'rem';
        
        if (text.length > maxCharsPerLine) {
            const words = text.split(' ');
            let currentLine = '';
            let result = '';
            let lineCount = 0;
            
            words.forEach((word, index) => {
                const testLine = currentLine + (currentLine ? ' ' : '') + word;
                if (testLine.length > maxCharsPerLine && currentLine && lineCount < 1) {
                    result += currentLine + '\n';
                    currentLine = word;
                    lineCount++;
                } else {
                    currentLine = testLine;
                }
            });
            
            result += currentLine;
            textElement.textContent = result;
        }
    });
    
    // Add click animation for navigation cards
    const navCards = document.querySelectorAll('.nav-card');
    
    navCards.forEach((card) => {
        card.addEventListener('click', function() {
            // Add click animation
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });
    
    // Add click handlers for header buttons
    const loginBtn = document.querySelector('.btn-login');
    const signupBtn = document.querySelector('.btn-signup');
    
    // 로그인과 회원가입 버튼은 이제 링크로 처리되므로 별도 이벤트 리스너 불필요
    
    // Add loading animation for hero image
    const heroImage = document.querySelector('.main-hero-image');
    if (heroImage) {
        heroImage.addEventListener('load', function() {
            this.style.opacity = '1';
        });
    }
    
    // Add parallax effect to hero section
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const hero = document.querySelector('.hero');
        if (hero) {
            const rate = scrolled * -0.5;
            hero.style.transform = `translateY(${rate}px)`;
        }
    });
    
    // Add hover effects to cards
    navCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
});

// Add ripple effect to buttons
function createRipple(event) {
    const button = event.currentTarget;
    const circle = document.createElement('span');
    const diameter = Math.max(button.clientWidth, button.clientHeight);
    const radius = diameter / 2;
    
    circle.style.width = circle.style.height = `${diameter}px`;
    circle.style.left = `${event.clientX - button.offsetLeft - radius}px`;
    circle.style.top = `${event.clientY - button.offsetTop - radius}px`;
    circle.classList.add('ripple');
    
    const ripple = button.getElementsByClassName('ripple')[0];
    if (ripple) {
        ripple.remove();
    }
    
    button.appendChild(circle);
}

// Add ripple effect to all buttons
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.btn-login, .btn-signup');
    buttons.forEach(button => {
        button.addEventListener('click', createRipple);
    });
});

// Hero Slider functionality
let currentSlideIndex = 0;
let slideInterval;

// Global functions for onclick handlers
window.changeSlide = function(direction) {
    const slides = document.querySelectorAll('.slide');
    const indicators = document.querySelectorAll('.indicator');
    const totalSlides = slides.length;
    
    if (totalSlides === 0) return;
    
    stopAutoSlide();
    currentSlideIndex = (currentSlideIndex + direction + totalSlides) % totalSlides;
    showSlide(currentSlideIndex);
    startAutoSlide();
};

window.currentSlide = function(slideNumber) {
    const slides = document.querySelectorAll('.slide');
    
    if (slides.length === 0) return;
    
    stopAutoSlide();
    showSlide(slideNumber - 1);
    startAutoSlide();
};

function startAutoSlide() {
    const slides = document.querySelectorAll('.slide');
    const totalSlides = slides.length;
    
    if (totalSlides === 0) return;
    
    // 기존 타이머가 있다면 먼저 정리
    stopAutoSlide();
    
    slideInterval = setInterval(() => {
        currentSlideIndex = (currentSlideIndex + 1) % totalSlides;
        showSlide(currentSlideIndex);
    }, 20000); // Change slide every 20 seconds
}

function stopAutoSlide() {
    if (slideInterval) {
        clearInterval(slideInterval);
        slideInterval = null;
    }
}

function showSlide(index) {
    const slides = document.querySelectorAll('.slide');
    const indicators = document.querySelectorAll('.indicator');
    
    if (slides.length === 0) return;
    
    // Remove active class from all slides and indicators
    slides.forEach(slide => slide.classList.remove('active'));
    indicators.forEach(indicator => indicator.classList.remove('active'));
    
    // Add active class to current slide and indicator
    if (slides[index]) {
        slides[index].classList.add('active');
    }
    if (indicators[index]) {
        indicators[index].classList.add('active');
    }
    
    currentSlideIndex = index;
}

// 게임 슬라이더 변수
let currentFunSlideIndex = 1; // 중앙 슬라이드가 기본
let funSlideInterval;
let isDragging = false;
let startX = 0;
let currentX = 0;
let threshold = 30; // 드래그 임계값 (더 작게)
let visibleSlides = 3; // 보이는 슬라이드 수

// 게임 슬라이더 함수들
function changeFunSlide(direction) {
    const funSlides = document.querySelectorAll('.fun-slide');
    const totalFunSlides = funSlides.length;
    
    if (totalFunSlides === 0) return;
    
    currentFunSlideIndex = (currentFunSlideIndex + direction + totalFunSlides) % totalFunSlides;
    showFunSlide(currentFunSlideIndex);
}

// 전역으로도 등록
window.changeFunSlide = changeFunSlide;
window.showFunSlide = showFunSlide;

// AR 슬라이더 변수
let currentArSlideIndex = 0;

// AR 슬라이드 변경 함수
function changeArSlide(direction) {
    const arSlides = document.querySelectorAll('.ar-slide');
    const totalArSlides = arSlides.length;
    
    if (totalArSlides === 0) return;
    
    currentArSlideIndex = (currentArSlideIndex + direction + totalArSlides) % totalArSlides;
    showArSlide(currentArSlideIndex);
}

// AR 슬라이드 표시 함수
function showArSlide(index) {
    const arSlides = document.querySelectorAll('.ar-slide');
    const totalArSlides = arSlides.length;
    
    if (totalArSlides === 0) return;
    
    // 모든 슬라이드에서 active와 hidden 클래스 제거
    arSlides.forEach(slide => {
        slide.classList.remove('active', 'hidden');
        slide.style.order = '';
    });
    
    // 슬라이드가 2개일 때 특별 처리
    if (totalArSlides === 2) {
        arSlides.forEach((slide, i) => {
            if (i === index) {
                slide.classList.add('active');
                slide.classList.remove('hidden');
                slide.style.order = '2'; // 가운데
            } else {
                slide.classList.remove('active');
                slide.classList.remove('hidden');
                slide.style.order = i < index ? '1' : '3';
            }
        });
        
        currentArSlideIndex = index;
        return;
    }
    
    // 선택된 슬라이드가 항상 가운데 오도록 보이는 슬라이드 범위 계산
    let startIndex, endIndex;
    
    if (totalArSlides === 3) {
        // 전체 슬라이드가 3개면 모두 표시
        startIndex = 0;
        endIndex = totalArSlides - 1;
    } else if (totalArSlides > 3) {
        // 순환 로직으로 3개 슬라이드 표시
        if (index === 0) {
            // 첫 번째: 4, 0, 1 표시 (0이 가운데)
            startIndex = totalArSlides - 1;
            endIndex = 1;
        } else if (index === totalArSlides - 1) {
            // 마지막: 3, 4, 0 표시 (4가 가운데)
            startIndex = totalArSlides - 2;
            endIndex = 0;
        } else if (index === 1) {
            // 두 번째: 0, 1, 2 표시 (1이 가운데)
            startIndex = 0;
            endIndex = 2;
        } else if (index === totalArSlides - 2) {
            // 마지막에서 두 번째: 2, 3, 4 표시 (3이 가운데)
            startIndex = totalArSlides - 3;
            endIndex = totalArSlides - 1;
        } else {
            // 중간 슬라이드들: 선택된 슬라이드를 가운데로
            startIndex = index - 1;
            endIndex = index + 1;
        }
    }
    
    // 보이는 슬라이드들 표시 (순환 고려)
    if (startIndex <= endIndex) {
        // 일반적인 경우: startIndex <= endIndex
        for (let i = startIndex; i <= endIndex; i++) {
            if (arSlides[i]) {
                arSlides[i].classList.remove('hidden');
            }
        }
    } else {
        // 순환하는 경우: startIndex > endIndex (예: 4, 0, 1)
        for (let i = startIndex; i < totalArSlides; i++) {
            if (arSlides[i]) {
                arSlides[i].classList.remove('hidden');
            }
        }
        for (let i = 0; i <= endIndex; i++) {
            if (arSlides[i]) {
                arSlides[i].classList.remove('hidden');
            }
        }
    }
    
    // 나머지 슬라이드들 숨기기
    for (let i = 0; i < totalArSlides; i++) {
        let shouldHide = false;
        if (startIndex <= endIndex) {
            shouldHide = (i < startIndex || i > endIndex);
        } else {
            shouldHide = (i > endIndex && i < startIndex);
        }
        
        if (shouldHide) {
            if (arSlides[i]) {
                arSlides[i].classList.add('hidden');
            }
        }
    }
    
    // 선택된 슬라이드를 가운데로 이동 (CSS order 사용)
    const currentArSlides = document.querySelectorAll('.ar-slide');
    const selectedSlide = currentArSlides[index];
    
    
    if (selectedSlide) {
        // 3개 슬라이드 표시: [이전, 현재, 다음]
        const totalSlides = currentArSlides.length;
        
        // 이전 슬라이드 인덱스 (순환)
        const prevIndex = (index - 1 + totalSlides) % totalSlides;
        // 다음 슬라이드 인덱스 (순환)
        const nextIndex = (index + 1) % totalSlides;
        
        const prevSlide = currentArSlides[prevIndex];
        const nextSlide = currentArSlides[nextIndex];
        
        
        // 모든 슬라이드의 order 초기화
        currentArSlides.forEach(slide => {
            slide.style.order = '';
        });
        
        // 보이는 슬라이드들에서 hidden 클래스 제거 (order 설정 전에)
        if (prevSlide) {
            prevSlide.classList.remove('hidden');
        }
        selectedSlide.classList.remove('hidden');
        if (nextSlide) {
            nextSlide.classList.remove('hidden');
        }
        
        // 순서 설정: [이전, 현재, 다음]
        const visibleArSlides = [prevSlide, selectedSlide, nextSlide].filter(slide => slide);
        
        // 보이는 슬라이드들에 순서 설정
        visibleArSlides.forEach((slide, i) => {
            slide.style.order = (i + 1).toString();
            slide.style.setProperty('order', (i + 1).toString(), 'important');
        });
        
    }
    
    // 현재 슬라이드 활성화
    if (currentArSlides[index]) {
        currentArSlides[index].classList.add('active');
    }
    
    currentArSlideIndex = index;
}

// AR 슬라이드 클릭으로 활성화
window.setActiveArSlide = function(index) {
    showArSlide(index);
}

// 전역으로도 등록
window.changeArSlide = changeArSlide;
window.showArSlide = showArSlide;

// VR 슬라이더 변수
let currentVrSlideIndex = 0;

// VR 슬라이드 변경 함수
function changeVrSlide(direction) {
    const vrSlides = document.querySelectorAll('.vr-slide');
    const totalVrSlides = vrSlides.length;
    
    if (totalVrSlides === 0) return;
    
    currentVrSlideIndex = (currentVrSlideIndex + direction + totalVrSlides) % totalVrSlides;
    showVrSlide(currentVrSlideIndex);
}

// VR 슬라이드 표시 함수
function showVrSlide(index) {
    const vrSlides = document.querySelectorAll('.vr-slide');
    const totalVrSlides = vrSlides.length;
    
    if (totalVrSlides === 0) return;
    
    // 모든 슬라이드에서 active와 hidden 클래스 제거
    vrSlides.forEach(slide => {
        slide.classList.remove('active', 'hidden');
        slide.style.order = '';
    });
    
    // 슬라이드가 2개일 때 특별 처리
    if (totalVrSlides === 2) {
        vrSlides.forEach((slide, i) => {
            if (i === index) {
                slide.classList.add('active');
                slide.classList.remove('hidden');
                slide.style.order = '2'; // 가운데
            } else {
                slide.classList.remove('active');
                slide.classList.remove('hidden');
                slide.style.order = i < index ? '1' : '3';
            }
        });
        
        currentVrSlideIndex = index;
        return;
    }
    
    // 선택된 슬라이드가 항상 가운데 오도록 보이는 슬라이드 범위 계산
    let startIndex, endIndex;
    
    if (totalVrSlides === 3) {
        // 전체 슬라이드가 3개면 모두 표시
        startIndex = 0;
        endIndex = totalVrSlides - 1;
    } else if (totalVrSlides > 3) {
        // 순환 로직으로 3개 슬라이드 표시
        if (index === 0) {
            // 첫 번째: 4, 0, 1 표시 (0이 가운데)
            startIndex = totalVrSlides - 1;
            endIndex = 1;
        } else if (index === totalVrSlides - 1) {
            // 마지막: 3, 4, 0 표시 (4가 가운데)
            startIndex = totalVrSlides - 2;
            endIndex = 0;
        } else if (index === 1) {
            // 두 번째: 0, 1, 2 표시 (1이 가운데)
            startIndex = 0;
            endIndex = 2;
        } else if (index === totalVrSlides - 2) {
            // 마지막에서 두 번째: 2, 3, 4 표시 (3이 가운데)
            startIndex = totalVrSlides - 3;
            endIndex = totalVrSlides - 1;
        } else {
            // 중간 슬라이드들: 선택된 슬라이드를 가운데로
            startIndex = index - 1;
            endIndex = index + 1;
        }
    }
    
    // 보이는 슬라이드들 표시 (순환 고려)
    if (startIndex <= endIndex) {
        // 일반적인 경우: startIndex <= endIndex
        for (let i = startIndex; i <= endIndex; i++) {
            if (vrSlides[i]) {
                vrSlides[i].classList.remove('hidden');
            }
        }
    } else {
        // 순환하는 경우: startIndex > endIndex (예: 4, 0, 1)
        for (let i = startIndex; i < totalVrSlides; i++) {
            if (vrSlides[i]) {
                vrSlides[i].classList.remove('hidden');
            }
        }
        for (let i = 0; i <= endIndex; i++) {
            if (vrSlides[i]) {
                vrSlides[i].classList.remove('hidden');
            }
        }
    }
    
    // 나머지 슬라이드들 숨기기
    for (let i = 0; i < totalVrSlides; i++) {
        let shouldHide = false;
        if (startIndex <= endIndex) {
            shouldHide = (i < startIndex || i > endIndex);
        } else {
            shouldHide = (i > endIndex && i < startIndex);
        }
        
        if (shouldHide) {
            if (vrSlides[i]) {
                vrSlides[i].classList.add('hidden');
            }
        }
    }
    
    // 선택된 슬라이드를 가운데로 이동 (CSS order 사용)
    const currentVrSlides = document.querySelectorAll('.vr-slide');
    const selectedSlide = currentVrSlides[index];
    
    
    if (selectedSlide) {
        // 3개 슬라이드 표시: [이전, 현재, 다음]
        const totalSlides = currentVrSlides.length;
        
        // 이전 슬라이드 인덱스 (순환)
        const prevIndex = (index - 1 + totalSlides) % totalSlides;
        // 다음 슬라이드 인덱스 (순환)
        const nextIndex = (index + 1) % totalSlides;
        
        const prevSlide = currentVrSlides[prevIndex];
        const nextSlide = currentVrSlides[nextIndex];
        
        
        // 모든 슬라이드의 order 초기화
        currentVrSlides.forEach(slide => {
            slide.style.order = '';
        });
        
        // 보이는 슬라이드들에서 hidden 클래스 제거 (order 설정 전에)
        if (prevSlide) {
            prevSlide.classList.remove('hidden');
        }
        selectedSlide.classList.remove('hidden');
        if (nextSlide) {
            nextSlide.classList.remove('hidden');
        }
        
        // 순서 설정: [이전, 현재, 다음]
        const visibleVrSlides = [prevSlide, selectedSlide, nextSlide].filter(slide => slide);
        
        // 보이는 슬라이드들에 순서 설정
        visibleVrSlides.forEach((slide, i) => {
            slide.style.order = (i + 1).toString();
            slide.style.setProperty('order', (i + 1).toString(), 'important');
        });
        
    }
    
    // 현재 슬라이드 활성화
    if (currentVrSlides[index]) {
        currentVrSlides[index].classList.add('active');
    }
    
    currentVrSlideIndex = index;
}

// VR 슬라이드 클릭으로 활성화
window.setActiveVrSlide = function(index) {
    showVrSlide(index);
}

// 전역으로도 등록
window.changeVrSlide = changeVrSlide;
window.showVrSlide = showVrSlide;

// 네비게이션 카드 슬라이더 변수
let currentNavCardsIndex = 0;
let navCardsVisible = 4; // 한 번에 보이는 카드 수

// 네비게이션 카드 슬라이더 변경 함수
function changeNavCards(direction) {
    const slider = document.querySelector('.nav-cards-slider');
    const cards = document.querySelectorAll('.nav-cards-slider .nav-card');
    const totalCards = cards.length;
    
    if (totalCards === 0 || !slider) return;
    
    // 반응형으로 보이는 카드 수 조정
    let visible = 4;
    if (window.innerWidth <= 480) {
        visible = 1;
    } else if (window.innerWidth <= 768) {
        visible = 2;
    } else if (window.innerWidth <= 1024) {
        visible = 3;
    }
    
    // 현재 인덱스 업데이트
    currentNavCardsIndex += direction;
    
    // 경계 체크
    const maxIndex = Math.max(0, totalCards - visible);
    if (currentNavCardsIndex < 0) {
        currentNavCardsIndex = maxIndex;
    } else if (currentNavCardsIndex > maxIndex) {
        currentNavCardsIndex = 0;
    }
    
    // 슬라이더 이동
    const container = slider.parentElement;
    const containerWidth = container ? container.offsetWidth : 0;
    const gap = 30; // CSS gap 값
    const cardWidth = (containerWidth - (gap * (visible - 1))) / visible;
    const translateX = -currentNavCardsIndex * (cardWidth + gap);
    slider.style.transform = `translateX(${translateX}px)`;
}

// 전역으로 등록
window.changeNavCards = changeNavCards;

// 쇼핑 슬라이더 변수
let currentShopSlideIndex = 0;

// 쇼핑 슬라이드 변경 함수
function changeShopSlide(direction) {
    const shopSlides = document.querySelectorAll('.shop-slide');
    const totalShopSlides = shopSlides.length;
    
    if (totalShopSlides === 0) return;
    
    currentShopSlideIndex = (currentShopSlideIndex + direction + totalShopSlides) % totalShopSlides;
    showShopSlide(currentShopSlideIndex);
}

// 쇼핑 슬라이드 표시 함수
function showShopSlide(index) {
    const shopSlides = document.querySelectorAll('.shop-slide');
    const totalShopSlides = shopSlides.length;
    
    if (totalShopSlides === 0) return;
    
    // 모든 슬라이드에서 active와 hidden 제거
    shopSlides.forEach(slide => {
        slide.classList.remove('active', 'hidden');
        slide.style.order = '';
    });
    
    // 슬라이드가 2개일 때 특별 처리
    if (totalShopSlides === 2) {
        // 2개일 때는 선택된 슬라이드만 표시하거나 둘 다 표시
        shopSlides.forEach((slide, i) => {
            if (i === index) {
                slide.classList.add('active');
                slide.classList.remove('hidden');
                slide.style.order = '2'; // 가운데
            } else {
                slide.classList.remove('active');
                slide.classList.remove('hidden');
                // 다른 하나는 옆에 표시
                slide.style.order = i < index ? '1' : '3';
            }
        });
        
        currentShopSlideIndex = index;
        return;
    }
    
    // 선택된 슬라이드가 항상 가운데 오도록 보이는 슬라이드 범위 계산
    let startIndex, endIndex;
    
    if (totalShopSlides === 3) {
        // 전체 슬라이드가 3개면 모두 표시
        startIndex = 0;
        endIndex = totalShopSlides - 1;
    } else if (totalShopSlides > 3) {
        // 순환 로직으로 3개 슬라이드 표시
        if (index === 0) {
            // 첫 번째: 3, 0, 1 표시 (0이 가운데)
            startIndex = totalShopSlides - 1;
            endIndex = 1;
        } else if (index === totalShopSlides - 1) {
            // 마지막: 2, 3, 0 표시 (3이 가운데)
            startIndex = totalShopSlides - 2;
            endIndex = 0;
        } else if (index === 1) {
            // 두 번째: 0, 1, 2 표시 (1이 가운데)
            startIndex = 0;
            endIndex = 2;
        } else if (index === totalShopSlides - 2) {
            // 마지막에서 두 번째: 1, 2, 3 표시 (2가 가운데)
            startIndex = totalShopSlides - 3;
            endIndex = totalShopSlides - 1;
        } else {
            // 중간 슬라이드들: 선택된 슬라이드를 가운데로
            startIndex = index - 1;
            endIndex = index + 1;
        }
    }
    
    // 보이는 슬라이드들 표시 (순환 고려)
    if (startIndex <= endIndex) {
        // 일반적인 경우: startIndex <= endIndex
        for (let i = startIndex; i <= endIndex; i++) {
            if (shopSlides[i]) {
                shopSlides[i].classList.remove('hidden');
            }
        }
    } else {
        // 순환하는 경우: startIndex > endIndex (예: 3, 0, 1)
        for (let i = startIndex; i < totalShopSlides; i++) {
            if (shopSlides[i]) {
                shopSlides[i].classList.remove('hidden');
            }
        }
        for (let i = 0; i <= endIndex; i++) {
            if (shopSlides[i]) {
                shopSlides[i].classList.remove('hidden');
            }
        }
    }
    
    // 나머지 슬라이드들 숨기기
    for (let i = 0; i < totalShopSlides; i++) {
        let shouldHide = false;
        if (startIndex <= endIndex) {
            shouldHide = (i < startIndex || i > endIndex);
        } else {
            shouldHide = (i > endIndex && i < startIndex);
        }
        
        if (shouldHide) {
            if (shopSlides[i]) {
                shopSlides[i].classList.add('hidden');
            }
        }
    }
    
    // 선택된 슬라이드를 가운데로 이동 (CSS order 사용)
    const currentShopSlides = document.querySelectorAll('.shop-slide');
    const selectedSlide = currentShopSlides[index];
    
    
    if (selectedSlide) {
        // 3개 슬라이드 표시: [이전, 현재, 다음]
        const totalSlides = currentShopSlides.length;
        
        // 이전 슬라이드 인덱스 (순환)
        const prevIndex = (index - 1 + totalSlides) % totalSlides;
        // 다음 슬라이드 인덱스 (순환)
        const nextIndex = (index + 1) % totalSlides;
        
        const prevSlide = currentShopSlides[prevIndex];
        const nextSlide = currentShopSlides[nextIndex];
        
        
        // 모든 슬라이드의 order 초기화
        currentShopSlides.forEach(slide => {
            slide.style.order = '';
        });
        
        // 보이는 슬라이드들에서 hidden 클래스 제거 (order 설정 전에)
        if (prevSlide) {
            prevSlide.classList.remove('hidden');
        }
        selectedSlide.classList.remove('hidden');
        if (nextSlide) {
            nextSlide.classList.remove('hidden');
        }
        
        // 순서 설정: [이전, 현재, 다음]
        const visibleShopSlides = [prevSlide, selectedSlide, nextSlide].filter(slide => slide);
        
        // 보이는 슬라이드들에 순서 설정
        visibleShopSlides.forEach((slide, i) => {
            slide.style.order = (i + 1).toString();
            slide.style.setProperty('order', (i + 1).toString(), 'important');
        });
        
    }
    
    // 현재 슬라이드 활성화
    if (currentShopSlides[index]) {
        currentShopSlides[index].classList.add('active');
    }
    
    currentShopSlideIndex = index;
}

// 쇼핑 슬라이드 클릭으로 활성화
window.setActiveShopSlide = function(index) {
    showShopSlide(index);
}

// 전역으로도 등록
window.changeShopSlide = changeShopSlide;
window.showShopSlide = showShopSlide;


function startFunAutoSlide() {
    const autoFunSlides = document.querySelectorAll('.fun-slide');
    const totalFunSlides = autoFunSlides.length;
    
    if (totalFunSlides === 0) return;
    
    funSlideInterval = setInterval(() => {
        currentFunSlideIndex = (currentFunSlideIndex + 1) % totalFunSlides;
        showFunSlide(currentFunSlideIndex);
    }, 5000); // Change slide every 5 seconds
}

function stopFunAutoSlide() {
    if (funSlideInterval) {
        clearInterval(funSlideInterval);
        funSlideInterval = null;
    }
}

function showFunSlide(index) {
    const funSlides = document.querySelectorAll('.fun-slide');
    const totalFunSlides = funSlides.length;
    
    if (totalFunSlides === 0) return;
    
    // 모든 슬라이드에서 active와 hidden 클래스 제거
    funSlides.forEach(slide => {
        slide.classList.remove('active', 'hidden');
        slide.style.order = '';
    });
    
    // 슬라이드가 2개일 때 특별 처리
    if (totalFunSlides === 2) {
        funSlides.forEach((slide, i) => {
            if (i === index) {
                slide.classList.add('active');
                slide.classList.remove('hidden');
                slide.style.order = '2'; // 가운데
            } else {
                slide.classList.remove('active');
                slide.classList.remove('hidden');
                slide.style.order = i < index ? '1' : '3';
            }
        });
        
        currentFunSlideIndex = index;
        return;
    }
    
    // 선택된 슬라이드가 항상 가운데 오도록 보이는 슬라이드 범위 계산
    let startIndex, endIndex;
    
    if (totalFunSlides === 3) {
        // 전체 슬라이드가 3개면 모두 표시
        startIndex = 0;
        endIndex = totalFunSlides - 1;
    } else if (totalFunSlides > 3) {
        // 순환 로직으로 3개 슬라이드 표시
        if (index === 0) {
            // 첫 번째: 4, 0, 1 표시 (0이 가운데)
            startIndex = totalFunSlides - 1;
            endIndex = 1;
        } else if (index === totalFunSlides - 1) {
            // 마지막: 3, 4, 0 표시 (4가 가운데)
            startIndex = totalFunSlides - 2;
            endIndex = 0;
        } else if (index === 1) {
            // 두 번째: 0, 1, 2 표시 (1이 가운데)
            startIndex = 0;
            endIndex = 2;
        } else if (index === totalFunSlides - 2) {
            // 마지막에서 두 번째: 2, 3, 4 표시 (3이 가운데)
            startIndex = totalFunSlides - 3;
            endIndex = totalFunSlides - 1;
        } else {
            // 중간 슬라이드들: 선택된 슬라이드를 가운데로
            startIndex = index - 1;
            endIndex = index + 1;
        }
    }
    
    // 보이는 슬라이드들 표시 (순환 고려)
    if (startIndex <= endIndex) {
        // 일반적인 경우: startIndex <= endIndex
        for (let i = startIndex; i <= endIndex; i++) {
            if (funSlides[i]) {
                funSlides[i].classList.remove('hidden');
            }
        }
    } else {
        // 순환하는 경우: startIndex > endIndex (예: 4, 0, 1)
        for (let i = startIndex; i < totalFunSlides; i++) {
            if (funSlides[i]) {
                funSlides[i].classList.remove('hidden');
            }
        }
        for (let i = 0; i <= endIndex; i++) {
            if (funSlides[i]) {
                funSlides[i].classList.remove('hidden');
            }
        }
    }
    
    // 나머지 슬라이드들 숨기기
    for (let i = 0; i < totalFunSlides; i++) {
        let shouldHide = false;
        if (startIndex <= endIndex) {
            shouldHide = (i < startIndex || i > endIndex);
        } else {
            shouldHide = (i > endIndex && i < startIndex);
        }
        
        if (shouldHide) {
            if (funSlides[i]) {
                funSlides[i].classList.add('hidden');
            }
        }
    }
    
    // 선택된 슬라이드를 가운데로 이동 (CSS order 사용)
    const currentFunSlides = document.querySelectorAll('.fun-slide');
    const selectedSlide = currentFunSlides[index];
    
    if (selectedSlide) {
        // 3개 슬라이드 표시: [이전, 현재, 다음]
        const totalSlides = currentFunSlides.length;
        
        // 이전 슬라이드 인덱스 (순환)
        const prevIndex = (index - 1 + totalSlides) % totalSlides;
        // 다음 슬라이드 인덱스 (순환)
        const nextIndex = (index + 1) % totalSlides;
        
        const prevSlide = currentFunSlides[prevIndex];
        const nextSlide = currentFunSlides[nextIndex];
        
        // 모든 슬라이드의 order 초기화
        currentFunSlides.forEach(slide => {
            slide.style.order = '';
        });
        
        // 보이는 슬라이드들에서 hidden 클래스 제거 (order 설정 전에)
        if (prevSlide) {
            prevSlide.classList.remove('hidden');
        }
        selectedSlide.classList.remove('hidden');
        if (nextSlide) {
            nextSlide.classList.remove('hidden');
        }
        
        // 순서 설정: [이전, 현재, 다음]
        const visibleFunSlides = [prevSlide, selectedSlide, nextSlide].filter(slide => slide);
        
        // 보이는 슬라이드들에 순서 설정
        visibleFunSlides.forEach((slide, i) => {
            slide.style.order = (i + 1).toString();
            slide.style.setProperty('order', (i + 1).toString(), 'important');
        });
    }
    
    // 현재 슬라이드 활성화
    if (currentFunSlides[index]) {
        currentFunSlides[index].classList.add('active');
    }
    
    currentFunSlideIndex = index;
}

// 슬라이드 클릭으로 활성화
window.setActiveSlide = function(index) {
    showFunSlide(index);
}

// 드래그 시작
function startDrag(e) {
    isDragging = true;
    startX = e.type === 'mousedown' ? e.clientX : e.touches[0].clientX;
    currentX = startX;
    
    const dragFunSlider = document.querySelector('.fun-slider');
    if (dragFunSlider) {
        dragFunSlider.style.cursor = 'grabbing';
    }
}

// 드래그 중
function drag(e) {
    if (!isDragging) return;
    
    e.preventDefault();
    currentX = e.type === 'mousemove' ? e.clientX : e.touches[0].clientX;
}

// 드래그 종료
function endDrag(e) {
    if (!isDragging) return;
    
    isDragging = false;
    const deltaX = currentX - startX;
    
    const endFunSlider = document.querySelector('.fun-slider');
    if (endFunSlider) {
        endFunSlider.style.cursor = 'grab';
    }
    
    // 임계값을 넘으면 슬라이드 변경
    if (Math.abs(deltaX) > threshold) {
        if (deltaX > 0) {
            changeFunSlide(-1); // 오른쪽으로 드래그하면 이전 슬라이드
        } else {
            changeFunSlide(1); // 왼쪽으로 드래그하면 다음 슬라이드
        }
    }
}

// Initialize slider when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.slide');
    const indicators = document.querySelectorAll('.indicator');
    const heroSlider = document.querySelector('.hero-slider');
    
    // Start auto slide
    if (slides.length > 0) {
        showSlide(0); // Show first slide
        // 기존 타이머가 있다면 정리 후 시작
        stopAutoSlide();
        startAutoSlide();
    }
    
    // Pause auto slide on hover
    if (heroSlider) {
        heroSlider.addEventListener('mouseenter', function() {
            stopAutoSlide();
        });
        heroSlider.addEventListener('mouseleave', function() {
            startAutoSlide();
        });
    }
    
    // 게임 슬라이더 초기화
    const initFunSlides = document.querySelectorAll('.fun-slide');
    const initFunSlider = document.querySelector('.fun-slider');
    
    if (initFunSlides.length > 0) {
        // 활성 슬라이드가 있으면 그 인덱스 사용
        let initialIndex = initFunSlides.length <= 2 ? 0 : 1;
        initFunSlides.forEach((slide, index) => {
            if (slide.classList.contains('active')) {
                initialIndex = index;
            }
        });
        // 활성 슬라이드가 없으면: 2개 이하면 첫 번째(0), 3개 이상이면 중간(1)
        if (!Array.from(initFunSlides).some(slide => slide.classList.contains('active'))) {
            initialIndex = initFunSlides.length <= 2 ? 0 : Math.min(1, Math.floor(initFunSlides.length / 2));
        }
        showFunSlide(initialIndex);
    }
    
    // AR 슬라이더 초기화
    const initArSlides = document.querySelectorAll('.ar-slide');
    const initArSlider = document.querySelector('.ar-slider');
    
    if (initArSlides.length > 0) {
        // 활성 슬라이드가 있으면 그 인덱스 사용, 없으면 첫 번째 인덱스 사용
        let initialIndex = 0;
        initArSlides.forEach((slide, index) => {
            if (slide.classList.contains('active')) {
                initialIndex = index;
            }
        });
        showArSlide(initialIndex);
    }
    
    // VR 슬라이더 초기화
    const initVrSlides = document.querySelectorAll('.vr-slide');
    const initVrSlider = document.querySelector('.vr-slider');
    
    if (initVrSlides.length > 0) {
        // 활성 슬라이드가 있으면 그 인덱스 사용, 없으면 첫 번째 인덱스 사용
        let initialIndex = 0;
        initVrSlides.forEach((slide, index) => {
            if (slide.classList.contains('active')) {
                initialIndex = index;
            }
        });
        showVrSlide(initialIndex);
    }
    
    // 네비게이션 카드 슬라이더 초기화
    const navCardsSlider = document.querySelector('.nav-cards-slider');
    const navCards = document.querySelectorAll('.nav-cards-slider .nav-card');
    if (navCards.length > 0 && navCardsSlider) {
        // 반응형으로 보이는 카드 수 및 너비 조정
        const updateVisibleCards = () => {
            const container = navCardsSlider.parentElement;
            if (!container) return;
            
            let visible = 4;
            if (window.innerWidth <= 480) {
                visible = 1;
            } else if (window.innerWidth <= 768) {
                visible = 2;
            } else if (window.innerWidth <= 1024) {
                visible = 3;
            }
            
            navCardsVisible = visible;
            const containerWidth = container.offsetWidth;
            const gap = 30; // CSS gap 값
            const cardWidth = (containerWidth - (gap * (visible - 1))) / visible;
            
            navCards.forEach(card => {
                card.style.width = `${cardWidth}px`;
                card.style.flex = `0 0 ${cardWidth}px`;
            });
            
            // 현재 인덱스 재조정
            const maxIndex = Math.max(0, navCards.length - visible);
            if (currentNavCardsIndex > maxIndex) {
                currentNavCardsIndex = maxIndex;
            }
            
            // 슬라이더 위치 업데이트
            const translateX = -currentNavCardsIndex * cardWidth;
            navCardsSlider.style.transform = `translateX(${translateX}px)`;
        };
        
        updateVisibleCards();
        window.addEventListener('resize', updateVisibleCards);
    }
    
    // 쇼핑 슬라이더 초기화
    const initShopSlides = document.querySelectorAll('.shop-slide');
    const initShopSlider = document.querySelector('.shop-slider');
    
    if (initShopSlides.length > 0) {
        // 슬라이드가 2개 이하면 첫 번째(0), 3개 이상이면 중간(1) 표시
        const initialShopIndex = initShopSlides.length <= 2 ? 0 : 1;
        showShopSlide(initialShopIndex);
    }
    
           // 게임 슬라이더는 자동 슬라이드 없음
           // if (funSlider) {
           //     funSlider.addEventListener('mouseenter', function() {
           //         stopFunAutoSlide();
           //     });
           //     funSlider.addEventListener('mouseleave', function() {
           //         startFunAutoSlide();
           //     });
           // }
           
           // 드래그 이벤트 리스너 추가
           if (initFunSlider) {
               // 마우스 이벤트
               initFunSlider.addEventListener('mousedown', function(e) {
                   startDrag(e);
               });
               
               document.addEventListener('mousemove', function(e) {
                   if (isDragging) {
                       drag(e);
                   }
               });
               
               document.addEventListener('mouseup', function(e) {
                   if (isDragging) {
                       endDrag(e);
                   }
               });
               
               // 터치 이벤트 (모바일 지원)
               initFunSlider.addEventListener('touchstart', function(e) {
                   startDrag(e);
               }, { passive: false });
               
               initFunSlider.addEventListener('touchmove', function(e) {
                   if (isDragging) {
                       drag(e);
                   }
               }, { passive: false });
               
               initFunSlider.addEventListener('touchend', function(e) {
                   if (isDragging) {
                       endDrag(e);
                   }
               });
               
               // 커서 스타일 설정
               initFunSlider.style.cursor = 'grab';
           }
});

// 쇼핑 아이템 링크 클릭 이벤트 (모바일/데스크톱 구분)
document.addEventListener('DOMContentLoaded', function() {
    // 모바일/데스크톱 URL 가져오기 함수
    function getShopUrl() {
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || 
                        (window.innerWidth <= 768);
        const mobileUrl = 'https://m.smartstore.naver.com/inlove7';
        const desktopUrl = 'https://smartstore.naver.com/inlove7';
        return isMobile ? mobileUrl : desktopUrl;
    }
    
    // 네비게이션 카드 쇼핑 버튼
    const navCardShop = document.querySelector('.nav-card-shop');
    if (navCardShop) {
        navCardShop.addEventListener('click', function(e) {
            e.preventDefault();
            const shopUrl = getShopUrl();
            window.location.href = shopUrl;
        });
    }
    
    // 인덱스 쇼핑 아이템 링크
    const shopItemLinks = document.querySelectorAll('.shop-item-link[data-shop-link]');
    
    shopItemLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // 슬라이드 변경 이벤트 방지
            const shopUrl = getShopUrl();
            window.location.href = shopUrl;
        });
    });
    
    // Scroll to top functionality
    const scrollTopBtn = document.querySelector('.scroll-top-btn');
    
    if (scrollTopBtn) {
        scrollTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Show/hide scroll to top button based on scroll position
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollTopBtn.style.opacity = '1';
                scrollTopBtn.style.visibility = 'visible';
            } else {
                scrollTopBtn.style.opacity = '0';
                scrollTopBtn.style.visibility = 'hidden';
            }
        });
        
        // Initially hide the button
        scrollTopBtn.style.opacity = '0';
        scrollTopBtn.style.visibility = 'hidden';
        scrollTopBtn.style.transition = 'opacity 0.3s ease, visibility 0.3s ease';
    }
});

// Add CSS for ripple effect
const style = document.createElement('style');
style.textContent = `
    .btn-login, .btn-signup {
        position: relative;
        overflow: hidden;
    }
    
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: scale(0);
        animation: ripple-animation 0.6s linear;
        pointer-events: none;
    }
    
    @keyframes ripple-animation {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);