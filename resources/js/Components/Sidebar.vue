<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

import LogoutModal from './LogoutModal.vue';

const page = usePage();
const isCollapsed = ref(
    localStorage.getItem('sidebar-collapsed') === 'true'
);

onMounted(() => {
    // Force admin pages to never use dark mode
    document.documentElement.classList.remove('dark')
})

const links = [
    { 
        url: '/admin/dashboard', 
        urlPage: 'Dashboard', 
        image: '/Images/SVG/squares-four.svg',
        svg: `<svg class="w-5 h-5 text-white transition duration-75 dark:text-gray-400 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 21">
            <path d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z"/>
            <path d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z"/>
        </svg>`
    },
    { 
        url: '/admin/reports', 
        urlPage: 'Reports', 
        image: '/Images/SVG/clipboard-text.svg',
        svg: `<svg class="shrink-0 w-5 h-5 text-white transition duration-75 dark:text-gray-400 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M5 5V.13a2.96 2.96 0 0 0-1.293.749L.879 3.707A2.96 2.96 0 0 0 .13 5H5Z"/>
            <path d="M6.737 11.061a2.961 2.961 0 0 1 .81-1.515l6.117-6.116A4.839 4.839 0 0 1 16 2.141V2a1.97 1.97 0 0 0-1.933-2H7v5a2 2 0 0 1-2 2H0v11a1.969 1.969 0 0 0 1.933 2h12.134A1.97 1.97 0 0 0 16 18v-3.093l-1.546 1.546c-.413.413-.94.695-1.513.81l-3.4.679a2.947 2.947 0 0 1-1.85-.227 2.96 2.96 0 0 1-1.635-3.257l.681-3.397Z"/>
            <path d="M8.961 16a.93.93 0 0 0 .189-.019l3.4-.679a.961.961 0 0 0 .49-.263l6.118-6.117a2.884 2.884 0 0 0-4.079-4.078l-6.117 6.117a.96.96 0 0 0-.263.491l-.679 3.4A.961.961 0 0 0 8.961 16Zm7.477-9.8a.958.958 0 0 1 .68-.281.961.961 0 0 1 .682 1.644l-.315.315-1.36-1.36.313-.318Zm-5.911 5.911 4.236-4.236 1.359 1.359-4.236 4.237-1.7.339.341-1.699Z"/>
        </svg>`
    },
    { 
        url: '/admin/issue-type', 
        urlPage: 'Type of Issues', 
        image: '/Images/SVG/circles-four.svg',
        svg: `<svg class="shrink-0 w-5 h-5 text-white transition duration-75 dark:text-gray-400 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 18">
            <path d="M6.143 0H1.857A1.857 1.857 0 0 0 0 1.857v4.286C0 7.169.831 8 1.857 8h4.286A1.857 1.857 0 0 0 8 6.143V1.857A1.857 1.857 0 0 0 6.143 0Zm10 0h-4.286A1.857 1.857 0 0 0 10 1.857v4.286C10 7.169 10.831 8 11.857 8h4.286A1.857 1.857 0 0 0 18 6.143V1.857A1.857 1.857 0 0 0 16.143 0Zm-10 10H1.857A1.857 1.857 0 0 0 0 11.857v4.286C0 17.169.831 18 1.857 18h4.286A1.857 1.857 0 0 0 8 16.143v-4.286A1.857 1.857 0 0 0 6.143 10Zm10 0h-4.286A1.857 1.857 0 0 0 10 11.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 18 16.143v-4.286A1.857 1.857 0 0 0 16.143 10Z"/>
        </svg>`
    },
    { 
        url: '/admin/reviews', 
        urlPage: 'Reviews', 
        image: '/Images/SVG/chat-circle-dots.svg',
        svg: `<svg class="shrink-0 w-5 h-5 text-white transition duration-75 dark:text-gray-400 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="m17.418 3.623-.018-.008a6.713 6.713 0 0 0-2.4-.569V2h1a1 1 0 1 0 0-2h-2a1 1 0 0 0-1 1v2H9.89A6.977 6.977 0 0 1 12 8v5h-2V8A5 5 0 1 0 0 8v6a1 1 0 0 0 1 1h8v4a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-4h6a1 1 0 0 0 1-1V8a5 5 0 0 0-2.582-4.377ZM6 12H4a1 1 0 0 1 0-2h2a1 1 0 0 1 0 2Z"/>
        </svg>`
    },
    { 
        url: '/admin/locations', 
        urlPage: 'Locations', 
        image: '/Images/SVG/map-pin-area.svg',
        svg: `<svg class="shrink-0 w-5 h-5 text-white transition duration-75 dark:text-gray-400 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 20">
            <path d="M8 0a7.992 7.992 0 0 0-6.583 12.535 1 1 0 0 0 .12.183l.12.146c.112.145.227.285.326.4l5.245 6.374a1 1 0 0 0 1.545-.003l5.092-6.205c.206-.222.4-.455.578-.7l.127-.155a.934.934 0 0 0 .122-.192A8.001 8.001 0 0 0 8 0ZM6 8a2 2 0 1 1 4 0 2 2 0 0 1-4 0Z"/>
        </svg>`
    },
    {
        url: '/admin/settings',
        urlPage: 'Settings',
        image: '/Images/SVG/gear-six.svg',
        svg: `<svg class="shrink-0 w-5 h-5 text-white transition duration-75 
            dark:text-gray-400 dark:group-hover:text-white" 
            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" 
            fill="currentColor" viewBox="0 0 20 20">
        <path d="M11.983 1.588a1 1 0 0 0-1.966 0l-.254 1.53a6.993 6.993 0 0 0-1.475.854l-1.42-.821a1 1 0 0 0-1.366.366l-1 1.732a1 1 0 0 0 .366 1.366l1.42.821a6.993 6.993 0 0 0 0 1.708l-1.42.821a1 1 0 0 0-.366 1.366l1 1.732a1 1 0 0 0 1.366.366l1.42-.821c.456.35.95.635 1.475.854l.254 1.53a1 1 0 0 0 1.966 0l.254-1.53a6.993 6.993 0 0 0 1.475-.854l1.42.821a1 1 0 0 0 1.366-.366l1-1.732a1 1 0 0 0-.366-1.366l-1.42-.821a6.993 6.993 0 0 0 0-1.708l1.42-.821a1 1 0 0 0 .366-1.366l-1-1.732a1 1 0 0 0-1.366-.366l-1.42.821a6.993 6.993 0 0 0-1.475-.854l-.254-1.53ZM10 13a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z"/>
        </svg>`
    }
]

const isLinkActive = (linkUrl) => {
    const currentUrl = page.url
    
    // Remove query parameters and trailing slashes for comparison
    const cleanCurrentUrl = currentUrl.split('?')[0].replace(/\/$/, '')
    const cleanLinkUrl = linkUrl.replace(/\/$/, '')
    
    // Exact match first
    if (cleanCurrentUrl === cleanLinkUrl) return true
    
    // For nested routes (like pagination with route parameters)
    // Check if current URL starts with the link URL followed by a slash
    if (cleanCurrentUrl.startsWith(cleanLinkUrl + '/')) return true
    
    return false
}

function toggleSidebar() {
    isCollapsed.value = !isCollapsed.value;
    localStorage.setItem('sidebar-collapsed', isCollapsed.value);
}

const showLogoutModal = ref(false)

function openLogoutModal () {
    showLogoutModal.value = true
}

function confirmLogout() {
    router.post(route('logout'))
}
</script>

<template>
    <!-- Sidebar Component that pushes content -->
    <aside 
        class="transition-all duration-300 ease-in-out bg-blue-700 dark:bg-gray-800 min-h-screen flex flex-col relative"
        :class="isCollapsed ? 'w-16' : 'w-64'">
    
        <div class="h-full px-3 py-4 overflow-y-auto flex flex-col">
            <div class="flex justify-between mb-9">
                <!-- Logo -->
                <Link 
                    href="/admin/dashboard" 
                    class="transition-all duration-300 flex"
                    :class="isCollapsed ? ' items-center' : ''"
                >
                    <img
                        v-if="!isCollapsed"
                        :src="'/Images/CivicWatch(1).png'"
                        alt="CivicWatch"
                        class="h-11 transition-all  duration-300"
                        
                    >
                </Link>

                <!-- Toggle Button -->
                <button 
                    @click="toggleSidebar"
                    class="p-3 text-white transition duration-75 rounded-lg bg-blue-600 hover:bg-blue-900 dark:hover:bg-gray-700 dark:text-white group mb-2 "
                    :class="isCollapsed ? 'justify-center' : ''"
                >
                    <svg 
                        class="shrink-0 w-auto h-5 text-white transition-transform duration-300 dark:text-gray-400 group-hover:white dark:group-hover:text-white" 
                        aria-hidden="true" 
                        xmlns="http://www.w3.org/2000/svg" 
                        fill="none" 
                        viewBox="0 0 24 24"
                        :class="isCollapsed ? 'rotate-180' : ''"
                    >
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.72 11.47a.75.75 0 0 0 0 1.06l7.5 7.5a.75.75 0 1 0 1.06-1.06L12.31 12l6.97-6.97a.75.75 0 0 0-1.06-1.06l-7.5 7.5Z"/>
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.72 11.47a.75.75 0 0 0 0 1.06l7.5 7.5a.75.75 0 1 0 1.06-1.06L6.31 12l6.97-6.97a.75.75 0 0 0-1.06-1.06l-7.5 7.5Z"/>
                    </svg>
                    <span 
                        v-show="!isCollapsed"
                        class=" transition-opacity duration-300"
                    >
                        {{ isCollapsed ? '' : '' }}
                    </span>
                </button>
            </div>
            
            <!-- Navigation Links -->
            <nav class="flex-1">
                <ul class="space-y-2 font-medium">
                    <li v-for="(link, index) in links" :key="index">
                        <Link 
                            :href="link.url" 
                            class="flex items-center p-3 text-white rounded-lg dark:text-white hover:bg-blue-600 dark:hover:bg-gray-700 group transition-colors duration-200"
                            :class="[
                                isLinkActive(link.url) ? 'bg-blue-900 dark:bg-gray-700 text-black' : '',
                                isCollapsed ? 'justify-center' : ''
                            ]"
                        >
                            <!-- SVG Icon -->
                            <div v-html="link.svg" class="shrink-0"></div>
                            
                            <!-- Link Text -->
                            <span 
                                v-show="!isCollapsed"
                                class="ms-3 transition-opacity duration-300"
                                :class="isCollapsed ? 'opacity-0' : 'opacity-100'"
                            >
                                {{ link.urlPage }}
                            </span>
                        </Link>
                    </li>
                </ul>
            </nav>

            <!-- Bottom section with toggle and logout -->
            <div class="mt-auto pt-4 border-t border-gray-200 dark:border-gray-700">
            
                <!-- Logout Button -->
                <button 
                    @click="openLogoutModal"
                    class="flex items-center w-full p-3 text-white transition duration-75 rounded-lg bg-blue-600 hover:bg-blue-900 dark:hover:bg-gray-700 dark:text-white group"
                    :class="isCollapsed ? 'justify-center' : ''"
                >
                    <svg class="shrink-0 w-5 h-5 text-white transition duration-75 dark:text-gray-400 group-hover:text-white dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 16">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 8h11m0 0L8 4m4 4-4 4m4-11h3a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-3"/>
                    </svg>
                    <span 
                        v-show="!isCollapsed"
                        class="ms-3 transition-opacity duration-300"
                    >
                        Logout
                    </span>
                </button>
            </div>
        </div>
    </aside>

    <!-- LogoutModal - positioned outside sidebar for proper centering -->
    <LogoutModal 
        :show="showLogoutModal"
        @logout="confirmLogout"
        @close="showLogoutModal = false"
    />
</template>

<style scoped>
/* Smooth transitions for all elements */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
}

/* Custom scrollbar for sidebar */
.overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background-color: rgba(156, 163, 175, 0.7);
}

/* Dark mode scrollbar */
.dark .overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(75, 85, 99, 0.5);
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background-color: rgba(75, 85, 99, 0.7);
}
</style>

