<script setup>
import { Link } from '@inertiajs/vue3';

import Header from '@/Components/Header.vue';
import Footer from '@/Components/Footer.vue';
</script>

<template>
    <div class="">
        <Header />
        <main class="">
            <slot></slot>
        </main>
        <Footer />
    </div>
</template>
