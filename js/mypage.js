// mypage.php에서 사용하는 JavaScript
// PHP 변수는 전역 변수로 선언됨 (mypage.php에서 정의)

// 현재 선택된 기간과 날짜는 전역 변수로 선언 (PHP에서 초기화)
// let currentPeriod = 'day';
// let currentDate = '<?php echo date('Y-m-d'); ?>';
// let isNavigating = false;

if (typeof currentPeriodLabel === 'undefined') {
    window.currentPeriodLabel = '';
}

// AJAX로 데이터 가져오기
async function fetchMetricsData(period, date) {
    try {
        const url = `metrics_api.php?period=${period}&date=${date}`;
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const text = await response.text();
        
        if (!text.trim()) {
            throw new Error('Empty response received');
        }
        
        const result = JSON.parse(text);
        
        if (result.success) {
            return result;
        } else {
            console.error('데이터 가져오기 실패:', result.error);
            return null;
        }
    } catch (error) {
        console.error('AJAX 오류:', error);
        console.error('Error stack:', error.stack);
        return null;
    }
}

// 날짜 계산 함수들
function getNextDate(date, period) {
    const d = new Date(date);
    
    switch (period) {
        case 'day':
            d.setDate(d.getDate() + 1);
            break;
        case 'week':
            d.setDate(d.getDate() + 7);
            break;
        case 'month':
            d.setMonth(d.getMonth() + 1);
            break;
        case 'year':
            d.setFullYear(d.getFullYear() + 1);
            break;
        case '10year':
            d.setFullYear(d.getFullYear() + 10);
            break;
        case '30year':
            d.setFullYear(d.getFullYear() + 30);
            break;
        case '100year':
            d.setFullYear(d.getFullYear() + 100);
            break;
    }
    
    return d.toISOString().split('T')[0];
}

function getPrevDate(date, period) {
    const d = new Date(date);
    
    switch (period) {
        case 'day':
            d.setDate(d.getDate() - 1);
            break;
        case 'week':
            d.setDate(d.getDate() - 7);
            break;
        case 'month':
            d.setMonth(d.getMonth() - 1);
            break;
        case 'year':
            d.setFullYear(d.getFullYear() - 1);
            break;
        case '10year':
            d.setFullYear(d.getFullYear() - 10);
            break;
        case '30year':
            d.setFullYear(d.getFullYear() - 30);
            break;
        case '100year':
            d.setFullYear(d.getFullYear() - 100);
            break;
    }
    
    return d.toISOString().split('T')[0];
}

// 날짜와 함께 데이터 업데이트
async function updateMetricsDataWithDate(period, date) {
    currentPeriod = period;
    currentDate = date;
    
    // 로딩 표시
    showLoading();
    const periodLabel = getPeriodLabel(period, date);
    console.log('updateMetricsDataWithDate 호출:', { period, date, periodLabel });
    
    // 즉시 날짜 표시 업데이트
    updateDateDisplay(period, date, periodLabel);
    
    try {
        const result = await fetchMetricsData(period, date);
        
        if (result && result.success) {
            updateMetricsData(period, result.data, result.achievementRates, periodLabel);
            // 데이터 업데이트 후 날짜 다시 업데이트
            setTimeout(() => {
                updateDateDisplay(period, date, periodLabel);
            }, 100);
        } else {
            updateMetricsData(period, todayStats, achievementRates, periodLabel);
            setTimeout(() => {
                updateDateDisplay(period, date, periodLabel);
            }, 100);
        }
    } catch (error) {
        console.error('AJAX 오류:', error);
        console.error('Error stack:', error.stack);
        updateMetricsData(period, todayStats, achievementRates, periodLabel);
        setTimeout(() => {
            updateDateDisplay(period, date, periodLabel);
        }, 100);
    }
    
    hideLoading();
    
    // 최종적으로 한 번 더 업데이트
    setTimeout(() => {
        updateDateDisplay(period, date, periodLabel);
    }, 500);
}

// 로딩 표시
function showLoading() {
    const metricsContainer = document.querySelector('.metrics-container');
    if (metricsContainer) {
        metricsContainer.style.opacity = '0.5';
        metricsContainer.style.pointerEvents = 'none';
    }
}

function hideLoading() {
    const metricsContainer = document.querySelector('.metrics-container');
    if (metricsContainer) {
        metricsContainer.style.opacity = '1';
        metricsContainer.style.pointerEvents = 'auto';
    }
}

function padNumber(num) {
    return String(num).padStart(2, '0');
}

function parseDateParts(dateStr) {
    if (!dateStr) {
        return null;
    }
    const match = /^([0-9]{4})-([0-9]{2})-([0-9]{2})$/.exec(dateStr);
    if (!match) {
        return null;
    }
    const year = parseInt(match[1], 10);
    const month = parseInt(match[2], 10);
    const day = parseInt(match[3], 10);
    if (Number.isNaN(year) || Number.isNaN(month) || Number.isNaN(day)) {
        return null;
    }
    return {
        year,
        month,
        day,
        dateObj: new Date(year, month - 1, day)
    };
}

function getPeriodLabel(period, date) {
    try {
        const parsed = parseDateParts(date);
        const d = parsed ? parsed.dateObj : new Date(date);
        let displayText = '';
        
        console.log('getPeriodLabel 호출:', { period, date, parsed, d: d.toString() });
        
        switch (period) {
            case 'day': {
                const year = parsed ? parsed.year : d.getFullYear();
                const month = parsed ? parsed.month : (d.getMonth() + 1);
                const day = parsed ? parsed.day : d.getDate();
                displayText = `${year}.${padNumber(month)}.${padNumber(day)}`;
                break;
            }
            case 'week': {
                const baseDate = parsed ? new Date(parsed.year, parsed.month - 1, parsed.day) : new Date(d);
                const weekStart = new Date(baseDate);
                weekStart.setDate(baseDate.getDate() - baseDate.getDay());
                const weekEnd = new Date(weekStart);
                weekEnd.setDate(weekStart.getDate() + 6);
                
                const startYear = weekStart.getFullYear();
                const startMonth = padNumber(weekStart.getMonth() + 1);
                const startDay = padNumber(weekStart.getDate());
                const endYear = weekEnd.getFullYear();
                const endMonth = padNumber(weekEnd.getMonth() + 1);
                const endDay = padNumber(weekEnd.getDate());
                
                displayText = `${startYear}.${startMonth}.${startDay} - ${endYear}.${endMonth}.${endDay}`;
                break;
            }
            case 'month': {
                const monthYear = parsed ? parsed.year : d.getFullYear();
                const monthNum = parsed ? parsed.month : (d.getMonth() + 1);
                displayText = `${monthYear}.${padNumber(monthNum)}`;
                break;
            }
            case 'year':
                displayText = (parsed ? parsed.year : d.getFullYear()) + '년';
                break;
            case '10year': {
                const endYear = parsed ? parsed.year : d.getFullYear();
                const startYearDecade = endYear - 9;
                displayText = `${startYearDecade} ~ ${endYear}`;
                console.log('10year 레이블 생성:', { endYear, startYearDecade, displayText });
                break;
            }
            case '30year': {
                const endYear30 = parsed ? parsed.year : d.getFullYear();
                const startYear30 = endYear30 - 29;
                displayText = `${startYear30} ~ ${endYear30}`;
                console.log('30year 레이블 생성:', { endYear30, startYear30, displayText });
                break;
            }
            case '100year': {
                const endYear100 = parsed ? parsed.year : d.getFullYear();
                const startYear100 = endYear100 - 99;
                displayText = `${startYear100} ~ ${endYear100}`;
                console.log('100year 레이블 생성:', { endYear100, startYear100, displayText });
                break;
            }
            default:
                displayText = '';
                console.warn('알 수 없는 기간:', period);
        }
        
        console.log('getPeriodLabel 결과:', displayText);
        return displayText;
    } catch (error) {
        console.error('getPeriodLabel 오류:', error);
        return '';
    }
}

// 날짜 표시 업데이트
function updateDateDisplay(period, date, labelText = '') {
    const dateDisplay = document.querySelector('.date-display');
    if (!dateDisplay) {
        console.warn('날짜 표시 요소를 찾을 수 없습니다.');
        return;
    }
    
    const displayText = labelText || getPeriodLabel(period, date);
    if (!displayText) {
        console.warn('표시할 날짜 텍스트가 없습니다.', { period, date, labelText });
        return;
    }
    
    // 업데이트
    dateDisplay.textContent = displayText;
    currentPeriodLabel = displayText;
}

// 메인 업데이트 함수 - 파일이 너무 길어서 핵심 부분만 포함
// 전체 코드는 mypage.php에 남겨두되, 반복되는 부분은 함수로 분리

// 레이더 차트 그리기 함수 등은 mypage.php에 남겨둠 (PHP 변수 의존성 때문)

