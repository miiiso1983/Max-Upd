/**
 * MaxCon ERP - Mobile Interactions JavaScript
 * Handles responsive behavior and mobile-specific interactions
 */

class MobileInteractions {
    constructor() {
        this.init();
    }

    init() {
        this.setupMobileMenu();
        this.setupResponsiveTables();
        this.setupTouchGestures();
        this.setupResponsiveForms();
        this.setupResponsiveCards();
        this.handleOrientationChange();
    }

    // Mobile Menu Management
    setupMobileMenu() {
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');

        if (!mobileMenuToggle || !sidebar || !sidebarOverlay) return;

        // Toggle menu
        mobileMenuToggle.addEventListener('click', () => {
            this.toggleMobileMenu(sidebar, sidebarOverlay);
        });

        // Close on overlay click
        sidebarOverlay.addEventListener('click', () => {
            this.closeMobileMenu(sidebar, sidebarOverlay);
        });

        // Close on link click (mobile only)
        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    this.closeMobileMenu(sidebar, sidebarOverlay);
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                this.closeMobileMenu(sidebar, sidebarOverlay);
            }
        });
    }

    toggleMobileMenu(sidebar, overlay) {
        const isOpen = sidebar.classList.contains('open');
        
        if (isOpen) {
            this.closeMobileMenu(sidebar, overlay);
        } else {
            this.openMobileMenu(sidebar, overlay);
        }
    }

    openMobileMenu(sidebar, overlay) {
        sidebar.classList.add('open');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Add animation class
        sidebar.style.transform = 'translateX(0)';
    }

    closeMobileMenu(sidebar, overlay) {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
        
        // Reset transform
        sidebar.style.transform = '';
    }

    // Responsive Tables
    setupResponsiveTables() {
        const tables = document.querySelectorAll('table');
        
        tables.forEach(table => {
            if (!table.closest('.table-responsive')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            }

            // Add mobile-friendly headers
            this.addMobileHeaders(table);
        });
    }

    addMobileHeaders(table) {
        const headers = table.querySelectorAll('th');
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            cells.forEach((cell, index) => {
                if (headers[index]) {
                    cell.setAttribute('data-label', headers[index].textContent);
                }
            });
        });
    }

    // Touch Gestures
    setupTouchGestures() {
        let startX, startY, currentX, currentY;
        
        document.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        });

        document.addEventListener('touchmove', (e) => {
            if (!startX || !startY) return;
            
            currentX = e.touches[0].clientX;
            currentY = e.touches[0].clientY;
            
            const diffX = startX - currentX;
            const diffY = startY - currentY;
            
            // Swipe right to open menu (from right edge)
            if (startX > window.innerWidth - 50 && diffX < -50 && Math.abs(diffY) < 100) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                if (sidebar && overlay && window.innerWidth < 1024) {
                    this.openMobileMenu(sidebar, overlay);
                }
            }
            
            // Swipe left to close menu
            if (diffX > 50 && Math.abs(diffY) < 100) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                if (sidebar && sidebar.classList.contains('open')) {
                    this.closeMobileMenu(sidebar, overlay);
                }
            }
        });

        document.addEventListener('touchend', () => {
            startX = null;
            startY = null;
        });
    }

    // Responsive Forms
    setupResponsiveForms() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            // Add responsive classes if not present
            if (!form.classList.contains('form-responsive')) {
                form.classList.add('form-responsive');
            }

            // Handle form groups
            const formGroups = form.querySelectorAll('.form-group, .mb-3, .mb-4');
            formGroups.forEach(group => {
                if (!group.classList.contains('form-group-responsive')) {
                    group.classList.add('form-group-responsive');
                }
            });

            // Handle buttons
            const buttons = form.querySelectorAll('button, input[type="submit"]');
            buttons.forEach(button => {
                if (!button.classList.contains('btn-responsive')) {
                    button.classList.add('btn-responsive');
                }
            });
        });
    }

    // Responsive Cards
    setupResponsiveCards() {
        const cards = document.querySelectorAll('.card, .bg-white');
        
        cards.forEach(card => {
            if (!card.classList.contains('card-responsive')) {
                card.classList.add('card-responsive');
            }
        });

        // Setup card grids
        const cardContainers = document.querySelectorAll('.grid');
        cardContainers.forEach(container => {
            if (!container.classList.contains('card-grid') && 
                !container.classList.contains('stats-grid')) {
                container.classList.add('card-grid');
            }
        });
    }

    // Handle Orientation Change
    handleOrientationChange() {
        window.addEventListener('orientationchange', () => {
            setTimeout(() => {
                // Recalculate layouts
                this.recalculateLayouts();
                
                // Close mobile menu if open
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                if (sidebar && overlay) {
                    this.closeMobileMenu(sidebar, overlay);
                }
            }, 100);
        });
    }

    recalculateLayouts() {
        // Trigger reflow for responsive elements
        const responsiveElements = document.querySelectorAll(
            '.card-responsive, .form-responsive, .table-responsive'
        );
        
        responsiveElements.forEach(element => {
            element.style.display = 'none';
            element.offsetHeight; // Trigger reflow
            element.style.display = '';
        });
    }

    // Utility Methods
    static isMobile() {
        return window.innerWidth < 768;
    }

    static isTablet() {
        return window.innerWidth >= 768 && window.innerWidth < 1024;
    }

    static isDesktop() {
        return window.innerWidth >= 1024;
    }

    static addResponsiveClass(element, mobileClass, tabletClass, desktopClass) {
        element.classList.remove(mobileClass, tabletClass, desktopClass);
        
        if (this.isMobile()) {
            element.classList.add(mobileClass);
        } else if (this.isTablet()) {
            element.classList.add(tabletClass);
        } else {
            element.classList.add(desktopClass);
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new MobileInteractions();
});

// Export for use in other scripts
window.MobileInteractions = MobileInteractions;
