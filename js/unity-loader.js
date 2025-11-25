// Unity WebGL 로더 함수들

// Road Unity 로드
function loadRoadUnity() {
    if (typeof UnityLoader !== 'undefined') {
        var unityInstance = UnityLoader.instantiate("unityContainer", "road/Build/Road_Web_250918.json", {onProgress: UnityProgress});
        return unityInstance;
    }
    return null;
}

// Minigame Unity 로드
function loadMinigameUnity() {
    if (typeof UnityLoader !== 'undefined') {
        var unityInstance = UnityLoader.instantiate("unityContainer", "minigame/Build/minigame.json", {onProgress: UnityProgress});
        return unityInstance;
    }
    return null;
}

// Kid Quiz Unity 로드
function loadKidQuizUnity() {
    if (typeof UnityLoader !== 'undefined') {
        var unityInstance = UnityLoader.instantiate("unityContainer", "Kid_Quiz/Build/Kid_Quiz.json", {onProgress: UnityProgress});
        return unityInstance;
    }
    return null;
}









