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
    'size' => 'md' // sm, md, lg
])

@php
    $selectId = $id ?: $name . '_' . uniqid();
    $sizeClasses = [
        'sm' => 'h-9 text-sm',
        'md' => 'h-11 text-sm',
        'lg' => 'h-12 text-base'
    ];
    $classes = 'modern-select w-full ' . ($sizeClasses[$size ?? 'md'] ?? $sizeClasses['md']) . ' ' . ($class ?? '');
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
                            @if($selected == $value) selected @endif
                            @foreach($label['data'] ?? [] as $dataKey => $dataValue)
                                data-{{ $dataKey }}="{{ $dataValue }}"
                            @endforeach>
                        {{ $label['text'] ?? $label['label'] ?? $value }}
                    </option>
                @else
                    <option value="{{ $value }}" @if($selected == $value) selected @endif>
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
/* Modern Select Styles */
.select2-container--default .select2-selection--single {
    height: 44px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 0 16px;
    background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.select2-container--default .select2-selection--single:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1), 0 4px 12px rgba(59, 130, 246, 0.15);
    background: #ffffff;
}

.select2-container--default .select2-selection--multiple {
    min-height: 44px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 4px 12px;
    background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.select2-container--default .select2-selection--multiple:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.select2-container--default.select2-container--focus .select2-selection--multiple {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1), 0 4px 12px rgba(59, 130, 246, 0.15);
    background: #ffffff;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 40px;
    padding-left: 0;
    padding-right: 0;
    color: #1f2937;
    font-weight: 500;
}

.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #9ca3af;
    font-weight: 400;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px;
    right: 12px;
    width: 20px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow b {
    border-color: #6b7280 transparent transparent transparent;
    border-style: solid;
    border-width: 6px 6px 0 6px;
    height: 0;
    left: 50%;
    margin-left: -6px;
    margin-top: -3px;
    position: absolute;
    top: 50%;
    width: 0;
}

.select2-dropdown {
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    background: #ffffff;
    margin-top: 4px;
}

.select2-search--dropdown .select2-search__field {
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 8px 12px;
    margin: 8px;
    width: calc(100% - 16px) !important;
    font-size: 14px;
    transition: all 0.3s ease;
}

.select2-search--dropdown .select2-search__field:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    outline: none;
}

.select2-results__options {
    max-height: 200px;
    padding: 4px;
}

.select2-results__option {
    padding: 12px 16px;
    border-radius: 8px;
    margin: 2px 0;
    transition: all 0.2s ease;
    font-weight: 500;
    color: #374151;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.select2-container--default .select2-results__option[aria-selected=true] {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    color: #1d4ed8;
    font-weight: 600;
    border-left: 4px solid #3b82f6;
}

.select2-container--default .select2-results__option--disabled {
    color: #9ca3af;
    background: #f9fafb;
}

.select2-selection__choice {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
    border: none !important;
    border-radius: 8px !important;
    color: white !important;
    padding: 4px 12px !important;
    margin: 2px !important;
    font-weight: 500 !important;
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3) !important;
}

.select2-selection__choice__remove {
    color: white !important;
    margin-left: 8px !important;
    font-weight: bold !important;
}

.select2-selection__choice__remove:hover {
    background: rgba(255, 255, 255, 0.2) !important;
    border-radius: 4px !important;
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

/* Size Variants */
.modern-select.h-9 + .select2-container .select2-selection--single {
    height: 36px;
}

.modern-select.h-9 + .select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 32px;
}

.modern-select.h-12 + .select2-container .select2-selection--single {
    height: 48px;
}

.modern-select.h-12 + .select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 44px;
}

/* Error State */
.modern-select.error + .select2-container .select2-selection--single,
.modern-select.error + .select2-container .select2-selection--multiple {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

/* Success State */
.modern-select.success + .select2-container .select2-selection--single,
.modern-select.success + .select2-container .select2-selection--multiple {
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

/* Loading State */
.select2-container--default .select2-results__option--loading {
    color: #6b7280;
    text-align: center;
    padding: 16px;
}

/* No Results */
.select2-results__message {
    color: #6b7280;
    text-align: center;
    padding: 16px;
    font-style: italic;
}
</style>
@endonce
@endpush

@push('scripts')
@once
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
// Modern Select initialization
function initializeModernSelects(container = document) {
    $(container).find('.modern-select').each(function() {
        if (!$(this).hasClass('select2-hidden-accessible')) {
            const $select = $(this);
            const isMultiple = $select.prop('multiple');
            const placeholder = $select.data('placeholder') || 'اختر من القائمة...';
            const searchPlaceholder = $select.data('search-placeholder') || 'ابحث هنا...';
            const allowClear = $select.data('allow-clear') !== false && !isMultiple;
            const showSearch = $select.data('show-search') !== false;
            const minimumInputLength = parseInt($select.data('minimum-input-length')) || 0;
            const hasIcon = $select.data('icon');
            
            const config = {
                placeholder: placeholder,
                allowClear: allowClear,
                dir: 'rtl',
                minimumInputLength: minimumInputLength,
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
            
            // Disable search for small option lists
            if (!showSearch || $select.find('option').length <= 5) {
                config.minimumResultsForSearch = Infinity;
            }
            
            // Custom search placeholder
            if (showSearch && searchPlaceholder) {
                config.language.inputTooShort = function() {
                    return searchPlaceholder;
                };
            }
            
            $select.select2(config);
            
            // Add custom styling based on state
            $select.on('select2:open', function() {
                $('.select2-search__field').attr('placeholder', searchPlaceholder);
            });
            
            // Handle icon positioning
            if (hasIcon) {
                $select.next('.select2-container').find('.select2-selection').css('padding-right', '40px');
            }
        }
    });
}

// Initialize on document ready
$(document).ready(function() {
    initializeModernSelects();
});

// Global utility object
window.ModernSelect = {
    reinitialize: function(container) {
        initializeModernSelects(container);
    },
    
    refresh: function(selector) {
        const $select = $(selector);
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }
        initializeModernSelects($select.parent());
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
