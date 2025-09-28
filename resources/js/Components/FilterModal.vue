<script setup>
import { ref, computed, watch } from 'vue';
import { initTooltips } from 'flowbite';

const emit = defineEmits(['close', 'apply-filters']);
const props = defineProps({
    show: Boolean,
    issueTypes: {
        type: Array,
        required: true,
        default: () => []
    },
    barangays: {
        type: Array,
        required: true,
        default: () => []
    },
    sitios: {
        type: Array,
        required: true,
        default: () => []
    }
});

// Filter state
const selectedIssueType = ref('');
const selectedPriorityLevel = ref(''); // Add this new variable
const selectedBarangay = ref('');
const selectedSitio = ref('');
const startDate = ref('');
const endDate = ref('');

// Computed property for filtered sitios based on selected barangay
const filteredSitios = computed(() => {
    if (!selectedBarangay.value) return props.sitios;
    return props.sitios.filter(sitio => sitio.barangay_id == selectedBarangay.value);
});

// Watch for barangay changes to reset sitio selection
watch(selectedBarangay, () => {
    selectedSitio.value = '';
});

// Reset filters
const resetFilters = () => {
    selectedIssueType.value = '';
    selectedPriorityLevel.value = ''; // Add this
    selectedBarangay.value = '';     
    selectedSitio.value = '';        
    startDate.value = '';
    endDate.value = '';
};

// Apply filters
const applyFilters = () => {
    const filters = {
        issueType: selectedIssueType.value,
        priorityLevel: selectedPriorityLevel.value, // Add this
        barangay: selectedBarangay.value,    
        sitio: selectedSitio.value,         
        startDate: startDate.value,
        endDate: endDate.value
    };
    
    console.log('Applying filters:', filters);
    emit('apply-filters', filters);
    emit('close');
};

// Validate date range
const isValidDateRange = computed(() => {
    if (!startDate.value || !endDate.value) return true;
    return new Date(startDate.value) <= new Date(endDate.value);
});

const setDateRange = (period) => {
    const today = new Date();
    
    switch (period) {
        case 'today':
            startDate.value = today.toISOString().split('T')[0];
            endDate.value = today.toISOString().split('T')[0];
            break;
        case 'week':
            const startOfWeek = new Date(today);
            startOfWeek.setDate(today.getDate() - today.getDay());
            startDate.value = startOfWeek.toISOString().split('T')[0];
            endDate.value = today.toISOString().split('T')[0];
            break;
        case 'month':
            const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
            startDate.value = startOfMonth.toISOString().split('T')[0];
            endDate.value = today.toISOString().split('T')[0];
            break;
        case 'year':
            const startOfYear = new Date(today.getFullYear(), 0, 1);
            startDate.value = startOfYear.toISOString().split('T')[0];
            endDate.value = today.toISOString().split('T')[0];
            break;
    }
};
</script>

<template>
    <div v-if="show" class="fixed z-[9999] inset-0 backdrop-blur-sm bg-white/20 flex justify-center items-center">
        <div class="w-[50%] rounded-lg shadow-lg bg-white">
            <!-- Header -->
            <div class="flex justify-between items-center p-5 border-b bg-blue-700 rounded-t-lg">
                <h3 class="font-[Poppins] font-semibold text-2xl text-white">Advanced Filter</h3>
                <button @click="$emit('close')" class="p-1 rounded">
                    <img :src="'/Images/SVG/x (white).svg'" alt="Close Icon" class="w-5 h-5">
                </button>
            </div>

            <div class="flex flex-col p-5 space-y-4 h-96 overflow-y-auto">
                <div class="flex gap-3">
                    <!-- Issue Type Filter -->
                    <div class="w-1/2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Issue Type
                        </label>
                        <select 
                            v-model="selectedIssueType"
                            class="w-full border border-gray-300 rounded-lg p-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                        >
                            <option value="">All Issue Types</option>
                            <option 
                                v-for="type in props.issueTypes"
                                :key="type.id"
                                :value="type.id"
                            >
                                {{ type.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Priority Level - FIXED -->
                    <div class="w-1/2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Priority Level
                        </label>
                        <select 
                            v-model="selectedPriorityLevel"
                            class="w-full border border-gray-300 rounded-lg p-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                        >
                            <option value="">All Priority Levels</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                </div>

                <!-- Rest of your existing template... -->
                <div class="flex gap-3">
                    <div class="flex flex-col w-1/2">
                        <!-- Location Filters -->
                        <div class="grid grid-rows-1 gap-3">
                            <!-- Barangay Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Barangay
                                </label>
                                <select 
                                    v-model="selectedBarangay"
                                    class="w-full border border-gray-300 rounded-lg p-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                                >
                                    <option value="">All Barangays</option>
                                    <option 
                                        v-for="barangay in props.barangays"
                                        :key="barangay.id"
                                        :value="barangay.id"
                                    >
                                        {{ barangay.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Sitio Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Sitio
                                </label>
                                <select 
                                    v-model="selectedSitio"
                                    :disabled="!selectedBarangay"
                                    class="w-full border border-gray-300 rounded-lg p-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none disabled:bg-gray-100 disabled:cursor-not-allowed"
                                >
                                    <option value="">All Sitios</option>
                                    <option 
                                        v-for="sitio in filteredSitios"
                                        :key="sitio.id"
                                        :value="sitio.id"
                                    >
                                        {{ sitio.name }}
                                    </option>
                                </select>
                                <p v-if="!selectedBarangay" class="text-xs text-gray-500 mt-1">
                                    Select a barangay first
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Date Range Filter -->
                    <div class="w-1/2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Date Range
                        </label>
                        <div class="grid grid-rows-1 gap-[10px]">
                            <input
                                v-model="startDate"
                                type="date"
                                class="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-3"
                                :max="endDate || undefined"
                            />
                            <p class="block text-sm font-medium text-gray-700 mb-2">To</p>
                            <input
                                v-model="endDate"
                                type="date"
                                class="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-3"
                                :min="startDate || undefined"
                            />
                        </div>
                        
                        <div v-if="!isValidDateRange" class="text-red-500 text-xs mt-1">
                            End date must be after start date
                        </div>
                    </div>
                </div>

                <!-- Quick Date Presets -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Quick Filters
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <button 
                            @click="setDateRange('today')"
                            class="p-3 text-xs border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Today
                        </button>
                        <button 
                            @click="setDateRange('week')"
                            class="p-3 text-xs border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            This Week
                        </button>
                        <button 
                            @click="setDateRange('month')"
                            class="p-3 text-xs border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            This Month
                        </button>
                        <button 
                            @click="setDateRange('year')"
                            class="p-3 text-xs border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            This Year
                        </button>
                    </div>
                </div>

                <!-- Active Filters Summary -->
                <div v-if="selectedIssueType || selectedPriorityLevel || selectedBarangay || selectedSitio || startDate || endDate" class="bg-blue-50 p-3 rounded-lg">
                    <h4 class="text-sm font-medium text-blue-800 mb-1">Active Filters:</h4>
                    <div class="text-xs text-blue-600 space-y-1">
                        <div v-if="selectedIssueType">
                            Issue Type: {{ props.issueTypes.find(t => t.id == selectedIssueType)?.name }}
                        </div>
                        <div v-if="selectedPriorityLevel">
                            Priority Level: {{ selectedPriorityLevel.charAt(0).toUpperCase() + selectedPriorityLevel.slice(1) }}
                        </div>
                        <div v-if="selectedBarangay">
                            Barangay: {{ props.barangays.find(b => b.id == selectedBarangay)?.name }}
                        </div>
                        <div v-if="selectedSitio">
                            Sitio: {{ filteredSitios.find(s => s.id == selectedSitio)?.name }}
                        </div>
                        <div v-if="startDate">
                            From: {{ new Date(startDate).toLocaleDateString() }}
                        </div>
                        <div v-if="endDate">
                            To: {{ new Date(endDate).toLocaleDateString() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer/Action Buttons -->
            <div class="flex justify-between p-5 border-t">
                <button 
                    @click="resetFilters" 
                    class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    Clear All
                </button>
                <div class="flex gap-3">
                    <button 
                        @click="$emit('close')" 
                        class="px-5 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="applyFilters" 
                        :disabled="!isValidDateRange"
                        :class="[
                            'px-5 py-2 rounded-lg transition-colors',
                            isValidDateRange 
                                ? 'bg-blue-500 text-white hover:bg-blue-600' 
                                : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                        ]"
                    >
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.3s ease;
}

.fade-slide-enter-from {
    opacity: 0;
    transform: translateY(-10px);
}

.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>