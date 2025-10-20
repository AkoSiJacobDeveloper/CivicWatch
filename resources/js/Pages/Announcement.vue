<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import AnnouncementClientDetailsModal from '@/Components/AnnouncementClientDetailsModal.vue';

const props = defineProps({
    announcements: Object,
    sort: String
})

// Use ref for sortOrder and watch for prop changes
const sortOrder = ref(props.sort || 'desc')

// Watch for changes in props.sort and update sortOrder
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
        <main class=" dark:text-[#FAF9F6] ">
            <section class="hero-section h-screen text-[#000] md:px-10 lg:px-32 flex ">
                <div class="w-full h-full md:w-1/2 md:flex justify-center items-center">
                    <div class="">
                        <h1 class="text-6xl font-bold font-[Poppins] mb-5 text-blue-700">Announcements</h1>
                        <p class="text-justify dark:text-white">Stay informed with the latest news, updates, and important announcements from your community. This page serves as your reliable source for events, reminders, and public information. Check back regularly to stay connected and never miss an important update or opportunity to get involved.</p>
                    </div>
                </div>
                <div class="hidden md:w-1/2 h-full md:flex justify-center items-center">
                    <div class="hidden md:flex justify-end">
                        <img :src="'/Images/online_information.svg'" alt="Announcement" class="w-[30rem]">
                    </div>
                </div>
            </section>

            <section class="md:px-10 lg:px-32 py-20 flex flex-col gap-10">
                <div class="flex justify-between items-center">
                    <div class="">
                        <h2 class="text-2xl lg:text-4xl font-bold font-[Poppins] dark:text-white ">Announcements</h2>
                        <p class="text-sm md:text-base text-gray-500 dark:text-[#FAF9F6]">Here's what's happening around you today.</p>
                    </div>

                    <!-- Sort Button -->
                    <div>
                        <button
                            @click="toggleSort"
                            class="border p-3 rounded flex items-center gap-2 hover:bg-blue-500 hover:text-white dark:hover:bg-gray-700 transition-colors group duration-300"
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
                </div>
                

                <div v-if="!announcements.data || announcements.data.length === 0"  class="text-center text-gray-500">
                    <p class="text-xl mb-4 py-20">No announcement yet. </p>
                </div>

                <div v-else class="grid grid-rows-1">
                    <div v-for="announcement in props.announcements.data" :key="announcement.id" class="shadow-lg mb-3 rounded-lg border-b-1 border-[#000] p-8 bg-white flex flex-col gap-6 dark:shadow-md dark:rounded-lg dark:bg-[#2c2c2c] ">
                        <div class="flex flex-col gap-3">
                            <div class="flex justify-between mb-2">
                                <h1 class="font-[Poppins] font-semibold text-lg">{{ announcement.title }}</h1>
                                <p class="text-gray-500 text-sm">{{ formatDate(announcement.event_date) }}</p>
                            </div>
                            <div class="mb-2">
                                <p class="font-light">{{ announcement.content }}</p>
                            </div>
                            <div class="flex justify-between">
                                <div class="flex items-center gap-5">
                                    <p
                                        class="p-2 uppercase rounded text-xs"
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
                                    <svg xmlns="http://www.w3.org/2000/svg" class="group-hover:text-blue-500" width="15" height="15" fill="currentColor" viewBox="0 0 256 256"><path d="M221.66,133.66l-72,72a8,8,0,0,1-11.32-11.32L196.69,136H40a8,8,0,0,1,0-16H196.69L138.34,61.66a8,8,0,0,1,11.32-11.32l72,72A8,8,0,0,1,221.66,133.66Z"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="announcements.data && announcements.data.length > 0" class="flex justify-end mt-10">
                    <div class="flex items-center gap-3 rounded">
                        <template v-for="link in (props.announcements.links || [])" :key="link?.label || 'empty'">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                :class="['w-10 h-10 grid place-items-center border border-gray-400 rounded justify-center hover:bg-blue-400 transition-all duration-300 hover:text-[#FAF9F6] font-[Poppins]', link.active ? 'bg-blue-600 text-[#FAF9F6] border-none' : '']"
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
                                :class="'px-3 py-1 text-gray-500 cursor-not-allowed'"
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
        <AnnouncementClientDetailsModal
            :show="openModal"
            :announcements="selectedAnnouncement"
            @close="closeModal"
        />
    </GuestLayout>
</template>