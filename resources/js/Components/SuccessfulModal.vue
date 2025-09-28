<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    show: Boolean,
    trackingCode: String
});

const copyToClipboard = async () => {
    try {
        await navigator.clipboard.writeText(props.trackingCode);
        alert('Tracking code copied to clipboard!');
    } catch (err) {
        alert('Failed to copy. Please try again.');
        console.error(err);
    }
};

defineEmits(['close']);
</script>


<template>
    <div v-if="show" class="fixed z-[9999] inset-0 backdrop-blur-sm bg-white/20 flex justify-center items-center">
        <div class="w-[35%] p-10 rounded shadow-lg border bg-white">
            
            <!-- Close Button -->
            <div class="flex justify-end">
                <img @click="$emit('close')" :src="'/Images/SVG/x.svg'" alt="Close Icon" class="w-5 h-5 hover:cursor-pointer">
            </div>

            <!-- Card Content -->
            <div class="flex flex-col">
                <div class="text-center text-black">
                    <div class="mb-10">
                        <!-- Icon -->
                        <div class="flex justify-center mb-5">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center bg-green-100">
                                <img :src="'/Images/SVG/check-circle-green.svg'" alt="Warning Icon">
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="text-center">
                            <h5 class="font-bold text-2xl">Report Successfully Submitted</h5>
                            <p class="text-sm text-gray-600">Thank you for your report. It has been received and will be reviewed by barangay officials.</p>
                        </div>
                    </div>

                    <!-- Tracking Code -->
                    <div class="flex flex-col my-6 justify-start">
                        <p class="text-xs text-start text-gray-600">Please save or copy this tracking code to check the status of your report later.</p>
                        <!-- Tracking Card -->
                        <div class="flex justify-between items-center border border-gray-400 p-2 rounded">
                            <p class="font-bold text-xs text-gray-600">{{ trackingCode || 'Not available' }}</p>
                            <button @click="copyToClipboard" title="Copy Tracking Code">
                                <font-awesome-icon icon="copy" class="text-blue-500 cursor-pointer text-sm" />
                            </button>   
                        </div>
                    </div>
                </div>

                <div class="flex justify-between">
                    <div class="flex justify-center items-center">
                        <Link
                            href="/review" class="text-gray-800 text-base hover:text-blue-500 transition-all duration-300"
                        >
                            Submit a review
                        </Link>
                    </div>
                    <div class="flex gap-1 text-[#FAF9F6] w-[50%] ">
                        <Link href="/track-reports" class="w-1/2 bg-gray-500 p-2 rounded hover:bg-gray-600 transition duration-300 text-center text-sm">Track Report</Link>
                        <button class="w-1/2 bg-blue-600 text-white p-2 rounded hover:bg-blue-700 transition-colors duration-300"
                            @click="$emit('close')"
                            >
                        Okay
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
                    