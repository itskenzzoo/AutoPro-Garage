document.addEventListener('DOMContentLoaded', () => {
    const themeBtn = document.getElementById('themeToggleBtn');
    const body = document.body;
    
    // Check local storage for theme
    const currentTheme = localStorage.getItem('theme');
    if (currentTheme === 'dark') {
        body.classList.add('dark-theme');
        if(themeBtn) themeBtn.innerHTML = '☀️ Light Mode';
    } else {
        if(themeBtn) themeBtn.innerHTML = '🌙 Dark Mode';
    }

    if(themeBtn) {
        themeBtn.addEventListener('click', () => {
            body.classList.toggle('dark-theme');
            let theme = 'light';
            if (body.classList.contains('dark-theme')) {
                theme = 'dark';
                themeBtn.innerHTML = '☀️ Light Mode';
            } else {
                themeBtn.innerHTML = '🌙 Dark Mode';
            }
            localStorage.setItem('theme', theme);
        });
    }
});
