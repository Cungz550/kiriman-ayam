</main>

<footer>
    <p>© 2025 CungzApp</p>
</footer>

<script>
// Initialize Choices.js for kiriman select if exists
document.addEventListener('DOMContentLoaded', function() {
    const kirimanElement = document.getElementById('kiriman');
    if (kirimanElement) {
        const kirimanSelect = new Choices('#kiriman', {
            searchEnabled: true,
            itemSelectText: '', // Biar ga ada teks "Press to select"
            noResultsText: 'Tidak ada hasil',
            noChoicesText: 'Tidak ada pilihan',
            loadingText: 'Memuat...',
        });
    }
});

// Dark mode functionality
const toggle = document.getElementById('darkModeToggle');
if (toggle) {
    // Check initial preference from localStorage
    if (localStorage.getItem('darkMode') === 'enabled') {
        document.body.classList.add('dark-mode');
    }

    toggle.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');

        // Save preference to localStorage
        if (document.body.classList.contains('dark-mode')) {
            localStorage.setItem('darkMode', 'enabled');
        } else {
            localStorage.setItem('darkMode', 'disabled');
        }
    });
}
</script>

</body>
</html>