<script setup>
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { nextTick, computed, ref, onMounted, onUnmounted } from 'vue';

import GuestLayout from '@/Layouts/GuestLayout.vue';
import ReportMap from '@/Components/ReportMap.vue';

const props = defineProps({ 
    reports: Array, 
    searchTerm: String
})

const trackCode = useForm({
    search: props.searchTerm || '',
})

// Ref for the results section
const resultsSection = ref(null);

// Intersection Observer setup
const observer = ref(null);
const observedElements = ref([]);

// Initialize Intersection Observer
onMounted(() => {
    observer.value = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    // Add a class or perform action when element is visible
                    entry.target.classList.add('is-visible');
                } else {
                    // Remove class when element is not visible
                    entry.target.classList.remove('is-visible');
                }
            });
        },
        {
            root: null, // viewport
            rootMargin: '0px',
            threshold: 0.1 // trigger when 10% of element is visible
        }
    );

    // Observe elements with the data-observe attribute
    nextTick(() => {
        const elements = document.querySelectorAll('[data-observe]');
        elements.forEach((el) => {
            observer.value.observe(el);
            observedElements.value.push(el);
        });
    });
});

// Cleanup observer on unmount
onUnmounted(() => {
    if (observer.value) {
        observedElements.value.forEach((el) => {
            observer.value.unobserve(el);
        });
        observer.value.disconnect();
    }
});

const performSearch = () => {
    const searchTerm = trackCode.search.trim();
    
    if (!searchTerm) {
        clearResults();
        return;
    }

    trackCode.get('/track-reports', {
        preserveState: false, 
        preserveScroll: true,
        replace: true,
        onSuccess: (page) => {
            console.log('Search completed', page.props);
            // Scroll to results after search
            nextTick(() => {
                scrollToResults();
                // Re-observe new elements after search
                const elements = document.querySelectorAll('[data-observe]');
                elements.forEach((el) => {
                    if (!observedElements.value.includes(el)) {
                        observer.value?.observe(el);
                        observedElements.value.push(el);
                    }
                });
            });
        },
        onError: (errors) => {
            console.error('Search failed', errors);
        }
    });
}

const clearResults = () => {
    trackCode.search = '';
    router.get('/track-reports', {}, {
        preserveState: false,
        preserveScroll: true,
        replace: true,
        onSuccess: () => {
            console.log('Results cleared');
            // Scroll to top after clearing results
            nextTick(() => {
                scrollToTop();
                // Re-observe elements after clearing
                const elements = document.querySelectorAll('[data-observe]');
                elements.forEach((el) => {
                    if (!observedElements.value.includes(el)) {
                        observer.value?.observe(el);
                        observedElements.value.push(el);
                    }
                });
            });
        }
    });
}

const clearAndFocusSearch = async () => {
    trackCode.search = '';
    
    await router.get('/track-reports', {}, {
        preserveState: false,
        preserveScroll: true,
        replace: true
    });
    
    await nextTick();
    document.getElementById('default-search')?.focus();
    // Scroll to top when clearing and focusing search
    scrollToTop();
}

// Scroll to results section
const scrollToResults = () => {
    if (resultsSection.value) {
        resultsSection.value.scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
        });
    } else {
        // Fallback: scroll to the results section by ID
        const element = document.getElementById('results-section');
        if (element) {
            element.scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        }
    }
}

// Scroll to top of page
const scrollToTop = () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString()
}

const getStatusClass = (status) => {
    const statusClasses = {
        'pending': 'text-yellow-800 bg-yellow-100 px-2 py-1 rounded font-bold',
        'in progress': 'text-blue-800 bg-blue-100 px-2 py-1 rounded font-bold', 
        'resolved': 'text-green-800 bg-green-100 px-2 py-1 rounded font-bold',
        'rejected': 'text-red-800 bg-red-100 px-2 py-1 rounded font-bold',
        'duplicate': 'text-purple-800 bg-purple-100 px-2 py-1 rounded font-bold '
    }
    return statusClasses[status.toLowerCase()] || 'text-gray-600 bg-gray-100 px-2 py-1 rounded'
}

// Make values reactive using computed
const values = computed(() => {
    const currentReport = props.reports?.data?.[0];
    
    if (!currentReport) {
        return [
            { label: 'Fingerprint', headline: 'Tracking Code:', data: '', img: '/Images/SVG/fingerprint.svg' },
            { label: 'Calendar', headline: 'Submitted At:', data: '', img: '/Images/SVG/calendar.svg' },
            { label: 'Tag', headline: 'Issue Type:', data: '', img: '/Images/SVG/tag.svg' },
            { label: 'Map Pin', headline: 'Location:', data: '', sitio: null, img: '/Images/SVG/map-pin.svg' },
            { label: 'Paper Plane Tilt', headline: 'Submitted By:', data: '', img: '/Images/SVG/paper-plane-tilt.svg' },
            { label: 'Phone', headline: 'Contact Number:', data: '', img: '/Images/SVG/phone.svg' },
            { label: 'Hash', headline: 'Report ID:', data: '', img: '/Images/SVG/hash.svg' },
            // ADD GPS STATUS
            { label: 'GPS', headline: 'Location Data:', data: 'Not captured', img: '/Images/SVG/gps.svg' },
        ];
    }

    return [
        { 
            label: 'Fingerprint', 
            headline: 'Tracking Code:', 
            data: currentReport.tracking_code || '', 
            img: '/Images/SVG/fingerprint.svg' 
        },
        { 
            label: 'Calendar', 
            headline: 'Submitted At:', 
            data: currentReport.created_at ? formatDate(currentReport.created_at) : '', 
            img: '/Images/SVG/calendar.svg' 
        },
        {
            label: 'Tag', 
            headline: 'Issue Type:', 
            data: currentReport.issue_type || '', 
            img: '/Images/SVG/tag.svg'
        },
        { 
            label: 'Map Pin', 
            headline: 'Location:', 
            data: currentReport.barangay_name || '',
            sitio: currentReport.sitio_name || null,
            img: '/Images/SVG/map-pin.svg' 
        },
        { 
            label: 'Paper Plane Tilt', 
            headline: 'Submitted By:', 
            data: currentReport.sender_name || '', 
            img: '/Images/SVG/paper-plane-tilt.svg' 
        },
        { 
            label: 'Phone', 
            headline: 'Contact Number:', 
            data: currentReport.contact_number || '', 
            img: '/Images/SVG/phone.svg' 
        },
        { 
            label: 'Hash', 
            headline: 'Report ID:', 
            data: currentReport.id || '', 
            img: '/Images/SVG/hash.svg' 
        },
        // ADD GPS STATUS
        { 
            label: 'GPS', 
            headline: 'Location Data:', 
            data: currentReport.latitude ? 'Captured ✓' : 'Not captured', 
            img: '/Images/SVG/gps.svg' 
        },
    ];
});
</script>

<template>
    <Head title="My Reports" />

    <GuestLayout>
        <main class="dark:text-[#FAF9F6]">
            <section class="pt-52 lg:pt-0 hero-section min-h-screen text-[#000] px-4 md:px-10 lg:px-32 flex flex-col lg:flex-row items-center" data-observe>
                <div class="w-full lg:w-1/2 flex justify-center items-center py-10 lg:py-0">
                    <div class="max-w-xl w-full">
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold font-[Poppins] mb-5 text-blue-700">Track Your Report</h1>
                        <p class="text-justify text-base sm:text-lg dark:text-[#faf9f6]">Enter your unique tracking code below to check the current status of your submitted report.</p>
                        
                        <div class="mt-6">
                            <form @submit.prevent="performSearch" class="">
                                <label for="default-search" class="mb-2 text-sm font-medium text-gray-900 sr-only dark:text-white">Search</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                        </svg>
                                    </div>
                                    <input 
                                        v-model="trackCode.search" 
                                        type="search" 
                                        id="default-search" 
                                        class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-[#2c2c2c] dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:border-none"
                                        placeholder="Search your tracking code here..." 
                                        :disabled="trackCode.processing"
                                        required 
                                    />
                                    <button 
                                        type="submit"
                                        :disabled="trackCode.processing"
                                        class="text-white absolute end-2.5 bottom-2.5 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 disabled:opacity-50 disabled:cursor-not-allowed">
                                        {{ trackCode.processing ? 'Searching...' : 'Search' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="hidden lg:flex w-full lg:w-1/2 justify-center items-center">
                    <div class="flex justify-center lg:justify-end">
                        <img :src="'/Images/web-search.svg'" alt="Community" class="w-[30rem]">
                    </div>
                </div>
            </section>
            
            <section class="px-4 sm:px-6 md:px-10 lg:px-32 py-10 lg:py-20 flex flex-col gap-8 lg:gap-10">
                <div class="flex justify-between items-center" data-observe>
                    <div class="">
                        <h2 class="text-2xl lg:text-4xl font-bold font-[Poppins] dark:text-white">Track Your Report</h2>
                        <p class="text-sm md:text-base text-gray-500 dark:text-[#FAF9F6]">Check the status of your report</p>
                    </div>
                </div>

                <!-- Result -->
                <section 
                    ref="resultsSection"
                    id="results-section"
                    class="" 
                    v-if="reports && reports.data && reports.data.length > 0"
                >
                    <div class="">
                        <!-- Header with Clear Button -->
                        <div class="flex justify-between items-center mb-4" data-observe>
                            <h2 class="text-lg font-bold font-[Poppins] dark:text-[#faf9f6]">Search Results</h2>
                            <button 
                                @click="clearResults"
                                class="flex items-center gap-2 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Show current search term -->
                        <div class="mb-4 p-3 border border-l-4 border-l-blue-500 rounded-lg bg-white dark:bg-[#2c2c2c] dark:border-none" data-observe>
                            <p class="text-sm dark:text-[#faf9f6]">Showing results for: {{ searchTerm }}</p>
                        </div>
                        
                        <!-- Results List -->
                        <div
                            v-for="report in reports.data"
                            :key="report.id"
                            class="p-4 sm:p-5 border border-gray-200 rounded-lg shadow-sm mb-4 hover:shadow-md transition-shadow duration-200 flex flex-col gap-6 sm:gap-8 bg-white dark:bg-[#2c2c2c] dark:border-none"
                            data-observe
                        >   
                            <div class="flex flex-col sm:flex-row sm:justify-between gap-4">
                                <h1 class="font-bold text-xl sm:text-2xl font-[Poppins] dark:text-[#faf9f6]">Report Summary</h1>

                                <!-- If the status of the report is duplicate -->
                                <div
                                    v-if="report.status === 'Duplicate' && report.duplicate_of_report_id"
                                    class="border-l-4 border-blue-800 bg-gradient-to-r from-blue-200 to-blue-100 w-full sm:w-[40%] p-3 rounded-lg"
                                >
                                    <p class="ml-1 text-sm">
                                        Your report has been consolidated with a similar submission to ensure efficient resolution. All updates will be reflected under the primary case.
                                    </p>
                                </div>

                                <!-- The primary report of the duplicate report -->
                                <div
                                    v-if="report.duplicates && report.duplicates.length > 0"
                                    class="border-l-4 border-blue-800 bg-gradient-to-r from-blue-200 to-blue-100 w-full sm:w-[40%] p-3 rounded-lg"
                                >
                                    <p class="ml-1 text-sm">
                                        Your report is serving as the primary case for this issue. We're addressing similar reports collectively for comprehensive resolution.
                                    </p>
                                </div>  
                            </div>
                            
                            <!--Upper Part-->
                            <div class="flex flex-col lg:flex-row gap-6 lg:gap-10">
                                <div class="w-full lg:w-1/2">
                                    <img 
                                        :src="`/storage/${report.image}`" 
                                        :alt="report.title"
                                        class="w-full h-64 lg:h-80 object-cover rounded-lg"
                                    >
                                </div>
                                
                                <div class="w-full lg:w-1/2">
                                    <div class="mb-4">
                                        <p class="font-bold text-xl sm:text-2xl font-[Poppins] dark:text-[#faf9f6]">{{ report.title }}</p>
                                        <span class="text-sm" :class="getStatusClass(report.status)">{{ report.status }}</span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <template v-for="value in values" :key="value.label">
                                            <!-- Location field with special handling -->
                                            <div v-if="value.label === 'Map Pin'" class="flex flex-col gap-1 py-2">
                                                <div class="flex items-center gap-2">
                                                    <img :src="value.img" alt="Icon" class="w-4 h-4">
                                                    <p class="font-semibold text-base font-[Poppins] dark:text-[#faf9f6]">{{ value.headline }}</p>
                                                </div>
                                                <p class="text-gray-700 ml-6 text-sm dark:text-[#faf9f6]">
                                                    {{ value.data }}
                                                    <span v-if="value.sitio" class="dark:text-[#faf9f6]">, {{ value.sitio }}</span>
                                                    <span v-else class="text-gray-700 text-sm italic">Not Specified</span>
                                                </p>
                                            </div>

                                            <!-- All other fields -->
                                            <div v-else class="flex flex-col gap-1 py-2">
                                                <div class="flex items-center gap-2">
                                                    <img :src="value.img" alt="Icon" class="w-4 h-4">
                                                    <p class="font-semibold text-base font-[Poppins] dark:text-[#faf9f6]">{{ value.headline }}</p>
                                                </div>
                                                <p class="text-gray-700 ml-6 text-sm dark:text-[#faf9f6]">{{ value.data }}</p>
                                            </div>
                                        </template>

                                        <!-- Rejection -->
                                        <div class="flex flex-col gap-1 py-2 sm:col-span-2">
                                            <div class="flex items-center gap-2">
                                                <img :src="'/Images/SVG/warning-circle.svg'" alt="Icon" class="w-4 h-4">
                                                <p class="font-semibold text-base font-[Poppins] dark:text-[#faf9f6]">Rejection Reason</p>
                                            </div>
                                            <p v-if="report.status === 'Rejected' " class="text-gray-700 ml-6 text-sm dark:text-[#faf9f6]">
                                                {{ report.rejection_reason }}
                                            </p>

                                            <p v-else class="text-gray-700 text-sm italic ml-6 dark:text-[#faf9f6]">
                                                This report has not been rejected.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Map -->
                            <div v-if="report.latitude && report.longitude" class="mt-6 lg:mt-10" data-observe>
                                <div class="flex items-center gap-2 mb-3">
                                    <p class="font-semibold text-base font-[Poppins] dark:text-[#faf9f6]">📍 Your Reported Location</p>
                                </div>
                                
                                <ReportMap 
                                    :latitude="report.latitude"
                                    :longitude="report.longitude"
                                    :accuracy="report.gps_accuracy"
                                    :report-location="`${report.barangay_name}, ${report.sitio_name}`"
                                />
                                
                                <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    <p v-if="report.gps_accuracy">
                                        <strong>GPS Accuracy:</strong> {{ report.gps_accuracy.toFixed(0) }} meters
                                    </p>
                                    <p><strong>Coordinates:</strong> {{ report.latitude.toFixed(6) }}, {{ report.longitude.toFixed(6) }}</p>
                                    <p class="text-xs mt-1">This shows the exact location where you submitted your report from.</p>
                                </div>
                            </div>

                            <div v-else class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg dark:bg-blue-900 dark:border-blue-700" data-observe>
                                <div class="flex items-center gap-2">
                                    <img :src="'/Images/SVG/info.svg'" alt="Info" class="w-4 h-4">
                                    <p class="text-blue-700 dark:text-blue-300">ℹ️ No GPS location data was captured for this report.</p>
                                </div>
                            </div>

                            <!--Lower Part-->
                            <div class="flex flex-col lg:flex-row gap-6 lg:gap-10">
                                <div class="w-full lg:w-1/2">
                                    <div class="flex flex-col gap-2">
                                        <div class="flex items-center gap-2">
                                            <img :src="'/Images/SVG/file-text.svg'" alt="Icon" class="w-5 h-5">
                                            <p class="font-semibold text-base font-[Poppins] dark:text-[#faf9f6]">Description</p>
                                        </div>
                                        <div class="h-32 border p-2 rounded overflow-y-auto">
                                            <p class="text-gray-700 text-sm dark:text-[#faf9f6]">{{ report.description }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="w-full lg:w-1/2">
                                    <div class="flex flex-col gap-2">
                                        <div class="flex items-center gap-2">
                                            <img :src="'/Images/SVG/chat-circle-text.svg'" alt="Icon" class="w-5 h-5">
                                            <p class="font-semibold text-base font-[Poppins] dark:text-[#faf9f6]">Remarks</p>
                                        </div>
                                        <div class="border p-2 rounded h-32 overflow-y-auto">
                                            <p v-if="report.remarks !== null" class="text-gray-700 text-sm dark:text-[#faf9f6]">
                                                {{ report.remarks }}
                                            </p>

                                            <p v-else class="text-gray-700 text-sm italic dark:text-[#faf9f6]">
                                                User didn't add any remarks.
                                            </p>
                                        </div>
                                    </div>  
                                </div>
                            </div>
                        </div>

                        <!-- Search Again Suggestion -->
                        <div class="mt-8 lg:mt-10 rounded-lg text-center" data-observe>
                            <p class="text-gray-600 mb-2">Want to search for another report?</p>
                            <button 
                                @click="clearAndFocusSearch"
                                class="text-blue-600 hover:text-blue-800 font-medium underline"
                            >
                                Clear and search again
                            </button>
                        </div>
                    </div>
                </section>
                
                <!-- No Results Message -->
                <div v-else-if="searchTerm && (!reports || !reports.data || reports.data.length === 0)" class="flex justify-center items-center py-20 lg:py-0 lg:h-screen text-center" data-observe>
                    <div class="max-w-md mx-auto">
                        <div class="mb-4">
                            <img :src="'/Images/SVG/not found.svg'" alt="Icon" class="h-32 lg:h-42 mx-auto">
                        </div>
                        <p class="text-gray-500 text-lg mb-2">No reports found for tracking code:</p>
                        <p class="text-xl font-mono font-bold text-red-600 mb-4">"{{ searchTerm }}"</p>
                        <p class="text-gray-400 mb-4">Please check your tracking code and try again.</p>
                        
                        <div class="space-y-2 text-sm text-gray-500">
                            <p><strong>Tips:</strong></p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>Make sure the tracking code is correct (e.g., CW-20250906-001)</li>
                                <li>Check for any typos or missing characters</li>
                                <li>Tracking codes are case-insensitive</li>
                            </ul>
                        </div>
                        
                        <button 
                            @click="clearResults"
                            class="mt-4 text-blue-600 hover:text-blue-800 font-medium underline"
                        >
                            Try a different tracking code
                        </button>
                    </div>
                </div>

                <div v-else class="flex justify-center items-center" data-observe>
                    <p class="text-gray-500 text-xl mb-4 py-20">Track reports now!</p>
                </div>
            </section>
        </main>
    </GuestLayout>
</template>

<style scoped>
/* Add smooth transition for observed elements */
[data-observe] {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s ease, transform 0.6s ease;
}

[data-observe].is-visible {
    opacity: 1;
    transform: translateY(0);
}

/* Staggered animation delays for multiple elements */
[data-observe]:nth-child(1) { transition-delay: 0.1s; }
[data-observe]:nth-child(2) { transition-delay: 0.2s; }
[data-observe]:nth-child(3) { transition-delay: 0.3s; }
[data-observe]:nth-child(4) { transition-delay: 0.4s; }
[data-observe]:nth-child(5) { transition-delay: 0.5s; }
</style>