/**
 * Searchable Select - Global Select2 initialization
 * MaxCon ERP SaaS System
 */

// Global Select2 configuration
const select2Config = {
    placeholder: 'اختر من القائمة...',
    allowClear: true,
    dir: 'rtl',
    language: {
        noResults: function() {
            return "لا توجد نتائج";
        },
        searching: function() {
            return "جاري البحث...";
        },
        loadingMore: function() {
            return "جاري تحميل المزيد...";
        },
        inputTooShort: function(args) {
            return "يرجى إدخال " + (args.minimum - args.input.length) + " أحرف أو أكثر";
        },
        inputTooLong: function(args) {
            return "يرجى حذف " + (args.input.length - args.maximum) + " أحرف";
        },
        maximumSelected: function(args) {
            return "يمكنك اختيار " + args.maximum + " عناصر فقط";
        }
    },
    width: '100%'
};

// Initialize Select2 for all searchable selects
function initializeSearchableSelects(container = document) {
    $(container).find('.searchable-select').each(function() {
        if (!$(this).hasClass('select2-hidden-accessible')) {
            const $select = $(this);
            const isMultiple = $select.prop('multiple');
            const customPlaceholder = $select.data('placeholder') || select2Config.placeholder;
            const allowClear = $select.data('allow-clear') !== false && !isMultiple;
            
            // Custom configuration for this select
            const config = {
                ...select2Config,
                placeholder: customPlaceholder,
                allowClear: allowClear
            };
            
            // Initialize Select2
            $select.select2(config);
            
            // Add custom styling class
            $select.next('.select2-container').addClass('select2-custom');
        }
    });
}

// Initialize on document ready
$(document).ready(function() {
    // Check if Select2 is loaded
    if (typeof $.fn.select2 !== 'undefined') {
        initializeSearchableSelects();
        
        // Re-initialize when new content is added dynamically
        $(document).on('DOMNodeInserted', function(e) {
            if ($(e.target).find('.searchable-select').length > 0) {
                setTimeout(function() {
                    initializeSearchableSelects(e.target);
                }, 100);
            }
        });
        
        // Handle AJAX content updates
        $(document).on('contentUpdated', function(e, container) {
            initializeSearchableSelects(container || document);
        });
        
    } else {
        console.warn('Select2 library not loaded. Searchable selects will not work.');
    }
});

// Utility functions
window.SearchableSelect = {
    // Reinitialize all selects
    reinitialize: function(container) {
        initializeSearchableSelects(container);
    },
    
    // Destroy and reinitialize a specific select
    refresh: function(selector) {
        const $select = $(selector);
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }
        $select.select2(select2Config);
    },
    
    // Get selected values from a select
    getValues: function(selector) {
        return $(selector).val();
    },
    
    // Set selected values for a select
    setValues: function(selector, values) {
        $(selector).val(values).trigger('change');
    },
    
    // Clear selection
    clear: function(selector) {
        $(selector).val(null).trigger('change');
    },
    
    // Add new option to select
    addOption: function(selector, value, text, selected = false) {
        const $select = $(selector);
        const newOption = new Option(text, value, selected, selected);
        $select.append(newOption);
        if (selected) {
            $select.trigger('change');
        }
    },
    
    // Remove option from select
    removeOption: function(selector, value) {
        $(selector).find('option[value="' + value + '"]').remove();
        $(selector).trigger('change');
    },
    
    // Update option text
    updateOption: function(selector, value, newText) {
        $(selector).find('option[value="' + value + '"]').text(newText);
        $(selector).trigger('change');
    },
    
    // Enable/disable select
    toggle: function(selector, enabled = true) {
        $(selector).prop('disabled', !enabled);
    },
    
    // Show/hide select
    visibility: function(selector, visible = true) {
        const $container = $(selector).next('.select2-container');
        if (visible) {
            $container.show();
        } else {
            $container.hide();
        }
    }
};

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = window.SearchableSelect;
}
