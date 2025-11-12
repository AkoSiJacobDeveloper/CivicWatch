<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';

import ApplicationLogo from './ApplicationLogo.vue';

const page = usePage();

const links = [
    { name: 'Home', url: '/' },
    { name: 'Report Issue', url: '/report-issue' },
    { name: 'Track Report', url: '/track-reports' },
    { name: 'Reported Issues', url: '/reported-issues' },
    { name: 'Review', url: '/review' }, 
    { name: 'Announcements', url: '/user-announcements' },
    { name: 'Achievements', url: '/brgy-achievements' },
    // { name: 'FAQ', url: '/faq' },
    // { name: 'About', url: '/about' },
    // { name: 'Contact Us', url: '/contact-us' },
];

const mblinks = [...links];

const isOpen = ref(false);
const isDark = ref(false);

function toggleMenu() {
    isOpen.value = !isOpen.value;
    // Prevent body scroll when menu is open
    if (isOpen.value) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
}

function closeMenu() {
    isOpen.value = false;
    document.body.style.overflow = '';
}

const isActiveLink = (linkUrl) => {
    const currentUrl = page.url;

    if (linkUrl === '/') {
        return currentUrl === '/';
    }
    return currentUrl.startsWith(linkUrl);
};

// Dark mode functionality
const initializeDarkMode = () => {
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
        isDark.value = true;
        document.documentElement.classList.add('dark');
    } else {
        isDark.value = false;
        document.documentElement.classList.remove('dark');
    }
};

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

// Close menu when clicking outside - FIXED VERSION
const handleClickOutside = (event) => {
    const headerElement = document.querySelector('header');
    if (isOpen.value && headerElement && !headerElement.contains(event.target)) {
        closeMenu();
    }
};

// Close menu on escape key
const handleEscapeKey = (event) => {
    if (event.key === 'Escape' && isOpen.value) {
        closeMenu();
    }
};

onMounted(() => {
    initializeDarkMode();
    document.addEventListener('click', handleClickOutside);
    document.addEventListener('keydown', handleEscapeKey);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('keydown', handleEscapeKey);
    document.body.style.overflow = ''; // Cleanup
});
</script>

<template>
    <header class="bg-blue-700 flex justify-between p-4 md:py-5 md:px-10 lg:px-32 fixed w-full z-50 dark:bg-[#1e1e1e] dark:text-[#FAF9F6]">
        <div class="flex justify-between items-center w-full">
            <!-- Logo Section -->
            <div class="flex items-center gap-2">
                <ApplicationLogo />
                
            </div>

            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center">
                <ul class="flex flex-wrap text-white space-x-2">
                    <li v-for="(link, index) in links" :key="index">
                        <div :class="[
                            'pb-1 transition-all duration-200',
                            isActiveLink(link.url) 
                                ? 'border-b-2 border-white dark:border-blue-400 font-semibold' 
                                : 'text-white/90 hover:text-white hover:border-b-2 hover:border-white/50'
                        ]">
                            <Link 
                                :href="link.url" 
                                class="text-sm lg:text-base px-2 py-1 transition-colors duration-200 whitespace-nowrap"
                            >
                                {{ link.name }}
                            </Link>
                        </div>
                    </li>
                </ul>
            </nav>

            <!-- Right Section - Dark Mode Toggle & Mobile Menu -->
            <div class="flex items-center space-x-3 md:space-x-4">
                <!-- Dark Mode Toggle -->
                <button 
                    @click="toggleDarkMode" 
                    class="p-2 rounded-full bg-white/10 hover:bg-white/20 transition-all duration-200 backdrop-blur-sm"
                    aria-label="Toggle dark mode"
                >
                    <font-awesome-icon 
                        :icon="isDark ? 'sun' : 'moon'" 
                        class="text-white size-4 md:size-5 transition-transform duration-300 hover:scale-110" 
                    />
                </button>

                <!-- Mobile Hamburger Button -->
                <button 
                    @click="toggleMenu" 
                    class="md:hidden flex items-center justify-center p-2 rounded-full bg-white/10 hover:bg-white/20 transition-all duration-200"
                    aria-label="Toggle menu"
                >
                    <svg 
                        v-if="!isOpen" 
                        xmlns="http://www.w3.org/2000/svg" 
                        viewBox="0 0 24 24" 
                        fill="white" 
                        class="size-5 transition-transform duration-300"
                    >
                        <path fill-rule="evenodd" d="M3 6.75A.75.75 0 0 1 3.75 6h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 6.75ZM3 12a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 12Zm0 5.25a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                    </svg>

                    <svg 
                        v-else 
                        xmlns="http://www.w3.org/2000/svg" 
                        viewBox="0 0 24 24" 
                        fill="white" 
                        class="size-5 transition-transform duration-300"
                    >
                        <path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 0 1 1.06 0L12 10.94l5.47-5.47a.75.75 0 1 1 1.06 1.06L13.06 12l5.47 5.47a.75.75 0 1 1-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 0 1-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div 
            v-if="isOpen" 
            class="md:hidden absolute top-full left-0 w-full bg-blue-700  shadow-lg border-t border-blue-600 dark:border-gray-700 transition-all duration-300 ease-in-out z-40 max-h-[80vh] overflow-y-auto"
            :class="isOpen ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-2'"
        >
            <div class="px-6 py-4">
                <ul class="space-y-1">
                    <li 
                        v-for="(mblink, index) in mblinks" 
                        :key="index" 
                        class="border-b border-blue-600/30 dark:border-gray-600/30 last:border-b-0"
                    >
                        <Link 
                            :href="mblink.url" 
                            @click="closeMenu"
                            :class="[
                                'block py-4 text-base font-medium transition-all duration-200 px-3 rounded-lg',
                                isActiveLink(mblink.url)
                                    ? 'text-white bg-blue-600/20 dark:bg-blue-500/20 border-l-4 border-white dark:border-blue-400'
                                    : 'text-white/90 hover:text-white hover:bg-white/10'
                            ]"
                        >
                            {{ mblink.name }}
                        </Link>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Backdrop for Mobile Menu -->
        <div 
            v-if="isOpen" 
            @click="closeMenu"
            class="md:hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-30 top-16"
        ></div>
    </header>
</template>

<style scoped>
/* Improve mobile touch targets */
@media (max-width: 768px) {
    button {
        min-height: 44px;
        min-width: 44px;
    }
    
    a {
        min-height: 44px;
        display: flex;
        align-items: center;
    }
}

/* Custom scrollbar for mobile menu */
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>