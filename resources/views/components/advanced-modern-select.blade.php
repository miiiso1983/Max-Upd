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
    'minimumInputLength' => 0,
    'icon' => null,
    'size' => 'md', // sm, md, lg
    'tags' => false,
    'maximumSelectionLength' => null,
    'ajax' => null,
    'theme' => 'modern' // modern, minimal, gradient
])

@php
    $selectId = $id ?: $name . '_' . uniqid();
    $sizeClasses = [
        'sm' => 'h-9 text-sm',
        'md' => 'h-11 text-sm',
        'lg' => 'h-12 text-base'
    ];
    $classes = 'advanced-modern-select w-full ' . ($sizeClasses[$size ?? 'md'] ?? $sizeClasses['md']) . ' ' . ($class ?? '');
@endphp

<div class="relative">
    @if($icon)
    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none z-10">
        <i class="{{ $icon }} text-gray-400"></i>
    </div>
    @endif
    
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
        data-icon="{{ $icon }}"
        data-tags="{{ $tags ? 'true' : 'false' }}"
        data-maximum-selection-length="{{ $maximumSelectionLength }}"
        data-theme="{{ $theme }}"
        @if($ajax)
            data-ajax-url="{{ $ajax['url'] ?? '' }}"
            data-ajax-delay="{{ $ajax['delay'] ?? 250 }}"
            data-ajax-cache="{{ $ajax['cache'] ?? 'true' }}"
        @endif
        @foreach($data as $key => $value)
            data-{{ $key }}="{{ $value }}"
        @endforeach
    >
        @if(!$multiple && $allowClear && !$tags)
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
                    <option value="{{ $value }}" @if($selected == $value || (is_array($selected) && in_array($value, $selected))) selected @endif>
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
/* Advanced Modern Select Styles */
.select2-container--default .select2-selection--single {
    height: 44px;
    border: 2px solid #e5e7eb;
    border-radius: 16px;
    padding: 0 20px;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
    position: relative;
    overflow: hidden;
}

.select2-container--default .select2-selection--single::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(147, 51, 234, 0.05) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.select2-container--default .select2-selection--single:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15), 0 2px 4px rgba(59, 130, 246, 0.1);
    transform: translateY(-1px);
}

.select2-container--default .select2-selection--single:hover::before {
    opacity: 1;
}

.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1), 0 8px 25px rgba(59, 130, 246, 0.15);
    background: #ffffff;
    transform: translateY(-2px);
}

.select2-container--default .select2-selection--multiple {
    min-height: 44px;
    border: 2px solid #e5e7eb;
    border-radius: 16px;
    padding: 6px 16px;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
}

.select2-container--default .select2-selection--multiple:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15), 0 2px 4px rgba(59, 130, 246, 0.1);
    transform: translateY(-1px);
}

.select2-container--default.select2-container--focus .select2-selection--multiple {
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1), 0 8px 25px rgba(59, 130, 246, 0.15);
    background: #ffffff;
    transform: translateY(-2px);
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 40px;
    padding-left: 0;
    padding-right: 0;
    color: #1f2937;
    font-weight: 600;
    font-size: 14px;
}

.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #9ca3af;
    font-weight: 400;
    font-style: italic;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px;
    right: 16px;
    width: 24px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow b {
    border-color: #6b7280 transparent transparent transparent;
    border-style: solid;
    border-width: 8px 8px 0 8px;
    height: 0;
    left: 50%;
    margin-left: -8px;
    margin-top: -4px;
    position: absolute;
    top: 50%;
    width: 0;
    transition: all 0.3s ease;
}

.select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
    border-color: transparent transparent #3b82f6 transparent;
    border-width: 0 8px 8px 8px;
    margin-top: -8px;
}

.select2-dropdown {
    border: 2px solid #e5e7eb;
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(59, 130, 246, 0.05);
    background: #ffffff;
    margin-top: 8px;
    overflow: hidden;
    backdrop-filter: blur(10px);
}

.select2-search--dropdown {
    padding: 12px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid #e2e8f0;
}

.select2-search--dropdown .select2-search__field {
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 12px 16px;
    width: 100% !important;
    font-size: 14px;
    transition: all 0.3s ease;
    background: #ffffff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.select2-search--dropdown .select2-search__field:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1), 0 4px 12px rgba(59, 130, 246, 0.15);
    outline: none;
}

.select2-results__options {
    max-height: 240px;
    padding: 8px;
}

.select2-results__option {
    padding: 14px 18px;
    border-radius: 12px;
    margin: 3px 0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 500;
    color: #374151;
    position: relative;
    overflow: hidden;
}

.select2-results__option::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
    transition: left 0.5s ease;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    transform: translateX(6px) scale(1.02);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4), 0 4px 12px rgba(59, 130, 246, 0.3);
    border-left: 4px solid #ffffff;
}

.select2-container--default .select2-results__option--highlighted[aria-selected]::before {
    left: 100%;
}

.select2-container--default .select2-results__option[aria-selected=true] {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    color: #1d4ed8;
    font-weight: 700;
    border-left: 4px solid #3b82f6;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
}

.select2-container--default .select2-results__option--disabled {
    color: #9ca3af;
    background: #f9fafb;
    opacity: 0.6;
}

/* Multi-select Tags */
.select2-selection__choice {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
    border: none !important;
    border-radius: 12px !important;
    color: white !important;
    padding: 6px 14px !important;
    margin: 3px !important;
    font-weight: 600 !important;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4) !important;
    transition: all 0.3s ease !important;
    position: relative !important;
    overflow: hidden !important;
}

.select2-selection__choice:hover {
    transform: translateY(-2px) scale(1.05) !important;
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5) !important;
}

.select2-selection__choice__remove {
    color: white !important;
    margin-left: 8px !important;
    font-weight: bold !important;
    font-size: 16px !important;
    transition: all 0.3s ease !important;
}

.select2-selection__choice__remove:hover {
    background: rgba(255, 255, 255, 0.3) !important;
    border-radius: 6px !important;
    transform: scale(1.2) !important;
}

/* Gradient Theme */
.advanced-modern-select[data-theme="gradient"] + .select2-container .select2-selection--single {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}

.advanced-modern-select[data-theme="gradient"] + .select2-container .select2-selection--single .select2-selection__rendered {
    color: white;
}

.advanced-modern-select[data-theme="gradient"] + .select2-container .select2-selection--single .select2-selection__placeholder {
    color: rgba(255, 255, 255, 0.7);
}

/* Minimal Theme */
.advanced-modern-select[data-theme="minimal"] + .select2-container .select2-selection--single {
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: #ffffff;
    box-shadow: none;
}

.advanced-modern-select[data-theme="minimal"] + .select2-container .select2-selection--single:hover {
    border-color: #9ca3af;
    box-shadow: none;
    transform: none;
}

/* Loading Animation */
.select2-container--default .select2-results__option--loading {
    color: #6b7280;
    text-align: center;
    padding: 20px;
    position: relative;
}

.select2-container--default .select2-results__option--loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #e5e7eb;
    border-top: 2px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* No Results */
.select2-results__message {
    color: #6b7280;
    text-align: center;
    padding: 20px;
    font-style: italic;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 12px;
    margin: 8px;
}

/* RTL Support */
.select2-container[dir="rtl"] .select2-selection--single .select2-selection__rendered {
    padding-right: 0;
    padding-left: 24px;
}

.select2-container[dir="rtl"] .select2-selection--single .select2-selection__arrow {
    left: 16px;
    right: auto;
}

/* Size Variants */
.advanced-modern-select.h-9 + .select2-container .select2-selection--single {
    height: 36px;
}

.advanced-modern-select.h-9 + .select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 32px;
}

.advanced-modern-select.h-12 + .select2-container .select2-selection--single {
    height: 48px;
}

.advanced-modern-select.h-12 + .select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 44px;
}

/* Error State */
.advanced-modern-select.error + .select2-container .select2-selection--single,
.advanced-modern-select.error + .select2-container .select2-selection--multiple {
    border-color: #ef4444;
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
    background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
}

/* Success State */
.advanced-modern-select.success + .select2-container .select2-selection--single,
.advanced-modern-select.success + .select2-container .select2-selection--multiple {
    border-color: #10b981;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
    background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
}
</style>
@endonce
@endpush

@push('scripts')
@once
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
// Advanced Modern Select initialization
function initializeAdvancedModernSelects(container = document) {
    $(container).find('.advanced-modern-select').each(function() {
        if (!$(this).hasClass('select2-hidden-accessible')) {
            const $select = $(this);
            const isMultiple = $select.prop('multiple');
            const placeholder = $select.data('placeholder') || 'اختر من القائمة...';
            const searchPlaceholder = $select.data('search-placeholder') || 'ابحث هنا...';
            const allowClear = $select.data('allow-clear') !== false && !isMultiple;
            const showSearch = $select.data('show-search') !== false;
            const minimumInputLength = parseInt($select.data('minimum-input-length')) || 0;
            const hasIcon = $select.data('icon');
            const tags = $select.data('tags') === 'true';
            const maximumSelectionLength = $select.data('maximum-selection-length');
            const theme = $select.data('theme') || 'modern';
            const ajaxUrl = $select.data('ajax-url');

            const config = {
                placeholder: placeholder,
                allowClear: allowClear,
                dir: 'rtl',
                minimumInputLength: minimumInputLength,
                tags: tags,
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
                dropdownAutoWidth: true
            };

            // Maximum selection length for multiple selects
            if (isMultiple && maximumSelectionLength) {
                config.maximumSelectionLength = parseInt(maximumSelectionLength);
            }

            // Disable search for small option lists
            if (!showSearch || (!ajaxUrl && $select.find('option').length <= 5)) {
                config.minimumResultsForSearch = Infinity;
            }

            // AJAX configuration
            if (ajaxUrl) {
                config.ajax = {
                    url: ajaxUrl,
                    dataType: 'json',
                    delay: parseInt($select.data('ajax-delay')) || 250,
                    cache: $select.data('ajax-cache') !== 'false',
                    data: function(params) {
                        return {
                            q: params.term,
                            page: params.page || 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: data.items || data.results || data,
                            pagination: {
                                more: data.pagination ? data.pagination.more : false
                            }
                        };
                    }
                };
            }

            $select.select2(config);

            // Add custom styling based on state
            $select.on('select2:open', function() {
                $('.select2-search__field').attr('placeholder', searchPlaceholder);
            });

            // Handle icon positioning
            if (hasIcon) {
                $select.next('.select2-container').find('.select2-selection').css('padding-right', '48px');
            }
        }
    });
}

// Initialize on document ready
$(document).ready(function() {
    initializeAdvancedModernSelects();
});

// Global utility object
window.AdvancedModernSelect = {
    reinitialize: function(container) {
        initializeAdvancedModernSelects(container);
    },

    refresh: function(selector) {
        const $select = $(selector);
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }
        initializeAdvancedModernSelects($select.parent());
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
    },

    setValue: function(selector, value) {
        $(selector).val(value).trigger('change');
    },

    addState: function(selector, state) {
        $(selector).addClass(state);
    },

    removeState: function(selector, state) {
        $(selector).removeClass(state);
    }
};
</script>
@endonce
@endpush
