<footer>
    <p>© 2025 CungzApp</p>
</footer>
<script>
    const toggle = document.getElementById('darkModeToggle');
    const body = document.body;
    if (localStorage.getItem('darkMode') === 'enabled') {
        body.classList.add('dark-mode');
        toggle.textContent = '☀️';
    }
    toggle.addEventListener('click', () => {
        body.classList.toggle('dark-mode');
        if (body.classList.contains('dark-mode')) {
            localStorage.setItem('darkMode', 'enabled');
            toggle.textContent = '☀️';
        } else {
            localStorage.setItem('darkMode', 'disabled');
            toggle.textContent = '🌙';
        }
    });
</script>
</body>
</html>