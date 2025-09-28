<script setup>
import { ref } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    duplicates: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close', 'force-submit', 'view-duplicate']);

const showDetails = ref({});

const toggleDetails = (reportId) => {
    showDetails.value[reportId] = !showDetails.value[reportId];
};

const formatSimilarity = (similarity) => {
    return Math.round(similarity * 100);
};

const getSimilarityColor = (similarity) => {
    const percentage = similarity * 100;
    if (percentage >= 80) return 'text-red-600 bg-red-50';
    if (percentage >= 60) return 'text-orange-600 bg-orange-50';
    return 'text-yellow-600 bg-yellow-50';
};

const handleForceSubmit = () => {
    emit('force-submit');
    emit('close');
};

const handleViewDuplicate = (report) => {
    emit('view-duplicate', report);
};
</script>

<template>
    <div 
        v-if="show" 
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        @click="$emit('close')"
    >
        <div 
            class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden"
            @click.stop
        >
            <!-- Header -->
            <div class="bg-amber-50 border-b border-amber-200 p-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-amber-900">Possible Duplicate Reports Found</h3>
                            <p class="text-sm text-amber-700">We found {{ duplicates.length }} similar report(s) that may be related to your submission.</p>
                        </div>
                    </div>
                    <button 
                        @click="$emit('close')"
                        class="text-amber-400 hover:text-amber-600 transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6 max-h-96 overflow-y-auto">
                <div class="space-y-4">
                    <div 
                        v-for="duplicate in duplicates" 
                        :key="duplicate.report.id"
                        class="border rounded-lg p-4 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <h4 class="font-semibold text-gray-900">{{ duplicate.report.title }}</h4>
                                    <span 
                                        :class="['px-2 py-1 rounded-full text-xs font-medium', getSimilarityColor(duplicate.similarity)]"
                                    >
                                        {{ formatSimilarity(duplicate.similarity) }}% similar
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mb-2">
                                    <span class="font-medium">Tracking Code:</span> {{ duplicate.report.tracking_code }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium">Submitted:</span> {{ duplicate.report.created_at }}
                                </p>
                            </div>
                            <div class="flex space-x-2 ml-4">
                                <button
                                    @click="handleViewDuplicate(duplicate.report)"
                                    class="px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors"
                                >
                                    View Details
                                </button>
                                <button
                                    @click="toggleDetails(duplicate.report.id)"
                                    class="px-3 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors"
                                >
                                    {{ showDetails[duplicate.report.id] ? 'Hide' : 'Show' }} Details
                                </button>
                            </div>
                        </div>

                        <!-- Expandable Details -->
                        <div v-if="showDetails[duplicate.report.id]" class="mt-3 p-3 bg-gray-50 rounded-md">
                            <div class="space-y-2">
                                <div>
                                    <p class="text-sm font-medium text-gray-700 mb-1">Description:</p>
                                    <p class="text-sm text-gray-600">{{ duplicate.report.description }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-700 mb-1">Why this might be similar:</p>
                                    <ul class="space-y-1">
                                        <li 
                                            v-for="reason in duplicate.reasons" 
                                            :key="reason"
                                            class="text-sm text-gray-600 flex items-center space-x-2"
                                        >
                                            <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span>{{ reason }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 border-t px-6 py-4">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-3 sm:space-y-0">
                    <div class="text-sm text-gray-600">
                        <p class="font-medium">What happens next?</p>
                        <p>If you continue, your report will be tagged as a potential duplicate and reviewed by administrators who may merge it with existing reports.</p>
                    </div>
                    <div class="flex space-x-3">
                        <button
                            @click="$emit('close')"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="handleForceSubmit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 transition-colors"
                        >
                            Submit Anyway
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>