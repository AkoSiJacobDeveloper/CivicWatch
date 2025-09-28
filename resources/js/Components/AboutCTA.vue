<script setup>
import { Link } from '@inertiajs/vue3';

const links = [
    { label: 'Submit Report', url: '/report-issue' },
    { label: 'Track Report', url: '/track-reports '},
]
</script>

<template>
    <div class="flex gap-1 text-center">
        <Link :href="link.url" v-for="(link, index) in links" :key="index" class="text-[#FAF9F6] w-[30%] p-3 bg-blue-500 rounded hover:bg-blue-700 transition-colors duration-300">
            {{ link.label }}
        </Link>
    </div>
</template>