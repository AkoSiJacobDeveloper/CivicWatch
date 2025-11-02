<script setup>
import Sidebar from '@/Components/Sidebar.vue';
</script>

<template>
    <div class="flex overflow-hidden">
        <Sidebar class="h-screen" />
        <main class="bg-[#FAFAFA] p-5 h-screen flex-1 overflow-y-auto">
            <slot></slot>
        </main>
    </div>
</template>
