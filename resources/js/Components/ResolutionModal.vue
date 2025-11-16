<script setup>
// In ResolutionModal.vue
import { ref, watch } from 'vue';

const emit = defineEmits(['close', 'confirm']);
const props = defineProps({
    show: Boolean,
    isBulk: {
        type: Boolean,
        default: false
    },
    reportCount: {
        type: Number,
        default: 1
    }
});

const resolutionText = ref('')
const errorMessage = ref('')

const resetFields = () => {
    resolutionText.value = ''
    errorMessage.value = ''
}

// ADD THIS WATCHER to handle external close events
watch(
    () => props.show,
    (newVal) => {
        if (!newVal) {
            resetFields()
        }
    }
)

const handleCancel = () => {
    resetFields()
    emit('close')
}

function confirmResolution() {
    if (!resolutionText.value.trim()) {
        errorMessage.value = 'Please provide a resolution description'
        return
    }

    if (resolutionText.value.trim().length < 10) {
        errorMessage.value = 'Resolution description must be at least 10 characters long'
        return
    }

    errorMessage.value = ''
    emit('confirm', resolutionText.value.trim())
    resetFields()
    // Don't emit close here - let the parent handle it after successful API call
}
</script>

<template>
    <div v-if="show" class="fixed z-[70] inset-0 backdrop-blur-sm bg-white/20 flex justify-center items-center">
        <div class="w-full max-w-xl mx-4 rounded-lg shadow-lg bg-white">
            <!-- Header -->
            <div class="flex justify-between items-center px-4 py-5 border-b bg-green-600 rounded-t-lg">
                <div>
                    <h3 class="text-lg font-semibold font-[Poppins] text-white">
                        {{ isBulk ? `Resolve ${reportCount} Reports` : 'Mark as Resolved' }}
                    </h3>
                </div>
                <!-- Close Button --> 
                <div class="flex justify-end items-center">
                    <img 
                        @click="handleCancel" 
                        :src="'/Images/SVG/x (white).svg'" 
                        alt="Close Icon" 
                        class="w-5 h-5 hover:cursor-pointer">
                </div>
            </div>
            <div class="flex flex-col p-5">
                <!-- Modal Content -->
                <div class="flex flex-col h-auto">
                    <p class="text-sm text-gray-600 mb-4">
                        {{ isBulk 
                            ? `Please describe how these ${reportCount} reports were resolved. This will be visible to the reporters.`
                            : 'Please describe how this issue was resolved. This will be visible to the reporter.'
                        }}
                    </p>
                    <!-- Error Message -->
                    <div v-if="errorMessage" class="mb-3 p-2 bg-red-100 border border-red-400 text-red-700 rounded">
                        {{ errorMessage }}
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Resolution Description *
                        </label>
                        <textarea 
                            v-model="resolutionText"
                            placeholder="Describe what actions were taken to resolve this issue, any solutions implemented, or final outcomes..."
                            class="w-full h-40 p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 resize-none placeholder:text-sm text-sm"
                            :class="{ 'border-red-300': errorMessage }"
                        ></textarea>
                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                            <span>Minimum 10 characters</span>
                            <span>{{ resolutionText.length }}/1000</span>
                        </div>
                    </div>

                    <!-- Resolution Examples -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                        <p class="text-sm font-medium text-blue-800 mb-2">Examples of good resolutions:</p>
                        <ul class="text-xs text-blue-700 list-disc list-inside space-y-1">
                            <li>"The pothole was filled with asphalt by the DPWH team on November 15, 2023."</li>
                            <li>"Garbage collection schedule has been adjusted to twice weekly starting December 1st."</li>
                            <li>"Street lights have been repaired and are now fully functional."</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Footer/Action Buttons -->
            <div class="flex justify-end border-t p-5">
                <div class="flex gap-3">
                    <button 
                        @click="handleCancel" 
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition-all duration-300 flex-1"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="confirmResolution" 
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-all duration-300 flex-1 flex items-center justify-center gap-1"
                    >
                        <img :src="'/Images/SVG/check-circle.svg'" alt="Resolve Icon" class="w-4 h-4">
                        {{ isBulk ? `Resolve ${reportCount} Reports` : 'Resolved' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>