@props([
    'name' => '',
    'id' => '',
    'placeholder' => 'اختر من القائمة...',
    'required' => false,
    'multiple' => false,
    'options' => [],
    'selected' => null,
    'allowClear' => true,
    'class' => '',
    'data' => []
])

@php
    $selectId = $id ?: $name;
    $classes = 'searchable-select w-full ' . $class;
@endphp

<select 
    name="{{ $name }}" 
    id="{{ $selectId }}"
    @if($required) required @endif
    @if($multiple) multiple @endif
    class="{{ $classes }}"
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

@push('styles')
@once
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container--default .select2-selection--single {
    height: 42px;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    padding: 0.5rem 0.75rem;
    background-color: white;
}

.select2-container--default .select2-selection--multiple {
    min-height: 42px;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    padding: 0.25rem 0.5rem;
    background-color: white;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 26px;
    padding-left: 0;
    padding-right: 0;
    color: #374151;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px;
    right: 10px;
}

.select2-dropdown {
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #3b82f6;
    color: white;
}

.select2-container--default .select2-results__option[aria-selected=true] {
    background-color: #eff6ff;
    color: #1d4ed8;
}

.select2-container {
    width: 100% !important;
}

.select2-search--dropdown .select2-search__field {
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.5rem;
    direction: rtl;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #3b82f6;
    border: 1px solid #2563eb;
    border-radius: 0.375rem;
    color: white;
    padding: 0.25rem 0.5rem;
    margin: 0.125rem;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: white;
    margin-left: 0.25rem;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #fecaca;
}

/* RTL Support */
.select2-container[dir="rtl"] .select2-selection--single .select2-selection__rendered {
    padding-right: 0;
    padding-left: 20px;
}

.select2-container[dir="rtl"] .select2-selection--single .select2-selection__arrow {
    left: 10px;
    right: auto;
}

/* Focus states */
.select2-container--default.select2-container--focus .select2-selection--single,
.select2-container--default.select2-container--focus .select2-selection--multiple {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Disabled state */
.select2-container--default .select2-selection--single.select2-selection--disabled,
.select2-container--default .select2-selection--multiple.select2-selection--disabled {
    background-color: #f9fafb;
    color: #9ca3af;
    cursor: not-allowed;
}
</style>
@endonce
@endpush

@push('scripts')
@once
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
// Global Select2 initialization function
function initializeSearchableSelects(container = document) {
    $(container).find('.searchable-select').each(function() {
        if (!$(this).hasClass('select2-hidden-accessible')) {
            const $select = $(this);
            const isMultiple = $select.prop('multiple');
            const placeholder = $select.data('placeholder') || 'اختر من القائمة...';
            const allowClear = $select.data('allow-clear') !== false;
            
            $select.select2({
                placeholder: placeholder,
                allowClear: allowClear && !isMultiple,
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
            });
        }
    });
}

// Initialize on document ready
$(document).ready(function() {
    initializeSearchableSelects();
});

// Re-initialize when new content is added dynamically
$(document).on('DOMNodeInserted', function(e) {
    if ($(e.target).find('.searchable-select').length > 0) {
        setTimeout(function() {
            initializeSearchableSelects(e.target);
        }, 100);
    }
});
</script>
@endonce
@endpush
