<script setup>
import { Head, router, usePage, useForm } from '@inertiajs/vue3';
import { ref, nextTick, onMounted, computed, onUnmounted } from 'vue';
import { Listbox, ListboxButton, ListboxOption, ListboxOptions } from '@headlessui/vue';
import Swal from 'sweetalert2';
import axios from 'axios';

import GuestLayout from '@/Layouts/GuestLayout.vue';
import SuccessfulModal from '@/Components/SuccessfulModal.vue';

const page = usePage();
const trackingCode = ref(null);
const showModal = ref(false);
const fileInput = ref(null);
const imagePreview = ref(null);
const issueTypes = ref([]);
const barangays = ref([]);
const isSubmitting = ref(false); 
const customIssueDescription = ref('');

// Form data
const form = useForm({
    title: '',
    issue_type: '',
    description: '',
    image: null,
    barangay_id: '',
    sitio_id: '',
    sender_name: '',
    contact_number: '',
    remarks: '',
    status: 'pending',
    latitude: null,      
    longitude: null,     
    gps_accuracy: null,  
});

const showCameraModal = ref(false);
const showImagePreviewModal = ref(false);

// ========== GPS LOCATION VARIABLES ==========
const isGettingLocation = ref(false);
const locationStatus = ref('');
const hasLocation = ref(false);

// ========== GPS LOCATION FUNCTIONS ==========
async function getCurrentLocation() {
    if (!navigator.geolocation) {
        Swal.fire({
            title: 'Location Not Supported',
            text: 'Your browser does not support location services. Please use a modern browser.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }

    isGettingLocation.value = true;
    locationStatus.value = 'Getting your location...';

    try {
        const position = await new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject, {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            });
        });

        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        const accuracy = position.coords.accuracy;

        // Update form data
        form.latitude = lat;
        form.longitude = lng;
        form.gps_accuracy = accuracy;

        hasLocation.value = true;
        locationStatus.value = `Location captured! Accuracy: ${accuracy.toFixed(1)} meters`;

        Swal.fire({
            title: 'Location Captured!',
            text: `Your location has been successfully captured with ${accuracy.toFixed(1)} meter accuracy.`,
            icon: 'success',
            confirmButtonText: 'OK',
            timer: 3000
        });

    } catch (error) {
        let errorMessage = 'Could not get your location. ';
        
        switch(error.code) {
            case error.PERMISSION_DENIED:
                errorMessage += 'Please allow location access in your browser settings.';
                break;
            case error.POSITION_UNAVAILABLE:
                errorMessage += 'Location information is unavailable. Try moving to an area with better signal.';
                break;
            case error.TIMEOUT:
                errorMessage += 'Location request timed out. Please try again.';
                break;
            default:
                errorMessage += 'Please try again.';
        }

        locationStatus.value = errorMessage;
        
        Swal.fire({
            title: 'Location Error',
            text: errorMessage,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    } finally {
        isGettingLocation.value = false;
    }
}

function clearLocation() {
    form.latitude = null;
    form.longitude = null;
    form.gps_accuracy = null;
    hasLocation.value = false;
    locationStatus.value = '';
}

// ========== CAMERA CAPTURE VARIABLES ==========
const isCameraActive = ref(false);
const stream = ref(null);
const videoElement = ref(null);
const useCamera = ref(false);
const capturedImage = ref(null);
const showFileUpload = ref(false);

// ========== CAMERA FUNCTIONS ==========
async function startCamera() {
    try {
        useCamera.value = true;
        isCameraActive.value = true;
        showFileUpload.value = false;
        
        // Show camera modal
        showCameraModal.value = true;
        
        // Wait for Vue to render the video element in the modal
        await nextTick();
        
        let videoEl = document.querySelector('#camera-modal video');
        if (videoEl) {
            videoElement.value = videoEl;
        } else {
            await new Promise(resolve => setTimeout(resolve, 200));
            videoEl = document.querySelector('#camera-modal video');
            if (videoEl) {
                videoElement.value = videoEl;
            }
        }
        
        if (!videoEl) {
            throw new Error('Video element not found in modal');
        }

        stream.value = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'environment',
                width: { ideal: 1280 },
                height: { ideal: 720 }
            } 
        });
        
        videoEl.srcObject = stream.value;
        
        await new Promise((resolve) => {
            videoEl.onloadedmetadata = () => {
                videoEl.play().then(resolve).catch(console.error);
            };
        });
        
        if (fileInput.value) {
            fileInput.value.value = '';
        }
        imagePreview.value = null;
        form.image = null;
        
    } catch (error) {
        console.error('Error accessing camera:', error);
        useCamera.value = false;
        isCameraActive.value = false;
        showCameraModal.value = false;
        videoElement.value = null;
        
        // Reset the video element reference if it failed
        videoElement.value = null;
        
        if (error.name === 'NotAllowedError') {
            Swal.fire({
                title: 'Camera Access Denied',
                text: 'Please allow camera access to use live capture, or use file upload instead.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else if (error.name === 'NotFoundError') {
            Swal.fire({
                title: 'No Camera Found',
                text: 'No camera device was found. Please use file upload instead.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else if (error.name === 'NotSupportedError') {
            Swal.fire({
                title: 'Browser Not Supported',
                text: 'Your browser does not support camera access. Please use a modern browser or file upload.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else {
            Swal.fire({
                title: 'Camera Error',
                text: 'Could not access camera. Please try again or use file upload.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    }
}

function stopCamera() {
    if (stream.value) {
        stream.value.getTracks().forEach(track => {
            track.stop();
        });
        stream.value = null;
    }
    isCameraActive.value = false;
    useCamera.value = false;
    showCameraModal.value = false;
}

function closeCameraModal() {
    stopCamera();
    showCameraModal.value = false;
}

function capturePhoto() {
    if (!videoElement.value || !stream.value) {
        console.error('Video element or stream not available');
        return;
    }
    
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');
    
    canvas.width = videoElement.value.videoWidth;
    canvas.height = videoElement.value.videoHeight;
    
    context.drawImage(videoElement.value, 0, 0, canvas.width, canvas.height);
    
    const now = new Date();
    const timestamp = now.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    });
    
    context.fillStyle = 'rgba(0, 0, 0, 0.7)';
    context.fillRect(10, 10, 400, 30);
    context.fillStyle = 'white';
    context.font = '14px Arial';
    context.fillText(`Verified: ${timestamp} - Barangay Report`, 20, 30);
    
    canvas.toBlob((blob) => {
        if (!blob) {
            console.error('Failed to create blob');
            return;
        }
        
        if (imagePreview.value) {
            URL.revokeObjectURL(imagePreview.value);
        }
        imagePreview.value = URL.createObjectURL(blob);
        
        const file = new File([blob], `verified-report-${Date.now()}.jpg`, { 
            type: 'image/jpeg' 
        });
        
        form.image = file;
        capturedImage.value = file;
        
        stopCamera();
        
        Swal.fire({
            title: 'Photo Captured!',
            text: 'Your photo has been captured successfully.',
            icon: 'success',
            confirmButtonText: 'OK',
            timer: 2000
        });
    }, 'image/jpeg', 0.8);
}

function showImagePreview() {
    if (imagePreview.value) {
        showImagePreviewModal.value = true;
    }
}

function closeImagePreview() {
    showImagePreviewModal.value = false;
}

function removeImage() {
    if (imagePreview.value) {
        URL.revokeObjectURL(imagePreview.value);
        imagePreview.value = null;
    }
    
    form.image = null;
    capturedImage.value = null;
    showImagePreviewModal.value = false;
    
    if (fileInput.value) {
        fileInput.value.value = '';
    }
    
    useCamera.value = false;
    showFileUpload.value = false;
    
    Swal.fire({
        title: 'Image Removed',
        text: 'The image has been removed.',
        icon: 'success',
        confirmButtonText: 'OK',
        timer: 2000
    });
}

function checkCameraSupport() {
    return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
}

// Computed property for available sitios
const availableSitios = computed(() => {
    if (!form.barangay_id) return [];
    const barangay = barangays.value.find(b => b.id == form.barangay_id);
    const sitios = barangay ? barangay.sitios.filter(s => s.is_available) : [];
    return sitios;
});

// Fetch issue types
async function fetchIssueType() {
    try {
        const response = await axios.get('/api/issue-type');
        issueTypes.value = response.data;
    } catch {
        issueTypes.value = [];
    }
}

// Fetch barangays with sitios
async function fetchBarangaysWithSitios() {
    try {
        const response = await axios.get('/barangays-with-sitios');
        barangays.value = response.data;
    } catch {
        barangays.value = [];
    }
}

// Handle barangay selection
const onBarangayChange = () => {
    form.sitio_id = '';
};

// Handle file upload
function handleFileChange(event) {
    const file = event.target.files[0];
    if (file && file.type.startsWith('image/')) {
        if (imagePreview.value) {
            URL.revokeObjectURL(imagePreview.value);
        }
        imagePreview.value = URL.createObjectURL(file);
        form.image = file;
        capturedImage.value = null;
        useCamera.value = false;
        
        if (isCameraActive.value) {
            stopCamera();
        }
        
        Swal.fire({
            title: 'Image Uploaded!',
            text: 'Your image has been uploaded successfully.',
            icon: 'success',
            confirmButtonText: 'OK',
            timer: 2000
        });
    } else {
        if (imagePreview.value) {
            URL.revokeObjectURL(imagePreview.value);
        }
        imagePreview.value = null;
        form.image = null;
        capturedImage.value = null;
    }
}


// Reset form
async function resetForm() {
    form.reset();
    customIssueDescription.value = '';

    clearLocation();
    
    if (imagePreview.value) {
        URL.revokeObjectURL(imagePreview.value);
        imagePreview.value = null;
    }
    
    // Stop camera if active
    if (isCameraActive.value) {
        stopCamera();
    }
    
    capturedImage.value = null;
    useCamera.value = false;
    showCameraModal.value = false;
    showImagePreviewModal.value = false;
    
    await nextTick();
    
    if (fileInput.value) {
        fileInput.value.value = '';
    } else {
        const inputs = document.querySelectorAll('input[type="file"]');
        inputs.forEach(input => input.value = '');
    }
    
    if (typeof grecaptcha.reset === 'function') {
        grecaptcha.reset();
    }
}

// Submit form
function submitForm() {
    const token = grecaptcha.getResponse();

    if (!token) {
        alert('Please complete the reCAPTCHA');
        return;
    }

    // GPS requirement check
    if (!form.latitude || !form.longitude) {
        Swal.fire({
            title: '📍 Location Verification Required',
            html: `
                <div class="text-left">
                    <p class="mb-3"><strong>To ensure reports come from within Cabulijan, we need to verify your location.</strong></p>
                    <p class="text-sm mb-3">Your report may be rejected without location verification.</p>
                    <div class="bg-yellow-50 p-3 rounded-lg border border-yellow-200">
                        <p class="text-sm font-semibold text-yellow-800">Why we need your location:</p>
                        <ul class="text-sm list-disc ml-4 mt-1 text-yellow-700">
                            <li>Verify you're in Barangay Cabulijan</li>
                            <li>Prevent fake reports from outside</li>
                            <li>Help officials locate issues accurately</li>
                        </ul>
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Proceed Anyway',
            cancelButtonText: 'Get My Location',
            reverseButtons: true
        }).then((result) => {
            if (!result.isConfirmed) {
                getCurrentLocation();
                return;
            }
            // Pass token to continueSubmission
            continueSubmission(token);
        });
        return;
    }
    
    // If GPS is available, continue with submission
    continueSubmission(token);
}

function continueSubmission(token) {
    if (!form.barangay_id) {
        alert('Please select a Barangay');
        return;
    }

    if (availableSitios.value.length > 0 && !form.sitio_id) {
        alert('Please select a Sitio');
        return;
    }

    isSubmitting.value = true;

    const formData = new FormData();
    formData.append('title', form.title || '');
    formData.append('issue_type', form.issue_type || '');
    formData.append('custom_issue_description', form.issue_type === 'Other' ? customIssueDescription.value : '');
    formData.append('description', form.description || '');
    formData.append('sender_name', form.sender_name || '');
    formData.append('contact_number', form.contact_number || '');
    formData.append('remarks', form.remarks || '');
    formData.append('status', 'pending');
    formData.append('barangay_id', form.barangay_id || '');
    formData.append('sitio_id', form.sitio_id || '');
    
    // Add GPS data if available
    if (form.latitude && form.longitude) {
        formData.append('latitude', form.latitude);
        formData.append('longitude', form.longitude);
        formData.append('gps_accuracy', form.gps_accuracy || 0);
    }
    
    if (form.image instanceof File) {
        formData.append('image', form.image);
    }
    
    formData.append('g-recaptcha-response', token);

    router.post('/report-issue', formData, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            trackingCode.value = page.props.flash?.tracking_code || null;
            showModal.value = true;
            isSubmitting.value = false;
            resetForm();
        },
        onError: (errors) => {
            isSubmitting.value = false;
            if (errors) {
                Object.keys(errors).forEach(key => {
                    form.errors[key] = errors[key];
                });
            }
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
}

onMounted(async () => {
    await fetchIssueType();
    await fetchBarangaysWithSitios();

    if (window.grecaptcha) {
        window.grecaptcha.render(document.querySelector('.g-recaptcha'), {
            sitekey: '6Ldq2nwrAAAAAHXbI9NLQEKa34n9VAWOkBOhY9dN'
        });
    }

    Swal.fire({
        title: '<strong>Important Notice</strong>',
        icon: 'warning',
        html: `
        <div class="">
            <p class="mb-3 text-center font-[Poppins] text-base font-medium">Please note that this system is designed only for non-urgent concerns, such as: </p>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm">
                <p class="font-medium text-blue-800 text-left">Please make sure:</p>
                <ul class="list-disc ml-4 my-2 text-left text-blue-700">
                    <li class="text-sm">Minor road or streetlight damage</li>
                    <li class="text-sm">Garbage collection issues</li>
                    <li class="text-sm">Public facility maintenance</li>
                    <li class="text-sm">Community disturbances (non-emergency)</li>
                </ul>
            </div>
            <p class="text-sm text-left mt-3">If an emergency report is detected such as fire, or crime a hotline will appear to guide you in reporting to the correct agency. </p>
        </div>
        `,
        showCloseButton: true,
        confirmButtonText: 'I Understand',
        confirmButtonColor: '#3085d6',
    });
});

onUnmounted(() => {
    if (stream.value) {
        stopCamera();
    }
    if (imagePreview.value) {
        URL.revokeObjectURL(imagePreview.value);
    }
});
</script>

<template>
    <Head title="Report Issue" />

    <GuestLayout>
        <main class="pt-[80px] md:pt-[120px] dark:text-[#FAF9F6] ">
            <section class="px-3 md:px-10 lg:px-32">
                <div>
                    <h1 class="font-bold text-lg md:text-3xl font-[Poppins]">Submit New Report</h1>
                    <p class="text-sm text-gray-500 dark:text-[#FAF9F6]">Describe a local issue you’ve noticed. Include the location and a photo to help barangay officials respond faster.</p>
                </div>

                <!-- NOTICE -->
                <div v-if="barangays.filter(b => !b.is_available).length > 0" class="mt-5 p-3 bg-yellow-50 border border-yellow-200 rounded-md dark:bg-yellow-900 dark:border-yellow-700 dark:text-yellow-100">
                    <p class="text-xs md:text-sm">
                        <strong>Notice:</strong> Our system only accepts <span class="font-bold">non-urgent</span> reports, such as minor maintenance issues, community concerns, or local observations that do not require immediate response.
                        If an <span class="font-bold">emergency</span> report is detected, a hotline will automatically appear to help you redirect your report to the appropriate agency for faster assistance.
                    </p>
                </div>
                
                <div class="my-5">
                    <form @submit.prevent="submitForm" enctype="multipart/form-data">
                        <div class="md:flex gap-3">
                            <div class="md:w-1/2 mb-1">
                                <!-- TITLE -->
                                <div class="relative">
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        id="title"
                                        placeholder=""
                                        maxlength="50"
                                        class="block p-4 pt-4 w-full text-base bg-white text-gray-900 bg-transparent rounded-lg border-1 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer dark:bg-[#2c2c2c]"
                                        required
                                    />
                                    <label 
                                        for="title" 
                                        class="absolute rounded text-base text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-4 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1 dark:bg-[#2c2c2c]">
                                        Title
                                    </label>
                                </div>

                                <!-- Character Indicator -->
                                <div 
                                    :class="[
                                        'text-xs text-right',
                                        form.title.length > 45 ? 'text-red-500' : 'text-gray-500'
                                    ]"
                                >
                                    {{ form.title.length }}/50
                                </div>

                                <!-- Error -->
                                <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.title }}
                                </div>
                            </div>
                    
                            <div class="md:w-1/2">
                                <!-- SENDER NAME -->
                                <div class="relative">
                                    <input
                                        v-model="form.sender_name"
                                        type="text"
                                        id="sender_name"
                                        placeholder=""
                                        maxlength="50"
                                        class="block p-4 pt-4 w-full text-base bg-white text-gray-900 bg-transparent rounded-lg border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer dark:bg-[#2c2c2c]"
                                        required
                                    />
                                    <label 
                                        for="sender_name" 
                                        class="absolute rounded text-base text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white dark:bg-[#2c2c2c] px-4 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
                                        Sender Name
                                    </label>
                                </div>
                                <!-- Character Indicator -->
                                <div 
                                    :class="[
                                        'text-xs text-right',
                                        form.sender_name.length > 45 ? 'text-red-500' : 'text-gray-500'
                                    ]"
                                >
                                    {{ form.sender_name.length }}/50
                                </div>
                                <!-- Error -->
                                <div v-if="form.errors.sender_name" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.sender_name }}
                                </div>
                            </div>
                        </div>

                        <!-- ISSUE TYPE -->
                        <div class="mb-4">
                            <!-- Headless UI-->
                            <Listbox 
                                v-model="form.issue_type"
                                as="div"
                                class="relative"
                            >
                                <ListboxButton
                                    class="flex justify-between items-center text-left p-4 w-full text-base bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline-none focus:ring-0 focus:border-gray-200 peer dark:bg-[#2c2c2c]"
                                    required
                                >
                                    {{ form.issue_type || 'Select an issue type' }}

                                    <svg
                                        class="w-5 h-5 text-gray-400"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                        >
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.23 7.21a.75.75 0 011.06.02L10 11.292l3.71-4.06a.75.75 0 111.08 1.04l-4.25 4.65a.75.75 0 01-1.08 0l-4.25-4.65a.75.75 0 01.02-1.06z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </ListboxButton>

                                <ListboxOptions
                                    class="absolute z-50 mt-4 w-full bg-white rounded-lg shadow-lg border border-gray-300 dark:bg-[#2c2c2c] max-h-56 overflow-y-auto"
                                >
                                    <ListboxOption
                                        v-for="issueType in issueTypes" 
                                        :key="issueType.id" 
                                        :value="issueType.name"
                                        class="dark:bg-[#2c2c2c] px-4 py-2 hover:bg-blue-100 transition-all duration-100"
                                    >
                                        {{ issueType.name }}
                                    </ListboxOption>

                                    <!-- Other -->
                                    <ListboxOption
                                        value="Other"
                                        class="dark:bg-[#2c2c2c] px-4 py-2 hover:bg-blue-100 transition-all duration-100 border-t border-gray-200 dark:border-gray-600"
                                    >
                                        Other
                                    </ListboxOption>

                                    
                                </ListboxOptions>
                            </Listbox>

                            <div v-if="form.issue_type === 'Other'" class="mt-3">
                                <div class="relative">
                                    <input
                                        v-model="customIssueDescription"
                                        type="text"
                                        id="custom_issue_description"
                                        placeholder=""
                                        maxlength="100"
                                        class="block p-4 pt-4 w-full text-base bg-white text-gray-900 bg-transparent rounded-lg border-1 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer dark:bg-[#2c2c2c]"
                                        required
                                    />
                                    <label 
                                        for="custom_issue_description" 
                                        class="absolute rounded text-base text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-4 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1 dark:bg-[#2c2c2c]">
                                        Please specify the issue type
                                    </label>
                                </div>

                                <div 
                                    :class="[
                                        'text-xs text-right',
                                        customIssueDescription.length > 90 ? 'text-red-500' : 'text-gray-500'
                                    ]"
                                >
                                    {{ customIssueDescription.length }}/100
                                </div>
                            </div>

                            <div v-if="form.errors.issue_type" class="text-red-500 text-sm mt-1">
                                {{ form.errors.issue_type }}
                            </div>
                        </div>

                        <!-- LOCATION SECTION -->
                        <div class="mb-4 flex flex-col gap-3">
                            <div class="md:flex md:gap-3 items-center">
                                <!-- BARANGAY -->
                                <div class="mb-4 md:w-1/2 md:mb-0">
                                    <Listbox 
                                        v-model="form.barangay_id"
                                        as="div"
                                        class="relative w-full"
                                    >
                                        <ListboxButton
                                            class="flex justify-between items-center text-left p-4 w-full text-base bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline-none focus:ring-0 focus:border-gray-200 peer dark:bg-[#2c2c2c]"
                                            required
                                        >
                                            {{ barangays.find(b => b.id === form.barangay_id)?.name || 'Select Barangay' }}

                                            <svg
                                                class="w-5 h-5 text-gray-400"
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.292l3.71-4.06a.75.75 0 111.08 1.04l-4.25 4.65a.75.75 0 01-1.08 0l-4.25-4.65a.75.75 0 01.02-1.06z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </ListboxButton>

                                        <ListboxOptions
                                            class="absolute z-50 mt-4 w-full bg-white rounded-lg shadow-lg border border-gray-300 dark:bg-[#2c2c2c] max-h-56 overflow-y-auto"
                                        >
                                            <!-- Available Barangays -->
                                            <ListboxOption
                                                v-for="barangay in barangays.filter(b => b.is_available)"
                                                :key="barangay.id"
                                                :value="barangay.id"
                                                class="px-4 py-2 hover:bg-blue-100 transition-all duration-100 cursor-pointer dark:hover:bg-gray-700"
                                            >
                                                {{ barangay.name }}
                                            </ListboxOption>
                                        </ListboxOptions>
                                    </Listbox>
                                    <div v-if="form.errors.barangay_id" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.barangay_id }}
                                    </div>
                                </div>

                                <!-- SITIO - Always Visible -->
                                <div class="md:w-1/2">
                                    <Listbox 
                                        v-model="form.sitio_id"
                                        as="div"
                                        class="relative w-full"
                                        :disabled="!form.barangay_id"
                                    >
                                        <ListboxButton
                                            :class="[
                                                'flex justify-between items-center text-left p-4 w-full text-base bg-white rounded-lg border focus:outline-none focus:ring-0 peer',
                                                !form.barangay_id 
                                                    ? 'text-gray-400 border-gray-200 bg-gray-50 cursor-not-allowed dark:bg-gray-800 dark:border-gray-700 dark:text-gray-500' 
                                                    : 'text-gray-500 border-gray-300 dark:text-gray-400 dark:border-gray-700 dark:bg-[#2c2c2c] focus:border-gray-200'
                                            ]"
                                            :disabled="!form.barangay_id"
                                        >
                                            {{ availableSitios.find(s => s.id === form.sitio_id)?.name || 'Select Sitio' }}

                                            <svg
                                                :class="[
                                                    'w-5 h-5',
                                                    !form.barangay_id ? 'text-gray-300' : 'text-gray-400'
                                                ]"
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.292l3.71-4.06a.75.75 0 111.08 1.04l-4.25 4.65a.75.75 0 01-1.08 0l-4.25-4.65a.75.75 0 01.02-1.06z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </ListboxButton>

                                        <ListboxOptions
                                            v-if="form.barangay_id && availableSitios.length > 0"
                                            class="absolute z-50 mt-4 w-full bg-white rounded-lg shadow-lg border border-gray-300 dark:bg-[#2c2c2c] max-h-56 overflow-y-auto"
                                        >
                                            <ListboxOption
                                                v-for="sitio in availableSitios"
                                                :key="sitio.id"
                                                :value="sitio.id"
                                                class="px-4 py-2 hover:bg-blue-100 transition-all duration-100 cursor-pointer dark:hover:bg-gray-700"
                                            >
                                                {{ sitio.name }}
                                            </ListboxOption>
                                        </ListboxOptions>
                                    </Listbox>
                                    
                                    <!-- Help text when disabled -->
                                    
                                    <div v-if="form.errors.sitio_id" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.sitio_id }}
                                    </div>
                                </div>
                            </div>

                            <!-- SELECTED LOCATION DISPLAY -->
                            <div v-if="form.barangay_id && form.sitio_id" class="p-3 bg-blue-50 rounded-md dark:text-blue-100 dark:bg-[#2c2c2c]">
                                <p class="text-sm">
                                    <strong>Selected Location: </strong> 
                                    {{ barangays.find(b => b.id == form.barangay_id)?.name }} - 
                                    {{ availableSitios.find(s => s.id == form.sitio_id)?.name }}
                                </p>
                            </div>

                            <!-- CONTACT NUMBER -->
                            <div class="md:w-1/2">
                                <label class="block text-sm font-semibold font-[Poppins] mb-1" for="review_message">Contact Number</label>
                                <div class="">
                                    <div class="">
                                        <img 
                                            :src="'/Images/SVG/phone-fill (500).svg'" 
                                            alt="Phone"
                                            class="w-5 h-5 absolute mt-4 ms-3 pointer-events-none" 
                                        >
                                    </div>
                                    <input
                                        v-model="form.contact_number"
                                        type="tel"
                                        id="contact_number"
                                        placeholder="+63xxxxxxxxxx"
                                        class="p-4 border bg-white border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:bg-[#2c2c2c]"
                                        required
                                    />
                                </div>
                                
                                <div v-if="form.errors.contact_number" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.contact_number }}
                                </div>
                            </div>

                            <div class="md:flex gap-3">
                                <!-- UPLOAD PHOTO -->
                                <div class="md:w-1/2 md:mb-0">
                                    <label class="block text-sm font-semibold font-[Poppins] mb-1">
                                        Upload Photo 
                                    </label>
                                    
                                    <div class="flex flex-col gap-2">
                                        <div class="w-full p-4 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-[#2c2c2c]">
                                            <!-- Show camera and upload buttons only when no image is selected -->
                                            <div v-if="!imagePreview">
                                                <!-- Take Photo Button -->
                                                <button 
                                                    type="button"
                                                    @click="startCamera"
                                                    :disabled="isCameraActive"
                                                    class="mb-2 flex items-center justify-center gap-2 px-4 py-3 w-full bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-green-400 transition-colors"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                    </svg>
                                                    <p class="text-sm">Take Photo</p>
                                                </button>

                                                <!-- Upload File Section -->
                                                <div class="mb-1">
                                                    <div v-if="!showFileUpload" class="text-center">
                                                        <button 
                                                            type="button"
                                                            @click="showFileUpload = true"
                                                            class="text-xs w-full px-4 py-3 flex gap-1 items-center justify-center border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                                        >
                                                            <img :src="'/Images/SVG/upload-simple (gray).svg'" alt="Icon" class="h-4 w-4">
                                                            <p class="text-sm text-medium text-gray-500">Upload File</p>
                                                        </button>
                                                    </div>

                                                    <!-- File Upload Input -->
                                                    <div v-if="showFileUpload" class="mb-3">
                                                        <span class="text-xs text-gray-500 block mb-1">Upload existing photo</span>
                                                        <input
                                                            type="file"
                                                            id="image"
                                                            ref="fileInput"
                                                            accept="image/*"
                                                            @change="handleFileChange"
                                                            class="block w-full p-2 text-sm text-gray-700 border border-gray-300 rounded-lg cursor-pointer 
                                                                bg-white dark:text-gray-300 focus:outline-none 
                                                                file:mr-4 file:py-2 file:px-4 
                                                                file:rounded-md file:border-0 
                                                                file:text-sm file:font-medium 
                                                                file:bg-blue-600 file:text-white 
                                                                hover:file:bg-blue-700 
                                                                dark:bg-[#2c2c2c] dark:border-gray-600 dark:placeholder-gray-400"
                                                        />
                                                        <button 
                                                            type="button"
                                                            @click="showFileUpload = false"
                                                            class="mt-1 text-xs text-red-500 hover:text-red-700 underline"
                                                        >
                                                            Cancel upload
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Show image preview controls when image is selected -->
                                            <div v-else class="mt-3">
                                                <div class="flex items-center gap-2">
                                                    <button 
                                                        type="button"
                                                        @click="showImagePreview"
                                                        class="flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex-1"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9a3 3 0 100 6 3 3 0 000-6z"/>
                                                        </svg>
                                                        View Image
                                                    </button>
                                                    <button 
                                                        type="button"
                                                        @click="removeImage"
                                                        class="flex items-center justify-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                        Remove
                                                    </button>
                                                </div>
                                                <p v-if="capturedImage" class="text-xs text-green-600 mt-2 text-center">
                                                    ✓ This photo includes verification timestamp
                                                </p>
                                                <p v-else class="text-xs text-gray-500 mt-2 text-center">
                                                    Uploaded image
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- GPS LOCATION SECTION -->
                                <div class="md:w-1/2">
                                    <label class="block text-sm font-semibold font-[Poppins] mb-1">
                                        Location Verification 
                                        <span class="text-xs font-normal text-blue-500">(Optional but recommended)</span>
                                    </label>
                                    
                                    <div class="p-4 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-[#2c2c2c]">
                                        <div class="space-y-3">
                                            <!-- Location Status -->
                                            <div v-if="locationStatus" class="p-3 rounded-lg text-sm" 
                                                :class="hasLocation ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-yellow-50 text-yellow-700 border border-yellow-200'">
                                                {{ locationStatus }}
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="flex flex-col sm:flex-row gap-2">
                                                <button 
                                                    type="button"
                                                    @click="getCurrentLocation"
                                                    :disabled="isGettingLocation"
                                                    class="flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-blue-400 transition-colors flex-1"
                                                >
                                                    <svg v-if="isGettingLocation" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    <span class="text-sm">{{ isGettingLocation ? 'Getting Location...' : 'Get My Current Location' }}</span>
                                                </button>

                                                <button 
                                                    v-if="hasLocation"
                                                    type="button"
                                                    @click="clearLocation"
                                                    class="flex items-center justify-center gap-2 px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex-1"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                    <span class="text-sm">Clear Location</span>
                                                </button>
                                            </div>

                                            <!-- Help Text -->
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                <p>• This helps barangay officials verify your exact location</p>
                                                <p>• Your coordinates will be shown on a map when officials review your report</p>
                                                <p>• You can still submit without location data</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="md:flex gap-3">
                            <!-- DESCRIPTION -->
                            <div class=" md:w-1/2 md:mb-0">
                                <label class="block text-sm font-semibold font-[Poppins] mb-1" for="review_message">Description</label>
                                <textarea
                                    v-model="form.description"
                                    id="description"
                                    placeholder="Describe the issue in detail..."
                                    rows="5"
                                    class="block p-2.5 w-full text-base bg-white text-gray-900 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-[#2c2c2c] dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 resize-none"
                                    required
                                ></textarea>
                                <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.description }}
                                </div>
                            </div>

                            <!-- ADDITIONAL NOTES -->
                            <div class=" md:w-1/2 md:mb-0">
                                <label class="block text-sm font-semibold font-[Poppins] mb-1" for="review_message">Additional Notes <span class="text-xs font-normal text-blue-500">(Optional)</span></label>
                                <textarea
                                    v-model="form.remarks"
                                    id="remarks"
                                    placeholder="Anything u want to say..."
                                    rows="5"
                                    class="block p-2.5 w-full text-base bg-white text-gray-900 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-[#2c2c2c] dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 resize-none"
                                ></textarea>
                                <div v-if="form.errors.remarks" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.remarks }}
                                </div>
                            </div>
                        </div>



                        
                        
                        <!-- SUBMIT SECTION -->
                        <div class="md:flex justify-between">
                            <div class="g-recaptcha my-4 :dark:bg-[#2c2c2c]"></div>
                        
                            <div class="flex justify-center items-center md:w-64 gap-2">
                                <button 
                                    type="submit" 
                                    class="flex-1 bg-blue-600 py-3 rounded text-white hover:bg-blue-700 transition-colors duration-300 flex items-center justify-center"
                                    :disabled="isSubmitting"
                                >
                                    <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm">{{ isSubmitting ? 'Submitting...' : 'Submit Report' }}</span>
                                </button>

                                <button 
                                    class="flex-1 bg-red-500 py-3 rounded text-white text-sm "
                                    type="button"
                                    @click="resetForm"
                                >
                                    Clear
                                </button>
                            </div>
                        </div>
                        
                    </form>
                </div>
            </section>

            <!-- Camera Modal -->
            <div v-if="showCameraModal" class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50" id="camera-modal">
                <div class="bg-white rounded-lg shadow-2xl w-full max-w-4xl mx-4 max-h-[90vh] overflow-hidden">
                    <!-- Modal Header -->
                    <div class="flex justify-between items-center p-4 border-b bg-blue-600 text-white">
                        <h3 class="text-lg font-semibold font-[Poppins]">Take Photo</h3>
                        <button 
                            @click="closeCameraModal" 
                            class="p-2 rounded-full hover:bg-blue-700 transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Camera Preview -->
                    <div class="flex-1 flex items-center justify-center bg-black p-4">
                        <video 
                            ref="videoElement" 
                            autoplay 
                            playsinline
                            muted
                            class="w-full max-w-2xl max-h-[60vh] object-contain rounded-lg"
                        ></video>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="p-4 border-t bg-gray-50">
                        <div class="flex justify-center gap-4">
                            <button 
                                type="button"
                                @click="closeCameraModal"
                                class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors flex items-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Cancel
                            </button>
                            <button 
                                type="button"
                                @click="capturePhoto"
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Capture Photo
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Image Preview Modal -->
            <div v-if="showImagePreviewModal" class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg shadow-2xl w-full max-w-4xl mx-4 max-h-[90vh] overflow-hidden">
                    <!-- Modal Header -->
                    <div class="flex justify-between items-center p-4 border-b bg-blue-600 text-white">
                        <h3 class="text-lg font-semibold font-[Poppins]">
                            {{ capturedImage ? 'Verified Photo' : 'Uploaded Image' }}
                        </h3>
                        <button 
                            @click="closeImagePreview" 
                            class="p-2 rounded-full hover:bg-blue-700 transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Image Preview -->
                    <div class="flex-1 flex items-center justify-center bg-black p-4">
                        <img 
                            :src="imagePreview" 
                            alt="Image Preview" 
                            class="max-w-full max-h-[70vh] object-contain rounded-lg"
                        />
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="p-4 border-t bg-gray-50">
                        <div class="flex justify-center gap-4">
                            <button 
                                type="button"
                                @click="removeImage"
                                class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Remove Image
                            </button>
                            <button 
                                type="button"
                                @click="closeImagePreview"
                                class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors flex items-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Close
                            </button>
                        </div>
                        <div v-if="capturedImage" class="mt-3 text-center">
                            <p class="text-green-600 text-sm font-medium">
                                ✓ This photo includes verification timestamp
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <SuccessfulModal
                :show="showModal"
                :tracking-code="trackingCode"
                @close="showModal = false"
            />
        </main>
    </GuestLayout>
</template>

<!-- pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" -->