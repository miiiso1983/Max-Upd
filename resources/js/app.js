import './bootstrap';

// MaxCon ERP Application JavaScript

// Global utilities
window.MaxCon = {
    // Format numbers for Arabic locale
    formatNumber: function(number) {
        return new Intl.NumberFormat('ar-IQ').format(number);
    },

    // Format currency
    formatCurrency: function(amount) {
        return this.formatNumber(amount) + ' د.ع';
    },

    // Format dates
    formatDate: function(dateString) {
        return new Date(dateString).toLocaleDateString('ar-IQ');
    },

    // Format datetime
    formatDateTime: function(dateString) {
        return new Date(dateString).toLocaleString('ar-IQ');
    },

    // Show notification
    showNotification: function(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm ${this.getNotificationClass(type)}`;
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${this.getNotificationIcon(type)} ml-3"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="mr-2 text-lg">&times;</button>
            </div>
        `;
        document.body.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    },

    // Get notification CSS class
    getNotificationClass: function(type) {
        switch(type) {
            case 'success': return 'bg-green-100 text-green-800 border border-green-200';
            case 'error': return 'bg-red-100 text-red-800 border border-red-200';
            case 'warning': return 'bg-yellow-100 text-yellow-800 border border-yellow-200';
            default: return 'bg-blue-100 text-blue-800 border border-blue-200';
        }
    },

    // Get notification icon
    getNotificationIcon: function(type) {
        switch(type) {
            case 'success': return 'fa-check-circle';
            case 'error': return 'fa-exclamation-circle';
            case 'warning': return 'fa-exclamation-triangle';
            default: return 'fa-info-circle';
        }
    },

    // Show loading spinner
    showLoading: function(element) {
        const spinner = document.createElement('div');
        spinner.className = 'flex items-center justify-center p-4';
        spinner.innerHTML = '<i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>';
        element.innerHTML = '';
        element.appendChild(spinner);
    }
};
