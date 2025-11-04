<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

import GuestLayout from '@/Layouts/GuestLayout.vue';
import CallToActionBtn from '@/Components/CallToActionBtn.vue';

const props = defineProps({ reviews: Array })

const steps = [
    {
        img: '/Images/SVG/chat-centered-dots.svg',
        headline: 'Report Issues',
        description: 'Describe and upload a photo of the issue you want to report.'
    },

    {
        img: '/Images/SVG/barcode.svg',
        headline: 'Tracking Code',
        description: 'After submitting, users get a unique code to track their report status.'
    },

    {
        img: '/Images/SVG/file-magnifying-glass.svg',
        headline: 'Review',
        description: 'Admin checks and validates the report'
    },

    {
        img: '/Images/SVG/arrow-bend-up-right.svg',
        headline: 'Forward',
        description: 'Issue is forwarded to the appropriate local authority for resolution.'
    }
]

const issues = [
    {
        img: '/Images/damage electric pole.jpeg',
        name: 'Damaged Electric Pole',
        label: 'Local Agency',
        agency: 'Boheco I (Bohol Electric Cooperative)'
    },

    {
        img: '/Images/garbage.jpg',
        name: 'Pile of Garbage',
        label: 'Local Agency',
        agency: 'Municipal Environment & Natural Resources Office (MENRO)'
    },

    {
        img: '/Images/hazardous holes.jpeg',
        name: 'Hazardous Road Conditions',
        label: 'Local Agency',
        agency: 'Department of Public Works and Highways (DPWH)'
    },

    {
        img: '/Images/messyelectricpole.jpeg',
        name: 'Messy Electric Pole',
        label: 'Local Agency',
        agency: 'Boheco I (Bohol Electric Cooperative)'
    },

    {
        img: '/Images/uncollectedgarbage.jpeg',
        name: 'Uncollected Garbage',
        label: 'Local Agency',
        agency: 'Municipal Environment & Natural Resources Office (MENRO)'
    },

    {
        img: '/Images/blocking tree.jpg',
        name: 'Blocking Tree',
        label: 'Local Agency',
        agency: 'Barangay Disaster Risk Reduction Unit (BDRRMC)'
    },

    {
        img: '/Images/waterpipeleakage.jpeg',
        name: 'Water Pipe Leakage',
        label: 'Local Agency',
        agency: 'Tubigon Water District (TWD)'
    },

    {
        img: '/Images/strayanimals.jpeg',
        name: 'Stray Animals',
        label: 'Local Agency',
        agency: 'Barangay Tanod / Municipal Agriculture Office (MAO)'
    }
]

const scrollAmount = 400;

const issuesScrollContainer = ref(null)
const howItWorksContainer = ref(null)

// Refs for animation elements
const heroContent = ref(null)
const sectionHeadings = ref([])
const howItWorksItems = ref([])
const issueCards = ref([])
const whyItems = ref([])
const testimonials = ref([])

// Ref for intersection observer
const observer = ref(null)

onMounted(() => {
    // Initialize animations with Intersection Observer
    initScrollAnimations()
})

const initScrollAnimations = () => {
    observer.value = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                // Add animation class when element comes into view
                entry.target.classList.add('animate-in')
                
        
                if (entry.target.dataset.stagger) {
                    const staggerIndex = parseInt(entry.target.dataset.stagger)
                    entry.target.style.transitionDelay = `${staggerIndex * 50}ms`
                }
                
                observer.value.unobserve(entry.target)
            }
        })
    }, {
        threshold: 0.01, 
        rootMargin: '50px' 
    })

    const elementsToObserve = document.querySelectorAll('.scroll-observe')
    elementsToObserve.forEach((el, index) => {
        // Add stagger index for sequential animations
        el.setAttribute('data-stagger', index)
        observer.value.observe(el)
    })
}

const scrollLeftHowItWorks = () => {
    howItWorksContainer.value.scrollBy({
        left: -400,
        behavior: 'smooth'
    })
}

const scrollRightHowItWorks = () => {
    howItWorksContainer.value.scrollBy({
        left: 400,
        behavior: 'smooth'
    })
}

const scrollLeftIssues = () => {
    issuesScrollContainer.value.scrollLeft -= scrollAmount
}

const scrollRightIssues = () => {
    issuesScrollContainer.value.scrollLeft += scrollAmount
}
</script>

<template>
    <Head title="Welcome Page" />

    <GuestLayout>
        <main class="pt-[50px]">
            <!-- Hero Section -->
            <section class="md:px-10 lg:px-32 py-20 flex scroll-observe">
                <div class="w-1/2 pt-10 scroll-observe" data-stagger="0">
                    <h2 class="font-bold text-7xl font-[Poppins] leading-none text-blue-950 dark:text-white">
                        Strengthening <span class="text-blue-600">Communication</span> Between the 
                        <span class="text-blue-700">Barangay</span> and Its <span class="text-blue-500">People</span>
                    </h2>
                    <p class="my-4 font-light dark:text-gray-300">Stay informed about local projects, announcements, and reports. Together, let's build a safer and more connected barangay.</p>
                    <CallToActionBtn class="scroll-observe" data-stagger="1" />
                </div>
                <div class="w-1/2 flex justify-end pt-10 scroll-observe" data-stagger="2">
                    <img 
                        :src="'/Images/Cabulijan/Official Cabulijan Logo.png'" 
                        alt="Cabulijan Logo"
                        class="flex h-[75%] transform transition-all duration-700"
                    >
                </div>
            </section>

            <!-- How It Works Section -->
            <section class="md:px-10 lg:px-32 py-20 flex flex-col gap-10">
                <div class="flex justify-between scroll-observe">
                    <div class="scroll-observe" data-stagger="0">
                        <h2 class="text-2xl lg:text-4xl font-bold font-[Poppins] dark:text-[#FAF9F6]">How It Works</h2>
                        <p class="text-sm md:text-base text-gray-500 dark:text-[#FAF9F6]">Learn how <span class="font-bold">CivicWatch</span> lets you report local issues in just a few simple steps.</p>
                    </div>
                    <div class="flex justify-center items-center gap-3 scroll-observe" data-stagger="1">
                        <!-- Left Button -->
                        <button class="p-4 border flex justify-center items-center rounded-full transform hover:scale-110 hover:bg-blue-600 transition duration-300" @click="scrollLeftHowItWorks">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="text-black fill-current size-6 hover:text-[#FAF9F6] transition-all duration-300 dark:text-white">
                                <path fill-rule="evenodd" d="M7.72 12.53a.75.75 0 0 1 0-1.06l7.5-7.5a.75.75 0 1 1 1.06 1.06L9.31 12l6.97 6.97a.75.75 0 1 1-1.06 1.06l-7.5-7.5Z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <!-- Right Button -->
                        <button class="p-4 border rounded-full flex justify-center items-center transform hover:scale-110 hover:bg-blue-600 transition duration-300" @click="scrollRightHowItWorks">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="text-black fill-current size-6 hover:text-[#FAF9F6] transition-all duration-300 dark:text-white">
                                <path fill-rule="evenodd" d="M16.28 11.47a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 0 1-1.06-1.06L14.69 12 7.72 5.03a.75.75 0 0 1 1.06-1.06l7.5 7.5Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div ref="howItWorksContainer" class="overflow-x-auto scroll-smooth hide-scrollbar">
                    <div class="flex gap-10">
                        <div
                            v-for="(step, index) in steps"
                            :key="index"
                            class="inline-block h-auto w-[400px] lg:w-96 lg:h-auto flex-shrink-0 scroll-observe"
                            :data-stagger="index"
                        >
                            <div class="border-t border-l border-r p-5 border-b-4 border-b-[#3B82F6] px-10 py-5 w-[300px] h-[300px] lg:h-64 lg:w-auto flex flex-col justify-center rounded-[20px] bg-white shadow-[5px_5px_16px_#bdbdbd,-5px_-5px_16px_#ffffff] dark:bg-[#2c2c2c] dark:text-[#FAF9F6] dark:border-b-4 dark:border-b-[#3B82F6] dark:border-t-0 dark:border-l-0 dark:border-r-0 dark:shadow-none overflow-y-hidden">
                                <div class="">
                                    <img :src="step.img" alt="Image" class="h-15 lg:h-16 mb-5 dark:invert-0" />
                                </div>
                                <div class="flex flex-col gap-2">
                                    <h3 class="font-[Poppins] font-bold text-2xl">{{ step.headline }}</h3>
                                    <p class="text-xs md:text-base text-gray-500">{{ step.description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Supported Issues Section -->
            <section class="md:px-10 lg:px-32 py-20 flex flex-col gap-10">
                <div class="flex justify-between items-center scroll-observe">
                    <div class="scroll-observe" data-stagger="0">
                        <h2 class="text-2xl lg:text-4xl font-bold font-[Poppins] dark:text-[#FAF9F6] ">Supported Issues</h2>
                        <p class="text-sm md:text-base text-gray-700 dark:text-gray-300">Discover the types of community problems you can report through our platform.</p>
                    </div>

                    <div class="flex justify-center items-center gap-3 scroll-observe" data-stagger="1">
                        <!-- Left Button -->
                        <button class="p-4 border rounded-full flex justify-center items-center transform hover:scale-110 hover:bg-blue-600 transition duration-300" @click="scrollLeftIssues">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"  class="text-black fill-current size-6 hover:text-[#FAF9F6] transition-all duration-300 dark:text-white">
                                <path fill-rule="evenodd" d="M7.72 12.53a.75.75 0 0 1 0-1.06l7.5-7.5a.75.75 0 1 1 1.06 1.06L9.31 12l6.97 6.97a.75.75 0 1 1-1.06 1.06l-7.5-7.5Z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <!-- Right Button -->
                        <button class="p-4 border rounded-full flex justify-center items-center transform hover:scale-110 hover:bg-blue-600 transition duration-300" @click="scrollRightIssues">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="text-black fill-current size-6 hover:text-[#FAF9F6] transition-all duration-300 dark:text-white">
                                <path fill-rule="evenodd" d="M16.28 11.47a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 0 1-1.06-1.06L14.69 12 7.72 5.03a.75.75 0 0 1 1.06-1.06l7.5 7.5Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
                <!--Scrollable Content -->
                <div ref="issuesScrollContainer" class="overflow-x-auto scroll-smooth hide-scrollbar mb-3">
                    <div class="flex gap-10 whitespace-nowrap snap-x snap-mandatory relative">
                        <div 
                            v-for="(issue, index) in issues" 
                            :key="index" 
                            class="inline-block h-auto w-[400px] lg:w-96 lg:h-auto flex-shrink-0 scroll-observe"
                            :data-stagger="index"
                        >
                            <div class="bg-white shadow-md rounded dark:bg-[#2c2c2c] dark:text-[#FAF9F6] dark:border-none">
                                <img :src="issue.img" alt="Issue Image" class="mb-1 h-80 lg:h-96 lg:w-96 object-cover rounded">
                                <div class="p-3 border-l border-r rounded border-b-4 border-b-[#3B82F6] dark:border-l-0 dark:border-r-0 dark:border-b-4 dark:border-b-[#3B82F6]">
                                    <h3 class="font-[Poppins] font-bold text-sm  md:text-lg">{{ issue.name }}</h3>
                                    <div class="flex gap-1 text-gray-600 h-10 dark:text-[#FAF9F6]">
                                        <p class="text-xs md:text-sm font-bold">{{ issue.label }}: </p>
                                        <p class="text-xs md:text-sm whitespace-normal break-words">{{ issue.agency }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <Link href="/about" class="flex justify-center scroll-observe" data-stagger="0">
                    <div class="flex gap-2 justify-center items-center px-3 py-2 bg-blue-600 md:px-4 md:py-2 rounded hover:bg-blue-700 transition-colors duration-300 dark:bg-[#343434] dark:text-[#FAF9F6] dark:hover:bg-[#454545]">
                        <span class="text-white text-sm md:text-base flex justify-center items-center">View All Issues</span>
                        <font-awesome-icon icon="chevron-right" class="text-white  flex justify-center items-center" />
                    </div>
                </Link>
            </section>

            <!-- Why CivicWatch Section -->
            <section class="md:px-10 lg:px-32 py-20 flex flex-col gap-10">
                <div class="scroll-observe" data-stagger="0">
                    <h2 class="text-2xl lg:text-4xl font-bold font-[Poppins] dark:text-white ">Why CivicWatch?</h2>
                    <p class="text-sm md:text-base text-gray-500 dark:text-[#FAF9F6]">See how <span class="font-bold">CivicWatch</span> improves community engagement, transparency, and response time.</p>
                </div>

                <div class="flex flex-col gap-3">
                    <section class="flex justify-between py-5 border-b border-gray-300 dark:border-gray-800 scroll-observe" data-stagger="0">
                        <div class="flex flex-col gap-2 justify-center w-2/3 md:w-1/2">
                            <div class="flex gap-2 items-center">
                                <span class="bg-blue-500 w-10 rounded-full px-4 py-2 flex justify-center text-[#FAF9F6] font-bold">01.</span>
                                <h3 class="font-bold text-base md:text-2xl font-[Poppins] dark:text-[#FAF9F6]">Transparency</h3>
                            </div>
                            
                            <p class="dark:text-[#FAF9F6] text-sm md:text-base text-gray-500">Every submitted report is tracked and visible throughout its status, from initial submission to resolution. This ensures that users are informed and that local agencies remain accountable for taking appropriate action.</p>
                        </div>
                        <div class="flex justify-center items-center md:justify-end w-3/2 md:w-1/2">
                            <img :src="'/Images/SVG/eye.svg'" alt="transparency" class="h-20 w-auto lg:h-auto lg:w-32">
                        </div>
                    </section>

                    <section class="flex justify-between py-5 border-b border-gray-300 dark:border-gray-800 scroll-observe" data-stagger="1">
                        <div class="flex justify-center items-center md:justify-start w-3/2 md:w-1/2">
                            <img :src="'/Images/SVG/navigation-arrow.svg'" alt="fasteresponse" class="h-20 w-auto lg:h-auto lg:w-32">
                        </div>
                        <div class="flex flex-col gap-2 justify-center w-2/3 md:w-1/2">
                            <span class="bg-blue-500 w-10 rounded-full px-4 py-2 flex justify-center text-[#FAF9F6] font-bold">02.</span>
                            <h3 class="font-bold text-base md:text-2xl font-[Poppins] dark:text-[#FAF9F6]">Faster Local Response</h3>
                            <p class="dark:text-[#FAF9F6] text-sm md:text-base text-gray-500">Reports are automatically routed to the correct barangay admin or department, enabling faster decision-making and quicker interventions for non-emergency local issues.</p>
                        </div>
                    </section>

                    <section class="flex justify-between py-5 border-b border-gray-300 dark:border-gray-800 scroll-observe" data-stagger="2">
                        <div class="flex flex-col gap-2 justify-center w-2/3 md:w-1/2">
                            <span class="bg-blue-500 w-10 rounded-full px-4 py-2 flex justify-center text-[#FAF9F6] font-bold">03.</span>
                            <h3 class="font-bold text-base md:text-2xl font-[Poppins] dark:text-[#FAF9F6]">Easy To Use</h3>
                            <p class="dark:text-[#FAF9F6] text-sm md:text-base text-gray-500">CivicWatch features a clean and intuitive interface with simple forms, guided steps, and helpful icons making it easy for any resident to report an issue without needing technical knowledge.</p>
                        </div>
                        <div class="flex justify-center items-center md:justify-end w-3/2 md:w-1/2">
                            <img :src="'/Images/SVG/magic-wand.svg'" alt="transparency" class="h-20 w-auto lg:h-auto lg:w-32">
                        </div>
                    </section>

                    <section class="flex justify-between py-5 scroll-observe" data-stagger="3">
                        <div class="flex justify-center items-center md:justify-start w-3/2 md:w-1/2">
                            <img :src="'/Images/SVG/devices.svg'" alt="fasteresponse" class="h-20 w-auto lg:h-auto lg:w-32">
                        </div>
                        <div class="flex flex-col gap-2 justify-center w-1/2">
                            <span class="bg-blue-500 w-10 rounded-full px-4 py-2 flex justify-center text-[#FAF9F6] font-bold">04.</span>
                            <h3 class="font-bold text-base md:text-2xl font-[Poppins] dark:text-[#FAF9F6]">Mobile Friendly</h3>
                            <p class="dark:text-[#FAF9F6] text-sm md:text-base text-gray-500">Whether you're using a smartphone or tablet, CivicWatch is fully responsive and optimized for small screens, allowing users to report issues anytime and anywhere within their community.</p>
                        </div>
                    </section>
                </div>
            </section>

            <!-- Testimonial Section -->
            <section class="md:px-10 lg:px-32 py-20 flex flex-col gap-10 ">
                <div class="scroll-observe" data-stagger="0">
                    <h2 class="text-2xl lg:text-4xl font-bold font-[Poppins] dark:text-white ">Trusted by Our Community</h2>
                    <p class="text-sm md:text-base text-gray-500 dark:text-[#FAF9F6]">Hear how we've made a difference.</p>
                </div>

                <div class="grid grid-cols-3 gap-5">
                    <div 
                        v-for="(review, index) in reviews" 
                        :key="review.id" 
                        class="border p-8 gap-6 scroll-observe dark:border-none rounded-[20px] bg-white shadow-[5px_5px_16px_#bdbdbd,-5px_-5px_16px_#ffffff] dark:bg-[#2c2c2c] dark:shadow-none"
                        :data-stagger="index"
                    >
                        <div class="flex flex-col gap-3">
                            <div class="flex justify-between items-center">
                                <img :src="'/Images/SVG/quote-30-double-open.svg'" alt="Quotation Icon" class="h-12">
                                <p class="text-sm text-gray-600 dark:text-[#faf9f6]">{{ review.created_at }}</p>
                            </div>
                            <div class="h-[145px] overflow-y-auto">
                                <p class="text-gray-600 text-base dark:text-[#faf9f6]">{{ review.review_message }}</p>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-1">
                                <div>
                                    <img 
                                        :src="'/Images/SVG/user-circle-fill.svg'"
                                        :alt="Icon"
                                        :class="review.is_anonymous ? 'opacity-55': 'opacity-100'"
                                        class="h-10 w-10"
                                    />
                                </div>
                    
                                <div>
                                    <!-- Update these lines to use display names -->
                                    <span class="font-bold text-base font-[Poppins]">
                                        {{ review.is_anonymous ? 'Anonymous User' : review.name }}
                                    </span>
                                    <p class="text-gray-600 text-xs dark:text-[#faf9f6]">
                                        {{ review.location }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center space-x-1">
                                    <span v-for="star in 5" :key="star" 
                                        :class="star <= review.rating ? 'text-yellow-400' : 'text-gray-300'">
                                        ★
                                    </span>
                                    <span v-if="review.rating" class="ml-1 text-sm text-gray-600">
                                        ({{ review.rating }}/5)
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <Link href="/review" class="flex justify-center scroll-observe" data-stagger="0">
                    <div class="flex gap-2 justify-center items-center px-3 py-2 bg-blue-600 md:px-4 md:py-2 rounded hover:bg-blue-700 transition-colors duration-300 dark:bg-[#343434] dark:text-[#FAF9F6] dark:hover:bg-[#454545]">
                        <span class="text-white text-sm md:text-base flex justify-center items-center">View All Reviews</span>
                        <font-awesome-icon icon="chevron-right" class="text-white  flex justify-center items-center" />
                    </div>
                </Link>
            </section>
        </main>
    </GuestLayout>
</template>

<style scoped>
/* Animation classes for scroll-driven animations */
.scroll-observe {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}

.scroll-observe.animate-in {
    opacity: 1;
    transform: translateY(0);
}

/* Specific animations for different elements */
.scroll-observe.animate-in img {
    transform: scale(1);
}

/* Hide scrollbar for a cleaner look */
.hide-scrollbar {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
.hide-scrollbar::-webkit-scrollbar {
    display: none;  /* Chrome, Safari and Opera */
}

/* Additional smooth transitions */
.scroll-observe {
    transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}

.scroll-observe img {
    transition: transform 0.7s ease-out;
}
</style>