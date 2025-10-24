// Admin Sidebar Toggle Script
(function() {
    'use strict';

    // DOM Elements
    const body = document.body;
    const toggleBtn = document.querySelector('.btn-toggle-sidebar');
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const navLinks = document.querySelectorAll('.nav-link');

    // Toggle sidebar function
    function toggleSidebar() {
        body.classList.toggle('sidebar-open');
    }

    // Close sidebar function
    function closeSidebar() {
        body.classList.remove('sidebar-open');
    }

    // Toggle button click handler
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleSidebar();
        });
    }

    // Overlay click handler - close sidebar
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            e.preventDefault();
            closeSidebar();
        });
    }

    // Close sidebar when clicking nav links on mobile
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 992) {
                closeSidebar();
            }
        });
    });

    // Close sidebar on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && body.classList.contains('sidebar-open')) {
            closeSidebar();
        }
    });

    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            // Close sidebar on resize if switching to desktop
            if (window.innerWidth > 992) {
                closeSidebar();
            }
        }, 250);
    });

    // Prevent sidebar from closing when clicking inside it
    if (sidebar) {
        sidebar.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

})();