// Sidebar functionality
function initializeSidebar() {
    // Sidebar toggle
    $('#sidebarToggle').on('click', function() {
        $('#sidebar').toggleClass('show');
        $('#sidebarOverlay').toggleClass('show');
    });

    // Close sidebar when clicking overlay
    $('#sidebarOverlay').on('click', function() {
        $('#sidebar').removeClass('show');
        $('#sidebarOverlay').removeClass('show');
    });

    // Close sidebar on escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('#sidebar').removeClass('show');
            $('#sidebarOverlay').removeClass('show');
        }
    });

    // Theme Toggle Logic
    $('#themeToggle').on('click', function() {
        $('body').toggleClass('light-theme dark-theme');
        if ($('body').hasClass('light-theme')) {
            localStorage.setItem('theme', 'light');
        } else {
            localStorage.setItem('theme', 'dark');
        }
    });

    // Load theme preference on page load
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        $('body').removeClass('light-theme dark-theme').addClass(savedTheme + '-theme');
    } else {
        $('body').addClass('dark-theme');
    }
}
