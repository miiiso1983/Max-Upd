@props([
    'name' => '',
    'id' => '',
    'placeholder' => 'اختر من القائمة...',
    'searchPlaceholder' => 'ابحث أو اكتب للتصفية...',
    'required' => false,
    'multiple' => false,
    'options' => [],
    'selected' => null,
    'allowClear' => true,
    'allowCreate' => false,
    'class' => '',
    'data' => [],
    'showSearch' => true,
    'minimumInputLength' => 0,
    'maxResults' => 50,
    'groupBy' => null,
    'sortBy' => null,
    'filterBy' => [],
    'showFilters' => false,
    'ajaxUrl' => null,
    'ajaxDelay' => 250,
    'cacheResults' => true
])

@php
    $selectId = $id ?: $name . '_' . uniqid();
    $classes = 'filtered-searchable-select w-full ' . $class;
    $wrapperId = $selectId . '_wrapper';
@endphp

<div class="filtered-select-wrapper" id="{{ $wrapperId }}">
    <!-- Filter Controls (if enabled) -->
    @if($showFilters && count($filterBy) > 0)
    <div class="filter-controls mb-3 p-3 bg-gray-50 rounded-lg border">
        <div class="flex flex-wrap gap-2 items-center">
            <span class="text-sm font-medium text-gray-700">تصفية حسب:</span>
            @foreach($filterBy as $filterKey => $filterConfig)
            <div class="filter-group">
                <label class="text-xs text-gray-600">{{ $filterConfig['label'] }}</label>
                <select class="filter-select text-sm border-gray-300 rounded px-2 py-1" 
                        data-filter="{{ $filterKey }}">
                    <option value="">الكل</option>
                    @foreach($filterConfig['options'] as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @endforeach
            <button type="button" class="clear-filters text-xs text-blue-600 hover:text-blue-800 mr-2">
                مسح التصفية
            </button>
        </div>
    </div>
    @endif

    <!-- Main Select Element -->
    <div class="relative">
        <select 
            name="{{ $name }}" 
            id="{{ $selectId }}"
            @if($required) required @endif
            @if($multiple) multiple @endif
            class="{{ $classes }}"
            data-placeholder="{{ $placeholder }}"
            data-search-placeholder="{{ $searchPlaceholder }}"
            data-allow-clear="{{ $allowClear ? 'true' : 'false' }}"
            data-allow-create="{{ $allowCreate ? 'true' : 'false' }}"
            data-show-search="{{ $showSearch ? 'true' : 'false' }}"
            data-minimum-input-length="{{ $minimumInputLength }}"
            data-max-results="{{ $maxResults }}"
            data-group-by="{{ $groupBy }}"
            data-sort-by="{{ $sortBy }}"
            data-ajax-url="{{ $ajaxUrl }}"
            data-ajax-delay="{{ $ajaxDelay }}"
            data-cache-results="{{ $cacheResults ? 'true' : 'false' }}"
            @foreach($data as $key => $value)
                data-{{ $key }}="{{ $value }}"
            @endforeach
        >
            @if(!$multiple && $allowClear)
                <option value="">{{ $placeholder }}</option>
            @endif
            
            @if($ajaxUrl)
                <!-- Options will be loaded via AJAX -->
            @elseif(is_array($options) && count($options) > 0)
                @if($groupBy && is_array($options) && isset($options[0][$groupBy]))
                    @php
                        $groupedOptions = collect($options)->groupBy($groupBy);
                    @endphp
                    @foreach($groupedOptions as $groupName => $groupOptions)
                        <optgroup label="{{ $groupName }}">
                            @foreach($groupOptions as $option)
                                @php
                                    $value = is_array($option) ? $option['value'] : $option;
                                    $label = is_array($option) ? $option['label'] : $option;
                                    $isSelected = $multiple ? 
                                        (is_array($selected) && in_array($value, $selected)) : 
                                        ($selected == $value);
                                @endphp
                                <option value="{{ $value }}" 
                                        @if($isSelected) selected @endif
                                        @if(is_array($option) && isset($option['data']))
                                            @foreach($option['data'] as $dataKey => $dataValue)
                                                data-{{ $dataKey }}="{{ $dataValue }}"
                                            @endforeach
                                        @endif>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                @else
                    @foreach($options as $value => $label)
                        @if(is_array($label))
                            @php
                                $isSelected = $multiple ? 
                                    (is_array($selected) && in_array($value, $selected)) : 
                                    ($selected == $value);
                            @endphp
                            <option value="{{ $value }}" 
                                    @if($isSelected) selected @endif
                                    @foreach($label['data'] ?? [] as $dataKey => $dataValue)
                                        data-{{ $dataKey }}="{{ $dataValue }}"
                                    @endforeach>
                                {{ $label['text'] ?? $label['label'] ?? $value }}
                            </option>
                        @else
                            @php
                                $isSelected = $multiple ? 
                                    (is_array($selected) && in_array($value, $selected)) : 
                                    ($selected == $value);
                            @endphp
                            <option value="{{ $value }}" @if($isSelected) selected @endif>
                                {{ $label }}
                            </option>
                        @endif
                    @endforeach
                @endif
            @else
                {{ $slot }}
            @endif
        </select>

        <!-- Loading indicator -->
        <div class="loading-indicator hidden absolute left-3 top-1/2 transform -translate-y-1/2">
            <i class="fas fa-spinner fa-spin text-gray-400"></i>
        </div>
    </div>

    <!-- Results counter -->
    <div class="results-counter text-xs text-gray-500 mt-1 hidden">
        <span class="results-text"></span>
    </div>
</div>

@push('styles')
@once
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
/* Filtered Searchable Select Styling */
.filtered-select-wrapper {
    position: relative;
}

.filter-controls {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid #e2e8f0;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.filter-select {
    min-width: 120px;
    font-size: 0.875rem;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    transition: all 0.2s ease;
}

.filter-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.clear-filters {
    padding: 4px 8px;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.clear-filters:hover {
    background-color: rgba(59, 130, 246, 0.1);
}

/* Enhanced Select2 Styling */
.select2-container--default .select2-selection--single {
    height: 48px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 0.75rem 1rem;
    background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.select2-container--default .select2-selection--multiple {
    min-height: 48px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 0.5rem 0.75rem;
    background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.select2-container--default.select2-container--focus .select2-selection--single,
.select2-container--default.select2-container--focus .select2-selection--multiple {
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1), 0 4px 6px rgba(0, 0, 0, 0.1);
}

.select2-container--default .select2-selection__rendered {
    color: #374151;
    line-height: 1.5;
    padding: 0;
}

.select2-container--default .select2-selection__placeholder {
    color: #9ca3af;
    font-weight: 400;
}

.select2-container--default .select2-selection__arrow {
    height: 46px;
    right: 12px;
}

.select2-container--default .select2-selection__arrow b {
    border-color: #6b7280 transparent transparent transparent;
    border-width: 6px 6px 0 6px;
    margin-left: -6px;
    margin-top: -3px;
}

/* Dropdown styling */
.select2-dropdown {
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    overflow: hidden;
}

.select2-search--dropdown {
    padding: 12px;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
}

.select2-search--dropdown .select2-search__field {
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 14px;
    background: white;
    transition: all 0.2s ease;
}

.select2-search--dropdown .select2-search__field:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    outline: none;
}

.select2-results__options {
    max-height: 300px;
    padding: 8px 0;
}

.select2-results__option {
    padding: 12px 16px;
    font-size: 14px;
    line-height: 1.5;
    transition: all 0.2s ease;
    border-bottom: 1px solid #f3f4f6;
}

.select2-results__option:last-child {
    border-bottom: none;
}

.select2-results__option--highlighted {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
}

.select2-results__option[aria-selected="true"] {
    background: #f0f9ff;
    color: #1e40af;
    font-weight: 500;
}

/* Group styling */
.select2-results__group {
    padding: 8px 16px;
    font-weight: 600;
    color: #374151;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Multiple selection styling */
.select2-selection__choice {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border: none;
    border-radius: 6px;
    color: white;
    padding: 4px 8px;
    margin: 2px;
    font-size: 12px;
    font-weight: 500;
}

.select2-selection__choice__remove {
    color: rgba(255, 255, 255, 0.8);
    margin-left: 6px;
    font-weight: bold;
}

.select2-selection__choice__remove:hover {
    color: white;
}

/* Loading state */
.loading-indicator {
    z-index: 10;
}

/* Results counter */
.results-counter {
    text-align: left;
    font-style: italic;
}

/* RTL Support */
.select2-container[dir="rtl"] .select2-selection__arrow {
    left: 12px;
    right: auto;
}

.select2-container[dir="rtl"] .select2-selection__placeholder {
    text-align: right;
}

/* Responsive design */
@media (max-width: 640px) {
    .filter-controls {
        padding: 12px;
    }
    
    .filter-controls .flex {
        flex-direction: column;
        gap: 8px;
        align-items: stretch;
    }
    
    .filter-group {
        flex-direction: row;
        align-items: center;
        gap: 8px;
    }
    
    .filter-select {
        min-width: auto;
        flex: 1;
    }
    
    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
        height: 44px;
        padding: 0.5rem 0.75rem;
    }
    
    .select2-container--default .select2-selection__arrow {
        height: 42px;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .filter-controls {
        background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        border-color: #374151;
    }
    
    .filter-select {
        background: #1f2937;
        border-color: #374151;
        color: #f9fafb;
    }
    
    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
        background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        border-color: #374151;
        color: #f9fafb;
    }
    
    .select2-dropdown {
        background: #1f2937;
        border-color: #374151;
    }
    
    .select2-search--dropdown {
        background: #111827;
        border-color: #374151;
    }
    
    .select2-search--dropdown .select2-search__field {
        background: #1f2937;
        border-color: #374151;
        color: #f9fafb;
    }
    
    .select2-results__option {
        color: #f9fafb;
        border-color: #374151;
    }
    
    .select2-results__group {
        background: #111827;
        color: #d1d5db;
        border-color: #374151;
    }
}
</style>
@endonce
@endpush

@push('scripts')
@once
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
// Filtered Searchable Select initialization
function initializeFilteredSearchableSelects(container = document) {
    $(container).find('.filtered-searchable-select').each(function() {
        if (!$(this).hasClass('select2-hidden-accessible')) {
            const $select = $(this);
            const $wrapper = $select.closest('.filtered-select-wrapper');
            const isMultiple = $select.prop('multiple');
            const placeholder = $select.data('placeholder') || 'اختر من القائمة...';
            const searchPlaceholder = $select.data('search-placeholder') || 'ابحث أو اكتب للتصفية...';
            const allowClear = $select.data('allow-clear') !== false && !isMultiple;
            const allowCreate = $select.data('allow-create') === true;
            const showSearch = $select.data('show-search') !== false;
            const minimumInputLength = parseInt($select.data('minimum-input-length')) || 0;
            const maxResults = parseInt($select.data('max-results')) || 50;
            const ajaxUrl = $select.data('ajax-url');
            const ajaxDelay = parseInt($select.data('ajax-delay')) || 250;
            const cacheResults = $select.data('cache-results') !== false;

            // Store original options for filtering
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

            // Select2 configuration
            const config = {
                placeholder: placeholder,
                allowClear: allowClear,
                dir: 'rtl',
                language: {
                    noResults: function() {
                        return "لا توجد نتائج مطابقة";
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
                width: '100%',
                dropdownParent: $wrapper,
                escapeMarkup: function(markup) {
                    return markup;
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    // Custom template with highlighting
                    const $result = $('<div class="select2-result-item"></div>');
                    $result.text(data.text);

                    // Add data attributes for filtering
                    if (data.element && data.element.dataset) {
                        Object.keys(data.element.dataset).forEach(key => {
                            $result.attr('data-' + key, data.element.dataset[key]);
                        });
                    }

                    return $result;
                },
                templateSelection: function(data) {
                    return data.text;
                }
            };

            // Add AJAX configuration if URL provided
            if (ajaxUrl) {
                config.ajax = {
                    url: ajaxUrl,
                    dataType: 'json',
                    delay: ajaxDelay,
                    cache: cacheResults,
                    data: function(params) {
                        // Get active filters
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
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        // Update results counter
                        updateResultsCounter($wrapper, data.total || data.results.length);

                        return {
                            results: data.results || data,
                            pagination: {
                                more: data.pagination ? data.pagination.more : false
                            }
                        };
                    },
                    transport: function(params, success, failure) {
                        showLoading($wrapper, true);

                        const request = $.ajax(params);

                        request.then(function(data) {
                            showLoading($wrapper, false);
                            success(data);
                        });

                        request.fail(function(jqXHR, textStatus, errorThrown) {
                            showLoading($wrapper, false);
                            failure();
                        });

                        return request;
                    }
                };
            }

            // Add tag creation if allowed
            if (allowCreate) {
                config.tags = true;
                config.createTag = function(params) {
                    const term = $.trim(params.term);

                    if (term === '') {
                        return null;
                    }

                    return {
                        id: term,
                        text: term + ' (جديد)',
                        newTag: true
                    };
                };

                config.templateResult = function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    const $result = $('<div></div>');
                    $result.text(data.text);

                    if (data.newTag) {
                        $result.addClass('select2-result-new-tag');
                        $result.prepend('<i class="fas fa-plus-circle mr-2 text-green-500"></i>');
                    }

                    return $result;
                };
            }

            // Initialize Select2
            $select.select2(config);

            // Setup filter functionality
            setupFilters($wrapper, $select, originalOptions);

            // Setup search enhancements
            setupSearchEnhancements($wrapper, $select);

            // Update results counter for static options
            if (!ajaxUrl) {
                updateResultsCounter($wrapper, originalOptions.length);
            }
        }
    });
}

// Setup filter functionality
function setupFilters($wrapper, $select, originalOptions) {
    const $filterSelects = $wrapper.find('.filter-select');
    const $clearFilters = $wrapper.find('.clear-filters');

    // Filter change handler
    $filterSelects.on('change', function() {
        applyFilters($wrapper, $select, originalOptions);
    });

    // Clear filters handler
    $clearFilters.on('click', function() {
        $filterSelects.val('').trigger('change');
    });
}

// Apply filters to options
function applyFilters($wrapper, $select, originalOptions) {
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
        initializeFilteredSearchableSelects($wrapper);
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
    $select.empty();

    if ($select.data('allow-clear') !== false && !$select.prop('multiple')) {
        $select.append('<option value="">' + $select.data('placeholder') + '</option>');
    }

    filteredOptions.forEach(option => {
        $select.append($(option.element).clone());
    });

    // Refresh Select2
    $select.trigger('change');

    // Update results counter
    updateResultsCounter($wrapper, filteredOptions.length);
}

// Setup search enhancements
function setupSearchEnhancements($wrapper, $select) {
    // Custom search behavior
    $select.on('select2:open', function() {
        // Focus search field
        setTimeout(function() {
            const $searchField = $wrapper.find('.select2-search__field');
            $searchField.focus();
        }, 100);
    });

    // Search result highlighting
    $select.on('select2:results', function() {
        const searchTerm = $wrapper.find('.select2-search__field').val();
        if (searchTerm) {
            highlightSearchResults($wrapper, searchTerm);
        }
    });
}

// Highlight search results
function highlightSearchResults($wrapper, searchTerm) {
    $wrapper.find('.select2-results__option').each(function() {
        const $option = $(this);
        const text = $option.text();

        if (text && searchTerm) {
            const regex = new RegExp('(' + escapeRegex(searchTerm) + ')', 'gi');
            const highlightedText = text.replace(regex, '<mark class="bg-yellow-200 text-yellow-800 px-1 rounded">$1</mark>');
            $option.html(highlightedText);
        }
    });
}

// Escape regex special characters
function escapeRegex(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

// Show/hide loading indicator
function showLoading($wrapper, show) {
    const $loading = $wrapper.find('.loading-indicator');
    if (show) {
        $loading.removeClass('hidden');
    } else {
        $loading.addClass('hidden');
    }
}

// Update results counter
function updateResultsCounter($wrapper, count) {
    const $counter = $wrapper.find('.results-counter');
    const $text = $counter.find('.results-text');

    if (count !== undefined) {
        $text.text(`${count} نتيجة متاحة`);
        $counter.removeClass('hidden');
    } else {
        $counter.addClass('hidden');
    }
}

// Initialize on document ready
$(document).ready(function() {
    if (typeof $.fn.select2 !== 'undefined') {
        initializeFilteredSearchableSelects();

        // Re-initialize when new content is added
        $(document).on('DOMNodeInserted', function(e) {
            if ($(e.target).find('.filtered-searchable-select').length > 0) {
                setTimeout(function() {
                    initializeFilteredSearchableSelects(e.target);
                }, 100);
            }
        });
    }
});

// Global utility object
window.FilteredSearchableSelect = {
    reinitialize: function(container) {
        initializeFilteredSearchableSelects(container);
    },

    refresh: function(selector) {
        const $select = $(selector);
        const $wrapper = $select.closest('.filtered-select-wrapper');
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }
        initializeFilteredSearchableSelects($wrapper);
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
        updateResultsCounter($wrapper, Object.keys(options).length);
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
    }
};
</script>
@endonce
@endpush
