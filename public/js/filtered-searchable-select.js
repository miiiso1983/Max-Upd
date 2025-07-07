/**
 * Filtered Searchable Select - Advanced JavaScript
 * MaxCon ERP SaaS System
 */

(function($) {
    'use strict';

    // Global configuration
    const FilteredSearchableSelect = {
        defaults: {
            placeholder: 'اختر من القائمة...',
            searchPlaceholder: 'ابحث أو اكتب للتصفية...',
            allowClear: true,
            allowCreate: false,
            showSearch: true,
            minimumInputLength: 0,
            maxResults: 50,
            ajaxDelay: 250,
            cacheResults: true,
            dir: 'rtl',
            language: {
                noResults: function() { return "لا توجد نتائج مطابقة"; },
                searching: function() { return "جاري البحث..."; },
                loadingMore: function() { return "جاري تحميل المزيد..."; },
                inputTooShort: function(args) {
                    return "يرجى إدخال " + (args.minimum - args.input.length) + " أحرف أو أكثر";
                },
                inputTooLong: function(args) {
                    return "يرجى حذف " + (args.input.length - args.maximum) + " أحرف";
                },
                maximumSelected: function(args) {
                    return "يمكنك اختيار " + args.maximum + " عناصر فقط";
                }
            }
        },

        // Initialize all filtered searchable selects
        init: function(container = document) {
            const self = this;
            $(container).find('.filtered-searchable-select').each(function() {
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    self.initializeSelect($(this));
                }
            });
        },

        // Initialize a single select element
        initializeSelect: function($select) {
            const self = this;
            const $wrapper = $select.closest('.filtered-select-wrapper');
            const config = this.buildConfig($select);
            
            // Store original options for filtering
            const originalOptions = this.storeOriginalOptions($select);
            
            // Initialize Select2
            $select.select2(config);
            
            // Setup additional functionality
            this.setupFilters($wrapper, $select, originalOptions);
            this.setupSearchEnhancements($wrapper, $select);
            this.setupEventHandlers($wrapper, $select);
            
            // Update results counter for static options
            if (!$select.data('ajax-url')) {
                this.updateResultsCounter($wrapper, originalOptions.length);
            }
        },

        // Build Select2 configuration
        buildConfig: function($select) {
            const $wrapper = $select.closest('.filtered-select-wrapper');
            const isMultiple = $select.prop('multiple');
            const config = $.extend({}, this.defaults, {
                placeholder: $select.data('placeholder') || this.defaults.placeholder,
                allowClear: $select.data('allow-clear') !== false && !isMultiple,
                width: '100%',
                dropdownParent: $wrapper,
                escapeMarkup: function(markup) { return markup; },
                templateResult: this.templateResult,
                templateSelection: this.templateSelection
            });

            // Add AJAX configuration if URL provided
            const ajaxUrl = $select.data('ajax-url');
            if (ajaxUrl) {
                config.ajax = this.buildAjaxConfig($select, $wrapper);
            }

            // Add tag creation if allowed
            if ($select.data('allow-create') === true) {
                config.tags = true;
                config.createTag = this.createTag;
                config.templateResult = this.templateResultWithNewTag;
            }

            return config;
        },

        // Build AJAX configuration
        buildAjaxConfig: function($select, $wrapper) {
            const self = this;
            const ajaxUrl = $select.data('ajax-url');
            const ajaxDelay = parseInt($select.data('ajax-delay')) || this.defaults.ajaxDelay;
            const cacheResults = $select.data('cache-results') !== false;
            const maxResults = parseInt($select.data('max-results')) || this.defaults.maxResults;

            return {
                url: ajaxUrl,
                dataType: 'json',
                delay: ajaxDelay,
                cache: cacheResults,
                data: function(params) {
                    return self.buildAjaxData(params, $wrapper, maxResults);
                },
                processResults: function(data, params) {
                    return self.processAjaxResults(data, params, $wrapper);
                },
                transport: function(params, success, failure) {
                    return self.ajaxTransport(params, success, failure, $wrapper);
                }
            };
        },

        // Build AJAX request data
        buildAjaxData: function(params, $wrapper, maxResults) {
            const filters = {};
            $wrapper.find('.filter-select').each(function() {
                const filterKey = $(this).data('filter');
                const filterValue = $(this).val();
                if (filterValue) {
                    filters[filterKey] = filterValue;
                }
            });

            return {
                q: params.term || '',
                page: params.page || 1,
                per_page: maxResults,
                filters: filters
            };
        },

        // Process AJAX results
        processAjaxResults: function(data, params, $wrapper) {
            params.page = params.page || 1;
            this.updateResultsCounter($wrapper, data.total || data.results.length);

            return {
                results: data.results || data,
                pagination: {
                    more: data.pagination ? data.pagination.more : false
                }
            };
        },

        // AJAX transport with loading indicator
        ajaxTransport: function(params, success, failure, $wrapper) {
            this.showLoading($wrapper, true);
            
            const request = $.ajax(params);
            
            request.then((data) => {
                this.showLoading($wrapper, false);
                success(data);
            });
            
            request.fail(() => {
                this.showLoading($wrapper, false);
                failure();
            });
            
            return request;
        },

        // Store original options for filtering
        storeOriginalOptions: function($select) {
            const originalOptions = [];
            $select.find('option').each(function() {
                if ($(this).val()) {
                    originalOptions.push({
                        id: $(this).val(),
                        text: $(this).text(),
                        element: this,
                        data: $(this).data()
                    });
                }
            });
            return originalOptions;
        },

        // Setup filter functionality
        setupFilters: function($wrapper, $select, originalOptions) {
            const self = this;
            const $filterSelects = $wrapper.find('.filter-select');
            const $clearFilters = $wrapper.find('.clear-filters');
            
            // Filter change handler
            $filterSelects.on('change', function() {
                self.applyFilters($wrapper, $select, originalOptions);
            });
            
            // Clear filters handler
            $clearFilters.on('click', function() {
                $filterSelects.val('').trigger('change');
            });
        },

        // Apply filters to options
        applyFilters: function($wrapper, $select, originalOptions) {
            const activeFilters = {};
            
            // Get active filters
            $wrapper.find('.filter-select').each(function() {
                const filterKey = $(this).data('filter');
                const filterValue = $(this).val();
                if (filterValue) {
                    activeFilters[filterKey] = filterValue;
                }
            });
            
            // If AJAX, trigger refresh
            if ($select.data('ajax-url')) {
                $select.select2('destroy');
                this.initializeSelect($select);
                return;
            }
            
            // Filter static options
            let filteredOptions = originalOptions;
            
            if (Object.keys(activeFilters).length > 0) {
                filteredOptions = originalOptions.filter(option => {
                    return Object.keys(activeFilters).every(filterKey => {
                        const filterValue = activeFilters[filterKey];
                        const optionValue = option.data[filterKey] || $(option.element).data(filterKey);
                        return !filterValue || optionValue == filterValue;
                    });
                });
            }
            
            // Update select options
            this.updateSelectOptions($select, filteredOptions);
            this.updateResultsCounter($wrapper, filteredOptions.length);
        },

        // Update select options
        updateSelectOptions: function($select, options) {
            $select.empty();
            
            if ($select.data('allow-clear') !== false && !$select.prop('multiple')) {
                $select.append('<option value="">' + $select.data('placeholder') + '</option>');
            }
            
            options.forEach(option => {
                $select.append($(option.element).clone());
            });
            
            $select.trigger('change');
        },

        // Setup search enhancements
        setupSearchEnhancements: function($wrapper, $select) {
            const self = this;
            
            // Focus search field on open
            $select.on('select2:open', function() {
                setTimeout(function() {
                    $wrapper.find('.select2-search__field').focus();
                }, 100);
            });
            
            // Highlight search results
            $select.on('select2:results', function() {
                const searchTerm = $wrapper.find('.select2-search__field').val();
                if (searchTerm) {
                    self.highlightSearchResults($wrapper, searchTerm);
                }
            });
        },

        // Setup event handlers
        setupEventHandlers: function($wrapper, $select) {
            // Custom events
            $select.on('select2:select', function(e) {
                $wrapper.trigger('filtered-select:select', [e.params.data]);
            });

            $select.on('select2:unselect', function(e) {
                $wrapper.trigger('filtered-select:unselect', [e.params.data]);
            });

            $select.on('select2:clear', function() {
                $wrapper.trigger('filtered-select:clear');
            });
        },

        // Template functions
        templateResult: function(data) {
            if (data.loading) return data.text;
            
            const $result = $('<div class="select2-result-item"></div>');
            $result.text(data.text);
            
            if (data.element && data.element.dataset) {
                Object.keys(data.element.dataset).forEach(key => {
                    $result.attr('data-' + key, data.element.dataset[key]);
                });
            }
            
            return $result;
        },

        templateResultWithNewTag: function(data) {
            if (data.loading) return data.text;
            
            const $result = $('<div></div>');
            $result.text(data.text);
            
            if (data.newTag) {
                $result.addClass('select2-result-new-tag');
                $result.prepend('<i class="fas fa-plus-circle mr-2 text-green-500"></i>');
            }
            
            return $result;
        },

        templateSelection: function(data) {
            return data.text;
        },

        createTag: function(params) {
            const term = $.trim(params.term);
            
            if (term === '') return null;
            
            return {
                id: term,
                text: term + ' (جديد)',
                newTag: true
            };
        },

        // Utility functions
        highlightSearchResults: function($wrapper, searchTerm) {
            $wrapper.find('.select2-results__option').each(function() {
                const $option = $(this);
                const text = $option.text();
                
                if (text && searchTerm) {
                    const regex = new RegExp('(' + this.escapeRegex(searchTerm) + ')', 'gi');
                    const highlightedText = text.replace(regex, '<mark class="bg-yellow-200 text-yellow-800 px-1 rounded">$1</mark>');
                    $option.html(highlightedText);
                }
            });
        },

        escapeRegex: function(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        },

        showLoading: function($wrapper, show) {
            const $loading = $wrapper.find('.loading-indicator');
            if (show) {
                $loading.removeClass('hidden');
            } else {
                $loading.addClass('hidden');
            }
        },

        updateResultsCounter: function($wrapper, count) {
            const $counter = $wrapper.find('.results-counter');
            const $text = $counter.find('.results-text');
            
            if (count !== undefined) {
                $text.text(`${count} نتيجة متاحة`);
                $counter.removeClass('hidden');
            } else {
                $counter.addClass('hidden');
            }
        },

        // Public API methods
        reinitialize: function(container) {
            this.init(container);
        },

        refresh: function(selector) {
            const $select = $(selector);
            const $wrapper = $select.closest('.filtered-select-wrapper');
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            this.initializeSelect($select);
        },

        updateOptions: function(selector, options) {
            const $select = $(selector);
            const $wrapper = $select.closest('.filtered-select-wrapper');
            
            $select.empty();
            
            if ($select.data('allow-clear') !== false) {
                $select.append('<option value="">' + ($select.data('placeholder') || 'اختر من القائمة...') + '</option>');
            }
            
            $.each(options, function(value, text) {
                $select.append('<option value="' + value + '">' + text + '</option>');
            });
            
            $select.trigger('change');
            this.updateResultsCounter($wrapper, Object.keys(options).length);
        },

        setFilters: function(selector, filters) {
            const $select = $(selector);
            const $wrapper = $select.closest('.filtered-select-wrapper');
            
            Object.keys(filters).forEach(filterKey => {
                $wrapper.find('.filter-select[data-filter="' + filterKey + '"]').val(filters[filterKey]);
            });
            
            $wrapper.find('.filter-select').first().trigger('change');
        },

        clearFilters: function(selector) {
            const $select = $(selector);
            const $wrapper = $select.closest('.filtered-select-wrapper');
            $wrapper.find('.clear-filters').trigger('click');
        },

        getValue: function(selector) {
            return $(selector).val();
        },

        setValue: function(selector, value) {
            $(selector).val(value).trigger('change');
        },

        clear: function(selector) {
            $(selector).val(null).trigger('change');
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        if (typeof $.fn.select2 !== 'undefined') {
            FilteredSearchableSelect.init();
            
            // Re-initialize when new content is added
            $(document).on('DOMNodeInserted', function(e) {
                if ($(e.target).find('.filtered-searchable-select').length > 0) {
                    setTimeout(function() {
                        FilteredSearchableSelect.init(e.target);
                    }, 100);
                }
            });
        }
    });

    // Expose to global scope
    window.FilteredSearchableSelect = FilteredSearchableSelect;

})(jQuery);
