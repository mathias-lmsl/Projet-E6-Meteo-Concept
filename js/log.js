// Fonction pour basculer entre le mode sombre et clair
function toggleDarkMode() {
    document.body.classList.toggle('darkmode');
    const isDark = document.body.classList.contains('darkmode');
    localStorage.setItem('darkmode', isDark ? '1' : '0');
}

// Fonction pour garder le mode sombre après le rechargement de la page
window.addEventListener('DOMContentLoaded', () => {
    if (localStorage.getItem('darkmode') === '1') {
        document.body.classList.add('darkmode');
    }
});