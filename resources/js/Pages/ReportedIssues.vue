<script setup>
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, watch, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { debounce } from 'lodash';
import { onClickOutside } from '@vueuse/core'

import GuestLayout from '@/Layouts/GuestLayout.vue';

const props = defineProps({
    reports: Object,
    filters: Object,
    sitios: Array,
});

const sortOrder = ref(props.filters.sort || 'desc');
const search = ref(props.filters.search || '');
const selectedSitio = ref(props.filters.sitio || '');
const dropdownOpen = ref(false); 
const dropdownButtonText = computed(() => {
    return selectedSitio.value || 'All Sitios';
});

const dropdownRef = ref(null)
const contentKey = ref(0);

// Polling setup
let pollInterval = null;
const previousReportsCount = ref(props.reports.total || 0);
const currentReportIds = ref(new Set());
const newReportIds = ref(new Map()); // Changed to Map to store timestamps

// Intersection Observer setup
const observer = ref(null);
const observedElements = ref([]);

// Storage functions for persistence
const getStoredNewReports = () => {
    try {
        const stored = localStorage.getItem('newReportIds');
        return stored ? new Map(Object.entries(JSON.parse(stored))) : new Map();
    } catch {
        return new Map();
    }
};

const setStoredNewReports = (reportIdsMap) => {
    try {
        localStorage.setItem('newReportIds', JSON.stringify(Object.fromEntries(reportIdsMap)));
    } catch (error) {
        console.error('Failed to store new reports:', error);
    }
};

// Function to clean up old new reports (older than 24 hours)
const cleanOldNewReports = () => {
    const oneDayAgo = Date.now() - (24 * 60 * 60 * 1000);
    for (const [id, timestamp] of newReportIds.value.entries()) {
        if (timestamp < oneDayAgo) {
            newReportIds.value.delete(id);
        }
    }
    setStoredNewReports(newReportIds.value);
};

const isNewReport = (reportId) => {
    return newReportIds.value.has(reportId);
};

onClickOutside(dropdownRef, () => {
    dropdownOpen.value = false
})

watch(() => props.filters, (newFilters) => {
    search.value = newFilters.search || '';
    selectedSitio.value = newFilters.sitio || '';
    sortOrder.value = newFilters.sort || 'desc';
}, { deep: true, immediate: false });

watch(() => props.reports, () => {
    contentKey.value++;
}, { deep: true });

const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value;
};

const selectSitio = (sitio) => {
    selectedSitio.value = sitio;
    dropdownOpen.value = false;
    
    // Trigger search immediately when sitio is selected
    performSearch();
};

const performSearch = debounce(() => {
    router.get(route('user.get.reportedIssues'), {
        search: search.value,
        sitio: selectedSitio.value,
        sort: sortOrder.value
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 300);

watch([search, selectedSitio], () => {
    performSearch();
});

const pollForNewReports = () => {
    router.reload({
        preserveState: true,
        preserveScroll: true,
        only: ['reports'], 
        onSuccess: (page) => {
            // ✅ Add safety checks for undefined values
            if (!page?.props?.reports) {
                console.warn('Reports data not available');
                return;
            }

            const currentReportsCount = page.props.reports.total || 0;

            if (currentReportsCount > previousReportsCount.value) {
                // ✅ Check if data array exists
                if (page.props.reports.data && Array.isArray(page.props.reports.data)) {
                    page.props.reports.data.forEach(report => {
                        if (!currentReportIds.value.has(report.id)) {
                            // Store with timestamp
                            newReportIds.value.set(report.id, Date.now());
                            currentReportIds.value.add(report.id);
                        }
                    });
                    
                    // Persist to localStorage
                    setStoredNewReports(newReportIds.value);
                }
                
                previousReportsCount.value = currentReportsCount;
            }

            // Re-observe elements after reload
            nextTick(() => {
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
            console.error('Error polling for new reports:', errors);
        }
    });
};

const refreshData = () => {
    // Keep filters but reload data
    pollForNewReports();
};

const toggleSort = () => {
    sortOrder.value = sortOrder.value === 'desc' ? 'asc' : 'desc';
    
    router.get(route('user.get.reportedIssues'), {
        search: search.value,
        sitio: selectedSitio.value, 
        sort: sortOrder.value
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    search.value = '';
    selectedSitio.value = ''; 
};

const hasActiveFilters = computed(() => {
    return search.value || selectedSitio.value; 
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

const getStatusTimeline = (report) => {
    const timeline = [];
    
    timeline.push({
        status: 'Reported',
        date: report.created_at,
        icon: '📝'
    });
    
    // Approved date
    if (report.approved_at) {
        timeline.push({
            status: 'Approved',
            date: report.approved_at,
            icon: '✅'
        });
    }
    
    // Rejected date
    if (report.rejected_at) {
        timeline.push({
            status: 'Rejected',
            date: report.rejected_at,
            icon: '❌'
        });
    }
    
    // Resolved date
    if (report.resolved_at) {
        timeline.push({
            status: 'Resolved',
            date: report.resolved_at,
            icon: '🏁'
        });
    }

    // Duplicate
    if (report.duplicate_at) {
        timeline.push({
            status: 'Duplicate',
            date: report.duplicate_at,
            icon: '📋'
        })
    }
    
    return timeline;
}

const displayName = (report) => {
    if (report.is_anonymous === 0 || report.is_anonymous === false) {
        return 'Anonymous User';
    }
    return report.sender_name;
};

onMounted(() => {
    // Load persisted new reports from localStorage
    newReportIds.value = getStoredNewReports();
    
    // Clean up old new reports (older than 24 hours)
    cleanOldNewReports();
    
    // ✅ Add safety check for reports.data
    if (props.reports && props.reports.data && Array.isArray(props.reports.data)) {
        // Initialize current report IDs
        props.reports.data.forEach(report => {
            currentReportIds.value.add(report.id);
        });
    }
    
    // Initialize Intersection Observer
    observer.value = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                } else {
                    entry.target.classList.remove('is-visible');
                }
            });
        },
        {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        }
    );

    // Observe elements with the data-observe attribute
    nextTick(() => {
        const elements = document.querySelectorAll('[data-observe]');
        elements.forEach((el) => {
            observer.value.observe(el);
            observedElements.value.push(el);
        });
        contentKey.value++;
    });
});

onUnmounted(() => {
    // Cleanup observer
    if (observer.value) {
        observedElements.value.forEach((el) => {
            observer.value.unobserve(el);
        });
        observer.value.disconnect();
    }
});
</script>

<template>
    <Head title="Reported Issues" />

    <GuestLayout>
        <main class="dark:text-[#FAF9F6]">
            <section class="pt-52 lg:pt-0 hero-section min-h-screen text-[#000] px-4 md:px-10 lg:px-32 flex flex-col lg:flex-row items-center animate-fade-in-up">
                <div class="w-full lg:w-1/2 flex justify-center items-center py-10 lg:py-0">
                    <div class="">
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold font-[Poppins] mb-5 text-blue-700">Tracking Every Issue in Your Neighborhood</h1>
                        <p class="text-justify text-base sm:text-lg dark:text-[#faf9f6]">See the issues residents have reported and how our platform helps track and resolve them. From small fixes to community improvements, stay informed on local problems and solutions.</p>
                    </div>
                </div>
                <div class="hidden lg:flex w-full lg:w-1/2 justify-center items-center">
                    <div class="flex justify-center lg:justify-end">
                        <img :src="'/Images/reported-issues.svg'" alt="Community" class="w-[30rem]">
                    </div>
                </div>
            </section>

            <!-- Reported Issue Section -->
            <section class="px-4 sm:px-6 md:px-10 lg:px-32 py-10 lg:py-20 flex flex-col relative">
                <!-- Header and Search Section -->
                <div class="flex flex-col lg:flex-row justify-between items-start gap-4 mb-8 animate-fade-in-up relative z-50" :style="{ animationDelay: '0.1s' }">
                    <div class="w-full lg:w-1/2 mb-4 lg:mb-0">
                        <h2 class="text-2xl lg:text-4xl font-bold font-[Poppins] dark:text-white">Reported Issues</h2>
                        <p class="text-sm md:text-base text-gray-500 dark:text-[#FAF9F6]">See what issues are already reported in your neighborhood</p>
                    </div>

                    <!-- Search with sitio dropdown -->
                    <div class="w-full lg:w-1/2 animate-fade-in-up" :style="{ animationDelay: '0.2s' }">
                        <form @submit.prevent="performSearch" class="w-full">
                            <div class="flex flex-col sm:flex-row relative" ref="dropdownRef">
                                <!-- Dropdown Button -->
                                <button 
                                    id="dropdown-button" 
                                    @click="toggleDropdown"
                                    type="button"
                                    class="shrink-0 inline-flex items-center py-3 px-4 text-sm font-medium text-center text-gray-900 bg-gray-100 border border-gray-300 rounded-t-lg sm:rounded-s-lg sm:rounded-t-none hover:bg-gray-200 focus:ring-4 focus:outline-none focus:ring-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-700 dark:text-white dark:border-gray-600"
                                >
                                    {{ dropdownButtonText }}
                                    <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                                    </svg>
                                </button>
                                
                                <!-- Dropdown Menu -->
                                <div 
                                    v-show="dropdownOpen"
                                    class="absolute top-full mt-1 left-0 right-0 sm:left-auto sm:right-auto bg-white divide-y divide-gray-100 rounded-lg shadow-xl w-full sm:w-44 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 z-[100]"
                                >
                                    <ul class="py-2 text-sm text-gray-700 dark:text-gray-200">
                                        <li>
                                            <button 
                                                type="button" 
                                                @click="selectSitio('')"
                                                class="inline-flex text-xs w-full px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white text-left"
                                                :class="{ 'bg-blue-50 dark:bg-blue-900': selectedSitio === '' }"
                                            >
                                                All Sitios
                                            </button>
                                        </li>
                                        <li v-for="sitio in sitios" :key="sitio">
                                            <button 
                                                type="button" 
                                                @click="selectSitio(sitio)"
                                                class="inline-flex w-full text-xs px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white text-left"
                                                :class="{ 'bg-blue-50 dark:bg-blue-900': selectedSitio === sitio }"
                                            >
                                                {{ sitio }}
                                            </button>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Search Input -->
                                <div class="relative w-full">
                                    <input 
                                        type="search" 
                                        v-model="search"
                                        class="block p-3 w-full text-sm text-gray-900 bg-gray-50 rounded-b-lg sm:rounded-e-lg sm:rounded-b-none border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:border-blue-500" 
                                        placeholder="Search by title, description, or issue type..." 
                                    />
                                    <button 
                                        type="submit" 
                                        class="absolute top-0 end-0 p-3 text-sm font-medium h-full text-white bg-blue-700 rounded-e-lg border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                                    >
                                        <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                        </svg>
                                        <span class="sr-only">Search</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Refresh Button and Sort Button -->
                <div class="flex items-center justify-end gap-3 mb-8 animate-fade-in-up relative z-40" :style="{ animationDelay: '0.3s' }">
                    <button
                        @click="refreshData"
                        class="border p-2 sm:p-3 rounded flex items-center gap-1 sm:gap-2 hover:bg-green-500 hover:text-white transition-colors group duration-300 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-green-600 text-sm sm:text-base"
                    >
                        <span class="hidden sm:inline">Refresh</span>
                        <span class="sm:hidden">Refresh</span>
                        <img 
                            :src="'/Images/SVG/arrows-clockwise.svg'" 
                            alt="Icon" 
                            class="h-5 w-5 group-hover:hidden dark:invert"
                        >
                        <img 
                            :src="'/Images/SVG/arrows-clockwise (white).svg'" 
                            alt="Icon" 
                            class="h-5 w-5 hidden group-hover:block group-hover:animate-spin"
                        >
                    </button>
                    
                    <button
                        @click="toggleSort"
                        class="border p-2 sm:p-3 rounded flex items-center gap-2 hover:bg-blue-500 hover:text-white dark:hover:bg-gray-700 transition-colors group duration-300 text-sm sm:text-base dark:border-gray-600"
                    >
                        <span class="hidden sm:inline">{{ sortOrder === 'desc' ? 'Newest' : 'Oldest' }}</span>
                        <span class="sm:hidden">{{ sortOrder === 'desc' ? 'Newest' : 'Oldest' }}</span>
                        <svg 
                            xmlns="http://www.w3.org/2000/svg" 
                            width="16" 
                            height="16" 
                            fill="currentColor" 
                            viewBox="0 0 256 256"
                            :class="sortOrder === 'desc' ? 'rotate-0' : 'rotate-180'"
                            class="transition-transform duration-300 group-hover:scale-110"
                        >
                            <path d="M213.66,165.66a8,8,0,0,1-11.32,0L128,91.31,53.66,165.66a8,8,0,0,1-11.32-11.32l80-80a8,8,0,0,1,11.32,0l80,80A8,8,0,0,1,213.66,165.66Z"></path>
                        </svg>
                    </button>
                </div>

                <div v-if="!reports.data || reports.data.length === 0" class="text-center text-gray-500 animate-fade-in-up py-20" :style="{ animationDelay: '0.4s' }">
                    <p class="text-lg sm:text-xl mb-4">No reports found.</p>
                </div>

                <!-- Reports List -->
                <div class="grid grid-cols-1 gap-5 relative z-0" :key="contentKey">
                    <div 
                        v-for="(report, index) in reports.data"
                        :key="report.id"
                        class="animate-fade-in-up"
                        :style="{ animationDelay: `${0.5 + (index * 0.1)}s` }"
                    >
                        <!-- Mobile Layout -->
                        <div class="block lg:hidden border p-4 bg-white rounded-lg shadow-md dark:bg-[#2c2c2c] dark:border-none">
                            <!-- Image Section -->
                            <div class="w-full h-48 mb-4 overflow-hidden rounded">
                                <img :src="`/storage/${report.image}`" alt="Report Image" class="w-full h-full object-cover">
                            </div>
                            
                            <!-- Content Section -->
                            <div class="space-y-4 ">
                                <!-- Header -->
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h1 class="font-medium text-lg font-[Poppins] mb-2">Report Details</h1>
                                        <div class="flex justify-between items-start mb-2">
                                            <p class="font-medium text-sm">{{ displayName(report) }}</p>
                                            <span
                                                :class="[
                                                    'text-center border rounded-full font-semibold px-2 py-1 text-xs capitalize',
                                                    report.status === 'Pending' ? 'bg-amber-100 text-amber-800 border-amber-200' :
                                                    report.status === 'In Progress' ? 'bg-blue-100 text-[#3B82F6] border-blue-200' :
                                                    report.status === 'Resolved' ? 'bg-green-100 text-[#16A34A] border-green-200' :
                                                    report.status === 'Rejected' ? 'bg-red-100 text-[#EF4444] border-red-200' :
                                                    report.status === 'Duplicate' ? 'bg-purple-100 text-purple-800 border-purple-200' : ''
                                                ]" 
                                            >
                                                {{ report.status }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 mb-1">{{ report.barangay_name }} - {{ report.sitio_name }}</p>
                                        <p class="text-xs text-gray-500">ID: {{ report.id }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ formatDate(report.created_at) }}</p>
                                    </div>
                                </div>

                                <hr>

                                <!-- Description -->
                                <div>
                                    <p class="font-medium text-sm mb-2">Description</p>
                                    <div class="bg-blue-50 border-l-4 border-blue-600 p-2 rounded-md">
                                        <p class="mb-1 text-xs font-medium dark:text-black">Title: {{ report.title }}</p>
                                        <p class="text-justify text-blue-600 text-xs">{{ report.description }}</p>
                                    </div>
                                </div>

                                <!-- Remarks -->
                                <div v-if="report.remarks">
                                    <p class="font-medium text-sm mb-2">Remarks</p>
                                    <div class="bg-blue-100 border-l-4 border-blue-600 p-2 rounded-md">
                                        <p class="text-justify text-blue-600 text-xs">{{ report.remarks }}</p>
                                    </div>
                                </div>

                                <!-- Status Timeline (Simplified for mobile) -->
                                <div class="mt-3">
                                    <p class="font-medium text-sm mb-2">Status Timeline</p>
                                    <div class="space-y-2">
                                        <div v-for="(event, index) in getStatusTimeline(report)" :key="index" 
                                            class="flex items-center gap-2 p-2 rounded border text-xs">
                                            <span class="text-base">{{ event.icon }}</span>
                                            <div class="flex-1">
                                                <p class="font-medium">{{ event.status }}</p>
                                                <p class="text-gray-500">{{ formatDate(event.date) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Sections (Conditional) -->
                                <div v-if="report.resolution" class="mt-3">
                                    <p class="font-medium text-sm mb-2">Resolution</p>
                                    <div class="bg-green-50 border-l-4 border-green-600 p-2 rounded-md">
                                        <p class="text-green-600 text-xs">{{ report.resolution }}</p>
                                    </div>
                                </div>

                                <div v-if="report.rejection_reason" class="mt-3">
                                    <p class="font-medium text-sm mb-2">Rejection Reason</p>
                                    <div class="bg-red-50 border-l-4 border-red-600 p-2 rounded-md">
                                        <p class="text-red-600 text-xs">{{ report.rejection_reason }}</p>
                                    </div>
                                </div>

                                <div v-if="report.status === 'Duplicate'" class="mt-3">
                                    <p class="font-medium text-sm mb-2">Duplicate</p>
                                    <div class="bg-purple-50 border-l-4 border-purple-600 p-2 rounded-md">
                                        <p class="text-purple-600 text-xs">
                                            This report has been identified as a duplicate of ID: #{{ report.duplicate_of_report_id }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Desktop Layout -->
                        <div class="hidden lg:flex border p-3 gap-5 bg-white rounded-lg shadow-md dark:bg-[#2c2c2c] dark:border-none">
                            <div class="w-1/2">
                                <div class="h-96 w-full overflow-hidden">
                                    <img :src="`/storage/${report.image}`" alt="Report Image" class="w-full h-full object-cover rounded">
                                </div>
                            </div>
                            
                            <div class="w-1/2 p-5 h-96 flex flex-col gap-6 overflow-y-auto">
                                <!-- ... your existing desktop content remains exactly the same ... -->
                                <div class="">
                                    <div class="flex items-center justify-between">
                                        <h1 class="font-medium text-xl font-[Poppins]">Report Details</h1>
                                        <p class="text-xs">{{ formatDate(report.created_at) }}</p>
                                    </div>
                                    <hr class="my-5">
                                    <div class="flex justify-between">
                                        <p class="font-medium text-normal font-[Poppins]">{{ displayName(report) }}</p>
                                        <span
                                            :class="[
                                                'text-center border rounded-full font-semibold px-3 py-1 text-sm capitalize',
                                                report.status === 'Pending' ? 'bg-amber-100 text-amber-800 border-amber-200 font-bold' :
                                                report.status === 'In Progress' ? 'bg-blue-100 text-[#3B82F6] border-blue-200 font-bold' :
                                                report.status === 'Resolved' ? 'bg-green-100 text-[#16A34A] border-green-200 font-bold' :
                                                report.status === 'Rejected' ? 'bg-red-100 text-[#EF4444] border-red-200 font-bold' :
                                                report.status === 'Duplicate' ? 'bg-purple-100 text-purple-800 border-purple-200 font-bold' : ''
                                            ]" 
                                        >
                                            {{ report.status }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex flex-col">
                                        <p class="text-sm text-gray-500">{{ report.barangay_name }} - {{ report.sitio_name }}</p>
                                        <p class="text-sm text-gray-500">ID: {{ report.id }}</p>
                                    </div>
                                    
                                </div>
                                
                                <!-- Description-->
                                <div>
                                    <p class="font-medium text-base"> Description </p>
                                    <div class="bg-blue-50 border-l-4 border-blue-600 p-2 rounded-md">
                                        <p class="mb-1 text-xs dark:text-black">Title: {{ report.title }}</p>
                                        <p class="text-justify text-blue-600 text-sm">{{ report.description }}</p>
                                    </div>
                                </div>

                                <!-- Remarks -->
                                <div v-if="report.remarks">
                                    <p class="font-medium text-base"> Remarks </p>
                                    <div class="bg-blue-100 border-l-4 border-blue-600 p-2 rounded-md">
                                        <p class="text-justify text-blue-600 text-sm">{{ report.remarks }}</p>
                                    </div>
                                </div>
                                
                                <!-- Status History -->
                                <div class="mt-4">
                                    <div class="flex flex-col gap-2 mb-3">
                                        <p class="font-medium text-base">Status Timeline</p>
                                        <!-- Dynamic Timeline -->
                                        <div class="space-y-2">
                                            <div v-for="(event, index) in getStatusTimeline(report)" :key="index" 
                                                class="flex items-center gap-3 p-2 rounded border">
                                                <span class="text-lg">{{ event.icon }}</span>
                                                <div class="flex-1">
                                                    <p class="text-sm font-medium">{{ event.status }}</p>
                                                    <p class="text-xs text-gray-500">{{ formatDate(event.date) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="p-3 bg-gray-50 rounded-lg">
                                            <h4 class="text-sm font-semibold mb-2 dark:text-black">Current Status:</h4>
                                            <div class="flex flex-col gap-2 text-xs">
                                                <div class="flex justify-between">
                                                    <span class="text-amber-500">Reported:</span>
                                                    <span class="font-medium text-amber-500">{{ formatDate(report.created_at) }}</span>
                                                </div>
                                                <div v-if="report.approved_at" class="flex justify-between">
                                                    <span class="text-blue-500">Approved:</span>
                                                    <span class="font-medium text-blue-500">{{ formatDate(report.approved_at) }}</span>
                                                </div>
                                                <div v-if="report.resolved_at" class="flex justify-between">
                                                    <span class="text-green-500">Resolved:</span>
                                                    <span class="font-medium text-green-500">{{ formatDate(report.resolved_at) }}</span>
                                                </div>
                                                <div v-if="report.rejected_at" class="flex justify-between">
                                                    <span class="text-red-500">Rejected:</span>
                                                    <span class="font-medium text-red-500">{{ formatDate(report.rejected_at) }}</span>
                                                </div>
                                                <div v-if="report.duplicate_at" class="flex justify-between">
                                                    <span class="text-purple-500">Duplicate:</span>
                                                    <span class="font-medium text-purple-500">{{ formatDate(report.duplicate_at) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-if="report.resolution" class="">
                                    <p class="font-medium text-base">Resolution</p>
                                    <div class="bg-green-50 border-l-4 border-green-600 p-3 rounded-md">
                                        <p class="text-green-600 text-sm">{{ report.resolution }}</p>
                                    </div>
                                </div>
                                
                                <div v-if="report.rejection_reason" class="">
                                    <p class="font-medium text-base">Rejection Reason</p>
                                    <div class="bg-red-50 border-l-4 border-red-600 p-3 rounded-md">
                                        <p class="text-red-600 text-sm">{{ report.rejection_reason }}</p>
                                    </div>
                                </div>

                                <div v-if="report.status === 'Duplicate'" class="">
                                    <p class="font-medium text-base">Duplicate</p>
                                    <div class="bg-purple-50 border-l-4 border-purple-600 p-3 rounded-md">
                                        <p class="text-purple-600 text-sm">This report has been identified as a duplicate of ID: #{{ report.duplicate_of_report_id }}
                                            All updates and resolutions will be tracked under the main report.
                                        </p>
                                    </div>
                                </div>

                                <div v-if="report.duplicates && report.duplicates.length > 0">
                                    <p class="font-medium text-base">Primary Report</p>
                                    <div class="bg-purple-50 border-l-4 border-purple-600 p-3 rounded-md text-purple-600 text-sm">
                                        This is the original report for this issue with a Report ID of 
                                        <span class="font-semibold" v-for="(dup, index) in report.duplicates" :key="dup.id">
                                            #{{ dup.id }}<span v-if="index < report.duplicates.length - 1">, </span>
                                        </span>
                                        duplicate report(s) have been merged with this one to avoid duplication.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="reports && reports.links && reports.links.length > 1" class="flex justify-center mt-10 relative z-0" data-observe>
                    <div class="flex flex-wrap items-center justify-center gap-2 bg-white dark:bg-gray-800 rounded-xl p-4 shadow-lg border border-gray-200 dark:border-gray-700">
                        <template v-for="(link, index) in reports.links" :key="index">
                            <!-- Previous Button with Icon -->
                            <Link
                                v-if="link.url && link.label.includes('Previous')"
                                :href="link.url + (link.url.includes('?') ? '&' : '?') + `sort=${sortOrder}`"
                                :preserve-state="true"
                                :preserve-scroll="true"
                                :class="[
                                    'min-w-8 h-8 sm:min-w-10 sm:h-10 px-2 sm:px-3 flex items-center justify-center border-2 rounded-lg text-xs sm:text-sm font-medium transition-all duration-200 transform hover:scale-105 active:scale-95',
                                    'border-blue-200 text-blue-700 hover:bg-blue-500 hover:text-white hover:border-blue-500 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-blue-600 dark:hover:border-blue-600'
                                ]"
                                title="Previous Page"
                            >
                                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 256 256">
                                    <path d="M165.66,202.34a8,8,0,0,1-11.32,11.32l-80-80a8,8,0,0,1,0-11.32l80-80a8,8,0,0,1,11.32,11.32L91.31,128Z"></path>
                                </svg>
                            </Link>
                            
                            <!-- Next Button with Icon -->
                            <Link
                                v-else-if="link.url && link.label.includes('Next')"
                                :href="link.url + (link.url.includes('?') ? '&' : '?') + `sort=${sortOrder}`"
                                :preserve-state="true"
                                :preserve-scroll="true"
                                :class="[
                                    'min-w-8 h-8 sm:min-w-10 sm:h-10 px-2 sm:px-3 flex items-center justify-center border-2 rounded-lg text-xs sm:text-sm font-medium transition-all duration-200 transform hover:scale-105 active:scale-95',
                                    'border-blue-200 text-blue-700 hover:bg-blue-500 hover:text-white hover:border-blue-500 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-blue-600 dark:hover:border-blue-600'
                                ]"
                                title="Next Page"
                            >
                                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 256 256">
                                    <path d="M181.66,133.66l-80,80a8,8,0,0,1-11.32-11.32L164.69,128,90.34,53.66a8,8,0,0,1,11.32-11.32l80,80A8,8,0,0,1,181.66,133.66Z"></path>
                                </svg>
                            </Link>
                            
                            <!-- Page Numbers -->
                            <Link
                                v-else-if="link.url && !link.label.includes('Previous') && !link.label.includes('Next')"
                                :href="link.url + (link.url.includes('?') ? '&' : '?') + `sort=${sortOrder}`"
                                :preserve-state="true"
                                :preserve-scroll="true"
                                :class="[
                                    'min-w-8 h-8 sm:min-w-10 sm:h-10 px-2 sm:px-3 flex items-center justify-center border-2 rounded-lg text-xs sm:text-sm font-medium transition-all duration-200 transform hover:scale-105 active:scale-95',
                                    link.active 
                                        ? 'bg-blue-600 text-white border-blue-600 shadow-md scale-105' 
                                        : 'border-blue-200 text-blue-700 hover:bg-blue-500 hover:text-white hover:border-blue-500 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-blue-600 dark:hover:border-blue-600'
                                ]"
                                v-html="link.label"
                            />
                            
                            <span
                                v-else
                                :class="[
                                    'min-w-8 h-8 sm:min-w-10 sm:h-10 px-2 sm:px-3 flex items-center justify-center border-2 rounded-lg text-xs sm:text-sm font-medium',
                                    link.label.includes('Previous') || link.label.includes('Next')
                                        ? 'border-gray-300 text-gray-400 cursor-not-allowed dark:border-gray-700 dark:text-gray-600'
                                        : 'border-blue-100 bg-blue-50 text-blue-400 cursor-not-allowed dark:border-gray-700 dark:bg-gray-900 dark:text-gray-500'
                                ]"
                            >
                                <template v-if="link.label.includes('Previous')">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 256 256">
                                        <path d="M165.66,202.34a8,8,0,0,1-11.32,11.32l-80-80a8,8,0,0,1,0-11.32l80-80a8,8,0,0,1,11.32,11.32L91.31,128Z"></path>
                                    </svg>
                                </template>
                                <template v-else-if="link.label.includes('Next')">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 256 256">
                                        <path d="M181.66,133.66l-80,80a8,8,0,0,1-11.32-11.32L164.69,128,90.34,53.66a8,8,0,0,1,11.32-11.32l80,80A8,8,0,0,1,181.66,133.66Z"></path>
                                    </svg>
                                </template>
                                <template v-else>
                                    {{ link.label }}
                                </template>
                            </span>
                        </template>
                    </div>
                </div>
            </section>
        </main>
    </GuestLayout>
</template>

<style scoped>
.animate-fade-in-up {
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.6s ease forwards;
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.border:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease;
}
</style>