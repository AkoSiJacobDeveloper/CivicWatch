<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import { initTooltips } from 'flowbite';
import AdminLayout from '@/Layouts/AdminLayout.vue';

// const tabs = computed(() => [
//     {
//         text: 'Achievements',
//         value: 'pinned',
//         count: filteredPinnedAnnouncements.value.length
//     },
//     {
//         text: 'Archive Achievement',
//         value: 'archive',
//         count: filteredArchivedAnnouncements.value.length 
//     }
// ])

function applyFilter(value) {
    activeStatus.value = value;
}

onMounted(() => {
    initTooltips();
})
</script>

<template>
    <Head title="Achievements" />

    <AdminLayout>
        <main class="flex flex-col gap-7 text-sm">
            <section class="flex justify-between items-center">
                <div class="flex gap-1 items-center">
                    <img 
                        :src="'/Images/SVG/medal.svg'" 
                        alt="icon"
                        class="h-8"
                    >
                    <h1 class="font-semibold text-3xl font-[Poppins] my-3">Achievements</h1>
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
                        Celebrate the milestones of our Barangay community. This platform showcases completed projects, 
                        awards, and recognitions that highlight our collective progress and dedication. Inspire others 
                        by sharing the achievements that make our Barangay proud.
                    </p>
                </div>
            </section>

            <div class="flex flex-col gap-7">
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
                        href="/admin/achievements/create-achievements"
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
                        Record Achievement
                    </Link>
                </ul>
            </div>
        </main>
    </AdminLayout>
</template>