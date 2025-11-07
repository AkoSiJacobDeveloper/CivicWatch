<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationImage from './ApplicationImage.vue';
</script>

<template>
    <Link href="/">
        <div class="flex items-center gap-1">
            <!-- <img :src="'/Images/Cabulijan/Official Cabulijan Logo.png'" alt="Cabulijan Logo" class="h-10 md:h-14 lg:h-14 w-auto"> -->
            <ApplicationImage />
            <div class="flex flex-col">
                <div class="flex flex-col leading-none">
                    <div class="border-b-2 border-white">
                        <p class="text-2xl font-bold font-[Poppins] text-white">CivicWatch</p>
                    </div>
                    <p class="text-xs mt-1 font-semibold text-blue-900">REPORT.ACT.RESOLVE</p>
                </div>
            </div>
        </div>
    </Link>
</template>
