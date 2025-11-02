<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

import GuestLayout from '@/Layouts/GuestLayout.vue';
import ReviewModal from '@/Components/ReviewModal.vue';

const props = defineProps({ reviews: Object })

const showReviewModal = ref(false)
const sortOrder = ref('desc') // Add this - default to newest first

function openReviewModal() {
    showReviewModal.value = true;
}

function toggleSort() {
    sortOrder.value = sortOrder.value === 'desc' ? 'asc' : 'desc'
    
    router.get('/review', { sort: sortOrder.value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    })
}
</script>

<template>
    <Head title="Review" />

    <GuestLayout>
        <main class="dark:text-[#FAF9F6]">
            <section class="hero-section h-screen text-[#000] md:px-10 lg:px-32 flex ">
                <div class="w-full h-full md:w-1/2 md:flex justify-center items-center">
                    <div class="">
                        <h1 class="text-6xl font-bold font-[Poppins] mb-5 text-blue-700">What People Are Saying</h1>
                        <p class="text-justify text-lg dark:text-[#faf9f6]">See how our platform is helping residents report and solve local problems. These stories show real impact, from fixing small issues to improving the community, and why people rely on our system to make their neighborhoods better.</p>
                        <button @click="openReviewModal" class="bg-blue-500 px-4 py-2 mt-4 rounded text-[#FAF9F6] hover:bg-blue-600 transition-all duration-300">Write a Review</button>
                    </div>
                </div>
                <div class="hidden md:w-1/2 h-full md:flex justify-center items-center">
                    <div class="hidden md:flex justify-end">
                        <img :src="'/Images/testimonials.svg'" alt="Community" class="w-[30rem]">
                    </div>
                </div>
            </section>

            <!-- Review Section -->
            <section class="md:px-10 lg:px-32 py-20 flex flex-col gap-10">
                <div class="flex justify-between items-center">
                    <div class="">
                        <h2 class="text-2xl lg:text-4xl font-bold font-[Poppins] dark:text-white ">Reviews</h2>
                        <p class="text-sm md:text-base text-gray-500 dark:text-[#FAF9F6]">See what people are saying and share your own experience.</p>
                    </div>

                    <!-- Sort Button -->
                    <div>
                        <button
                            @click="toggleSort"
                            class="border p-3 rounded flex items-center gap-2 hover:bg-blue-500 hover:text-white dark:hover:bg-gray-700 transition-colors group duration-300"
                        >
                            <span>{{ sortOrder === 'desc' ? 'Newest' : 'Oldest' }}</span>
                            <svg 
                                xmlns="http://www.w3.org/2000/svg" 
                                width="16" 
                                height="16" 
                                fill="currentColor" 
                                viewBox="0 0 256 256"
                                :class="sortOrder === 'desc' ? 'rotate-0' : 'rotate-180'"
                                class="transition-transform duration-300 group-hover:scale-110"
                            >
                                <path d="M213.66,165.66a8,8,0,0,1-11.32,0L128,91.31,53.66,165.66a8,8,0,0,1-11.32-11.32l80-80a8,8,0,0,1,11.32,0l80,80A8,8,0,0,1,213.66,165.66Z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div v-if="!reviews.data || reviews.data.length === 0"  class="text-center text-gray-500">
                    <p class="text-xl mb-4 py-20">No reviews yet. Be the first to write one!</p>
                </div>
                <div class="grid grid-cols-3 gap-5">
                    <div v-for="review in props.reviews.data" :key="review.id" class="shadow-lg rounded-lg p-8 bg-white flex flex-col gap-6 dark:shadow-md dark:rounded-lg dark:bg-[#2c2c2c] ">
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
                
                <!-- Pagination -->
                <div v-if="reviews.data && reviews.data.length > 0" class="flex justify-end mt-10">
                    <div class="flex items-center gap-3 rounded">
                        <template v-for="link in (props.reviews.links || [])" :key="link?.label || 'empty'">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                :class="['w-10 h-10 grid place-items-center border border-gray-400 rounded justify-center hover:bg-blue-400 transition-all duration-300 hover:text-[#FAF9F6] font-[Poppins]', link.active ? 'bg-blue-600 text-[#FAF9F6] border-none' : '']"
                            >
                                <span v-if="link.label.includes('Previous')" class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#000000" viewBox="0 0 256 256">
                                        <path d="M165.66,202.34a8,8,0,0,1-11.32,11.32l-80-80a8,8,0,0,1,0-11.32l80-80a8,8,0,0,1,11.32,11.32L91.31,128Z"></path>
                                    </svg>
                                </span>
                                <span v-else-if="link.label.includes('Next')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#000000" viewBox="0 0 256 256">
                                        <path d="M181.66,133.66l-80,80a8,8,0,0,1-11.32-11.32L164.69,128,90.34,53.66a8,8,0,0,1,11.32-11.32l80,80A8,8,0,0,1,181.66,133.66Z"></path>
                                    </svg>
                                </span>
                                <span v-else v-html="link.label"></span>
                            </Link>
                            <span
                                v-else
                                :class="'px-3 py-1 text-gray-500 cursor-not-allowed'"
                            >
                                <span v-if="link.label.includes('Previous')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#000000" viewBox="0 0 256 256">
                                        <path d="M165.66,202.34a8,8,0,0,1-11.32,11.32l-80-80a8,8,0,0,1,0-11.32l80-80a8,8,0,0,1,11.32,11.32L91.31,128Z"></path>
                                    </svg>
                                </span>
                                <span v-else-if="link.label.includes('Next')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#000000" viewBox="0 0 256 256">
                                        <path d="M181.66,133.66l-80,80a8,8,0,0,1-11.32-11.32L164.69,128,90.34,53.66a8,8,0,0,1,11.32-11.32l80,80A8,8,0,0,1,181.66,133.66Z"></path>
                                    </svg>
                                </span>
                                <span v-else v-html="link.label"></span>
                            </span>
                        </template> 
                    </div>
                </div>
            </section>
            
            <ReviewModal 
                :show="showReviewModal"
                @close="showReviewModal = false"
            />
        </main>
    </GuestLayout>
</template>

<!-- 📍 -->