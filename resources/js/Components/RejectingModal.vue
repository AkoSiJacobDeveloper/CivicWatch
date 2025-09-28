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

const resetFields = () => {
    selectedRejectReason.value = ''
    otherReason.value = ''
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
    const reasonToSend = selectedRejectReason.value === 'Other' 
        ? otherReason.value
        : selectedRejectReason.value

    emit('confirm', reasonToSend)
    resetFields
}
</script>

<template>
    <div v-if="show"  class="fixed z-[9999] inset-0 backdrop-blur-sm bg-white/20 flex justify-center items-center">
        <div class="w-[35%] rounded-lg shadow-lg bg-white">
            <!-- Header -->
            <div class="flex justify-between items-center p-5 border-b bg-blue-700 rounded-t-lg">
                <div>
                    <h3 class="font-[Poppins] font-semibold text-2xl text-white">Reject Report</h3>
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

            <div class="flex flex-col gap-10 p-5">
                <!-- Modal Content -->
                <div class="flex flex-col">
                    <p class="font-semibold text-lg mb-2">Reason for Rejecting the Report</p>
                    <label 
                        v-for="(reason, index) in reasons" 
                        :key="index"
                        class="mb-1"
                    >
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

                    <transition name="fade-slide">
                        <textarea 
                            v-if="selectedRejectReason === 'Other'" 
                            v-model="otherReason"
                            placeholder="Please specify..."
                            name="otherReason"
                            class="mt-2 h-28 rounded border border-gray-400 transition-all duration-300"
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