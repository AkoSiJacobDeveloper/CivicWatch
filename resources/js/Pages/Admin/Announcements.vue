<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, reactive } from 'vue';
import { initTooltips } from 'flowbite';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import AnnouncementDetailsModal from '@/Components/AnnouncementDetailsModal.vue';

const props = defineProps({
    announcements: Object,
})

// Create reactive copies of the announcements
const announcementsState = reactive({
    pinned: [...props.announcements.pinned || []],
    regular: [...props.announcements.regular || []],
    archived: [...props.announcements.archived || []]
})

const selectedAnnouncement = ref(null)
const showModal = ref(false)
const activeStatus = ref('pinned')
const searchQuery = ref('')

// Computed properties for filtered announcements
const filteredPinnedAnnouncements = computed(() => {
    if (!searchQuery.value.trim()) {
        return announcementsState.pinned;
    }
    
    const query = searchQuery.value.toLowerCase().trim();
    return announcementsState.pinned.filter(announcement => 
        announcement.title.toLowerCase().includes(query) ||
        announcement.content.toLowerCase().includes(query)
    );
});

const filteredRegularAnnouncements = computed(() => {
    if (!searchQuery.value.trim()) {
        return announcementsState.regular;
    }
    
    const query = searchQuery.value.toLowerCase().trim();
    return announcementsState.regular.filter(announcement => 
        announcement.title.toLowerCase().includes(query) ||
        announcement.content.toLowerCase().includes(query)
    );
});

const filteredArchivedAnnouncements = computed(() => {
    if (!searchQuery.value.trim()) {
        return announcementsState.archived;
    }
    
    const query = searchQuery.value.toLowerCase().trim();
    return announcementsState.archived.filter(announcement => 
        announcement.title.toLowerCase().includes(query) ||
        announcement.content.toLowerCase().includes(query)
    );
});

// Update tabs with dynamic counts based on search
const tabs = computed(() => [
    {
        text: 'Pinned Announcements',
        value: 'pinned',
        count: filteredPinnedAnnouncements.value.length
    },
    {
        text: 'Regular Announcements',
        value: 'regular',
        count: filteredRegularAnnouncements.value.length
    },
    {
        text: 'Archive Announcement',
        value: 'archive',
        count: filteredArchivedAnnouncements.value.length // Fixed: Use actual archived count
    }
])

function applyFilter(value) {
    activeStatus.value = value;
}

// Clear search function
const clearSearch = () => {
    searchQuery.value = '';
}

const openAnnouncement = (announcement) => {
    selectedAnnouncement.value = announcement
    showModal.value = true;
}

const closeModal = () => {
    showModal.value = false
    setTimeout(() => {
        selectedAnnouncement.value = null
    }, 300)
}

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString)
    return date.toLocaleString('en-US', {
        month: 'long',
        day: 'numeric', 
        year: 'numeric', 
        hour: 'numeric',
        minute: '2-digit',
    })
}

// Archive an announcement
const archiveAnnouncement = (id) => {
    if (confirm('Are you sure you want to archive this announcement?')) {
        router.post(route('admin.archive.announcement', { id: id }), {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Find and move the announcement from active to archived
                let archivedAnnouncement = null;
                
                // Check pinned announcements
                const pinnedIndex = announcementsState.pinned.findIndex(a => a.id === id);
                if (pinnedIndex !== -1) {
                    archivedAnnouncement = announcementsState.pinned.splice(pinnedIndex, 1)[0];
                }
                
                // Check regular announcements  
                const regularIndex = announcementsState.regular.findIndex(a => a.id === id);
                if (regularIndex !== -1) {
                    archivedAnnouncement = announcementsState.regular.splice(regularIndex, 1)[0];
                }
                
                // Add to archived with current timestamp
                if (archivedAnnouncement) {
                    archivedAnnouncement.archived_at = new Date().toISOString();
                    announcementsState.archived.unshift(archivedAnnouncement);
                }
            }
        });
    }
}

// Restore an announcement from archive
const restoreAnnouncement = (id) => {
    if (confirm('Are you sure you want to restore this announcement?')) {
        router.post(route('admin.restore.announcement', { id: id }), {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Find and move the announcement from archived back to appropriate active list
                const archivedIndex = announcementsState.archived.findIndex(a => a.id === id);
                if (archivedIndex !== -1) {
                    const restoredAnnouncement = announcementsState.archived.splice(archivedIndex, 1)[0];
                    restoredAnnouncement.archived_at = null;
                    
                    // Add back to pinned or regular based on is_pinned status
                    if (restoredAnnouncement.is_pinned) {
                        announcementsState.pinned.unshift(restoredAnnouncement);
                    } else {
                        announcementsState.regular.unshift(restoredAnnouncement);
                    }
                }
            }
        });
    }
}

// Handle announcement deletion
const handleAnnouncementDeleted = (deletedId) => {
    // Remove from all lists
    announcementsState.pinned = announcementsState.pinned.filter(announcement => announcement.id !== deletedId);
    announcementsState.regular = announcementsState.regular.filter(announcement => announcement.id !== deletedId);
    announcementsState.archived = announcementsState.archived.filter(announcement => announcement.id !== deletedId);
    closeModal();
}

const handleAnnouncementArchived = (archivedId) => {
    // Find and move the announcement from active to archived
    let archivedAnnouncement = null;
    
    // Check pinned announcements
    const pinnedIndex = announcementsState.pinned.findIndex(a => a.id === archivedId);
    if (pinnedIndex !== -1) {
        archivedAnnouncement = announcementsState.pinned.splice(pinnedIndex, 1)[0];
    }
    
    // Check regular announcements  
    const regularIndex = announcementsState.regular.findIndex(a => a.id === archivedId);
    if (regularIndex !== -1) {
        archivedAnnouncement = announcementsState.regular.splice(regularIndex, 1)[0];
    }
    
    // Add to archived with current timestamp
    if (archivedAnnouncement) {
        archivedAnnouncement.archived_at = new Date().toISOString();
        announcementsState.archived.unshift(archivedAnnouncement);
    }
    
    closeModal();
}

const handleAnnouncementRestored = (restoredId) => {
    // Find and move the announcement from archived back to appropriate active list
    const archivedIndex = announcementsState.archived.findIndex(a => a.id === restoredId);
    if (archivedIndex !== -1) {
        const restoredAnnouncement = announcementsState.archived.splice(archivedIndex, 1)[0];
        restoredAnnouncement.archived_at = null;
        
        // Add back to pinned or regular based on is_pinned status
        if (restoredAnnouncement.is_pinned) {
            announcementsState.pinned.unshift(restoredAnnouncement);
        } else {
            announcementsState.regular.unshift(restoredAnnouncement);
        }
    }
    
    closeModal();
}

onMounted(() => {
    initTooltips();
});
</script>

<template>
    <Head title="Manage Announcements" />

    <AdminLayout>
        <main class="flex flex-col gap-7 text-sm">
            <section class="flex justify-between items-center">
                <div class="flex gap-1 items-center">
                    <img 
                        :src="'/Images/SVG/megaphone.svg'" 
                        alt="icon"
                        class="h-8"
                    >
                    <h1 class="font-semibold text-3xl font-[Poppins] my-3">Announcement</h1>
                </div>

                <!-- Search -->
                <div class="flex items-center gap-5 w-[30%]">
                    <div class="relative w-full flex items-center">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                            </svg>
                        </div>
                        <input 
                            type="text"
                            class="block w-full p-3 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Search announcements title..."
                            v-model="searchQuery"
                        >
                        <button
                            class="text-white absolute right-2 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2"
                        >
                            Search
                        </button>
                    </div>
                </div>
            </section>
            
            <section>
                <div class="bg-blue-200 p-3 rounded-lg flex gap-2 border-l-4 border-blue-900">
                    <div class="flex justify-center">
                        <img 
                            :src="'/Images/SVG/info (blue).svg'" 
                            alt="Info Icon"
                            class="h-10"
                        >
                    </div>
                    <p class="text-blue-900">
                        Keep our Barangay community informed and engaged. This platform allows you to 
                        share important updates, event invitations, safety alerts, and service announcements 
                        with residents. Ensure timely communication by scheduling posts and targeting 
                        specific community groups.
                    </p>
                </div>
            </section>

            <div class="flex flex-col gap-7">
                <div class="">
                    <ul class="flex justify-between">
                        <div class="font-[Poppins] flex flex-wrap text-sm font-medium text-center border-b border-gray-200 dark:border-gray-700">
                            <li 
                                v-for="(tab, index) in tabs" 
                                :key="index"
                                class="me-2"
                            >
                                <button
                                    @click="applyFilter(tab.value)"
                                    :class="[
                                        'flex items-center gap-2 px-4 py-2 rounded-t-lg transition-all duration-300 border-b-2 font-medium',
                                        activeStatus === tab.value 
                                            ? 'text-blue-700 bg-blue-50 border-blue-700' 
                                            : 'border-transparent hover:text-blue-600 hover:bg-blue-50 hover:border-blue-300'
                                    ]"
                                >
                                    <div :class="[
                                        'w-2 h-2 rounded-full mr-1',
                                        tab.value === 'pinned' ? 'bg-blue-700' :
                                        tab.value === 'regular' ? 'bg-blue-400' :
                                        'bg-gray-400'
                                    ]"></div>
                                    
                                    <span>{{ tab.text }}</span>
                                    <span class="bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded-full">
                                        {{ tab.count }}
                                    </span>
                                </button>
                            </li>
                        </div>

                        <Link
                            href="/admin/announcements/create-announcement"
                            class="group flex gap-2 text-gray-600 font-medium p-3 rounded-lg hover:bg-blue-700 hover:text-white transition-all duration-300 border border-gray-300 shadow-sm w-auto"
                        >
                            <img 
                                :src="'/Images/SVG/plus-circle.svg'" 
                                alt="Icon" 
                                class="h-5 block group-hover:hidden">
                            <img 
                                :src="'/Images/SVG/plus-circle (white).svg'" 
                                alt="Icon White" 
                                class="h-5 hidden group-hover:block">
                            Create Announcement
                        </Link>
                    </ul>
                </div>
            </div>

            <section class="flex flex-col gap-5">
                <!-- Pinned Announcements -->
                <div v-if="activeStatus === 'pinned'">
                    <div 
                        v-if="filteredPinnedAnnouncements.length === 0"
                        class="mt-3 flex flex-col items-center justify-center h-64 p-10 mb-2"
                    >   
                        <img 
                            :src="'/Images/SVG/not found.svg'" 
                            alt="SVG" 
                            class="h-44"
                        >
                        <p class="text-gray-500 text-lg">
                            {{ searchQuery ? `No pinned announcements found for "${searchQuery}"` : 'No pinned announcements available.' }}
                        </p>
                        <button 
                            v-if="searchQuery"
                            @click="clearSearch"
                            class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                        >
                            Clear Search
                        </button>
                    </div>

                    <div 
                        v-else
                        class="mt-3 grid grid-cols-3 gap-4"
                    >
                        <div 
                            v-for="pinned in filteredPinnedAnnouncements"
                            :key="pinned.id"
                            class="w-full max-w-[500px]"
                        >   
                            <div 
                                class="group h-64 shadow-container-announcement p-10 mb-2 bg-white hover:cursor-pointer hover:bg-blue-700 transition-all duration-300 transform hover:-translate-y-1 flex flex-col justify-between"
                                @click="openAnnouncement(pinned)"
                            >
                                <div class="flex justify-between">
                                    <div class="flex justify-center items-center">
                                        <span class="text-gray-500 text-xs group-hover:text-gray-200 transition-colors duration-300">ID: {{ pinned.id }}</span>
                                    </div>

                                    <span class="bg-blue-700 text-white text-xs font-semibold px-2 py-1 rounded flex items-center gap-1 group-hover:bg-blue-500 transition-all duration-300">
                                        <img :src="'/Images/SVG/push-pin.svg'" alt="Pin" class="h-4 w-4">
                                        Pinned
                                    </span>
                                </div>
                                <div class="mt-6 flex justify-between items-center ">
                                    <div class="">
                                        <h1 class="truncate overflow-hidden whitespace-nowrap text-blue-500 font-semibold text-lg font-[Poppins] group-hover:text-gray-200 transition-colors duration-300">{{ pinned.title }}</h1>
                                        <p class="line-clamp-2 text-gray-400 text-sm group-hover:text-gray-200 transition-colors duration-300">{{ pinned.content }}</p>
                                    </div>
                                </div>
                                <div class="flex justify-start mt-6">
                                    <div>
                                        <span class="text-xs text-gray-400 group-hover:text-gray-200 transition-colors duration-300">{{ formatDate( pinned.created_at) }}</span>
                                    </div>
                                </div>

                                <!-- <div class="flex justify-end mt-4">
                                    <button
                                        @click.stop="archiveAnnouncement(pinned.id)"
                                        class="px-3 py-1 bg-gray-500 text-white text-xs rounded hover:bg-gray-600 transition-colors"
                                    >
                                        Archive
                                    </button>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Regular Announcements -->
                <div v-if="activeStatus === 'regular'">
                    <div 
                        v-if="filteredRegularAnnouncements.length === 0"
                        class="mt-3 flex flex-col items-center justify-center h-64 p-10 mb-2"
                    >
                        <img 
                            :src="'/Images/SVG/not found.svg'" 
                            alt="SVG" 
                            class="h-44"
                        >
                        <p class="text-gray-500 text-lg">
                            {{ searchQuery ? `No regular announcements found for "${searchQuery}"` : 'No announcements available.' }}
                        </p>
                        <button 
                            v-if="searchQuery"
                            @click="clearSearch"
                            class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                        >
                            Clear Search
                        </button>
                    </div>

                    <div 
                        v-else
                        class="mt-3 grid grid-cols-3 gap-4"
                    >
                        <div 
                            v-for="regular in filteredRegularAnnouncements"
                            :key="regular.id"
                        >
                            <div 
                                class="group h-64 shadow-container-announcement p-10 mb-2 bg-white hover:cursor-pointer hover:bg-blue-700 transition-all duration-300 transform hover:-translate-y-1 flex flex-col justify-between"
                                @click="openAnnouncement(regular)"
                            >
                                <div class="flex justify-between">
                                    <div class="flex justify-center items-center">
                                        <span class="text-gray-500 text-xs group-hover:text-gray-200 transition-colors duration-300">ID: {{ regular.id }}</span>
                                    </div>

                                    <span class="bg-blue-100 text-blue-700 text-xs font-semibold px-2 py-1 rounded flex items-center gap-1">
                                        <img :src="'/Images/SVG/megaphone (700).svg'" alt="Icon"  class="h-4 w-4">
                                        Regular
                                    </span>
                                </div>
                                <div class="mt-6 flex justify-between">
                                    <div>
                                        <h1 class="truncate font-semibold text-lg font-[Poppins] text-blue-500 group-hover:text-gray-200 transition-colors duration-300">{{ regular.title }}</h1>
                                        <p class="line-clamp-2 text-gray-400 text-sm group-hover:text-gray-200 transition-colors duration-300">{{ regular.content }}</p>
                                    </div>
                                </div>
                                <div class="flex justify-start mt-6">
                                    <div>
                                        <span class="text-xs text-gray-400 group-hover:text-gray-200 transition-colors duration-300">{{ formatDate( regular.created_at) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Archive Announcement -->
                <div v-if="activeStatus === 'archive'">
                    <div 
                        v-if="filteredArchivedAnnouncements.length === 0"
                        class="mt-3 flex flex-col items-center justify-center h-64 p-10 mb-2"
                    >
                        <img 
                            :src="'/Images/SVG/not found.svg'" 
                            alt="SVG" 
                            class="h-44"
                        >
                        <p class="text-gray-500 text-lg">No archived announcements available.</p>
                    </div>

                    <div 
                        v-else
                        class="mt-3 grid grid-cols-3 gap-4"
                    >
                        <div 
                            v-for="archived in filteredArchivedAnnouncements"
                            :key="archived.id"
                        >
                            <div 
                                class="group h-72 shadow-container-announcement p-10 mb-2 bg-gray-50 hover:cursor-pointer hover:bg-blue-700 transition-all duration-300 transform hover:-translate-y-1 flex flex-col justify-between border border-gray-200"
                                @click="openAnnouncement(archived)"
                            >
                                <div class="flex justify-between">
                                    <div class="flex justify-center items-center">
                                        <span class="text-gray-500 text-xs group-hover:text-gray-200 transition-colors duration-300">ID: {{ archived.id }}</span>
                                    </div>

                                    <span class="bg-gray-500 text-white text-xs font-semibold px-2 py-1 rounded flex items-center gap-1">
                                        <img :src="'/Images/SVG/archive.svg'" alt="Archive" class="h-4 w-4">
                                        Archived
                                    </span>
                                </div>
                                
                                <div class="mt-6 flex justify-between">
                                    <div>
                                        <h1 class="truncate font-semibold text-lg font-[Poppins] text-gray-600 group-hover:text-gray-200 transition-colors duration-300">{{ archived.title }}</h1>
                                        <p class="line-clamp-2 text-gray-400 text-sm group-hover:text-gray-200 transition-colors duration-300">{{ archived.content }}</p>
                                    </div>
                                </div>
                                
                                <div class="flex justify-between items-center mt-6">
                                    <div>
                                        <span class="text-xs text-gray-400 group-hover:text-gray-200 transition-colors duration-300">
                                            Archived: {{ formatDate(archived.archived_at) }}
                                        </span>
                                    </div>
                                    
                                    <!-- Restore Button -->
                                    <!-- <button
                                        @click.stop="restoreAnnouncement(archived.id)"
                                        class="px-3 py-1 bg-green-500 text-white text-xs rounded hover:bg-green-600 transition-colors"
                                    >
                                        Restore
                                    </button> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <AnnouncementDetailsModal 
            :show="showModal"
            :announcement="selectedAnnouncement"
            @close="closeModal"
            @deleted="handleAnnouncementDeleted"
            @archived="handleAnnouncementArchived"
            @restored="handleAnnouncementRestored"
        />
    </AdminLayout>
</template>