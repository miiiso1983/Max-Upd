@props([
    'name' => '',
    'value' => '',
    'placeholder' => 'اختر خيار',
    'searchPlaceholder' => 'ابحث...',
    'options' => [],
    'required' => false,
    'disabled' => false,
    'class' => ''
])

<div class="searchable-dropdown {{ $class }}"
     data-name="{{ $name }}"
     data-value="{{ $value }}"
     @if($required) data-required="true" @endif
     @if($disabled) data-disabled="true" @endif>

    <!-- Hidden input to submit the value -->
    <input type="hidden" name="{{ $name }}" value="{{ $value }}" class="dropdown-hidden-input">
    
    <div class="dropdown-input {{ $disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}" 
         onclick="{{ $disabled ? '' : 'toggleDropdown(this)' }}">
        <span class="selected-text placeholder">{{ $placeholder }}</span>
        <i class="fas fa-chevron-down dropdown-arrow"></i>
    </div>
    
    @if(!$disabled)
    <div class="dropdown-menu">
        <input type="text" 
               class="search-input" 
               placeholder="{{ $searchPlaceholder }}" 
               onkeyup="filterOptions(this)">
        <div class="dropdown-options">
            @foreach($options as $option)
                <div class="dropdown-option" 
                     data-value="{{ $option['value'] ?? '' }}" 
                     onclick="selectOption(this)">
                    {{ $option['text'] ?? $option['label'] ?? $option }}
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

@once
@push('styles')
<style>
.searchable-dropdown {
    position: relative;
    width: 100%;
}

.searchable-dropdown .dropdown-input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background-color: white;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    min-height: 42px;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.searchable-dropdown .dropdown-input:hover:not(.opacity-50) {
    border-color: #9ca3af;
}

.searchable-dropdown .dropdown-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.searchable-dropdown .dropdown-input .placeholder {
    color: #9ca3af;
}

.searchable-dropdown .dropdown-input .selected-text {
    color: #111827;
    flex: 1;
    text-align: right;
}

.searchable-dropdown .dropdown-arrow {
    transition: transform 0.2s;
    color: #6b7280;
    margin-left: 8px;
}

.searchable-dropdown.open .dropdown-arrow {
    transform: rotate(180deg);
}

.searchable-dropdown .dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    z-index: 50;
    max-height: 300px;
    overflow: hidden;
    display: none;
    margin-top: 4px;
}

.searchable-dropdown.open .dropdown-menu {
    display: block;
    animation: fadeIn 0.15s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.searchable-dropdown .search-input {
    width: 100%;
    padding: 12px;
    border: none;
    border-bottom: 1px solid #e5e7eb;
    outline: none;
    font-size: 14px;
    background: #f9fafb;
}

.searchable-dropdown .search-input:focus {
    border-bottom-color: #3b82f6;
    background: white;
}

.searchable-dropdown .dropdown-options {
    max-height: 200px;
    overflow-y: auto;
}

.searchable-dropdown .dropdown-option {
    padding: 12px;
    cursor: pointer;
    border-bottom: 1px solid #f3f4f6;
    transition: background-color 0.2s;
    text-align: right;
}

.searchable-dropdown .dropdown-option:hover {
    background-color: #f3f4f6;
}

.searchable-dropdown .dropdown-option.selected {
    background-color: #dbeafe;
    color: #1e40af;
    font-weight: 500;
}

.searchable-dropdown .dropdown-option:last-child {
    border-bottom: none;
}

.searchable-dropdown .no-options {
    padding: 16px;
    text-align: center;
    color: #6b7280;
    font-style: italic;
    background: #f9fafb;
}

/* RTL Support */
.searchable-dropdown .dropdown-input {
    direction: rtl;
}

.searchable-dropdown .search-input {
    direction: rtl;
    text-align: right;
}

.searchable-dropdown .dropdown-option {
    direction: rtl;
}

/* Focus states for accessibility */
.searchable-dropdown .dropdown-option:focus {
    outline: 2px solid #3b82f6;
    outline-offset: -2px;
    background-color: #dbeafe;
}

/* Disabled state */
.searchable-dropdown[data-disabled="true"] .dropdown-input {
    background-color: #f3f4f6;
    color: #9ca3af;
    cursor: not-allowed;
}

/* Required field indicator */
.searchable-dropdown[data-required="true"] .dropdown-input {
    border-color: #f59e0b;
}

.searchable-dropdown[data-required="true"] .dropdown-input:focus {
    border-color: #3b82f6;
}

/* Error state */
.searchable-dropdown.error .dropdown-input {
    border-color: #ef4444;
}

.searchable-dropdown.error .dropdown-input:focus {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

/* Success state */
.searchable-dropdown.success .dropdown-input {
    border-color: #10b981;
}

/* Small size variant */
.searchable-dropdown.size-sm .dropdown-input {
    padding: 6px 10px;
    min-height: 36px;
    font-size: 13px;
}

.searchable-dropdown.size-sm .search-input {
    padding: 8px 10px;
    font-size: 13px;
}

.searchable-dropdown.size-sm .dropdown-option {
    padding: 8px 10px;
    font-size: 13px;
}

/* Large size variant */
.searchable-dropdown.size-lg .dropdown-input {
    padding: 12px 16px;
    min-height: 48px;
    font-size: 16px;
}

.searchable-dropdown.size-lg .search-input {
    padding: 14px 16px;
    font-size: 15px;
}

.searchable-dropdown.size-lg .dropdown-option {
    padding: 14px 16px;
    font-size: 15px;
}
</style>
@endpush

@push('scripts')
<script>
// Searchable Dropdown Functions
if (typeof window.searchableDropdownInitialized === 'undefined') {
    window.searchableDropdownInitialized = true;

    function toggleDropdown(element) {
        const dropdown = element.closest('.searchable-dropdown');
        if (dropdown.getAttribute('data-disabled') === 'true') return;
        
        const isOpen = dropdown.classList.contains('open');
        
        // Close all other dropdowns
        document.querySelectorAll('.searchable-dropdown.open').forEach(dd => {
            dd.classList.remove('open');
        });
        
        // Toggle current dropdown
        if (!isOpen) {
            dropdown.classList.add('open');
            const searchInput = dropdown.querySelector('.search-input');
            setTimeout(() => searchInput?.focus(), 100);
        }
    }

    function selectOption(element) {
        const dropdown = element.closest('.searchable-dropdown');
        const selectedText = dropdown.querySelector('.selected-text');
        const value = element.getAttribute('data-value');
        const text = element.textContent.trim();
        
        // Update display
        selectedText.textContent = text;
        selectedText.classList.remove('placeholder');
        if (!value) {
            selectedText.classList.add('placeholder');
        }
        
        // Store value
        dropdown.setAttribute('data-value', value);

        // Update hidden input
        const hiddenInput = dropdown.querySelector('.dropdown-hidden-input');
        if (hiddenInput) {
            hiddenInput.value = value;
        }

        // Update selected state
        dropdown.querySelectorAll('.dropdown-option').forEach(opt => {
            opt.classList.remove('selected');
        });
        element.classList.add('selected');
        
        // Close dropdown
        dropdown.classList.remove('open');
        
        // Clear search
        const searchInput = dropdown.querySelector('.search-input');
        if (searchInput) {
            searchInput.value = '';
            filterOptions(searchInput);
        }
        
        // Trigger change event
        dropdown.dispatchEvent(new CustomEvent('change', {
            detail: { value: value, text: text }
        }));
    }

    function filterOptions(searchInput) {
        const searchTerm = searchInput.value.toLowerCase();
        const dropdown = searchInput.closest('.searchable-dropdown');
        const options = dropdown.querySelectorAll('.dropdown-option');
        let hasVisibleOptions = false;
        
        options.forEach(option => {
            const text = option.textContent.toLowerCase();
            const isVisible = text.includes(searchTerm);
            option.style.display = isVisible ? 'block' : 'none';
            if (isVisible) hasVisibleOptions = true;
        });
        
        // Show/hide no options message
        let noOptionsMsg = dropdown.querySelector('.no-options');
        if (!hasVisibleOptions && searchTerm) {
            if (!noOptionsMsg) {
                noOptionsMsg = document.createElement('div');
                noOptionsMsg.className = 'no-options';
                noOptionsMsg.textContent = 'لا توجد نتائج';
                dropdown.querySelector('.dropdown-options').appendChild(noOptionsMsg);
            }
            noOptionsMsg.style.display = 'block';
        } else if (noOptionsMsg) {
            noOptionsMsg.style.display = 'none';
        }
    }

    function getDropdownValue(name) {
        const dropdown = document.querySelector(`[data-name="${name}"]`);
        return dropdown ? dropdown.getAttribute('data-value') : '';
    }

    function setDropdownValue(name, value) {
        const dropdown = document.querySelector(`[data-name="${name}"]`);
        if (dropdown) {
            const option = dropdown.querySelector(`[data-value="${value}"]`);
            if (option) {
                selectOption(option);
            } else {
                // If option not found, just update the value
                dropdown.setAttribute('data-value', value);
                const hiddenInput = dropdown.querySelector('.dropdown-hidden-input');
                if (hiddenInput) {
                    hiddenInput.value = value;
                }
            }
        }
    }

    // Initialize dropdowns with selected values
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.searchable-dropdown').forEach(dropdown => {
            const value = dropdown.getAttribute('data-value');
            const hiddenInput = dropdown.querySelector('.dropdown-hidden-input');

            // Ensure hidden input has the correct value
            if (hiddenInput && value) {
                hiddenInput.value = value;
            }

            if (value) {
                const option = dropdown.querySelector(`[data-value="${value}"]`);
                if (option) {
                    selectOption(option);
                }
            }
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.searchable-dropdown')) {
            document.querySelectorAll('.searchable-dropdown.open').forEach(dd => {
                dd.classList.remove('open');
            });
        }
    });

    // Keyboard navigation
    document.addEventListener('keydown', function(event) {
        const openDropdown = document.querySelector('.searchable-dropdown.open');
        if (!openDropdown) return;

        const options = Array.from(openDropdown.querySelectorAll('.dropdown-option:not([style*="display: none"])'));
        const currentSelected = openDropdown.querySelector('.dropdown-option.selected');
        let currentIndex = options.indexOf(currentSelected);

        switch(event.key) {
            case 'ArrowDown':
                event.preventDefault();
                currentIndex = Math.min(currentIndex + 1, options.length - 1);
                if (options[currentIndex]) {
                    options.forEach(opt => opt.classList.remove('selected'));
                    options[currentIndex].classList.add('selected');
                    options[currentIndex].scrollIntoView({ block: 'nearest' });
                }
                break;
            case 'ArrowUp':
                event.preventDefault();
                currentIndex = Math.max(currentIndex - 1, 0);
                if (options[currentIndex]) {
                    options.forEach(opt => opt.classList.remove('selected'));
                    options[currentIndex].classList.add('selected');
                    options[currentIndex].scrollIntoView({ block: 'nearest' });
                }
                break;
            case 'Enter':
                event.preventDefault();
                if (options[currentIndex]) {
                    selectOption(options[currentIndex]);
                }
                break;
            case 'Escape':
                openDropdown.classList.remove('open');
                break;
        }
    });
}
</script>
@endpush
@endonce
