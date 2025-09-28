<script setup>
import { Head } from '@inertiajs/vue3';
// import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';


</script>

<template>
    <Head title="Manage Categories" />

    <AdminLayout>
        <main class="flex flex-col gap-5 text-sm">
            <div class="flex justify-between items-center">
                <div class="flex gap-1 items-center">
                    <img 
                        :src="'/Images/SVG/map-pin-area (black).svg'" 
                        alt="icon"
                        class="h-8"
                    >
                    <h1 class="font-semibold text-3xl font-[Poppins] my-3">Locations</h1>
                </div>
            </div>
        </main>
    </AdminLayout>
</template>