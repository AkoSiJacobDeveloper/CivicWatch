<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import AnnouncementDetailsModal from '@/Components/AnnouncementDetailsModal.vue';

const props = defineProps({
    announcements: Object,
})

const pinnedAnnouncements = props.announcements.pinned
const regularAnnouncements = props.announcements.regular

const selectedAnnouncement = ref(null)
const showModal = ref(false)

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

// const moreDetails = (id) => {
//     router.visit(`/admin/announcements/${id}`); 
// }

const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleString('en-US', {
        month: 'long',
        day: 'numeric', 
        year: 'numeric', 
        hour: 'numeric',
        minute: '2-digit',
    })
}
</script>

<template>
    <Head title="Manage Categories" />

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
                    <Link
                        href="/admin/announcements/create-announcement"
                        class="group flex gap-2 text-gray-600 font-medium p-3 rounded-lg hover:bg-blue-700 hover:text-white transition-all duration-300 border border-gray-300 shadow-sm"
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
                        <p class="text-blue-900  ">
                            Keep our Barangay community informed and engaged. This platform allows you to 
                            share important updates, event invitations, safety alerts, and service announcements 
                            with residents. Ensure timely communication by scheduling posts and targeting 
                            specific community groups.
                        </p>
                    </div>
                </section>
                <section class=" flex flex-col gap-5">
                    <!--Pinned Announcement-->
                    <div>
                        <p class="text-sm font-medium font-[Poppins] text-gray-500">Important Notice</p>
                        <div 
                            v-if="pinnedAnnouncements.length === 0"
                            class="mt-3 flex items-center justify-center h-64 p-10 mb-2"
                            
                        >
                            <p class="text-gray-500 text-lg">No pinned announcements available.</p>
                        </div>

                        <div 
                            v-else
                            class="mt-3 grid grid-cols-3 gap-4"
                        >
                            <div 
                                v-for="pinned in pinnedAnnouncements"
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
                                    <div class="flex justify-end mt-6">
                                        <div>
                                            <span class="text-xs text-gray-400 group-hover:text-gray-200 transition-colors duration-300">{{ formatDate( pinned.created_at) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Regular Announcement-->
                        <div>
                            <p class="text-sm font-medium font-[Poppins] text-gray-500">Latest Announcement</p>
                            <div 
                                v-if="regularAnnouncements.length === 0"
                                class="mt-3 flex items-center justify-center h-64 p-10 mb-2"
                            >
                                <p class="text-gray-500 text-lg">No announcements available.</p>
                            </div>

                            <div 
                                v-else
                                class="mt-3 grid grid-cols-3 gap-4"
                            >
                                <div 
                                    v-for="regular in regularAnnouncements"
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
                                            <p class="line-clamp-2 truncate w-64 text-gray-400 text-sm group-hover:text-gray-200 transition-colors duration-300">{{ regular.content }}</p>
                                        </div>
                                    </div>
                                    <div class="flex justify-end mt-6">
                                        <div>
                                            <span class="text-xs text-gray-400 group-hover:text-gray-200 transition-colors duration-300">{{ formatDate( regular.created_at) }}</span>
                                        </div>
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
            />
    </AdminLayout>
</template>

<!-- @click="moreDetails(pinned.id)" -->
<!-- @click="moreDetails(regular.id)" -->