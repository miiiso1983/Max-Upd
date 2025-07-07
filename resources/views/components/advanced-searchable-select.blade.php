@props([
    'name' => '',
    'id' => '',
    'placeholder' => 'اختر من القائمة...',
    'searchPlaceholder' => 'ابحث هنا...',
    'required' => false,
    'multiple' => false,
    'options' => [],
    'selected' => null,
    'allowClear' => true,
    'class' => '',
    'data' => [],
    'showSearch' => true,
    'minimumInputLength' => 0
])

@php
    $selectId = $id ?: $name . '_' . uniqid();
    $classes = 'advanced-searchable-select w-full ' . $class;
@endphp

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
        data-show-search="{{ $showSearch ? 'true' : 'false' }}"
        data-minimum-input-length="{{ $minimumInputLength }}"
        @foreach($data as $key => $value)
            data-{{ $key }}="{{ $value }}"
        @endforeach
    >
        @if(!$multiple && $allowClear)
            <option value="">{{ $placeholder }}</option>
        @endif
        
        @if(is_array($options) && count($options) > 0)
            @foreach($options as $value => $label)
                @if(is_array($label))
                    <option value="{{ $value }}" 
                            @if($selected == $value || (is_array($selected) && in_array($value, $selected))) selected @endif
                            @foreach($label['data'] ?? [] as $dataKey => $dataValue)
                                data-{{ $dataKey }}="{{ $dataValue }}"
                            @endforeach>
                        {{ $label['text'] ?? $label['label'] ?? $value }}
                    </option>
                @else
                    <option value="{{ $value }}" 
                            @if($selected == $value || (is_array($selected) && in_array($value, $selected))) selected @endif>
                        {{ $label }}
                    </option>
                @endif
            @endforeach
        @else
            {{ $slot }}
        @endif
    </select>
</div>

@push('styles')
@once
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
/* Advanced Searchable Select Styling */
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

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 32px;
    padding-left: 0;
    padding-right: 0;
    color: #374151;
    font-weight: 500;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 44px;
    right: 12px;
    top: 2px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow b {
    border-color: #6b7280 transparent transparent transparent;
    border-width: 6px 6px 0 6px;
}

/* Dropdown Styling */
.select2-dropdown {
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    background: white;
    overflow: hidden;
}

/* Search Box Styling */
.select2-search--dropdown {
    padding: 12px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid #e2e8f0;
}

.select2-search--dropdown .select2-search__field {
    border: 2px solid #cbd5e1;
    border-radius: 8px;
    padding: 12px 16px;
    font-size: 14px;
    direction: rtl;
    background: white;
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    width: 100%;
}

.select2-search--dropdown .select2-search__field:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    outline: none;
}

.select2-search--dropdown .select2-search__field::placeholder {
    color: #9ca3af;
    font-style: italic;
}

/* Results Styling */
.select2-results {
    max-height: 300px;
    overflow-y: auto;
}

.select2-results__options {
    padding: 8px 0;
}

.select2-container--default .select2-results__option {
    padding: 12px 16px;
    color: #374151;
    font-weight: 400;
    transition: all 0.2s ease;
    border-bottom: 1px solid #f3f4f6;
}

.select2-container--default .select2-results__option:last-child {
    border-bottom: none;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    transform: translateX(-2px);
    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
}

.select2-container--default .select2-results__option[aria-selected=true] {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    color: #1d4ed8;
    font-weight: 600;
    border-left: 4px solid #3b82f6;
}

/* No Results Message */
.select2-results__message {
    padding: 20px;
    text-align: center;
    color: #6b7280;
    font-style: italic;
    background: #f9fafb;
}

/* Loading Message */
.select2-results__option--loading {
    text-align: center;
    color: #3b82f6;
    font-weight: 500;
}

/* Focus States */
.select2-container--default.select2-container--focus .select2-selection--single,
.select2-container--default.select2-container--focus .select2-selection--multiple {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    background: white;
}

.select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
    border-color: transparent transparent #6b7280 transparent;
    border-width: 0 6px 6px 6px;
}

/* Multiple Selection Tags */
.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border: none;
    border-radius: 8px;
    color: white;
    padding: 6px 12px;
    margin: 4px;
    font-weight: 500;
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: white;
    margin-left: 8px;
    font-weight: bold;
    transition: all 0.2s ease;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #fecaca;
    transform: scale(1.2);
}

/* RTL Support */
.select2-container[dir="rtl"] .select2-selection--single .select2-selection__rendered {
    padding-right: 0;
    padding-left: 20px;
}

.select2-container[dir="rtl"] .select2-selection--single .select2-selection__arrow {
    left: 12px;
    right: auto;
}

/* Disabled State */
.select2-container--default .select2-selection--single.select2-selection--disabled,
.select2-container--default .select2-selection--multiple.select2-selection--disabled {
    background: #f3f4f6;
    color: #9ca3af;
    cursor: not-allowed;
    border-color: #d1d5db;
}

/* Container Width */
.select2-container {
    width: 100% !important;
}

/* Custom Scrollbar for Results */
.select2-results::-webkit-scrollbar {
    width: 6px;
}

.select2-results::-webkit-scrollbar-track {
    background: #f1f5f9;
}

.select2-results::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.select2-results::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Animation for dropdown */
.select2-dropdown {
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
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
// Advanced Searchable Select initialization
function initializeAdvancedSearchableSelects(container = document) {
    $(container).find('.advanced-searchable-select').each(function() {
        if (!$(this).hasClass('select2-hidden-accessible')) {
            const $select = $(this);
            const isMultiple = $select.prop('multiple');
            const placeholder = $select.data('placeholder') || 'اختر من القائمة...';
            const searchPlaceholder = $select.data('search-placeholder') || 'ابحث هنا...';
            const allowClear = $select.data('allow-clear') !== false && !isMultiple;
            const showSearch = $select.data('show-search') !== false;
            const minimumInputLength = parseInt($select.data('minimum-input-length')) || 0;
            
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
                dropdownAutoWidth: true,
                minimumInputLength: minimumInputLength
            };
            
            // Add search functionality
            if (showSearch) {
                config.templateResult = function(option) {
                    if (!option.id) {
                        return option.text;
                    }
                    
                    // Create custom option display
                    const $option = $(
                        '<div class="select2-result-option">' +
                            '<div class="option-text">' + option.text + '</div>' +
                        '</div>'
                    );
                    
                    return $option;
                };
                
                config.templateSelection = function(option) {
                    return option.text || option.id;
                };
            }
            
            // Initialize Select2
            $select.select2(config);
            
            // Custom search placeholder
            $select.on('select2:open', function() {
                setTimeout(function() {
                    $('.select2-search--dropdown .select2-search__field').attr('placeholder', searchPlaceholder);
                }, 1);
            });
            
            // Add custom class to container
            $select.next('.select2-container').addClass('select2-advanced');
        }
    });
}

// Initialize on document ready
$(document).ready(function() {
    if (typeof $.fn.select2 !== 'undefined') {
        initializeAdvancedSearchableSelects();
        
        // Re-initialize when new content is added
        $(document).on('DOMNodeInserted', function(e) {
            if ($(e.target).find('.advanced-searchable-select').length > 0) {
                setTimeout(function() {
                    initializeAdvancedSearchableSelects(e.target);
                }, 100);
            }
        });
    }
});

// Global utility object
window.AdvancedSearchableSelect = {
    reinitialize: function(container) {
        initializeAdvancedSearchableSelects(container);
    },
    
    refresh: function(selector) {
        const $select = $(selector);
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }
        initializeAdvancedSearchableSelects($select.parent());
    },
    
    updateOptions: function(selector, options) {
        const $select = $(selector);
        $select.empty();
        
        if ($select.data('allow-clear') !== false) {
            $select.append('<option value="">' + ($select.data('placeholder') || 'اختر من القائمة...') + '</option>');
        }
        
        $.each(options, function(value, text) {
            $select.append('<option value="' + value + '">' + text + '</option>');
        });
        
        $select.trigger('change');
    }
};
</script>
@endonce
@endpush
