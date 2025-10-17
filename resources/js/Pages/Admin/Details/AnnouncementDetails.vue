<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    announcement: Object
})

const urlParams = new URLSearchParams(window.location.search);
const fromParam = urlParams.get('from');

const backUrl = computed(() => {
    switch(fromParam) {
        case 'announcements':
            return '/admin/announcements'
        default:
            return '/admin/announcements';
    }
})
</script>

<template>
    <Head title="Announcements Details" />

    <AdminLayout>
        <main class="flex flex-col gap-7 text-sm">
            <section class="flex justify-between items-center mt-3">
                <div class="flex gap-1">
                    <Link :href="backUrl">
                        <img :src="'/Images/SVG/arrow-circle-left-fill (700).svg'" alt="Back Icon">
                    </Link>
                    <h1 class="font-semibold text-3xl font-[Poppins]">Edit Announcement</h1>
                </div>
            </section>

            <section class="bg-white shadow-lg rounded-lg">
                <!-- Header -->
                <header class="px-10 py-7 rounded-lg border-b border-gray-200 mb-5 shadow">
                    <div class="flex justify-between">
                        <div class="mt-3">
                            <span class="text-gray-500 font-[Poppins] font-medium">Title:</span>
                            <h3 class="font-bold text-2xl">{{ announcement.title }}</h3>
                        </div>

                        <span
                            v-if="announcement.is_pinned == 1"
                            class="bg-blue-700 text-white text-sm font-semibold px-3 py-1 rounded flex items-center gap-1 group-hover:bg-blue-500 transition-all duration-300 h-10 mt-6"
                        >
                            <img :src="'/Images/SVG/push-pin.svg'" alt="Pin" class="h-5 w-5">
                            Pinned
                        </span>

                        <span
                            v-else
                            class="bg-blue-100 text-blue-700 text-sm font-semibold px-3 py-1 rounded flex items-center gap-1 mt-6"
                        >
                            <img :src="'/Images/SVG/megaphone (700).svg'" alt="Icon"  class="h-5 w-5">
                            Regular
                        </span>

                        <!-- <span class="bg-blue-700 text-white text-xs font-semibold px-2 py-1 rounded flex items-center gap-1 group-hover:bg-blue-500 transition-all duration-300">
                            <img :src="'/Images/SVG/push-pin.svg'" alt="Pin" class="h-4 w-4">
                            Pinned
                        </span> -->
                    </div>
                </header>
            </section>
        </main>
    </AdminLayout>
</template>