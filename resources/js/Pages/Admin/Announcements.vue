<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, reactive } from 'vue';
import { initTooltips } from 'flowbite';
import Swal from 'sweetalert2';
import { useToast } from 'vue-toastification'

import AdminLayout from '@/Layouts/AdminLayout.vue';
import AnnouncementDetailsModal from '@/Components/AnnouncementDetailsModal.vue';

const toast = useToast();

const props = defineProps({
    announcements: Object,
})

// Create reactive copies of the announcements
const announcementsState = reactive({
    pinned: [...props.announcements.pinned || []],
    regular: [...props.announcements.regular || []],
    archived: [...props.announcements.archived || []]
})

const selectedAnnouncements = ref(new Set());
const bulkAction = ref('');

// Bulk action options
const bulkActions = computed(() => {
    if (activeStatus.value === 'archive') {
        return [
            { value: 'restore', label: 'Restore Selected', icon: 'arrows-counter-clockwise (green)' },
            { value: 'delete', label: 'Delete Selected', icon: 'trash (red)' }
        ];
    } else {
        return [
            { value: 'archive', label: 'Archive Selected', icon: 'archive (blue)' }
        ];
    }
});

// Select/deselect all announcements in current view
const toggleSelectAll = () => {
    const currentAnnouncements = currentDisplayedAnnouncements.value;
    
    if (selectedAnnouncements.value.size === currentAnnouncements.length) {
        // Deselect all
        selectedAnnouncements.value.clear();
    } else {
        // Select all
        currentAnnouncements.forEach(announcement => {
            selectedAnnouncements.value.add(announcement.id);
        });
    }
}

// Toggle individual announcement selection
const toggleAnnouncementSelection = (id) => {
    if (selectedAnnouncements.value.has(id)) {
        selectedAnnouncements.value.delete(id);
    } else {
        selectedAnnouncements.value.add(id);
    }
}

// Check if all announcements are selected
const isAllSelected = computed(() => {
    const currentAnnouncements = currentDisplayedAnnouncements.value;
    return currentAnnouncements.length > 0 && 
            selectedAnnouncements.value.size === currentAnnouncements.length;
});

// Check if some announcements are selected
const hasSelectedAnnouncements = computed(() => {
    return selectedAnnouncements.value.size > 0;
});

// Execute bulk action
const executeBulkAction = () => {
    if (!bulkAction.value || !hasSelectedAnnouncements.value) return;

    const selectedIds = Array.from(selectedAnnouncements.value);
    
    switch(bulkAction.value) {
        case 'archive':
            archiveBulkAnnouncements(selectedIds); // This will show SweetAlert2 directly
            break;
        case 'restore':
            restoreBulkAnnouncements(selectedIds); // You'll need to update this too
            break;
        case 'delete':
            deleteBulkAnnouncements(selectedIds); // And this one
            break;
    }
    
    // Reset bulk action dropdown
    bulkAction.value = '';
}

// Bulk archive announcements
const archiveBulkAnnouncements = (ids) => {
    Swal.fire({
        title: 'Archive Announcements?',
        html: 
            `
            <div class="flex flex-col gap-2 text-left">
                <p class="text-base">You are about to archive <b>${ids.length}</b> announcement(s).</p>
                <div class="bg-blue-100 text-left p-2 border-l-4 border-l-blue-700 rounded-md">
                    <span class="text-sm">This will hide them from public view. You can restore them anytime in the archive section.</span>
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, archive them!',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        customClass: {
            confirmButton: 'bg-blue-500 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-300',
            cancelButton: 'bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition-colors duration-200'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'info',
                title: 'Archiving...',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            router.post(route('admin.bulk.archive.announcement'), { ids }, {
                preserveScroll: true,
                onSuccess: () => {
                    // Close the loading SweetAlert first
                    Swal.close();
                    
                    // Your existing success logic...
                    ids.forEach(id => {
                        let archivedAnnouncement = null;
                        const pinnedIndex = announcementsState.pinned.findIndex(a => a.id === id);
                        if (pinnedIndex !== -1) {
                            archivedAnnouncement = announcementsState.pinned.splice(pinnedIndex, 1)[0];
                        }
                        const regularIndex = announcementsState.regular.findIndex(a => a.id === id);
                        if (regularIndex !== -1) {
                            archivedAnnouncement = announcementsState.regular.splice(regularIndex, 1)[0];
                        }
                        if (archivedAnnouncement) {
                            archivedAnnouncement.archived_at = new Date().toISOString();
                            announcementsState.archived.unshift(archivedAnnouncement);
                        }
                    });
                    
                    selectedAnnouncements.value.clear();
                    
                    // Success notification (unchanged)
                    toast.success('Announcement(s) succesfully archived!');
                },
                onError: (errors) => {
                    Swal.close();
                    
                    // Error notification
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to archive announcements. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}


// Bulk restore announcements
const restoreBulkAnnouncements = (ids) => {
    Swal.fire({
        title: 'Restore Announcements?',
        html: 
            `
            <div class="flex flex-col gap-2 text-left">
                <p class="text-base">You are about to restore <b>${ids.length}</b> announcement(s) from the archive.</p>
                <div class="bg-green-100 text-left p-2 border-l-4 border-l-green-700 rounded-md">
                    <span class="text-sm text-green-500">These announcements will be visible to the public again once restored.</span>
                </div>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, restore them!',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        customClass: {
            confirmButton: 'bg-green-500 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200',
            cancelButton: 'bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition-colors duration-200'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'info',
                title: 'Restoring...',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            router.post(route('admin.bulk.restore.announcement'), { ids }, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.close();
                    ids.forEach(id => {
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
                    });
                    
                    // Clear selection
                    selectedAnnouncements.value.clear();
                    
                    // Success notification
                    toast.success('Announcement(s) restored successfully!');
                },
                onError: (errors) => {
                    Swal.close();
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to restore announcements. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

// Bulk delete announcements
const deleteBulkAnnouncements = (ids) => {
    Swal.fire({
        title: 'Delete Announcements?',
        html: 
            `
            <div class="flex flex-col gap-2 text-left">
                <p class="text-base">You are about to permanently delete ${ids.length} announcement(s).</p>
                <div class="bg-red-100 text-left p-2 border-l-4 border-l-red-700 rounded-md">
                    <span class="text-sm text-red-500">This action cannot be undone. The deleted announcements will be removed permanently from the system.</span>
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete permanently!',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        customClass: {
            confirmButton: 'bg-red-500 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200',
            cancelButton: 'bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition-colors duration-200'
        }
    }).then((result) => {
        Swal.fire({
                icon: 'info',
                title: 'Deleting...',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        if (result.isConfirmed) {
            router.post(route('admin.bulk.delete.announcements'), { ids }, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.close();
                    // Remove from all lists
                    announcementsState.pinned = announcementsState.pinned.filter(a => !ids.includes(a.id));
                    announcementsState.regular = announcementsState.regular.filter(a => !ids.includes(a.id));
                    announcementsState.archived = announcementsState.archived.filter(a => !ids.includes(a.id));
                    
                    // Clear selection
                    selectedAnnouncements.value.clear();
                    
                    // Success notification
                    toast.success('Announcement(s) deleted successfully!');
                },
                onError: (errors) => {
                    Swal.close();
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to delete announcements. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

// Clear all selections
const clearSelection = () => {
    selectedAnnouncements.value.clear();
}

const selectedAnnouncement = ref(null)
const showModal = ref(false)
const activeStatus = ref('pinned')
const searchQuery = ref('')

const sortStates = ref({
    pinned: 'newest',
    regular: 'newest', 
    archived: 'newest'
})

// Sorting function
const sortAnnouncements = (announcements, sortType) => {
    const sorted = [...announcements];
    
    switch(sortType) {
        case 'newest':
            return sorted.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        case 'oldest':
            return sorted.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
        case 'title_asc':
            return sorted.sort((a, b) => a.title.localeCompare(b.title));
        case 'title_desc':
            return sorted.sort((a, b) => b.title.localeCompare(a.title));
        default:
            return sorted;
    }
}

// FIXED: Correct computed properties
const filteredPinnedAnnouncements = computed(() => {
    let announcements = announcementsState.pinned;
    
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase().trim();
        announcements = announcements.filter(announcement => 
            announcement.title.toLowerCase().includes(query) ||
            announcement.content.toLowerCase().includes(query)
        );
    }
    
    return sortAnnouncements(announcements, sortStates.value.pinned);
});

const filteredRegularAnnouncements = computed(() => {
    let announcements = announcementsState.regular;
    
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase().trim();
        announcements = announcements.filter(announcement => 
            announcement.title.toLowerCase().includes(query) ||
            announcement.content.toLowerCase().includes(query)
        );
    }
    
    return sortAnnouncements(announcements, sortStates.value.regular);
});

const filteredArchivedAnnouncements = computed(() => {
    let announcements = announcementsState.archived;
    
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase().trim();
        announcements = announcements.filter(announcement => 
            announcement.title.toLowerCase().includes(query) ||
            announcement.content.toLowerCase().includes(query)
        );
    }
    
    return sortAnnouncements(announcements, sortStates.value.archived);
});

const currentDisplayedAnnouncements = computed(() => {
    switch(activeStatus.value) {
        case 'pinned': return filteredPinnedAnnouncements.value;
        case 'regular': return filteredRegularAnnouncements.value;
        case 'archive': return filteredArchivedAnnouncements.value;
        default: return [];
    }
});

const currentSortState = computed(() => {
    return sortStates.value[activeStatus.value];
});

const handleSortChange = (sortType) => {
    sortStates.value[activeStatus.value] = sortType;
}

const sortOptions = [
    { value: 'newest', label: 'Newest First', icon: 'arrow-down' },
    { value: 'oldest', label: 'Oldest First', icon: 'arrow-up' },
    { value: 'title_asc', label: 'Title A-Z', icon: 'text-asc' },
    { value: 'title_desc', label: 'Title Z-A', icon: 'text-desc' }
];

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

const getSortLabel = (sortType) => {
    const option = sortOptions.find(opt => opt.value === sortType);
    return option ? option.label : 'Sort By';
}

const getSortIcon = (sortType) => {
    const option = sortOptions.find(opt => opt.value === sortType);
    return option ? option.icon : 'sort';
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

            <!-- Tab Pill -->
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

                        <div class="flex item-center gap-2">
                            <!-- Bulk Actions Dropdown -->
                            <div v-if="hasSelectedAnnouncements" class="relative group">
                                <button
                                    class="flex items-center gap-2 px-4 py-3 text-sm font-medium text-white bg-blue-700 border border-blue-600 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 shadow-sm"
                                    type="button"
                                >
                                    <img :src="`/Images/SVG/check-circle.svg`" alt="Bulk Actions" class="h-4 w-4">
                                    <span class="min-w-[120px] text-left">{{ selectedAnnouncements.size }} selected</span>
                                    <svg class="w-4 h-4 text-blue-200 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                
                                <!-- Bulk Actions Dropdown Menu -->
                                <div class="absolute right-0 z-50 mt-1 w-56 origin-top-right bg-white border border-gray-200 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform scale-95 group-hover:scale-100">
                                    <div class="py-2">
                                        <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide border-b border-gray-100">
                                            Bulk Actions
                                        </div>
                                        <div class="py-1">
                                            <button
                                                v-for="action in bulkActions"
                                                :key="action.value"
                                                @click="bulkAction = action.value; executeBulkAction()"
                                                class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-left text-gray-700 hover:bg-blue-50 transition-colors"
                                            >
                                                <img :src="`/Images/SVG/${action.icon}.svg`" alt="" class="h-4 w-4">
                                                <span class="flex-1">{{ action.label }}</span>
                                            </button>
                                        </div>
                                        <div class="border-t border-gray-100 pt-1">
                                            <button
                                                @click="clearSelection"
                                                class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-left text-gray-500 hover:bg-gray-50 transition-colors"
                                            >
                                                <img :src="`/Images/SVG/x-circle.svg`" alt="Clear" class="h-4 w-4">
                                                <span class="flex-1">Clear Selection</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sorting -->
                            <div v-if="activeStatus !== 'archive'" class="relative group">
                                <div class="relative group">
                                    <button
                                        class="flex items-center gap-2 px-4 py-3 text-sm font-medium text-gray-700 bg-white border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 shadow-sm"
                                        type="button"
                                    >
                                        <span class="min-w-[100px] text-left">{{ getSortLabel(currentSortState) }}</span>
                                        <svg class="w-4 h-4 text-gray-400 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    
                                    <!-- Dropdown Menu -->
                                    <div class="absolute right-0 z-50 mt-1 w-56 origin-top-right bg-white border border-gray-200 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform scale-95 group-hover:scale-100">
                                        <div class="py-2">
                                            <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide border-b border-gray-100">
                                                Sort Announcements
                                            </div>
                                            <div class="py-1">
                                                <button
                                                    v-for="option in sortOptions"
                                                    :key="option.value"
                                                    @click="handleSortChange(option.value)"
                                                    :class="[
                                                        'flex items-center gap-3 w-full px-4 py-2.5 text-sm text-left transition-colors',
                                                        currentSortState === option.value 
                                                            ? 'text-blue-600 bg-blue-50 border-r-2 border-blue-600' 
                                                            : 'text-gray-700 hover:bg-gray-50'
                                                    ]"
                                                >
                                                    
                                                    <span class="flex-1">{{ option.label }}</span>
                                                    <svg 
                                                        v-if="currentSortState === option.value" 
                                                        class="w-4 h-4 text-blue-600" 
                                                        fill="currentColor" 
                                                        viewBox="0 0 20 20"
                                                    >
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <Link
                                href="/admin/announcements/create-announcement"
                                class="group bg-white flex items-center gap-2 text-gray-600 font-medium p-2 rounded-lg hover:bg-blue-700 hover:text-white transition-all duration-300  border-gray-300 shadow-sm w-auto"
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
                        </div>
                    </ul>

                    <!-- Select All Checkbox -->
                    <div v-if="currentDisplayedAnnouncements.length > 0" class="mt-4 flex items-center gap-3">
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input
                                type="checkbox"
                                :checked="isAllSelected"
                                @change="toggleSelectAll"
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                            >
                            <span v-if="isAllSelected">Deselect All</span>
                            <span v-else>Select All {{ currentDisplayedAnnouncements.length }} announcements</span>
                        </label>
                        
                        <span v-if="hasSelectedAnnouncements" class="text-sm text-blue-600 font-medium">
                            {{ selectedAnnouncements.size }} announcement(s) selected
                        </span>
                    </div>
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
                                    <div class="flex items-center gap-3">
                                        <div class="flex justify-center items-center">
                                            <span class="text-gray-500 text-xs group-hover:text-gray-200 transition-colors duration-300">ID: {{ pinned.id }}</span>
                                        </div>
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
                                <div class="flex justify-between mt-6">
                                    <div>
                                        <span class="text-xs text-gray-400 group-hover:text-gray-200 transition-colors duration-300">{{ formatDate( pinned.created_at) }}</span>
                                    </div>
                                    <input
                                        type="checkbox"
                                        :checked="selectedAnnouncements.has(pinned.id)"
                                        @click.stop="toggleAnnouncementSelection(pinned.id)"
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                    >
                                </div>
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
                                    <div class="flex items-center gap-3">
                                        <div class="flex justify-center items-center">
                                            <span class="text-gray-500 text-xs group-hover:text-gray-200 transition-colors duration-300">ID: {{ regular.id }}</span>
                                        </div>
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
                                <div class="flex justify-between mt-6">
                                    <div>
                                        <span class="text-xs text-gray-400 group-hover:text-gray-200 transition-colors duration-300">{{ formatDate( regular.created_at) }}</span>
                                    </div>
                                    <input
                                        type="checkbox"
                                        :checked="selectedAnnouncements.has(regular.id)"
                                        @click.stop="toggleAnnouncementSelection(regular.id)"
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                    >
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