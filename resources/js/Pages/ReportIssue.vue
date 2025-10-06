<script setup>
import { Head, router, usePage, useForm } from '@inertiajs/vue3';
import { ref, nextTick, onMounted, computed } from 'vue';
import { Listbox, ListboxButton, ListboxOption, ListboxOptions } from '@headlessui/vue';
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
});

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
    } else {
        if (imagePreview.value) {
            URL.revokeObjectURL(imagePreview.value);
        }
        imagePreview.value = null;
        form.image = null;
    }
}

// Reset form
async function resetForm() {
    form.reset();
    customIssueDescription.value = '';
    
    if (imagePreview.value) {
        URL.revokeObjectURL(imagePreview.value);
        imagePreview.value = null;
    }
    
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

    if (!form.barangay_id) {
        alert('Please select a Barangay');
        return;
    }

    if (availableSitios.value.length > 0 && !form.sitio_id) {
        alert('Please select a Sitio');
        return;
    }

    isSubmitting.value = true; // Set loading state to true

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
            isSubmitting.value = false; // Reset loading state
            resetForm();
        },
        onError: (errors) => {
            isSubmitting.value = false; // Reset loading state on error
            if (errors) {
                Object.keys(errors).forEach(key => {
                    form.errors[key] = errors[key];
                });
            }
        },
        onFinish: () => {
            isSubmitting.value = false; // Ensure loading state is reset
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
});
</script>

<template>
    <Head title="Report Issue" />

    <GuestLayout>
        <main class="pt-[80px] md:pt-[120px] dark:text-[#FAF9F6] ">
            <section class="px-3 md:px-10 lg:px-32">
                <div>
                    <h1 class="font-bold text-lg md:text-3xl font-[Poppins]">Submit New Report</h1>
                    <p class="text-sm text-gray-500 dark:text-[#FAF9F6]">Provide details about a local issue you've observed. Include a description, location, and a photo if possible to help your barangay officials respond effectively and quickly.</p>
                </div>

                <!-- NOTICE -->
                <div v-if="barangays.filter(b => !b.is_available).length > 0" class="mt-5 p-3 bg-yellow-50 border border-yellow-200 rounded-md dark:bg-yellow-900 dark:border-yellow-700 dark:text-yellow-100">
                    <p class="text-xs md:text-sm">
                        <strong>Notice:</strong> We're currently piloting in select barangays only. Other locations coming soon!
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
                        <div class="mb-8 flex flex-col gap-3">
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

                                            <!-- Coming Soon Group -->
                                            <div v-if="barangays.some(b => !b.is_available)" class="px-4 py-2 text-gray-500 text-sm font-semibold">
                                                Coming Soon
                                            </div>
                                            <ListboxOption
                                                v-for="barangay in barangays.filter(b => !b.is_available)"
                                                :key="barangay.id"
                                                :value="null"
                                                disabled
                                                class="px-4 py-2 text-gray-400 cursor-not-allowed dark:text-gray-500"
                                            >
                                                {{ barangay.name }} (Not Available)
                                            </ListboxOption>
                                        </ListboxOptions>
                                    </Listbox>
                                    <div v-if="form.errors.barangay_id" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.barangay_id }}
                                    </div>
                                </div>

                                <!-- SITIO -->
                                <div 
                                    class="md:w-1/2"
                                    v-if="form.barangay_id && availableSitios.length > 0"
                                >
                                    <Listbox 
                                        v-model="form.sitio_id"
                                        as="div"
                                        class="relative w-full"
                                    >
                                        <ListboxButton
                                            class="flex justify-between items-center text-left p-4 w-full text-base bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline-none focus:ring-0 focus:border-gray-200 peer dark:bg-[#2c2c2c]"
                                            required
                                        >
                                            {{ availableSitios.find(s => s.id === form.sitio_id)?.name || 'Select Sitio' }}

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
                                                v-for="sitio in availableSitios"
                                                :key="sitio.id"
                                                :value="sitio.id"
                                                class="px-4 py-2 hover:bg-blue-100 transition-all duration-100 cursor-pointer dark:hover:bg-gray-700"
                                            >
                                                {{ sitio.name }}
                                            </ListboxOption>
                                        </ListboxOptions>
                                    </Listbox>
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

                        <div class="md:flex gap-3">
                            <!-- UPLOAD PHOTO -->
                            <div class="mb-4 md:w-1/2 md:mb-0">
                                <label class="block text-sm font-semibold font-[Poppins] mb-1" for="review_message">Upload Photo</label>
                                <input
                                    type="file"
                                    id="image"
                                    accept="image/*"
                                    @change="handleFileChange"
                                    key="file-input"
                                    class="block w-full p-2 text-sm text-gray-700 border border-gray-300 rounded-lg cursor-pointer 
                                        bg-white dark:text-gray-300 focus:outline-none 
                                        file:mr-4 file:py-2 file:px-4 
                                        file:rounded-md file:border-0 
                                        file:text-sm file:font-medium 
                                        file:bg-blue-600 file:text-white 
                                        hover:file:bg-blue-700 
                                        dark:bg-[#2c2c2c] dark:border-gray-600 dark:placeholder-gray-400"
                                />

                                <div v-if="imagePreview" class="mt-3">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Image Preview:</p>
                                    <img :src="imagePreview" alt="Image Preview" class="max-w-[200px] mt-2 rounded shadow">
                                </div>

                                <div v-if="form.errors.image" class="text-red-500 text-sm mt-2">
                                    {{ form.errors.image }}
                                </div>
                            </div>

                            <!-- CONTACT NUMBER -->
                            <div class="mb-4 md:w-1/2">
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
                        </div>

                        <div class="md:flex gap-3">
                            <!-- DESCRIPTION -->
                            <div class="mb-4 md:w-1/2 md:mb-0">
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
                            <div class="mb-4 md:w-1/2 md:mb-0">
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
            <SuccessfulModal
                :show="showModal"
                :tracking-code="trackingCode"
                @close="showModal = false"
            />
        </main>
    </GuestLayout>
</template>

<!-- pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" -->