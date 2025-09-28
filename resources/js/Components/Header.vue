<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

import ApplicationLogo from './ApplicationLogo.vue';

const page = usePage();

const links = [
    { name: 'Home', url: '/' },
    { name: 'Report Issue', url: '/report-issue' },
    { name: 'Track Report', url: '/track-reports' },
    { name: 'Review', url: '/review' }, 
    { name: 'FAQ', url: '/faq' },
    { name: 'About', url: '/about' },
    { name: 'Contact Us', url: '/contact-us' }
];

const mblinks = [...links];

const isOpen = ref(false);
function toggleMenu() {
    isOpen.value = !isOpen.value;
}

const savedTheme = localStorage.getItem('theme');
const isDark = ref(savedTheme === 'dark');

if (isDark.value) {
    document.documentElement.classList.add('dark');
} else {
    document.documentElement.classList.remove('dark');
}

const toggleDarkMode = () => {
    isDark.value = !isDark.value;
    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

</script>

<template>
    <header class="bg-blue-700 flex md:justify-between p-3 md:py-5 md:px-10 lg:px-32 fixed w-full z-[9999] dark:bg-[#1e1e1e] dark:text-[#FAF9F6]">
        <div class="flex justfify-between w-full">
            <ApplicationLogo />
            <nav class="flex text-center items-center">
                <!-- Desktop Navigation Bar -->
                <ul class="hidden md:flex space-x-4 text-white">
                    <li v-for="(link, index) in links" :key="index">
                        <div :class="[ page.url === link.url ? 'border-b-2 border-[#faf9f6] dark:border-[#1d4ed8]' : 'text-[#FAF9F6]' ]">
                            <Link :href="link.url" >{{ link.name }}</Link>
                        </div>
                    </li>
                </ul>

                <!--Mobile Hamburger Button -->
                <button @click="toggleMenu" class="md:hidden flex justify-center">
                    <svg v-if="!isOpen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" class="size-4">
                        <path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clip-rule="evenodd" />
                    </svg>

                    <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" class="size-4">
                        <path fill-rule="evenodd" d="M11.47 7.72a.75.75 0 0 1 1.06 0l7.5 7.5a.75.75 0 1 1-1.06 1.06L12 9.31l-6.97 6.97a.75.75 0 0 1-1.06-1.06l7.5-7.5Z" clip-rule="evenodd" />
                    </svg>
                </button>

                <!-- Mobile Navigation Bar -->
                <div v-if="isOpen" class="text-white md:hidden bg-blue-700 p-5 border-t mt-[64px] fixed top-0 left-0 w-full z-40">
                    <ul>
                        <li v-for="(mblink, index) in mblinks" :key="index" class="text-left mb-5">
                            <Link :href="mblink.url" class="text-lg">{{ mblink.name }}</Link>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
        <div class="flex items-center">
            <!-- <font-awesome-icon v-if="isDark" icon="moon" class="text-white" />
            <font-awesome-icon v-else icon="sun" class="text-white" /> -->
            <button @click="toggleDarkMode" class="ml-4">
                <font-awesome-icon :icon="isDark ? 'sun' : 'moon'" class="text-white hover:text-blue-600 transition-colors duration-200" />
            </button>
        </div>
    </header>
</template>