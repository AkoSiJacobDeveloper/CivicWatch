<script setup>
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, watch, computed, onMounted } from 'vue';
import { debounce } from 'lodash';

import GuestLayout from '@/Layouts/GuestLayout.vue';


const props = defineProps({
    reports: Object,
    filters: Object,
    sitios: Array,
})

const sortOrder = ref(props.filters.sort || 'desc');
const search = ref(props.filters.search || '');
const selectedSitio = ref(props.filters.sitio || '');
const dropdownOpen = ref(false); 
const dropdownButtonText = computed(() => {
    return selectedSitio.value || 'All Sitios';
});


const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value;
};


const selectSitio = (sitio) => {
    selectedSitio.value = sitio;
    dropdownOpen.value = false;
};


const performSearch = debounce(() => {
    router.get(route('user.get.reportedIssues'), {
        search: search.value,
        sitio: selectedSitio.value,
        sort: sortOrder.value
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
}, 300);

watch([search, selectedSitio], () => {
    performSearch();
});


const refreshData = () => {
    search.value = '';
    selectedSitio.value = ''; 
    sortOrder.value = 'desc';
    
    router.get(route('user.get.reportedIssues'), {
        sort: 'desc'
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
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
        replace: true
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

</script>

<template>
    <Head title="Reported Issues" />

    <GuestLayout>
        <main class="dark:text-[#FAF9F6]">
            <section class="pt-52 lg:pt-0 hero-section min-h-screen text-[#000] px-4 md:px-10 lg:px-32 flex flex-col lg:flex-row items-center" data-observe>
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
            <section class="px-4 sm:px-6 md:px-10 lg:px-32 py-10 lg:py-20 flex flex-col">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 " data-observe>
                    <div class="w-1/2">
                        <h2 class="text-2xl lg:text-4xl font-bold font-[Poppins] dark:text-white ">Reported Issues</h2>
                        <p class="text-sm md:text-base text-gray-500 dark:text-[#FAF9F6]">See what issues are already reported in your neighborhood</p>
                    </div>

                    <!-- Search with sitio dropdown -->
                    <div class="w-1/2" data-observe>
                        <form @submit.prevent="performSearch" class="w-full">
                            <div class="flex">
                                <!-- Dropdown Button -->
                                <button 
                                    id="dropdown-button" 
                                    @click="toggleDropdown"
                                    type="button"
                                    class="shrink-0 z-10 inline-flex items-center py-3 px-4 text-sm font-medium text-center text-gray-900 bg-gray-100 border border-gray-300 rounded-s-lg hover:bg-gray-200 focus:ring-4 focus:outline-none focus:ring-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-700 dark:text-white dark:border-gray-600" 
                                >
                                    {{ dropdownButtonText }}
                                    <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                                    </svg>
                                </button>
                                
                                <!-- Dropdown Menu -->
                                <div 
                                    v-show="dropdownOpen"
                                    class="absolute top-full mt-1 z-20 bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 dark:bg-gray-700 border border-gray-200 dark:border-gray-600"
                                >
                                    <ul class="py-2 text-sm text-gray-700 dark:text-gray-200">
                                        <li>
                                            <button 
                                                type="button" 
                                                @click="selectSitio('')"
                                                class="inline-flex text-xs w-full px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white"
                                                :class="{ 'bg-blue-50 dark:bg-blue-900': !selectedSitio }"
                                            >
                                                All Sitios
                                            </button>
                                        </li>
                                        <li v-for="sitio in sitios" :key="sitio">
                                            <button 
                                                type="button" 
                                                @click="selectSitio(sitio)"
                                                class="inline-flex w-full text-xs px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white"
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
                                        class="block p-3 w-full z-20 text-sm text-gray-900 bg-gray-50 rounded-e-lg border-s-gray-50 border-s-2 border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-s-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:border-blue-500" 
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
                <div class="flex items-center justify-end gap-3 mb-8">
                    <button
                        @click="refreshData"
                        class="border p-2 sm:p-3 rounded flex items-center gap-1 sm:gap-2 hover:bg-green-500 hover:text-white transition-colors group duration-300 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-green-600 text-sm sm:text-base"
                    >
                        <span>Refresh</span>
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
                        class="border p-2 sm:p-3 rounded flex items-center gap-2 hover:bg-blue-500 hover:text-white dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors group duration-300 text-sm sm:text-base"
                    >
                        <span>{{ sortOrder === 'desc' ? 'Newest' : 'Oldest' }}</span>
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
                

                <div v-if="!reports.data || reports.data.length === 0"  class="text-center text-gray-500" data-observe>
                    <p class="text-lg sm:text-xl mb-4 py-20">No reports yet. Report</p>
                </div>

                <!-- Reports List -->
                <div class="grid grid-cols-1 gap-5">
                    <div 
                        v-for="report in reports.data"
                        :key="report.id"
                    >
                        <div class="border p-3 flex gap-5 bg-white rounded-lg shadow-md dark:bg-[#2c2c2c] transition-all duration-500">
                            <div class="w-1/2">
                                <div class=" h-96 w-full overflow-hidden">
                                    <img :src="`/storage/${report.image}`" alt="Report Image" class="w-full h-full object-cover rounded">
                                </div>
                            </div>
                            
                            <div class="w-1/2 p-5 h-96 flex flex-col gap-6 overflow-y-auto">
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
                                    <div class="bg-blue-50 border-l-4 border-blue-600 p-2 rounded-md dark:bg-inherit  dark:border-t dark:border-r dark:border-b dark:border-l-4 dark:border-blue-600">
                                        <p class="mb-1 text-xs">Title: {{ report.title }}</p>
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
                                        <div class="p-3 bg-gray-50 rounded-lg dark:bg-gray-700 transition-all duration-500">
                                            <h4 class="text-sm font-semibold mb-2">Current Status:</h4>
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
                                            #{{ dup.id }}.
                                            <span v-if="index < report.duplicates.length - 1">, </span>
                                        </span>
                                        duplicate report(s) have been merged with this one to avoid duplication.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="reports.data && reports.data.length > 0" class="flex justify-center sm:justify-end mt-10" data-observe>
                    <div class="flex items-center gap-2 sm:gap-3 rounded">
                        <template v-for="link in (reports.links || [])" :key="link?.label || 'empty'">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                :preserve-state="true"
                                :preserve-scroll="true"
                                :class="['w-8 h-8 sm:w-10 sm:h-10 grid place-items-center border border-gray-400 rounded justify-center hover:bg-blue-400 transition-all duration-300 hover:text-[#FAF9F6] font-[Poppins] text-sm sm:text-base', link.active ? 'bg-blue-600 text-[#FAF9F6] border-none' : '']"
                            >
                                <span v-if="link.label.includes('Previous')" class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#000000" viewBox="0 0 256 256">
                                        <path d="M165.66,202.34a8,8,0,0,1-11.32,11.32l-80-80a8,8,0,0,1,0-11.32l80-80a8,8,0,0,1,11.32,11.32L91.31,128Z"></path>
                                    </svg>
                                </span>
                                <span v-else-if="link.label.includes('Next')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#000000" viewBox="0 0 256 256">
                                        <path d="M181.66,133.66l-80,80a8,8,0,0,1-11.32-11.32L164.69,128,90.34,53.66a8,8,0,0,1,11.32-11.32l80,80A8,8,0,0,1,181.66,133.66Z"></path>
                                    </svg>
                                </span>
                                <span v-else v-html="link.label"></span>
                            </Link>
                            <span
                                v-else
                                :class="'px-2 py-1 sm:px-3 sm:py-1 text-gray-500 cursor-not-allowed'"
                            >
                                <span v-if="link.label.includes('Previous')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#000000" viewBox="0 0 256 256">
                                        <path d="M165.66,202.34a8,8,0,0,1-11.32,11.32l-80-80a8,8,0,0,1,0-11.32l80-80a8,8,0,0,1,11.32,11.32L91.31,128Z"></path>
                                    </svg>
                                </span>
                                <span v-else-if="link.label.includes('Next')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#000000" viewBox="0 0 256 256">
                                        <path d="M181.66,133.66l-80,80a8,8,0,0,1-11.32-11.32L164.69,128,90.34,53.66a8,8,0,0,1,11.32-11.32l80,80A8,8,0,0,1,181.66,133.66Z"></path>
                                    </svg>
                                </span>
                                <span v-else v-html="link.label"></span>
                            </span>
                        </template> 
                    </div>
                </div>
            </section>
        </main>
    </GuestLayout>
</template>