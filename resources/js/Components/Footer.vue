<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import ApplicationLogo from './ApplicationLogo.vue';

// Enhanced data structure with better organization
const legalLinks = [
    { name: 'Privacy Policy', url: '/privacy-policy', icon: ['fas', 'shield-alt'] },
    { name: 'Terms & Conditions', url: '/terms-and-condition', icon: ['fas', 'file-contract'] },
    { name: 'Data Usage Notice', url: '/data-usage-notice', icon: ['fas', 'database'] },
    { name: 'Cookie Policy', url: '/cookie-policy', icon: ['fas', 'cookie-bite'] }
];

const quickLinks = [
    { urlName: 'Home', urlPage: '/', icon: ['fas', 'home'] },
    { urlName: 'FAQ', urlPage: '/faq', icon: ['fas', 'question-circle'] },
    { urlName: 'About', urlPage: '/about', icon: ['fas', 'info-circle'] },
    { urlName: 'Contact Us', urlPage: '/contact-us', icon: ['fas', 'envelope'] }
];

const contacts = [
    { 
        icon: ['fas', 'map-marker-alt'], 
        text: 'Cabulijan, Tubigon, Bohol',
        label: 'Address',
        action: () => window.open('https://maps.google.com/?q=Cabulijan,Tubigon,Bohol', '_blank')
    },
    { 
        icon: ['fas', 'phone'], 
        text: '0912 3456 789',
        label: 'Phone',
        action: () => window.open('tel:+639123456789')
    },
    { 
        icon: ['fas', 'envelope'], 
        text: 'civicwatch.brgy@gmail.com',
        label: 'Email',
        action: () => window.open('mailto:civicwatch.brgy@gmail.com')
    }
];

const socialLinks = [
    { name: 'Facebook', icon: ['fab', 'facebook'], url: '#', color: 'hover:text-blue-500' },
    { name: 'Twitter', icon: ['fab', 'twitter'], url: '#', color: 'hover:text-sky-400' },
    { name: 'Instagram', icon: ['fab', 'instagram'], url: '#', color: 'hover:text-pink-500' },
];

// Newsletter subscription
const email = ref('');
const isSubscribing = ref(false);
const subscriptionMessage = ref('');

const subscribeNewsletter = async () => {
    if (!email.value) return;
    
    isSubscribing.value = true;
    try {
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1000));
        subscriptionMessage.value = 'Thank you for subscribing!';
        email.value = '';
        setTimeout(() => {
            subscriptionMessage.value = '';
        }, 3000);
    } catch (error) {
        subscriptionMessage.value = 'Something went wrong. Please try again.';
    } finally {
        isSubscribing.value = false;
    }
};

// Back to top functionality
const isVisible = ref(false);

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const checkScroll = () => {
    isVisible.value = window.pageYOffset > 300;
};

onMounted(() => {
    window.addEventListener('scroll', checkScroll);
});
</script>

<template>
    <footer class="relative">
        <!-- Wave Separator -->
        <div class="wave-separator">
            <svg viewBox="0 0 1200 120" preserveAspectRatio="none" class="w-full h-16 fill-blue-700 dark:fill-[#1e1e1e]">
                <path d="M985.66,92.83C906.67,72,823.78,31,743.84,14.19c-82.26-17.34-168.06-16.33-250.45.39-57.84,11.73-114,31.07-172,41.86A600.21,600.21,0,0,1,0,27.35V120H1200V95.8C1132.19,118.92,1055.71,111.31,985.66,92.83Z"></path>
            </svg>
        </div>

        <div class="px-3 md:px-10 lg:px-32 py-12 bg-blue-700 text-[#FAF9F6] dark:bg-[#1e1e1e] dark:text-[#FAF9F6]">
            <!-- Main Footer Content -->
            <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-5 gap-8 mb-8">
                <!-- Company Info -->
                <div class="lg:col-span-2">
                    <ApplicationLogo class="mb-5" />
                    <p class="text-sm md:text-base mb-4 mt-5 leading-relaxed opacity-90">
                        CivicWatch empowers communities to report issues and track progress for a better, more responsive local government.
                    </p>


                    <!-- Social Links -->
                    <div>
                        <h5 class="font-semibold mb-3 text-sm md:text-base">Follow Us</h5>
                        <div class="flex gap-4">
                            <a 
                                v-for="social in socialLinks" 
                                :key="social.name"
                                :href="social.url"
                                :aria-label="social.name"
                                class="text-xl transition-all duration-300 hover:scale-110 hover:-translate-y-1"
                                :class="social.color"
                            >
                                <font-awesome-icon :icon="social.icon" />
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="font-bold text-sm md:text-base font-[Poppins] mb-4 relative">
                        Quick Links
                        <div class="absolute -bottom-1 left-0 w-8 h-0.5 bg-white rounded"></div>
                    </h4>
                    <nav>
                        <ul class="space-y-2">
                            <li v-for="(quickLink, index) in quickLinks" :key="index">
                                <Link 
                                    :href="quickLink.urlPage"
                                    class="group flex items-center gap-2 text-sm md:text-base hover:text-blue-200 transition-all duration-300 py-1"
                                >
                                    <font-awesome-icon 
                                        :icon="quickLink.icon" 
                                        class="text-xs opacity-70 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-300" 
                                    />
                                    <span class="group-hover:translate-x-1 transition-transform duration-300">
                                        {{ quickLink.urlName }}
                                    </span>
                                </Link>
                            </li>
                        </ul>
                    </nav>
                </div>

                <!-- Legal Links -->
                <div>
                    <h4 class="font-bold text-sm md:text-base font-[Poppins] mb-4 relative">
                        Legal
                        <div class="absolute -bottom-1 left-0 w-8 h-0.5 bg-white rounded"></div>
                    </h4>
                    <nav>
                        <ul class="space-y-2">
                            <li v-for="(legalLink, index) in legalLinks" :key="index">
                                <Link 
                                    :href="legalLink.url"
                                    class="group flex items-center gap-2 text-sm md:text-base hover:text-blue-200 transition-all duration-300 py-1"
                                >
                                    <font-awesome-icon 
                                        :icon="legalLink.icon" 
                                        class="text-xs opacity-70 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-300" 
                                    />
                                    <span class="group-hover:translate-x-1 transition-transform duration-300">
                                        {{ legalLink.name }}
                                    </span>
                                </Link>
                            </li>
                        </ul>
                    </nav>
                </div>

                <!-- Contact Info -->
                <div>
                    <h4 class="font-bold text-sm md:text-base font-[Poppins] mb-4 relative">
                        Contact Info
                        <div class="absolute -bottom-1 left-0 w-8 h-0.5 bg-white rounded"></div>
                    </h4>
                    <ul class="space-y-3">
                        <li v-for="(contact, index) in contacts" :key="index">
                            <button
                                @click="contact.action"
                                class="group flex items-start gap-3 text-left w-full hover:text-blue-200 transition-all duration-300 py-1"
                                :aria-label="`${contact.label}: ${contact.text}`"
                            >
                                <font-awesome-icon 
                                    :icon="contact.icon" 
                                    class="text-lg mt-0.5 opacity-70 group-hover:opacity-100 group-hover:scale-110 transition-all duration-300 flex-shrink-0" 
                                />
                                <div class="group-hover:translate-x-1 transition-transform duration-300">
                                    <div class="text-xs opacity-70 uppercase tracking-wide">{{ contact.label }}</div>
                                    <span class="text-xs md:text-base">{{ contact.text }}</span>
                                </div>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="border-t border-blue-600 dark:border-gray-700 pt-6">
                <div class="flex md:flex-row justify-between items-center gap-4">
                    <p class="text-xs md:text-sm opacity-80">
                        &copy; {{ new Date().getFullYear() }} CivicWatch. All rights reserved.
                    </p>
                    
                    <div class="flex items-center gap-4">
                        <!-- Admin Access -->
                        <Link 
                            href="/admin/admin-login"
                            class="group flex items-center gap-2 text-xs md:text-sm hover:text-blue-200 transition-all duration-300"
                            aria-label="Admin Login"
                        >
                            <font-awesome-icon 
                                icon="user-shield" 
                                class="text-sm group-hover:scale-110 transition-transform duration-300" 
                            />
                            <span class="hidden sm:inline">Admin</span>
                        </Link>
                        
                        <!-- Back to Top -->
                        <transition name="fade">
                            <button
                                v-if="isVisible"
                                @click="scrollToTop"
                                class="group p-2 bg-white/10 hover:bg-white/20 rounded-full transition-all duration-300 hover:scale-110"
                                aria-label="Back to top"
                            >
                                <font-awesome-icon 
                                    icon="chevron-up" 
                                    class="text-sm group-hover:-translate-y-0.5 transition-transform duration-300" 
                                />
                            </button>
                        </transition>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>

<style scoped>
.wave-separator {
    position: relative;
    top: 1px;
}

.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

/* Smooth scroll behavior */
html {
    scroll-behavior: smooth;
}

/* Custom scrollbar for webkit browsers */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #3b82f6;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #2563eb;
}
</style>