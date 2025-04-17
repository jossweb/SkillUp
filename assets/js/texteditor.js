var mdEditor = document.getElementById('md-editor');

function getCookie(name) {
    const cookies = document.cookie.split("; ");
    for (const cookie of cookies) {
        const [key, value] = cookie.split("=");
        if (key === name) return decodeURIComponent(value);
    }
    return null;
}

mdEditor.value = getCookie("activeChapMd");

function ChangeActiveChapter(id) {
    document.cookie = `activeChapId=${id}; path=./; max-age=3600`;
    window.location.href = window.location.href;
}