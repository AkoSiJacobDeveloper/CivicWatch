<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

import AdminLayout from '@/Layouts/AdminLayout.vue';

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
                <section>
                        
                </section>
            </main>
    </AdminLayout>
</template>