<script setup>
import { ref, computed } from 'vue'

const vClickOutside = {
    beforeMount(el, binding) {
        el.clickOutsideEvent = function(event) {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value(event)
            }
        }
        document.addEventListener('click', el.clickOutsideEvent)
    },
    unmounted(el) {
        document.removeEventListener('click', el.clickOutsideEvent)
    }
}

const props = defineProps({
    options: {
        type: Array,
        default: () => []
    },
    modelValue: {
        type: Array,
        default: () => []
    },
    placeholder: {
        type: String,
        default: 'Select options'
    },
    label: {
        type: String,
        default: 'name'
    },
    trackBy: {
        type: String,
        default: 'id'
    }
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const searchQuery = ref('')

const filteredOptions = computed(() => {
    if (!searchQuery.value) return props.options
    return props.options.filter(option => 
        option[props.label].toLowerCase().includes(searchQuery.value.toLowerCase())
    )
})

const selectedOptions = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

const toggleOption = (option) => {
    const optionId = option[props.trackBy]
    const currentIndex = selectedOptions.value.indexOf(optionId)
    
    if (currentIndex === -1) {
        // Add option
        selectedOptions.value = [...selectedOptions.value, optionId]
    } else {
        // Remove option
        selectedOptions.value = selectedOptions.value.filter(id => id !== optionId)
    }
}

const isSelected = (option) => {
    return selectedOptions.value.includes(option[props.trackBy])
}

const removeOption = (optionId, event) => {
    event.stopPropagation()
    event.preventDefault()
    
    console.log('Removing option:', optionId) // Debug log
    
    // Create a new array without the removed option
    const newValue = selectedOptions.value.filter(id => id !== optionId)
    
    // Update the selectedOptions
    selectedOptions.value = newValue
    
    // Emit the update to parent component
    emit('update:modelValue', newValue)
    
    console.log('New selected options:', newValue) // Debug log
}

const getSelectedLabels = computed(() => {
    return selectedOptions.value.map(selectedId => {
        const option = props.options.find(opt => opt[props.trackBy] === selectedId)
        return option ? option[props.label] : ''
    }).filter(Boolean)
})

const toggleDropdown = () => {
    isOpen.value = !isOpen.value
    if (isOpen.value) {
        searchQuery.value = ''
    }
}

const closeDropdown = () => {
    isOpen.value = false
    searchQuery.value = ''
}
</script>

<template>
    <div class="relative w-full" v-click-outside="closeDropdown">
        <!-- Selected Items Display -->
        <div
            class="flex items-center justify-between px-3 py-2 bg-white border border-gray-300 rounded-lg cursor-pointer transition-all duration-200 min-h-12 hover:border-gray-400"
            :class="{ 'ring-1 ring-blue-500 border-blue-500': isOpen }"
            @click="toggleDropdown"
        >
        <div class="flex flex-wrap items-center flex-1 gap-1">
            <span
            v-if="selectedOptions.length === 0"
            class="text-gray-400 text-sm"
            >
            {{ placeholder }}
            </span>

            <div v-else class="flex flex-wrap items-center gap-1">
                <span
                    v-for="(label, index) in getSelectedLabels.slice(0, 2)"
                    :key="index"
                    class="inline-flex items-center gap-1 bg-blue-500 text-white px-1.5 py-0.5 rounded text-xs font-medium"
                >
                    {{ label }}
                    <span
                    class="cursor-pointer text-sm hover:bg-white/20 px-0.5 rounded"
                    @click="removeOption(selectedOptions[index], $event)"
                    >
                    ×
                    </span>
                </span>

                <span
                    v-if="selectedOptions.length > 2"
                    class="text-gray-500 text-xs font-medium"
                >
                    +{{ selectedOptions.length - 2 }} more
                </span>
            </div>
        </div>

            <svg
                class="text-gray-500 transition-transform duration-200 flex-shrink-0"
                :class="{ 'rotate-180': isOpen }"
                width="20"
                height="20"
                viewBox="0 0 20 20"
                fill="currentColor"
            >
                <path
                fill-rule="evenodd"
                d="M5.23 7.21a.75.75 0 011.06.02L10 11.292l3.71-4.06a.75.75 0 111.08 1.04l-4.25 4.65a.75.75 0 01-1.08 0l-4.25-4.65a.75.75 0 01.02-1.06z"
                clip-rule="evenodd"
                />
            </svg>
        </div>

        <!-- Dropdown Options -->
        <div
            v-show="isOpen"
            class="absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-lg mt-1 shadow-lg z-50 max-h-60 flex flex-col"
            >
            <!-- Search Input -->
            <div class="p-2 border-b border-gray-200">
                <input
                v-model="searchQuery"
                type="text"
                placeholder="Search..."
                class="w-full p-2 border border-gray-300 rounded-md text-sm outline-none focus:border-blue-500"
                @click.stop
                />
            </div>

            <!-- Options List -->
            <div class="flex-1 overflow-y-auto max-h-40">
                <div
                    v-for="option in filteredOptions"
                    :key="option[trackBy]"
                    class="flex items-center gap-3 px-3 py-2 cursor-pointer hover:bg-gray-100 transition"
                    :class="{ 'bg-blue-50': isSelected(option) }"
                    @click="toggleOption(option)"
                    >
                    <div
                        class="w-4 h-4 border-2 border-gray-300 rounded flex items-center justify-center transition"
                        :class="{ 'bg-blue-500 border-blue-500': isSelected(option) }"
                    >
                        <svg
                        v-if="isSelected(option)"
                        width="12"
                        height="12"
                        viewBox="0 0 12 12"
                        fill="white"
                        >
                        <path
                            d="M10.28 2.28L3.989 8.575 1.695 6.28A1 1 0 00.28 7.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 2.28z"
                        />
                        </svg>
                    </div>
                    <span class="text-sm text-gray-700">{{ option[label] }}</span>
                </div>

                <div
                    v-if="filteredOptions.length === 0"
                    class="p-3 text-center text-gray-500 text-sm"
                >
                    No options found
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.dark .multiselect-trigger {
    background: #2c2c2c;
    border-color: #4b5563;
    color: white;
}

.dark .multiselect-dropdown {
    background: #2c2c2c;
    border-color: #4b5563;
}

.dark .search-input {
    background: #374151;
    border-color: #4b5563;
    color: white;
}

.dark .search-input:focus {
    border-color: #3b82f6;
}

.dark .option-item:hover {
    background: #374151;
}

.dark .option-item.selected {
    background: #1e3a8a;
}

.dark .option-label {
    color: #e5e7eb;
}

.dark .no-options {
    color: #9ca3af;
}

.dark .checkbox {
    border-color: #6b7280;
}

.dark .placeholder {
    color: #9ca3af;
}
</style>
