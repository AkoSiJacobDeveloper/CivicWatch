<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, onMounted, onUnmounted } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import AnnouncementClientDetailsModal from '@/Components/AnnouncementClientDetailsModal.vue';

const props = defineProps({
    announcements: Object,
    sort: String
})

const sortOrder = ref(props.sort || 'desc')

let pollInterval = null;
const previousAnnouncementsCount = ref(props.announcements.total || 0);
const currentAnnouncementIds = ref(new Set());
const newAnnouncementIds = ref(new Map()); // Changed to Map to store timestamps

// Intersection Observer setup
const observer = ref(null);
const observedElements = ref([]);

// Storage functions for persistence
const getStoredNewAnnouncements = () => {
    try {
        const stored = localStorage.getItem('newAnnouncementIds');
        return stored ? new Map(Object.entries(JSON.parse(stored))) : new Map();
    } catch {
        return new Map();
    }
};

const setStoredNewAnnouncements = (announcementIdsMap) => {
    try {
        localStorage.setItem('newAnnouncementIds', JSON.stringify(Object.fromEntries(announcementIdsMap)));
    } catch (error) {
        console.error('Failed to store new announcements:', error);
    }
};

// Function to clean up old new announcements (older than 24 hours)
const cleanOldNewAnnouncements = () => {
    const oneDayAgo = Date.now() - (24 * 60 * 60 * 1000);
    for (const [id, timestamp] of newAnnouncementIds.value.entries()) {
        if (timestamp < oneDayAgo) {
            newAnnouncementIds.value.delete(id);
        }
    }
    setStoredNewAnnouncements(newAnnouncementIds.value);
};

onMounted(() => {
    // Load persisted new announcements from localStorage
    newAnnouncementIds.value = getStoredNewAnnouncements();
    
    // Clean up old new announcements (older than 24 hours)
    cleanOldNewAnnouncements();
    
    props.announcements.data.forEach(announcement => {
        currentAnnouncementIds.value.add(announcement.id);
    });
    
    pollInterval = setInterval(pollForNewAnnouncements, 15000);

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
    const elements = document.querySelectorAll('[data-observe]');
    elements.forEach((el) => {
        observer.value.observe(el);
        observedElements.value.push(el);
    });
});

onUnmounted(() => {
    if (pollInterval) {
        clearInterval(pollInterval);
    }

    // Cleanup observer
    if (observer.value) {
        observedElements.value.forEach((el) => {
            observer.value.unobserve(el);
        });
        observer.value.disconnect();
    }
});

const pollForNewAnnouncements = () => {
    router.reload({
        preserveState: true,
        preserveScroll: true,
        only: ['announcements'], 
        onSuccess: (page) => {
            const currentAnnouncementsCount = page.props.announcements.total || 0;

            // ✅ REMOVED the setTimeout that was clearing after 5 seconds

            if (currentAnnouncementsCount > previousAnnouncementsCount.value) {
                page.props.announcements.data.forEach(announcement => {
                    if (!currentAnnouncementIds.value.has(announcement.id)) {
                        // Store with timestamp
                        newAnnouncementIds.value.set(announcement.id, Date.now());
                        currentAnnouncementIds.value.add(announcement.id);
                    }
                });
                
                // Persist to localStorage
                setStoredNewAnnouncements(newAnnouncementIds.value);
                
                previousAnnouncementsCount.value = currentAnnouncementsCount;
            }

            // Re-observe elements after reload
            const elements = document.querySelectorAll('[data-observe]');
            elements.forEach((el) => {
                if (!observedElements.value.includes(el)) {
                    observer.value?.observe(el);
                    observedElements.value.push(el);
                }
            });
        }
    });
};

const isNewAnnouncement = (announcementId) => {
    return newAnnouncementIds.value.has(announcementId);
};

watch(() => props.sort, (newSort) => {
    sortOrder.value = newSort
})

const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleString('en-US', {
        month: 'long',
        day: 'numeric', 
        year: 'numeric', 
        hour: 'numeric',
        minute: '2-digit',
    })
}

const openModal = ref(false)
const selectedAnnouncement = ref(null)

// Single toggleSort function
function toggleSort() {
    const newSort = sortOrder.value === 'desc' ? 'asc' : 'desc';
    sortOrder.value = newSort;
    router.get('/user-announcements', { sort: newSort }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
}

const openDetailsModal = (id) => {
    const announcement = props.announcements.data.find(item => item.id === id)
    if (announcement) {
        selectedAnnouncement.value = announcement
        openModal.value = true
    }
}

const closeModal = () => {
    openModal.value = false
    selectedAnnouncement.value = null
}
</script>

<template>
    <Head title="Announcement" />

    <GuestLayout>
        <main class="dark:text-[#FAF9F6]">
            <section class="pt-52 lg:pt-0 hero-section min-h-screen text-[#000] px-4 md:px-10 lg:px-32 flex flex-col lg:flex-row items-center" data-observe>
                <div class="w-full lg:w-1/2 flex justify-center items-center py-10 lg:py-0">
                    <div class="">
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold font-[Poppins] mb-5 text-blue-700">Announcements</h1>
                        <p class="text-justify text-base sm:text-lg dark:text-[#faf9f6]">Stay informed with the latest news, updates, and important announcements from your community. This page serves as your reliable source for events, reminders, and public information. Check back regularly to stay connected and never miss an important update or opportunity to get involved.</p>
                    </div>
                </div>
                <div class="hidden lg:flex w-full lg:w-1/2 justify-center items-center">
                    <div class="flex justify-center lg:justify-end">
                        <img :src="'/Images/online_information.svg'" alt="Announcement" class="w-[30rem]">
                    </div>
                </div>
            </section>
            
            <section class="px-4 md:px-10 lg:px-32 py-10 lg:py-20 flex flex-col gap-6 lg:gap-10">
                <div class="flex flex-col sm:flex-row justify-between gap-4" data-observe>
                    <div class="text-left">
                        <h2 class="text-2xl lg:text-4xl font-bold font-[Poppins] dark:text-white">Announcements</h2>
                        <p class="text-sm md:text-base text-gray-500 dark:text-[#FAF9F6]">Here's what's happening around you today.</p>
                    </div>

                    <!-- Sort and Refresh Buttons -->
                    <div class="flex items-center gap-3">
                        <!-- Refresh Button -->
                        <button
                            @click="pollForNewAnnouncements"
                            class="border p-2 sm:p-3 rounded flex items-center gap-1 sm:gap-2 hover:bg-green-500 hover:text-white transition-colors group duration-300 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-green-600 text-sm sm:text-base"
                            title="Check for new announcements"
                        >
                            <span>Refresh</span>
                            <img 
                                :src="'/Images/SVG/arrows-clockwise.svg'" 
                                alt="Icon" 
                                class="h-4 w-4 sm:h-5 sm:w-5 group-hover:animate-spin group-hover:brightness-0 group-hover:invert dark:invert"
                            >
                        </button>
                        
                        <!-- Sort Button -->
                        <button
                            @click="toggleSort"
                            class="border p-2 sm:p-3 rounded flex items-center gap-1 sm:gap-2 hover:bg-blue-500 hover:text-white dark:hover:bg-gray-700 transition-colors group duration-300 text-sm sm:text-base"
                        >
                            <span>{{ sortOrder === 'desc' ? 'Newest' : 'Oldest' }}</span>
                            <svg 
                                xmlns="http://www.w3.org/2000/svg" 
                                width="14" 
                                height="14" 
                                fill="currentColor" 
                                viewBox="0 0 256 256"
                                :class="sortOrder === 'desc' ? 'rotate-0' : 'rotate-180'"
                                class="transition-transform duration-300 group-hover:scale-110"
                            >
                                <path d="M213.66,165.66a8,8,0,0,1-11.32,0L128,91.31,53.66,165.66a8,8,0,0,1-11.32-11.32l80-80a8,8,0,0,1,11.32,0l80,80A8,8,0,0,1,213.66,165.66Z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div v-if="!announcements.data || announcements.data.length === 0" class="text-center text-gray-500 py-10 lg:py-20" data-observe>
                    <p class="text-lg lg:text-xl mb-4">No announcement yet.</p>
                </div>

                <div v-else class="grid grid-cols-1 gap-4 lg:gap-6">
                    <div
                        v-for="announcement in props.announcements.data" :key="announcement.id" 
                        :class="[
                            'shadow-lg rounded-lg border-b-1 border-[#000] p-4 sm:p-6 lg:p-8 bg-white flex flex-col gap-4 lg:gap-6 dark:shadow-md dark:rounded-lg dark:bg-[#2c2c2c] transition-all duration-500',
                            isNewAnnouncement(announcement.id) ? 'border-2 border-green-500 bg-green-50 dark:bg-green-900/20 animate-pulse' : ''
                        ]"
                        data-observe
                    >
                        <div class="flex flex-col gap-3">
                            <div class="flex flex-col sm:flex-row justify-between mb-2 gap-2">
                                <h1 class="font-[Poppins] font-semibold text-base sm:text-lg lg:text-lg">{{ announcement.title }}</h1>
                                <p class="text-gray-500 text-xs sm:text-sm">{{ formatDate(announcement.event_date) }}</p>
                            </div>
                            <div class="mb-2">
                                <p class="font-light text-sm sm:text-base">{{ announcement.content }}</p>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 sm:gap-5">
                                    <p
                                        class="p-1 sm:p-2 uppercase rounded text-xs"
                                        :class="announcement?.level === 'urgent'
                                        ? 'text-red-700 border-2 border-red-700 font-semibold' : announcement?.level === 'important'
                                        ? 'text-amber-500 border-2 border-amber-500' : 'text-green-700 border-2 border-green-700'
                                        "
                                    >
                                        {{ announcement.level }}
                                    </p>
                                    <p class="text-xs text-gray-500">System Admin</p>
                                </div>
                                <button
                                    type="button"
                                    @click="openDetailsModal(announcement.id)"
                                    class="group flex gap-1 items-center text-gray-500 transition-all duration-300"
                                >
                                    <p class="text-xs group-hover:text-blue-500">More Details</p>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="group-hover:text-blue-500" width="14" height="14" fill="currentColor" viewBox="0 0 256 256"><path d="M221.66,133.66l-72,72a8,8,0,0,1-11.32-11.32L196.69,136H40a8,8,0,0,1,0-16H196.69L138.34,61.66a8,8,0,0,1,11.32-11.32l72,72A8,8,0,0,1,221.66,133.66Z"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="announcements && announcements.links && announcements.links.length > 1" class="flex justify-center mt-10" data-observe>
                    <div class="flex items-center gap-2 bg-white dark:bg-gray-800 rounded-xl p-4 shadow-lg border border-gray-200 dark:border-gray-700">
                        <template v-for="(link, index) in announcements.links" :key="index">
                            <!-- Previous Button with Icon -->
                            <Link
                                v-if="link.url && link.label.includes('Previous')"
                                :href="link.url + (link.url.includes('?') ? '&' : '?') + `sort=${sortOrder}`"
                                :preserve-state="true"
                                :preserve-scroll="true"
                                :class="[
                                    'min-w-10 h-10 px-3 flex items-center justify-center border-2 rounded-lg text-sm font-medium transition-all duration-200 transform hover:scale-105 active:scale-95',
                                    'border-blue-200 text-blue-700 hover:bg-blue-500 hover:text-white hover:border-blue-500 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-blue-600 dark:hover:border-blue-600'
                                ]"
                                title="Previous Page"
                            >
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 256 256">
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
                                    'min-w-10 h-10 px-3 flex items-center justify-center border-2 rounded-lg text-sm font-medium transition-all duration-200 transform hover:scale-105 active:scale-95',
                                    'border-blue-200 text-blue-700 hover:bg-blue-500 hover:text-white hover:border-blue-500 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-blue-600 dark:hover:border-blue-600'
                                ]"
                                title="Next Page"
                            >
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 256 256">
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
                                    'min-w-10 h-10 px-3 flex items-center justify-center border-2 rounded-lg text-sm font-medium transition-all duration-200 transform hover:scale-105 active:scale-95',
                                    link.active 
                                        ? 'bg-blue-600 text-white border-blue-600 shadow-md scale-105' 
                                        : 'border-blue-200 text-blue-700 hover:bg-blue-500 hover:text-white hover:border-blue-500 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-blue-600 dark:hover:border-blue-600'
                                ]"
                                v-html="link.label"
                            />
                            
                            <span
                                v-else
                                :class="[
                                    'min-w-10 h-10 px-3 flex items-center justify-center border-2 rounded-lg text-sm font-medium',
                                    link.label.includes('Previous') || link.label.includes('Next')
                                        ? 'border-gray-300 text-gray-400 cursor-not-allowed dark:border-gray-700 dark:text-gray-600'
                                        : 'border-blue-100 bg-blue-50 text-blue-400 cursor-not-allowed dark:border-gray-700 dark:bg-gray-900 dark:text-gray-500'
                                ]"
                            >
                                <template v-if="link.label.includes('Previous')">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 256 256">
                                        <path d="M165.66,202.34a8,8,0,0,1-11.32,11.32l-80-80a8,8,0,0,1,0-11.32l80-80a8,8,0,0,1,11.32,11.32L91.31,128Z"></path>
                                    </svg>
                                </template>
                                <template v-else-if="link.label.includes('Next')">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 256 256">
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
        <AnnouncementClientDetailsModal
            :show="openModal"
            :announcements="selectedAnnouncement"
            @close="closeModal"
        />
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
[data-observe]:nth-child(6) { transition-delay: 0.6s; }
[data-observe]:nth-child(7) { transition-delay: 0.7s; }
[data-observe]:nth-child(8) { transition-delay: 0.8s; }
[data-observe]:nth-child(9) { transition-delay: 0.9s; }
</style>