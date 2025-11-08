<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    show: Boolean,
    trackingCode: String
});

const hasCopied = ref(false);

// Define emits first and get the emit function
const emit = defineEmits(['close']);

const copyToClipboard = async () => {
    try {
        await navigator.clipboard.writeText(props.trackingCode);
        hasCopied.value = true;
        
        // Show SweetAlert2 success message
        Swal.fire({
            title: 'Copied!',
            text: 'Tracking code copied to clipboard!',
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: '#16a34a'
        });
    } catch (err) {
        Swal.fire({
            title: 'Failed',
            text: 'Failed to copy. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc2626'
        });
        console.error(err);
    }
};

const handleClose = () => {
    if (!hasCopied.value) {
        Swal.fire({
            title: 'Important!',
            text: 'Please copy your tracking code before closing this modal.',
            icon: 'warning',
            confirmButtonText: 'OK',
            confirmButtonColor: '#16a34a'
        });
        return;
    }
    // Reset the flag when closing
    hasCopied.value = false;
    // Emit the close event using the emit function
    emit('close');
};

// Prevent any link clicks from navigating until tracking code is copied
const handleLinkClick = (event, linkType) => {
    event.preventDefault(); // Prevent default navigation
    event.stopPropagation(); // Prevent event from bubbling up
    
    if (!hasCopied.value) {
        Swal.fire({
            title: 'Important!',
            text: `Please copy your tracking code before going to ${linkType}.`,
            icon: 'warning',
            confirmButtonText: 'OK',
            confirmButtonColor: '#16a34a'
        });
        return;
    }
    
    // If tracking code has been copied, allow navigation
    if (linkType === 'review') {
        window.location.href = '/review';
    } else if (linkType === 'track-reports') {
        window.location.href = '/track-reports';
    }
};
</script>

<template>
    <div v-if="show" class="fixed z-[50] inset-0 backdrop-blur-sm bg-white/20 flex justify-center items-center">
        <div class="w-[35%] shadow-lg border rounded-lg bg-white">
            <div class="bg-green-600 p-5 rounded-lg">
                <!-- Close Button -->
                <div class="flex justify-end">
                    <img @click="handleClose" :src="'/Images/SVG/x (white).svg'" alt="Close Icon" class="w-5 h-5 hover:cursor-pointer">
                </div>
                <div class="">
                    <!-- Icon -->
                    <div class="flex justify-center mb-5">
                        <div class="w-16 h-16 rounded-full flex items-center justify-center bg-green-100">
                            <img :src="'/Images/SVG/check-circle-green.svg'" alt="Warning Icon">
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="text-center text-white">
                        <h5 class="font-bold text-2xl">Report Successfully Submitted</h5>
                        <p class="text-sm font-light">Thank you for your report. It has been received and will be reviewed by barangay officials.</p>
                    </div>
                </div>
                
            </div>
            

            <!-- Card Content -->
            <div class="flex flex-col">
                <div class="text-center text-black p-5">
                    <!-- Tracking Code -->
                    <div class="flex flex-col justify-start border-2 p-5 border-green-700 rounded-md">
                        <p class="text-xs text-start text-green-700 font-medium mb-2">YOUR TRACKING CODE</p>
                        <!-- Tracking Card -->
                        <div class="flex gap-2">
                            <div class="flex justify-between items-center border-2 border-green-100 p-2 rounded w-full">
                                <p class="font-bold text-xs text-gray-600">{{ trackingCode || 'Not available' }}</p>
                            </div>
                            <button @click="copyToClipboard" title="Copy Tracking Code" class="bg-green-700 px-3 rounded-md">
                                <img :src="'/Images/SVG/copy (white).svg'" alt="Icon" class="h-5 w-5 cursor-pointer">
                            </button>  
                        </div>
                        
                        <hr class="my-2">
                        <p class="text-start text-sm"><span class="font-bold">Save this code!</span> You can use it to track the status of your report and read updates on any actions taken.</p>
                    </div>
                </div>

                <div class="flex justify-between px-5 pb-5">
                    <div class="flex justify-center items-center">
                        <a
                            href="/review" 
                            class="text-gray-800 text-base hover:text-blue-500 transition-all duration-300"
                            @click="(event) => handleLinkClick(event, 'review')"
                        >
                            Submit a review
                        </a>
                    </div>
                    <div class="flex gap-1 text-[#FAF9F6] w-[50%] ">
                        <a 
                            href="/track-reports" 
                            class="w-1/2 bg-gray-500 p-2 rounded hover:bg-gray-600 transition duration-300 text-center text-sm"
                            @click="(event) => handleLinkClick(event, 'track-reports')"
                        >
                            Track Report
                        </a>
                        <button class="w-1/2 bg-blue-600 text-white p-2 rounded hover:bg-blue-700 transition-colors duration-300"
                            @click="handleClose"
                            >
                        Okay
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>