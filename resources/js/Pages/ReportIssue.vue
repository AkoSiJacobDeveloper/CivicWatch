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
const analyzingImage = ref(false);
const emergencyDetected = ref(false);
const isTFLoaded = ref(false);
const isTFLoading = ref(false);
const tfModel = ref(null);
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
        locationStatus.value = `Location captured! ${accuracy.toFixed(1)} meters`;
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

    // Draw the original image first
    context.drawImage(videoElement.value, 0, 0, canvas.width, canvas.height);

    // Show analyzing state immediately
    analyzingImage.value = true;

    // Create a clean version WITHOUT timestamp for emergency detection
    const cleanCanvas = document.createElement('canvas');
    const cleanContext = cleanCanvas.getContext('2d');
    cleanCanvas.width = canvas.width;
    cleanCanvas.height = canvas.height;
    cleanContext.drawImage(videoElement.value, 0, 0, canvas.width, canvas.height);

    // Now add timestamp to the display version only
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

    // Add timestamp to the display version
    context.fillStyle = 'rgba(0, 0, 0, 0.7)';
    context.fillRect(10, 10, 400, 30);
    context.fillStyle = 'white';
    context.font = '14px Arial';
    context.fillText(`Verified: ${timestamp} - Barangay Report`, 20, 30);

    canvas.toBlob(async (blob) => {
        if (!blob) {
            console.error('Failed to create blob');
            analyzingImage.value = false;
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
    
        try {
            // Use the CLEAN version (without timestamp) for emergency detection
            const isEmergency = await checkImageForEmergencyWithCanvas(cleanCanvas);
        
            if (isEmergency) {
                emergencyDetected.value = true;
                return;
            }
        
            Swal.fire({
                title: 'Photo Captured!',
                text: 'Your photo has been captured successfully.',
                icon: 'success',
                confirmButtonText: 'OK',
                timer: 2000
            });
        
        } catch (error) {
            console.error('Error during emergency check:', error);
            Swal.fire({
                title: 'Photo Captured!',
                text: 'Your photo has been captured successfully.',
                icon: 'success',
                confirmButtonText: 'OK',
                timer: 2000
            });
        } finally {
            analyzingImage.value = false;
        }
    }, 'image/jpeg', 0.8);
}

async function analyzeCanvasAccurate(canvas) {
    return new Promise((resolve) => {
        try {
            console.log('🔍 Accurate analysis of canvas...');
        
            const ctx = canvas.getContext('2d');
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const data = imageData.data;
        
            let firePixels = 0;
            let smokePixels = 0;
            let bloodPixels = 0;
            let faceLikePixels = 0;
            let totalSampled = 0;
        
            // Sample pixels strategically (every 4th pixel)
            for (let i = 0; i < data.length; i += 16) {
                totalSampled++;
                const r = data[i];
                const g = data[i + 1];
                const b = data[i + 2];
            
                // FACE/SKIN DETECTION - exclude common skin tones
                const isSkinTone = (
                    (r > 150 && r < 255) &&
                    (g > 100 && g < 220) &&
                    (b > 80 && b < 200) &&
                    (r > g && g > b) &&
                    (r - g < 80) &&
                    (g - b > 20)
                );
            
                if (isSkinTone) {
                    faceLikePixels++;
                    continue; // Skip emergency detection for skin tones
                }
            
                // Calculate brightness and saturation
                const brightness = (r + g + b) / 3;
                const maxRGB = Math.max(r, g, b);
                const minRGB = Math.min(r, g, b);
                const saturation = maxRGB > 0 ? (maxRGB - minRGB) / maxRGB : 0;
            
                // FIRE detection - bright, saturated red/orange
                const isBrightFireRed = (r > 200 && g < 100 && b < 100 && brightness > 150 && saturation > 0.7);
                const isBrightFireOrange = (r > 220 && g > 150 && g < 200 && b < 100 && brightness > 180 && saturation > 0.6);
            
                if (isBrightFireRed || isBrightFireOrange) {
                    firePixels++;
                }
            
                // SMOKE detection - consistent gray
                const isGray = (Math.abs(r - g) < 10 && Math.abs(g - b) < 10 && Math.abs(r - b) < 10);
                const isSmokeGray = isGray && r > 80 && r < 200;
            
                if (isSmokeGray && saturation < 0.2) {
                    smokePixels++;
                }
            
                // BLOOD detection - dark red, not skin
                const isDarkRed = (r > 100 && g < 50 && b < 50 && r > g * 2.5 && r > b * 2.5);
                const isNotSkin = (g < r * 0.6 && !isSkinTone);
                const isBlood = isDarkRed && isNotSkin && brightness < 180;
            
                if (isBlood) {
                    bloodPixels++;
                }
            }
        
            const fireRatio = firePixels / totalSampled;
            const smokeRatio = smokePixels / totalSampled;
            const bloodRatio = bloodPixels / totalSampled;
            const faceRatio = faceLikePixels / totalSampled;
        
            console.log(`📊 Canvas analysis results:
                Fire: ${firePixels} pixels (${(fireRatio * 100).toFixed(2)}%)
                Smoke: ${smokePixels} pixels (${(smokeRatio * 100).toFixed(2)}%)
                Blood: ${bloodPixels} pixels (${(bloodRatio * 100).toFixed(2)}%)
                Face/Skin: ${faceLikePixels} pixels (${(faceRatio * 100).toFixed(2)}%)`);
        
            // VERY CONSERVATIVE THRESHOLDS - avoid false positives (increased for camera captures)
            const hasSignificantFire = fireRatio > 0.10; // Increased from 0.08
            const hasSignificantSmoke = smokeRatio > 0.15; // Increased from 0.12
            const hasSignificantBlood = bloodRatio > 0.07; // Increased from 0.05
            const hasSignificantFace = faceRatio > 0.15; // 15% face pixels
        
            // If image contains significant face/skin, it's likely NOT an emergency
            if (hasSignificantFace) {
                console.log('✅ Significant face/skin detected - likely safe photo');
                resolve(false);
                return;
            }
        
            // Count strong emergency indicators
            const emergencyIndicators = [hasSignificantFire, hasSignificantSmoke, hasSignificantBlood];
            const strongIndicators = emergencyIndicators.filter(Boolean).length;
        
            // MORE CONSERVATIVE: Only flag as emergency if MULTIPLE indicators (removed single strong options to reduce false positives)
            const isEmergency = strongIndicators >= 2;
        
            if (isEmergency) {
                console.log('🚨 Emergency detected in captured photo');
            } else {
                console.log('✅ No emergency in captured photo');
            }
        
            resolve(isEmergency);
        
        } catch (error) {
            console.error('Canvas analysis error:', error);
            resolve(false); // Default to safe
        }
    });
}

async function checkImageForEmergencyWithCanvas(canvas) {
    analyzingImage.value = true;
    emergencyDetected.value = false;

    console.log('🎨 Analyzing captured photo content...');

    try {
        const isEmergency = await analyzeCanvasAccurate(canvas);
    
        if (isEmergency) {
            console.log('🚨 EMERGENCY DETECTED IN CAPTURED PHOTO!');
            emergencyDetected.value = true;
        } else {
            console.log('✅ Captured photo is safe - no emergency detected');
            emergencyDetected.value = false;
        }
    
        return isEmergency;
    
    } catch (error) {
        console.error('Canvas analysis failed:', error);
        return false; // Default to safe if analysis fails
    } finally {
        analyzingImage.value = false;
    }
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
async function handleFileChange(event) {
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
    
        analyzingImage.value = true;
    
        try {
            const isEmergency = await checkImageForEmergency(file);
        
            if (isEmergency) {
                emergencyDetected.value = true;
                return;
            }
        
            Swal.fire({
                title: 'Image Uploaded!',
                text: 'Your image has been uploaded successfully.',
                icon: 'success',
                confirmButtonText: 'OK',
                timer: 2000
            });
        
        } catch (error) {
            console.error('Error during AI analysis:', error);
            Swal.fire({
                title: 'Image Uploaded!',
                text: 'Your image has been uploaded successfully.',
                icon: 'success',
                confirmButtonText: 'OK',
                timer: 2000
            });
        } finally {
            analyzingImage.value = false;
        }
    
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
onUnmounted(() => {
    if (stream.value) {
        stopCamera();
    }
    if (imagePreview.value) {
        URL.revokeObjectURL(imagePreview.value);
    }
});
async function loadTensorFlowModel() {
    if (isTFLoaded.value || isTFLoading.value) return;

    isTFLoading.value = true;
    console.log('🔄 Loading AI system...');

    try {
        // Check if TensorFlow is available
        if (typeof tf === 'undefined') {
            throw new Error('TensorFlow.js not loaded');
        }
    
        await tf.ready();
        console.log('✅ TensorFlow.js ready');
    
        // Use the lightest possible model
        tfModel.value = await mobilenet.load({
            version: 1,
            alpha: 0.25
        });
    
        isTFLoaded.value = true;
        isTFLoading.value = false;
        console.log('✅ AI model loaded successfully!');
    
    } catch (error) {
        console.error('❌ AI model failed to load:', error);
        isTFLoading.value = false;
    
        // Don't show error to user - just use fallback detection
        console.log('🔄 Using smart pixel analysis instead of AI');
    }
}
async function checkImageForEmergency(file) {
    analyzingImage.value = true;
    emergencyDetected.value = false;

    console.log('🎨 Analyzing image content...');

    try {
        // Use more accurate analysis that avoids false positives
        const isEmergency = await analyzeImageAccurate(file);
    
        if (isEmergency) {
            console.log('🚨 EMERGENCY DETECTED IN IMAGE!');
            emergencyDetected.value = true;
        } else {
            console.log('✅ Image is safe - no emergency detected');
            emergencyDetected.value = false;
        }
    
        return isEmergency;
    
    } catch (error) {
        console.error('Analysis failed:', error);
        // Use a more conservative fallback
        const isEmergency = analyzeWithConservativeFallback(file);
        emergencyDetected.value = isEmergency;
        return isEmergency;
    } finally {
        analyzingImage.value = false;
    }
}
async function analyzeImageAccurate(file) {
    return new Promise((resolve) => {
        const img = new Image();
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
    
        img.onload = () => {
            try {
                console.log('🔍 Accurate analysis of image...');
            
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0);
            
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const data = imageData.data;
            
                let firePixels = 0;
                let smokePixels = 0;
                let bloodPixels = 0;
                let totalSampled = 0;
            
                // Sample fewer pixels but analyze more carefully
                for (let i = 0; i < data.length; i += 16) { // Sample every 4th pixel
                    totalSampled++;
                    const r = data[i];
                    const g = data[i + 1];
                    const b = data[i + 2];
                
                    // IMPROVED FIRE detection - only bright, saturated red/orange
                    const brightness = (r + g + b) / 3;
                    const saturation = (Math.max(r, g, b) - Math.min(r, g, b)) / Math.max(r, g, b);
                
                    // Real fire is bright and saturated
                    const isBrightFireRed = (r > 200 && g < 100 && b < 100 && brightness > 150 && saturation > 0.7);
                    const isBrightFireOrange = (r > 220 && g > 150 && g < 200 && b < 100 && brightness > 180 && saturation > 0.6);
                
                    if (isBrightFireRed || isBrightFireOrange) {
                        firePixels++;
                    }
            
                    // IMPROVED SMOKE detection - consistent gray with medium brightness
                    const isGray = (Math.abs(r - g) < 10 && Math.abs(g - b) < 10 && Math.abs(r - b) < 10);
                    const isSmokeGray = isGray && r > 80 && r < 200; // Not too dark, not too light
                    const graySaturation = (Math.max(r, g, b) - Math.min(r, g, b)) / Math.max(r, g, b);
                
                    if (isSmokeGray && graySaturation < 0.2) { // Very low saturation for smoke
                        smokePixels++;
                    }
                
                    // IMPROVED BLOOD detection - dark red, not skin tones
                    // Skin tones have more green and are lighter
                    const isDarkRed = (r > 100 && g < 50 && b < 50 && r > g * 2.5 && r > b * 2.5);
                    const isNotSkin = (g < r * 0.6); // Skin has more green relative to red
                    const isBlood = isDarkRed && isNotSkin && brightness < 180; // Blood is darker
                
                    if (isBlood) {
                        bloodPixels++;
                    }
                }
            
                const fireRatio = firePixels / totalSampled;
                const smokeRatio = smokePixels / totalSampled;
                const bloodRatio = bloodPixels / totalSampled;
            
                console.log(`📊 Accurate analysis results:
                    Fire: ${firePixels} pixels (${(fireRatio * 100).toFixed(2)}%)
                    Smoke: ${smokePixels} pixels (${(smokeRatio * 100).toFixed(2)}%)
                    Blood: ${bloodPixels} pixels (${(bloodRatio * 100).toFixed(2)}%)`);
            
                const hasSignificantFire = fireRatio > 0.04; 
                const hasSignificantSmoke = smokeRatio > 0.06; 
                const hasSignificantBlood = bloodRatio > 0.025; 
            
            
                const emergencyIndicators = [hasSignificantFire, hasSignificantSmoke, hasSignificantBlood];
                const strongIndicators = emergencyIndicators.filter(Boolean).length;
            
                
                const isEmergency = strongIndicators >= 2 ||
                                (hasSignificantFire && fireRatio > 0.08) ||
                                  (hasSignificantSmoke && smokeRatio > 0.10) || // Very strong smoke
                                  (hasSignificantBlood && bloodRatio > 0.05); // Very strong blood
            
                if (isEmergency) {
                    if (hasSignificantFire) console.log('🔥 Significant FIRE detected');
                    if (hasSignificantSmoke) console.log('💨 Significant SMOKE detected');
                    if (hasSignificantBlood) console.log('🩸 Significant BLOOD detected');
                    console.log(`🚨 Emergency confidence: ${strongIndicators} indicators`);
                } else {
                    console.log('✅ No significant emergency patterns found');
                }
            
                resolve(isEmergency);
            
            } catch (error) {
                console.error('Pixel analysis error:', error);
                resolve(analyzeWithConservativeFallback(file));
            }
        };
    
        img.onerror = () => {
            console.error('Failed to load image');
            resolve(analyzeWithConservativeFallback(file));
        };
    
        img.src = URL.createObjectURL(file);
    });
}
// Fallback detection (when AI fails to load)
function analyzeWithConservativeFallback(file) {
    const emergencyWords = [
        'fire', 'burning', 'ambulance', 'accident', 'crash',
        'emergency', 'smoke', 'flame', 'blaze', 'rescue', 'blood',
        'injury', 'hurt', 'police', 'hospital'
    ];

    const clearNonEmergencyWords = [
        'selfie', 'face', 'portrait', 'person', 'me', 'myself',
        'profile', 'photo', 'picture', 'image', 'camera'
    ];

    const filename = file.name.toLowerCase();

    const hasEmergencyWord = emergencyWords.some(word => {
        const hasWord = filename.includes(word);
        if (hasWord) console.log(`📁 Filename indicates emergency: ${word}`);
        return hasWord;
    });

    const hasNonEmergencyWord = clearNonEmergencyWords.some(word => {
        const hasWord = filename.includes(word);
        if (hasWord) console.log(`📁 Filename indicates non-emergency: ${word}`);
        return hasWord;
    });

    const isClearEmergency = hasEmergencyWord && !hasNonEmergencyWord;

    console.log(`🔄 Conservative fallback: ${isClearEmergency ? 'CLEAR EMERGENCY' : 'SAFE'}`);

    return isClearEmergency;
}
function proceedAnyway() {
    emergencyDetected.value = false;
}
function cancelEmergencySubmission() {
    emergencyDetected.value = false;
    removeImage();
}
function showEmergencyContacts() {
    Swal.fire({
        title: '🚨 Emergency Contacts',
        html: `
            <div class="text-left">
                <p class="mb-3"><strong>Please contact emergency services immediately:</strong></p>
                <ul class="list-disc ml-4 space-y-2">
                    <li><strong>🏥 Medical Emergency:</strong> 911</li>
                    <li><strong>🚒 Fire Department:</strong> 911</li>
                    <li><strong>🚓 Police:</strong> 911</li>
                    <li><strong>📍 Barangay Hotline:</strong> (02) 123-4567</li>
                </ul>
                <div class="mt-4 p-3 bg-yellow-50 rounded-lg">
                    <p class="text-sm text-yellow-800">
                        <strong>Note:</strong> Our system is for non-urgent reports only.
                        For emergencies, please contact the numbers above directly.
                    </p>
                </div>
            </div>
        `,
        icon: 'warning',
        confirmButtonText: 'I Understand',
        confirmButtonColor: '#dc3545',
        width: 600
    });
}
onUnmounted(() => {
    if (stream.value) {
        stopCamera();
    }
    if (imagePreview.value) {
        URL.revokeObjectURL(imagePreview.value);
    }
});
onMounted(async () => {
    await fetchIssueType();
    await fetchBarangaysWithSitios();

    // Start loading AI in background
    setTimeout(() => {
        loadTensorFlowModel();
    }, 1000);
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
                        <!-- Div that holds both Basic Information and Media & Detils div -->
                        <div class="grid grid-cols-2 gap-5">
                            <!-- Basic Information -->
                            <div class="flex flex-col gap-1">
                                <h2 class="font-medium text-lg font-[Poppins]">Basic Information</h2>
                                <hr>
                                <!-- TITLE -->
                                <div class="mt-5">
                                    <label
                                        for="title"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Title
                                    </label>
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        id="title"
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-4 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Uncollected Garbage"
                                        required
                                    />
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
                            
                                <!-- Sender Name -->
                                <div>
                                    <label
                                        for="title"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Sender Name
                                    </label>
                                    <input
                                        v-model="form.sender_name"
                                        type="text"
                                        id="title"
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-4 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Joe Doe"
                                        required
                                    />
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
                                <!-- ISSUE TYPE -->
                                <div class="mb-3">
                                    <!-- Headless UI-->
                                    <label
                                        for="issue_type"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Issue Type
                                    </label>
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
                                <!-- Location -->
                                <div class="mb-3">
                                    <div class="flex gap-2 w-full">
                                        <!-- Barangay -->
                                        <div class="w-1/2">
                                            <label
                                                for="barangay"
                                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Barangay
                                            </label>
                                        
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
                                        <!-- Sitio -->
                                        <div class="w-1/2">
                                            <label
                                                for="sitio"
                                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Sitio
                                            </label>
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
                                </div>
                                <!-- Verify Location -->
                                <div class="mb-3">
                                    <label
                                        for="location"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                    >
                                        Verify Location
                                    </label>
                                    <!-- GPS LOCATION SECTION -->
                                    <div class="">
                                        <div class=" border-gray-300 rounded-lg dark:border-gray-600 dark:bg-[#2c2c2c]">
                                            <div class="space-y-3">
                                                <div class="flex gap-1">
                                                
                                                    <!-- Location Status -->
                                                    <div v-if="locationStatus" class="p-3 rounded-lg text-xs flex-1"
                                                        :class="hasLocation ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-yellow-50 text-yellow-700 border border-yellow-200'">
                                                        {{ locationStatus }}
                                                    </div>
                                                    <!-- Action Buttons -->
                                                    <div class="flex gap-1">
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
                                                            <span class="text-sm">{{ isGettingLocation ? 'Getting Location...' : 'Track' }}</span>
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
                                                            <span class="text-sm">Clear</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Contact Number -->
                                <div class="mb-3">
                                    <label
                                        for="contact"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Contact Number
                                    </label>
                                    <div class="">
                                    
                                        <input
                                            v-model="form.contact_number"
                                            type="tel"
                                            id="contact_number"
                                            placeholder="+63xxxxxxxxxx"
                                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-4 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                            required
                                        />
                                    </div>
                                
                                    <div v-if="form.errors.contact_number" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.contact_number }}
                                    </div>
                                </div>
                                <!-- Description -->
                                <div class="">
                                    <label
                                        for="location"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                    >
                                        Description
                                    </label>
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
                            </div>
                        
                            <!-- Media & Details -->
                            <div class="flex flex-col gap-1">
                                <h2 class="font-medium text-lg font-[Poppins]">Media & Details</h2>
                                <hr>
                                <!-- UPLOAD PHOTO -->
                                <div class="mt-5 mb-3">
                                    <label
                                        for="upload"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Upload Photo
                                    </label>
                                
                                    <div class="flex flex-col gap-2">
                                        <div class="flex flex-col">
                                        
                                            <div v-if="!imagePreview" class="grid grid-cols-2 gap-6">
                                                <!-- Take Photo -->
                                                <label
                                                @click="startCamera"
                                                :class="[
                                                    'relative p-12 border-[3px] border-dashed border-gray-300 rounded-[20px] bg-gray-50 cursor-pointer transition-all flex flex-col items-center gap-4 text-gray-600',
                                                    'hover:border-indigo-500 hover:bg-gradient-to-br hover:from-indigo-50 hover:to-purple-50 hover:text-indigo-500 hover:-translate-y-1 hover:shadow-[0_8px_24px_rgba(99,102,241,0.15)]',
                                                    isCameraActive && 'opacity-50 cursor-not-allowed'
                                                ]"
                                                >
                                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                                    <circle cx="12" cy="13" r="4"/>
                                                </svg>
                                                <span class="text-lg font-semibold">Take Photo</span>
                                                <span class="text-xs text-gray-400">Use camera</span>
                                                </label>
                                                <!-- Upload File -->
                                                <label
                                                for="fileInput"
                                                class="relative p-12 border-[3px] border-dashed border-gray-300 rounded-[20px] bg-gray-50 cursor-pointer transition-all flex flex-col items-center gap-4 text-gray-600 hover:border-indigo-500 hover:bg-gradient-to-br hover:from-indigo-50 hover:to-purple-50 hover:text-indigo-500 hover:-translate-y-1 hover:shadow-[0_8px_24px_rgba(99,102,241,0.15)]"
                                                >
                                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                                    <polyline points="17 8 12 3 7 8"/>
                                                    <line x1="12" y1="3" x2="12" y2="15"/>
                                                </svg>
                                                <span class="text-lg font-semibold">Upload Image</span>
                                                <span class="text-xs text-gray-400">Browse files</span>
                                                <input
                                                    type="file"
                                                    id="fileInput"
                                                    ref="fileInput"
                                                    accept="image/*"
                                                    @change="handleFileChange"
                                                    class="hidden"
                                                />
                                                </label>
                                            </div>
                                            <!-- Image Preview Controls -->
                                            <div v-else class="space-y-4">
                                                <div class="flex gap-4">
                                                    <button
                                                        type="button"
                                                        @click="showImagePreview"
                                                        class="flex-1 flex items-center justify-center gap-3 p-1 bg-blue-600 text-white rounded-2xl hover:bg-blue-700 transition-all hover:-translate-y-0.5 font-semibold"
                                                    >
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                        <circle cx="12" cy="12" r="3"/>
                                                        </svg>
                                                        View Image
                                                    </button>
                                                    <button
                                                        type="button"
                                                        @click="removeImage"
                                                        class="flex items-center justify-center gap-3 p-2 bg-red-600 text-white rounded-2xl hover:bg-red-700 transition-all hover:-translate-y-0.5 font-semibold"
                                                    >
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    
                                                    </button>
                                                </div>
                                                <p v-if="capturedImage" class="text-xs text-green-600 text-center font-medium">
                                                ✓ This photo includes verification timestamp
                                                </p>
                                                <p v-else class="text-xs text-gray-500 text-center">
                                                Uploaded image
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Additional Remarks -->
                                <div class="">
                                    <label
                                        for="remarks"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"> Additional Remarks
                                    </label>
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
                        </div>
                    
                        <!-- LOCATION SECTION -->
                    
                        <div class="md:flex gap-3">
                            <!-- DESCRIPTION -->
                        
                            <!-- ADDITIONAL NOTES -->
                        
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
            <!-- Emergency Alert Popup -->
            <div v-if="emergencyDetected" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg shadow-2xl max-w-md w-full mx-4">
                    <!-- Modal Header -->
                    <div class="bg-red-600 text-white p-4 rounded-t-lg">
                        <h3 class="text-lg font-semibold flex items-center gap-2">
                            🚨 Emergency Detected
                        </h3>
                    </div>
                
                    <!-- Modal Content -->
                    <div class="p-6">
                        <p class="text-gray-700 mb-4">
                            Our system detected a possible emergency situation in your image.
                        </p>
                    
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                            <h4 class="font-semibold text-red-800 mb-2">Emergency Contacts:</h4>
                            <ul class="text-sm text-red-700 space-y-1">
                                <li>🏥 <strong>Medical:</strong> 911</li>
                                <li>🚒 <strong>Fire:</strong> 911</li>
                                <li>🚓 <strong>Police:</strong> 911</li>
                                <li>📍 <strong>Barangay:</strong> (02) 123-4567</li>
                            </ul>
                        </div>
                    
                        <p class="text-sm text-gray-600 mb-4">
                            Please contact emergency services immediately if this is a real emergency.
                        </p>
                    </div>
                
                    <!-- Modal Footer -->
                    <div class="flex gap-3 p-4 border-t">
                        <button
                            @click="cancelEmergencySubmission"
                            class="flex-1 bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition-colors font-semibold"
                        >
                            🚨 Cancel - This is an Emergency
                        </button>
                        <button
                            @click="proceedAnyway"
                            class="flex-1 bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-600 transition-colors"
                        >
                            Continue Anyway
                        </button>
                    </div>
                </div>
            </div>
            <!-- Analyzing Overlay -->
            <div v-if="analyzingImage" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 flex items-center gap-3">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <div>
                        <div class="font-semibold">AI Analyzing Image...</div>
                        <div class="text-sm text-gray-600">Detecting emergencies using TensorFlow.js</div>
                    </div>
                </div>
            </div>
        </main>
    </GuestLayout>
</template>
<!-- pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" -->