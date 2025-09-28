<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex gap-2 w-[60%] md:w-[50%]">
        <Link href="/about" as="button" class="grow flex justify-center items-center bg-blue-600 py-2 px-3 md:py-3 md:px-4 rounded gap-1 hover:bg-blue-700 transition duration-300 w-[14vw]">
            <font-awesome-icon icon="info-circle" class="text-xs text-white  flex justify-center items-center" />
            <p class="text-white text-xs">Learn More</p>
        </Link>

        <Link href="/report-issue" as="button" class="grow flex justify-center items-center bg-blue-600 py-2 px-3 md:py-3 md:px-4 rounded gap-1 hover:bg-blue-700 transition duration-300 w-[14vw]">
            <font-awesome-icon icon="paper-plane" class="text-xs text-white" />
            <p class="text-white text-xs">Report Issue</p>
        </Link>
    </div>
</template>