<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import ApplicationLogo from './ApplicationLogo.vue';

const legalLinks = [
    { name: 'Privacy Policy', url: '/privacy-policy', icon: ['fas', 'shield-alt'] },
    { name: 'Terms & Conditions', url: '/terms-and-condition', icon: ['fas', 'file-contract'] },
    { name: 'Data Usage Notice', url: '/data-usage-notice', icon: ['fas', 'database'] },
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

const email = ref('');
const isSubscribing = ref(false);
const subscriptionMessage = ref('');

const subscribeNewsletter = async () => {
    if (!email.value) return;
    isSubscribing.value = true;
    try {
        await new Promise(resolve => setTimeout(resolve, 1000));
        subscriptionMessage.value = 'Thank you for subscribing!';
        email.value = '';
        setTimeout(() => subscriptionMessage.value = '', 3000);
    } catch (error) {
        subscriptionMessage.value = 'Something went wrong. Please try again.';
    } finally {
        isSubscribing.value = false;
    }
};

const isVisible = ref(false);
const scrollToTop = () => window.scrollTo({ top: 0, behavior: 'smooth' });
const checkScroll = () => isVisible.value = window.pageYOffset > 300;

onMounted(() => window.addEventListener('scroll', checkScroll));
</script>

<template>
    <footer class="relative">
        <!-- Wave Separator -->
        <div class="wave-separator">
            <svg viewBox="0 0 1200 120" preserveAspectRatio="none" class="w-full h-12 sm:h-16 lg:h-20 fill-blue-700 dark:fill-[#1e1e1e]">
                <path d="M985.66,92.83C906.67,72,823.78,31,743.84,14.19c-82.26-17.34-168.06-16.33-250.45.39-57.84,11.73-114,31.07-172,41.86A600.21,600.21,0,0,1,0,27.35V120H1200V95.8C1132.19,118.92,1055.71,111.31,985.66,92.83Z"></path>
            </svg>
        </div>

        <div class="px-4 sm:px-6 md:px-10 lg:px-20 xl:px-32 py-12 lg:py-16 bg-blue-700 text-[#FAF9F6] dark:bg-[#1e1e1e]">
            <div class="max-w-7xl mx-auto">
                <!-- Main Footer Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-12 mb-12">
                    <!-- Company Info (takes more space on large screens) -->
                    <div class="sm:col-span-2 lg:col-span-5">
                        <ApplicationLogo class="h-12 w-auto mb-6" />
                        <p class="text-sm lg:text-base leading-relaxed opacity-90 mb-8">
                            CivicWatch empowers communities to report issues and track progress for a better, more responsive local government.
                        </p>

                        <!-- Social Links -->
                        <div>
                            <h5 class="font-semibold text-sm lg:text-base mb-4">Follow Us</h5>
                            <div class="flex gap-5 text-2xl">
                                <a v-for="social in socialLinks" :key="social.name" :href="social.url" :aria-label="social.name"
                                   class="transition-all duration-300 hover:scale-110 hover:-translate-y-1" :class="social.color">
                                    <font-awesome-icon :icon="social.icon" />
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="lg:col-span-2">
                        <h4 class="font-bold text-lg font-[Poppins] mb-5 relative inline-block">
                            Quick Links
                            <span class="absolute -bottom-1 left-0 w-10 h-0.5 bg-white rounded"></span>
                        </h4>
                        <ul class="space-y-3">
                            <li v-for="(link, i) in quickLinks" :key="i">
                                <Link :href="link.urlPage"
                                      class="group flex items-center gap-3 text-sm lg:text-base hover:text-blue-200 transition-all duration-300">
                                    <font-awesome-icon :icon="link.icon"
                                                       class="text-xs opacity-70 group-hover:opacity-100 group-hover:translate-x-1 transition-all" />
                                    <span class="group-hover:translate-x-1 transition-transform">{{ link.urlName }}</span>
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Legal Links -->
                    <div class="lg:col-span-2">
                        <h4 class="font-bold text-lg font-[Poppins] mb-5 relative inline-block">
                            Legal
                            <span class="absolute -bottom-1 left-0 w-10 h-0.5 bg-white rounded"></span>
                        </h4>
                        <ul class="space-y-3">
                            <li v-for="(link, i) in legalLinks" :key="i">
                                <Link :href="link.url"
                                      class="group flex items-center gap-3 text-sm lg:text-base hover:text-blue-200 transition-all duration-300">
                                    <font-awesome-icon :icon="link.icon"
                                                       class="text-xs opacity-70 group-hover:opacity-100 group-hover:translate-x-1 transition-all" />
                                    <span class="group-hover:translate-x-1 transition-transform">{{ link.name }}</span>
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Contact Info -->
                    <div class="lg:col-span-3">
                        <h4 class="font-bold text-lg font-[Poppins] mb-5 relative inline-block">
                            Contact Info
                            <span class="absolute -bottom-1 left-0 w-10 h-0.5 bg-white rounded"></span>
                        </h4>
                        <ul class="space-y-5">
                            <li v-for="(contact, i) in contacts" :key="i">
                                <button @click="contact.action"
                                        class="group flex items-start gap-4 text-left w-full hover:text-blue-200 transition-all duration-300 text-sm lg:text-base">
                                    <font-awesome-icon :icon="contact.icon"
                                                       class="mt-1 text-lg opacity-70 group-hover:opacity-100 group-hover:scale-110 transition-all flex-shrink-0" />
                                    <div>
                                        <div class="text-xs opacity-70 uppercase tracking-wider">{{ contact.label }}</div>
                                        <div class="mt-0.5">{{ contact.text }}</div>
                                    </div>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Footer Bottom -->
                <div class="border-t border-blue-600 dark:border-gray-700 pt-6">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 text-center sm:text-left">
                        <p class="text-xs lg:text-sm opacity-80">
                            © {{ new Date().getFullYear() }} CivicWatch. All rights reserved.
                        </p>

                        <div class="flex items-center gap-6">
                            <!-- Admin Access -->
                            <Link href="/admin/admin-login"
                                  class="group flex items-center gap-2 text-xs lg:text-sm hover:text-blue-200 transition-all duration-300">
                                <font-awesome-icon icon="user-shield" class="group-hover:scale-110 transition-transform" />
                                <span class="hidden xs:inline">Admin Access</span>
                            </Link>

                            <!-- Back to Top -->
                            <transition name="fade">
                                <button v-if="isVisible" @click="scrollToTop"
                                        class="p-3 bg-white/10 hover:bg-white/20 rounded-full transition-all duration-300 hover:scale-110"
                                        aria-label="Back to top">
                                    <font-awesome-icon icon="chevron-up" class="text-lg" />
                                </button>
                            </transition>
                        </div>
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

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* Optional: nicer custom scrollbar (still works on desktop) */
::-webkit-scrollbar { width: 8px; }
::-webkit-scrollbar-track { background: #f1f1f1; }
::-webkit-scrollbar-thumb { background: #3b82f6; border-radius: 4px; }
::-webkit-scrollbar-thumb:hover { background: #2563eb; }
</style>