<script setup>
import { ref, watch } from 'vue';

const emit = defineEmits(['close', 'confirm']);
const props = defineProps({
    show: Boolean,
});

const reasons = [
    { placeholder: 'Inaapropriate Report' },
    { placeholder: 'Invalid Report' },
    { placeholder: 'Duplicate Report' },
    { placeholder: 'Spam Report' },
    { placeholder: 'Not Under Jurisdiction' },
    { placeholder: 'Resolved Prior to Review' },
    { placeholder: 'Other' }
]

const selectedRejectReason = ref('')
const otherReason = ref('')
const errorMessage = ref('')

const resetFields = () => {
    selectedRejectReason.value = ''
    otherReason.value = ''
    errorMessage.value = ''
}

watch(
    () => props.show,
    (newVal) => {
        if(newVal) {
            resetFields()
        }
    }
)

const handleCancel = () => {
    resetFields()
    emit('close')
}

function confirmRejection() {
    // Validate that a reason is provided
    if (!selectedRejectReason.value) {
        errorMessage.value = 'Please select a reason for rejection'
        return
    }

    // If "Other" is selected, validate that text is provided
    if (selectedRejectReason.value === 'Other' && !otherReason.value.trim()) {
        errorMessage.value = 'Please specify the reason for rejection'
        return
    }

    const reasonToSend = selectedRejectReason.value === 'Other' 
        ? otherReason.value
        : selectedRejectReason.value
    
    errorMessage.value = ''

    emit('confirm', reasonToSend)
    resetFields
}
</script>

<template>
    <div v-if="show" class="fixed z-[9999] inset-0 backdrop-blur-sm bg-white/20 flex justify-center items-center">
        <div class="w-full max-w-xl mx-4 rounded-lg shadow-lg bg-white">
            <!-- Header -->
            <div class="flex justify-between items-center p-5 border-b bg-blue-700 rounded-t-lg">
                <div>
                    <h3 class="text-lg font-semibold font-[Poppins] text-white">Reject Report</h3>
                </div>
                <!-- Close Button --> 
                <div class="flex justify-end items-center">
                    <img 
                        @click="$emit('close')" 
                        :src="'/Images/SVG/x (white).svg'" 
                        alt="Close Icon" 
                        class="w-5 h-5 hover:cursor-pointer">
                </div>
            </div>

            <div class="flex flex-col p-5">
                <!-- Modal Content -->
                <div class="flex flex-col h-auto overflow-y-auto">
                    <p class="text-sm text-gray-600 mb-4">Reason for rejecting the report</p>

                    <!-- Error Message -->
                    <div v-if="errorMessage" class="mb-3 p-2 bg-red-100 border border-red-400 text-red-700 rounded">
                        {{ errorMessage }}
                    </div>

                    <div
                        v-for="(reason, index) in reasons" 
                        :key="index"
                        class="mb-3 border p-3 rounded"
                    >
                        <label>
                            <input 
                                type="radio"
                                name="rejectReason"
                                v-model="selectedRejectReason"
                                :value="reason.placeholder" 
                                class="mr-2"
                                required
                            >
                            {{ reason.placeholder }}
                        </label>
                    </div>
                    
                    <transition name="fade-slide">
                        <textarea 
                            v-if="selectedRejectReason === 'Other'" 
                            v-model="otherReason"
                            placeholder="Please specify..."
                            name="otherReason"
                            class="mt-2 rounded border border-gray-400 transition-all duration-300 w-full h-20 p-2 resize-none"
                            required
                        >
                        </textarea>
                    </transition>
                </div>
            </div>

            <!--Footer/Action Buttons -->
            <div class="flex justify-end border-t">
                <div class="p-5 flex gap-3">
                    <button 
                        @click="handleCancel" 
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition-all duration-300 flex-1"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="confirmRejection" 
                        class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition-all duration-300 flex-1"
                    >
                        Confirm
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