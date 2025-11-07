<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { initTooltips } from 'flowbite';
import { reactive } from 'vue';
import Swal from 'sweetalert2';
import { useToast } from 'vue-toastification';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import AchievementsDetailsModal from '@/Components/AchievementsDetailsModal.vue';

const toast = useToast();

const props = defineProps({
    achievements: Array,
    totalAchievements: Number,
    totalArchived: Number, 
    filters: Object 
})

const selectedAchievement = ref(null);
const showModal = ref(false)


onMounted(() => {
    initTooltips();

    if (!props.filters?.status) {
        console.log('No status filter found, redirecting to set default...');
        router.get('/admin/achievements', { status: 'active' }, {
            preserveState: true,
            replace: true
        });
    } else {
        console.log('Status filter already set:', props.filters.status);
    }
})

// Use the actual backend status values
const activeStatus = computed(() => props.filters?.status || 'active');

const openAchievement = (achievement) => {
    selectedAchievement.value = achievement
    showModal.value = true
}

const closeModal = () => {
    selectedAchievement.value = null
    showModal.value = false
}

const tabs = computed(() => [
    {
        text: 'Achievements',
        value: 'active', 
        count: props.totalAchievements
    },
    {
        text: 'Archive Achievements',
        value: 'archive', 
        count: props.totalArchived
    }
])

function applyFilter(value) {
    router.get('/admin/achievements', { status: value }, {
        preserveState: true,
        replace: true
    });
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

const handleArchived = (achievementId) => {
    console.log('Achievement archived:', achievementId)
    closeModal();
}

const handleRestored = (achievementId) => {
    console.log('Achievement restored:', achievementId)
    closeModal();
}

const handleDeleted = (achievementId) => {
    console.log('Achievement deleted:', achievementId)
    closeModal();
}

// Add sorting state
const sortStates = ref({
    active: 'newest',
    archive: 'newest'
})

const currentSortState = computed(() => {
    return sortStates.value[activeStatus.value];
});

const handleSortChange = (sortType) => {
    sortStates.value[activeStatus.value] = sortType;
}

const sortedAchievements = computed(() => {
    return filteredAndSortedAchievements.value;
});

const sortOptions = [
    { value: 'newest', label: 'Newest First', icon: 'arrow-down' },
    { value: 'oldest', label: 'Oldest First', icon: 'arrow-up' },
    { value: 'title_asc', label: 'Title A-Z', icon: 'text-asc' },
    { value: 'title_desc', label: 'Title Z-A', icon: 'text-desc' }
];

// Sorting function
const sortAchievements = (achievements, sortType) => {
    const sorted = [...achievements];
    
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

const getSortLabel = (sortType) => {
    const option = sortOptions.find(opt => opt.value === sortType);
    return option ? option.label : 'Sort By';
}

const searchQuery = ref('');
const clearSearch = () => {
    searchQuery.value = '';
}

const filteredAndSortedAchievements = computed(() => {
    let filtered = props.achievements;
    
    // Apply search filter if search query exists
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase().trim();
        filtered = filtered.filter(achievement => 
            achievement.title.toLowerCase().includes(query) ||
            (achievement.content && achievement.content.toLowerCase().includes(query))
        );
    }
    
    // Apply sorting to the filtered results
    return sortAchievements(filtered, currentSortState.value);
});

const achievementsState = reactive({
    current: [...props.achievements || []]
})

const selectedAchievements = ref(new Set());
const bulkAction = ref('');

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

const toggleSelectAll = () => {
    const currentAchievements = sortedAchievements.value;
    
    if (selectedAchievements.value.size === currentAchievements.length) {
        // Deselect all
        selectedAchievements.value.clear();
    } else {
        // Select all
        currentAchievements.forEach(achievement => {
            selectedAchievements.value.add(achievement.id);
        });
    }
}

const toggleAchievementSelection = (id, event) => {
    event.stopPropagation();
    if (selectedAchievements.value.has(id)) {
        selectedAchievements.value.delete(id);
    } else {
        selectedAchievements.value.add(id);
    }
}

const isAllSelected = computed(() => {
    const currentAchievements = sortedAchievements.value;
    return currentAchievements.length > 0 && 
            selectedAchievements.value.size === currentAchievements.length;
});

const hasSelectedAchievements = computed(() => {
    return selectedAchievements.value.size > 0;
});

const executeBulkAction = () => {
    if (!bulkAction.value || !hasSelectedAchievements.value) return;

    const selectedIds = Array.from(selectedAchievements.value);
    
    switch(bulkAction.value) {
        case 'archive':
            archiveBulkAchievements(selectedIds);
            break;
        case 'restore':
            restoreBulkAchievements(selectedIds);
            break;
        case 'delete':
            deleteBulkAchievements(selectedIds);
            break;
    }
    
    bulkAction.value = '';
}

const archiveBulkAchievements = (ids) => {
    Swal.fire({
        title: 'Archive Achievements?',
        html: 
            `
            <div class="flex flex-col gap-2 text-left">
                <p class="text-base">You are about to archive <b>${ids.length}</b> achievement(s).</p>
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

            router.post(route('admin.bulk-archived.achievement'), { ids }, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.close();
                    achievementsState.current = achievementsState.current.filter(a => !ids.includes(a.id));
                    selectedAchievements.value.clear();
                    toast.success('Achievement(s) successfully archived!');
                },
                onError: (errors) => {
                    Swal.close();
                    
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to archive achievements. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

// Bulk restore achievements
const restoreBulkAchievements = (ids) => {
    Swal.fire({
        title: 'Restore Achievements?',
        html: 
            `
            <div class="flex flex-col gap-2 text-left">
                <p class="text-base">You are about to restore <b>${ids.length}</b> achievement(s) from the archive.</p>
                <div class="bg-green-100 text-left p-2 border-l-4 border-l-green-700 rounded-md">
                    <span class="text-sm text-green-500">These achievements will be visible to the public again once restored.</span>
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

            router.post(route('admin.bulk-restore.achievement'), { ids }, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.close();
                    
                    // Remove restored achievements from current view
                    achievementsState.current = achievementsState.current.filter(a => !ids.includes(a.id));
                    
                    selectedAchievements.value.clear();
                    
                    toast.success('Achievement(s) restored successfully!');
                },
                onError: (errors) => {
                    Swal.close();
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to restore achievements. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

// Bulk delete achievements
const deleteBulkAchievements = (ids) => {
    Swal.fire({
        title: 'Delete Achievements?',
        html: 
            `
            <div class="flex flex-col gap-2 text-left">
                <p class="text-base">You are about to permanently delete ${ids.length} achievement(s).</p>
                <div class="bg-red-100 text-left p-2 border-l-4 border-l-red-700 rounded-md">
                    <span class="text-sm text-red-500">This action cannot be undone. The deleted achievements will be removed permanently from the system.</span>
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
            router.delete(route('admin.bulk-deletes.achievement'), { 
                data: { ids },
                preserveScroll: true,
                onSuccess: () => {
                    Swal.close();
                    
                    // Remove from current view
                    achievementsState.current = achievementsState.current.filter(a => !ids.includes(a.id));                    
                    selectedAchievements.value.clear();
                    
                    toast.success('Achievement(s) deleted successfully!');
                },
                onError: (errors) => {
                    Swal.close();
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to delete achievements. Please try again.',
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
    selectedAchievements.value.clear();
}
</script>

<template>
    <Head title="Achievements" />

    <AdminLayout>
        <main class="flex flex-col gap-7 text-sm">
            <section class="flex justify-between items-center">
                <div class="flex gap-3 items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <img 
                            :src="'/Images/SVG/medal.svg'" 
                            alt="icon"
                            class="h-8"
                        >
                    </div>
                    <div>   
                        <h1 class="font-semibold text-3xl font-[Poppins]">Achievements</h1>
                        <p class="text-gray-600 text-sm">Celebrate success and milestones</p>
                    </div>
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
                            placeholder="Search achievements title..."
                            v-model="searchQuery"
                        >
                        <!-- Clear search button (X) when there's text -->
                        <button
                            v-if="searchQuery"
                            @click="clearSearch"
                            class="absolute right-20 text-gray-500 hover:text-gray-700 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
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
                        Celebrate the milestones of our Barangay community. This platform showcases completed projects, 
                        awards, and recognitions that highlight our collective progress and dedication. Inspire others 
                        by sharing the achievements that make our Barangay proud.
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

                        <div class="flex items-center gap-2">
                            <!-- Bulk Action -->
                            <div v-if="hasSelectedAchievements" class="relative group">
                                <button
                                    class="flex items-center gap-2 px-4 py-3 text-sm font-medium text-white bg-blue-700 border border-blue-600 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 shadow-sm"
                                    type="button"
                                >
                                    <img :src="`/Images/SVG/check-circle.svg`" alt="Bulk Actions" class="h-4 w-4">
                                    <span class="min-w-[120px] text-left">{{ selectedAchievements.size }} selected</span>
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
                                href="/admin/achievements/create-achievements"
                                class="group bg-white flex items-center gap-2 text-gray-600 font-medium p-3 rounded-lg hover:bg-blue-700 hover:text-white transition-all duration-300  border-gray-300 shadow-sm w-auto"
                            >
                                <img 
                                    :src="'/Images/SVG/plus-circle.svg'" 
                                    alt="Icon" 
                                    class="h-5 block group-hover:hidden">
                                <img 
                                    :src="'/Images/SVG/plus-circle (white).svg'" 
                                    alt="Icon White" 
                                    class="h-5 hidden group-hover:block">
                                Record Achievement
                            </Link>
                        </div>
                    </ul>
                    <div v-if="sortedAchievements.length > 0" class=" mt-2 flex items-center gap-3">
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input
                                type="checkbox"
                                :checked="isAllSelected"
                                @change="toggleSelectAll"
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                            >
                            <span v-if="isAllSelected">Deselect All</span>
                            <span v-else>Select All {{ sortedAchievements.length }} achievements</span>
                        </label>
                        
                        <span v-if="hasSelectedAchievements" class="text-sm text-blue-600 font-medium">
                            {{ selectedAchievements.size }} achievement(s) selected
                        </span>
                    </div>
                </div>
            </div>

            <div v-if="sortedAchievements.length === 0" class="mt-3 flex flex-col items-center justify-center h-64 p-10 mb-2">
                <img 
                    :src="'/Images/SVG/not found.svg'" 
                    alt="SVG" 
                    class="h-44"
                >
                <p class="text-gray-500 text-lg">
                    {{ searchQuery ? `No achievements found for "${searchQuery}"` : 'No achievements available.' }}
                </p>
                <button 
                    v-if="searchQuery"
                    @click="clearSearch"
                    class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                >
                    Clear Search
                </button>
            </div>


            <div class="mt-3 grid grid-cols-3 gap-4">
                <div
                    v-for="achievement in sortedAchievements"
                    :key="achievement.id"
                    @click="openAchievement(achievement)"
                    class="group h-auto shadow-container-announcement mb-2 bg-white hover:cursor-pointer hover:bg-blue-700 transition-all duration-300 transform hover:-translate-y-1 flex flex-col justify-between"
                >   
                    <div class="w-full h-72 object-cover p-1">
                        <img 
                            v-if="achievement.featured_image === null"
                            :src="'/Images/image_placeholder.png'" 
                            alt="img" 
                            class="h-full w-full rounded-2xl"
                        >

                        <img 
                            v-else
                            :src="`/storage/${achievement.featured_image}`"
                            alt="img"
                            class="h-full w-full rounded-2xl"
                        >
                        <!-- <img :src="'/Images/image_placeholder_gpt.png'" alt="img" class="h-full w-full rounded-2xl"> -->
                    </div>

                    <div class="mt-1 flex flex-col p-5">
                        <div class="flex justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <div class="flex justify-center items-center">
                                    <span class="text-gray-500 text-xs group-hover:text-gray-200 transition-colors duration-300">ID: {{ achievement.id }}</span>
                                </div>
                            </div>

                            <span v-if="achievement.archived_at === null" class="bg-blue-700 text-white text-xs font-semibold px-2 py-1 rounded flex items-center gap-1 group-hover:bg-blue-500 transition-all duration-300 capitalize">
                                <img :src="'/Images/SVG/check-circle.svg'" alt="Icon" class="h-4 w-4">
                                {{ achievement.status }}
                            </span>

                            <span v-else class="bg-gray-500 text-white text-xs font-semibold px-2 py-1 rounded flex items-center gap-1 group-hover:bg-blue-500 transition-all duration-300 capitalize">
                                <img :src="'/Images/SVG/archive.svg'" alt="Icon" class="h-4 w-4">
                                Archived
                            </span>
                        </div>

                        <div class="flex justify-between items-start">
                            <div class="min-w-0 flex-1">
                                <h1 class="truncate text-blue-500 font-semibold text-lg font-[Poppins] group-hover:text-gray-200 transition-colors duration-300">
                                    {{ achievement.title }}
                                </h1>
                                <p class="line-clamp-2 text-gray-400 text-sm group-hover:text-gray-200 transition-colors duration-300 mt-1">
                                    {{ achievement.content }}
                                </p>
                            </div>
                        </div>
                        <div class="flex justify-between py-3 mt-2">
                            <div>
                                <span v-if="achievement.archived_at === null" class="text-xs text-gray-400 group-hover:text-gray-200 transition-colors duration-300">{{ formatDate( achievement.created_at) }}</span>

                                <span v-else class="text-xs text-gray-400 group-hover:text-gray-200 transition-colors duration-300" >
                                    Archived: {{ formatDate( achievement.archived_at) }}
                                </span>
                            </div>
                            <input
                                v-if="achievement.archived_at === null"
                                type="checkbox"
                                :checked="selectedAchievements.has(achievement.id)"
                                @click.stop="toggleAchievementSelection(achievement.id, $event)"
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                            >
                        </div>
                    </div>
                    
                </div>
            </div> 
        </main>

        <AchievementsDetailsModal 
            :show="showModal"
            :achievements="selectedAchievement"
            @close="closeModal"
            @archived="handleArchived"
            @restored="handleRestored"
            @deleted="handleDeleted"
        />
    </AdminLayout>
</template>

<!-- :checked="selectedAchievement.has(achievement.id)" -->