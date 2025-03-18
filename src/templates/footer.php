        </main>
    </div><!-- End of wrapper -->
    
    <footer>
        <p>&copy; <?php echo date('Y'); ?> MyGoalie - Track your goals and achievements</p>
    </footer>

    <script>
        // Dark mode toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        const modeIcon = document.getElementById('modeIcon');
        
        // Initialize dark mode from localStorage
        function initDarkMode() {
            const isDarkMode = localStorage.getItem('darkMode') === 'enabled';
            document.documentElement.classList.toggle('dark-mode', isDarkMode);
            document.body.classList.toggle('dark-mode', isDarkMode);
            if (darkModeToggle) darkModeToggle.checked = isDarkMode;
            updateModeIcon(isDarkMode);
        }
        
        function updateModeIcon(isDarkMode) {
            if (modeIcon) {
                modeIcon.innerHTML = isDarkMode ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
            }
        }
        
        // Toggle dark mode
        if (darkModeToggle) {
            darkModeToggle.addEventListener('change', function() {
                if (this.checked) {
                    document.documentElement.classList.add('dark-mode');
                    document.body.classList.add('dark-mode');
                    localStorage.setItem('darkMode', 'enabled');
                    document.cookie = "dark_mode=enabled; path=/; max-age=31536000"; // 1 year
                } else {
                    document.documentElement.classList.remove('dark-mode');
                    document.body.classList.remove('dark-mode');
                    localStorage.setItem('darkMode', 'disabled');
                    document.cookie = "dark_mode=disabled; path=/; max-age=31536000"; // 1 year
                }
                updateModeIcon(this.checked);
            });
        }
        
        // Initialize on page load
        initDarkMode();
    </script>
</body>
</html> 