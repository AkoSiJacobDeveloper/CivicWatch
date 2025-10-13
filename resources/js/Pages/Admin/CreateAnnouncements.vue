<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import AnnouncementForm from '@/Components/AnnouncementForm.vue';

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

const props = defineProps({
    announcementCategories: Array,
    audiences: Array,
    activePuroks: Array,
    offices: Array,
    documents: Array,
})


</script>

<template>
    <Head title="Create Announcement" />

    <AdminLayout>
        <main class="flex flex-col gap-7 text-sm">
            <section class="flex justify-between mt-3">
                <div class="flex gap-2">
                    <Link :href="backUrl">
                        <img :src="'/Images/SVG/arrow-circle-left-fill (700).svg'" alt="Back Icon">
                    </Link>
                    <h1 class="font-semibold text-3xl font-[Poppins]">Create Announcement</h1>
                </div>
                <div class="bg-blue-200 p-3 rounded-lg flex gap-2 border-l-4 border-blue-900">
                    <div class="flex justify-center">
                        <img 
                            :src="'/Images/SVG/info (blue).svg'" 
                            alt="Info Icon"
                            class="h-5"
                        >
                    </div>
                    <p class="text-blue-900  ">
                        Need help? Click the <span>'<img :src="'/Images/SVG/question.svg'" alt="Icon" class="h-5 inline-flex">'</span> icon beside each field label for guidance
                    </p>
                </div>
            </section>

            <section>
                <AnnouncementForm 
                    :announcement-categories="announcementCategories" 
                    :active-puroks="activePuroks"
                    :audiences="audiences"
                    :offices="offices"
                    :documents="documents"
                />
            </section>
        </main>
    </AdminLayout>
</template>