<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import ReviewModal from '@/Components/ReviewModal.vue';

const { reviews } = defineProps({ reviews: Object })

const showReviewModal = ref(false)
const sortOrder = ref('desc') 
let pollInterval = null;

const seenReviewIds = ref(new Set())
const newReviewIds = ref(new Map())

// Define functions FIRST before using them in watchers
const getStoredSeenReviews = () => {
    try {
        const stored = localStorage.getItem('seenReviewIds');
        return stored ? new Set(JSON.parse(stored)) : new Set();
    } catch {
        return new Set();
    }
};

const setStoredSeenReviews = (reviewIds) => {
    try {
        localStorage.setItem('seenReviewIds', JSON.stringify([...reviewIds]));
    } catch (error) {
        console.error('Failed to store seen reviews:', error);
    }
};

// Use localStorage to persist new review IDs with timestamps
const getStoredNewReviews = () => {
    try {
        const stored = localStorage.getItem('newReviewIds');
        return stored ? new Map(Object.entries(JSON.parse(stored))) : new Map();
    } catch {
        return new Map();
    }
};

const setStoredNewReviews = (reviewIdsMap) => {
    try {
        localStorage.setItem('newReviewIds', JSON.stringify(Object.fromEntries(reviewIdsMap)));
    } catch (error) {
        console.error('Failed to store new reviews:', error);
    }
};

// Initialize from storage
newReviewIds.value = getStoredNewReviews();

// Function to clean up old new reviews (older than 24 hours)
const cleanOldNewReviews = () => {
    const oneDayAgo = Date.now() - (24 * 60 * 60 * 1000);
    for (const [id, timestamp] of newReviewIds.value.entries()) {
        if (timestamp < oneDayAgo) {
            newReviewIds.value.delete(id);
        }
    }
    setStoredNewReviews(newReviewIds.value);
};

// Check if a review is new
const isNewReview = (reviewId) => {
    return newReviewIds.value.has(reviewId);
};

// Shared logic to initialize seen/new reviews from current data
const initializeSeenAndNew = (currentReviewsData) => {
    const currentReviewIds = new Set(currentReviewsData.map(review => review.id));
    
    // Load seen from storage
    const storedSeen = getStoredSeenReviews();
    
    let trulyNew = [];
    if (storedSeen.size === 0) {
        // First visit: no new reviews, just set seen
        seenReviewIds.value = currentReviewIds;
    } else {
        // Compute new reviews based on previously seen
        trulyNew = [...currentReviewIds].filter(id => !storedSeen.has(id));
        seenReviewIds.value = new Set([...storedSeen, ...currentReviewIds]);
    }
    
    // Add any truly new to highlights
    if (trulyNew.length > 0) {
        trulyNew.forEach(reviewId => {
            newReviewIds.value.set(reviewId, Date.now());
        });
        setStoredNewReviews(newReviewIds.value);
    }
    
    // Store updated seen
    setStoredSeenReviews(seenReviewIds.value);
};

// Update logic after reload/navigation that affects reviews
const updateAfterReload = (page) => {
    initializeSeenAndNew(page.props.reviews.data);
    
    // Re-observe elements after update
    nextTick(() => {
        const elements = document.querySelectorAll('[data-observe]');
        elements.forEach((el) => {
            if (!observedElements.value.includes(el)) {
                observer.value?.observe(el);
                observedElements.value.push(el);
            }
        });
    });
};

// NOW define the watcher AFTER all functions are defined
// Watch for changes in reviews prop (when pagination changes)
watch(() => reviews, (newReviews) => {
    if (newReviews && newReviews.data) {
        initializeSeenAndNew(newReviews.data);
        
        // Re-observe elements after data update
        nextTick(() => {
            const elements = document.querySelectorAll('[data-observe]');
            elements.forEach((el) => {
                if (!observedElements.value.includes(el)) {
                    observer.value?.observe(el);
                    observedElements.value.push(el);
                }
            });
        });
    }
}, { immediate: true, deep: true });

// Intersection Observer setup
const observer = ref(null);
const observedElements = ref([]);

onMounted(() => {
    // Clean up old new reviews
    cleanOldNewReviews();
    
    // Initialize seen/new from current props
    initializeSeenAndNew(reviews.data);
    
    pollInterval = setInterval(pollForNewReviews, 15000);

    // Initialize Intersection Observer
    observer.value = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                } else {
                    entry.target.classList.remove('is-visible');
                }
            });
        },
        {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        }
    );

    // Observe elements with the data-observe attribute
    nextTick(() => {
        const elements = document.querySelectorAll('[data-observe]');
        elements.forEach((el) => {
            observer.value.observe(el);
            observedElements.value.push(el);
        });
    });
});

onUnmounted(() => {
    if (pollInterval) {
        clearInterval(pollInterval);
    }

    // Cleanup observer
    if (observer.value) {
        observedElements.value.forEach((el) => {
            observer.value.unobserve(el);
        });
        observer.value.disconnect();
    }
});

const pollForNewReviews = () => {
    router.reload({
        preserveState: true,
        preserveScroll: true,
        only: ['reviews'], 
        onSuccess: updateAfterReload
    });
};

function openReviewModal() {
    showReviewModal.value = true;
}

function toggleSort() {
    sortOrder.value = sortOrder.value === 'desc' ? 'asc' : 'desc'
    
    // Get current page from the reviews object
    const currentPage = reviews.current_page || 1;
    
    router.get('/review', { 
        sort: sortOrder.value,
        page: currentPage // Preserve current page when sorting
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onSuccess: updateAfterReload
    })
}

// Function to handle when a new review is submitted
const handleNewReviewSubmitted = (newReview) => {
    // Close the modal
    showReviewModal.value = false;
    
    // Force reload to get the latest reviews including the new one
    router.reload({
        preserveState: true,
        preserveScroll: false,
        only: ['reviews'],
        onSuccess: (page) => {
            updateAfterReload(page);
            
            // Wait for the DOM to update
            nextTick(() => {
                // Scroll to the top of reviews section
                const reviewsSection = document.querySelector('.grid');
                if (reviewsSection) {
                    reviewsSection.scrollIntoView({ 
                        behavior: 'smooth',
                        block: 'start'
                    });
                    
                    // Add highlight to the first review (the newest one)
                    const firstReview = reviewsSection.querySelector('[data-review-id]');
                    if (firstReview) {
                        firstReview.classList.add('highlight-new-review');
                        setTimeout(() => {
                            firstReview.classList.remove('highlight-new-review');
                        }, 3000);
                    }
                }
            });
        }
    });
}
</script>

<template>
    <Head title="Review" />

    <GuestLayout>
        <main class="dark:text-[#FAF9F6]">
            <section class="pt-52 lg:pt-0 hero-section min-h-screen text-[#000] px-4 md:px-10 lg:px-32 flex flex-col lg:flex-row items-center" data-observe>
                <div class="w-full lg:w-1/2 flex justify-center items-center py-10 lg:py-0">
                    <div class="">
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold font-[Poppins] mb-5 text-blue-700">What People Are Saying</h1>
                        <p class="text-justify text-base sm:text-lg dark:text-[#faf9f6]">See how our platform is helping residents report and solve local problems. These stories show real impact, from fixing small issues to improving the community, and why people rely on our system to make their neighborhoods better.</p>
                        <button @click="openReviewModal" class="bg-blue-500 px-4 py-2 mt-4 rounded text-[#FAF9F6] hover:bg-blue-600 transition-all duration-300">Write a Review</button>
                    </div>
                </div>
                <div class="hidden lg:flex w-full lg:w-1/2 justify-center items-center">
                    <div class="flex justify-center lg:justify-end">
                        <img :src="'/Images/testimonials.svg'" alt="Community" class="w-[30rem]">
                    </div>
                </div>
            </section>

            <!-- Review Section -->
            <section class="px-4 sm:px-6 md:px-10 lg:px-32 py-10 lg:py-20 flex flex-col gap-8 lg:gap-10">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 " data-observe>
                    <div class="">
                        <h2 class="text-2xl lg:text-4xl font-bold font-[Poppins] dark:text-white ">Reviews</h2>
                        <p class="text-sm md:text-base text-gray-500 dark:text-[#FAF9F6]">See what people are saying and share your own experience.</p>
                    </div>

                    <!-- Sort Button -->
                    <div class="flex items-center gap-3">
                        <!-- Refresh Button -->
                        <button
                            @click="pollForNewReviews"
                            class="border p-2 sm:p-3 rounded flex items-center gap-1 sm:gap-2 hover:bg-green-500 hover:text-white transition-colors group duration-300 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-green-600 text-sm sm:text-base"
                        >
                            <span>Refresh</span>
                            <img 
                                :src="'/Images/SVG/arrows-clockwise.svg'" 
                                alt="Icon" 
                                class="h-5 w-5 group-hover:hidden dark:invert"
                            >

                            <img 
                                :src="'/Images/SVG/arrows-clockwise (white).svg'" 
                                alt="Icon" 
                                class="h-5 w-5 hidden group-hover:block group-hover:animate-spin"
                            >
                        </button>
                        
                        <button
                            @click="toggleSort"
                            class="border p-2 sm:p-3 rounded flex items-center gap-2 hover:bg-blue-500 hover:text-white dark:hover:bg-gray-700 transition-colors group duration-300 text-sm sm:text-base"
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
                
                <div v-if="!reviews.data || reviews.data.length === 0"  class="text-center text-gray-500" data-observe>
                    <p class="text-lg sm:text-xl mb-4 py-20">No reviews yet. Be the first to write one!</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div 
                        v-for="review in reviews.data" 
                        :key="review.id" 
                        :data-review-id="review.id"
                        :class="[
                            'shadow-lg rounded-lg p-6 sm:p-8 bg-white flex flex-col gap-6 dark:shadow-md dark:rounded-lg dark:bg-[#2c2c2c] transition-all duration-500',
                            isNewReview(review.id) ? 'border-2 border-green-500 bg-green-50 dark:bg-green-900/20' : ''
                        ]"
                        data-observe
                    >
                        <div class="flex flex-col gap-3">
                            <div class="flex justify-between items-center">
                                <img :src="'/Images/SVG/quote-30-double-open.svg'" alt="Quotation Icon" class="h-10 sm:h-12">
                                <p class="text-xs sm:text-sm text-gray-600 dark:text-[#faf9f6]">{{ review.created_at }}</p>
                            </div>
                            <div class="h-[145px] overflow-y-auto">
                                <p class="text-sm sm:text-base text-gray-600 dark:text-[#faf9f6]">{{ review.review_message }}</p>
                            </div>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div class="flex items-center gap-1">
                                <div>
                                    <img 
                                        :src="'/Images/SVG/user-circle-fill.svg'"
                                        :alt="Icon"
                                        :class="review.is_anonymous ? 'opacity-55': 'opacity-100'"
                                        class="h-8 w-8 sm:h-10 sm:w-10"
                                    />
                                </div>
                                
                                <div>
                                    <span class="font-bold text-sm sm:text-base font-[Poppins]">
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
                                        :class="star <= review.rating ? 'text-yellow-400' : 'text-gray-300'"
                                        class="text-lg sm:text-xl">
                                        ★
                                    </span>
                                    <span v-if="review.rating" class="ml-1 text-xs sm:text-sm text-gray-600">
                                        ({{ review.rating }}/5)
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pagination -->
                <div v-if="reviews && reviews.links && reviews.links.length > 1" class="flex justify-center mt-10" data-observe>
                    <div class="flex items-center gap-2 bg-white dark:bg-gray-800 rounded-xl p-4 shadow-lg border border-gray-200 dark:border-gray-700">
                        <template v-for="(link, index) in reviews.links" :key="index">
                            <!-- Previous Button with Icon -->
                            <Link
                                v-if="link.url && link.label.includes('Previous')"
                                :href="link.url + (link.url.includes('?') ? '&' : '?') + `sort=${sortOrder}`"
                                :preserve-state="true"
                                :preserve-scroll="true"
                                :class="[
                                    'min-w-10 h-10 px-3 flex items-center justify-center border-2 rounded-lg text-sm font-medium transition-all duration-200 transform hover:scale-105 active:scale-95',
                                    'border-blue-200 text-blue-700 hover:bg-blue-500 hover:text-white hover:border-blue-500 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-blue-600 dark:hover:border-blue-600'
                                ]"
                                title="Previous Page"
                            >
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 256 256">
                                    <path d="M165.66,202.34a8,8,0,0,1-11.32,11.32l-80-80a8,8,0,0,1,0-11.32l80-80a8,8,0,0,1,11.32,11.32L91.31,128Z"></path>
                                </svg>
                            </Link>
                            
                            <!-- Next Button with Icon -->
                            <Link
                                v-else-if="link.url && link.label.includes('Next')"
                                :href="link.url + (link.url.includes('?') ? '&' : '?') + `sort=${sortOrder}`"
                                :preserve-state="true"
                                :preserve-scroll="true"
                                :class="[
                                    'min-w-10 h-10 px-3 flex items-center justify-center border-2 rounded-lg text-sm font-medium transition-all duration-200 transform hover:scale-105 active:scale-95',
                                    'border-blue-200 text-blue-700 hover:bg-blue-500 hover:text-white hover:border-blue-500 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-blue-600 dark:hover:border-blue-600'
                                ]"
                                title="Next Page"
                            >
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 256 256">
                                    <path d="M181.66,133.66l-80,80a8,8,0,0,1-11.32-11.32L164.69,128,90.34,53.66a8,8,0,0,1,11.32-11.32l80,80A8,8,0,0,1,181.66,133.66Z"></path>
                                </svg>
                            </Link>
                            
                            <!-- Page Numbers -->
                            <Link
                                v-else-if="link.url && !link.label.includes('Previous') && !link.label.includes('Next')"
                                :href="link.url + (link.url.includes('?') ? '&' : '?') + `sort=${sortOrder}`"
                                :preserve-state="true"
                                :preserve-scroll="true"
                                :class="[
                                    'min-w-10 h-10 px-3 flex items-center justify-center border-2 rounded-lg text-sm font-medium transition-all duration-200 transform hover:scale-105 active:scale-95',
                                    link.active 
                                        ? 'bg-blue-600 text-white border-blue-600 shadow-md scale-105' 
                                        : 'border-blue-200 text-blue-700 hover:bg-blue-500 hover:text-white hover:border-blue-500 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-blue-600 dark:hover:border-blue-600'
                                ]"
                                v-html="link.label"
                            />
                            
                            <span
                                v-else
                                :class="[
                                    'min-w-10 h-10 px-3 flex items-center justify-center border-2 rounded-lg text-sm font-medium',
                                    link.label.includes('Previous') || link.label.includes('Next')
                                        ? 'border-gray-300 text-gray-400 cursor-not-allowed dark:border-gray-700 dark:text-gray-600'
                                        : 'border-blue-100 bg-blue-50 text-blue-400 cursor-not-allowed dark:border-gray-700 dark:bg-gray-900 dark:text-gray-500'
                                ]"
                            >
                                <template v-if="link.label.includes('Previous')">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 256 256">
                                        <path d="M165.66,202.34a8,8,0,0,1-11.32,11.32l-80-80a8,8,0,0,1,0-11.32l80-80a8,8,0,0,1,11.32,11.32L91.31,128Z"></path>
                                    </svg>
                                </template>
                                <template v-else-if="link.label.includes('Next')">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 256 256">
                                        <path d="M181.66,133.66l-80,80a8,8,0,0,1-11.32-11.32L164.69,128,90.34,53.66a8,8,0,0,1,11.32-11.32l80,80A8,8,0,0,1,181.66,133.66Z"></path>
                                    </svg>
                                </template>
                                <template v-else>
                                    {{ link.label }}
                                </template>
                            </span>
                        </template>
                    </div>
                </div>
            </section>
            
            <ReviewModal 
                :show="showReviewModal"
                @close="showReviewModal = false"
                @review-submitted="handleNewReviewSubmitted"
            />
        </main>
    </GuestLayout>
</template>

<style scoped>
/* Your existing styles remain the same */
[data-observe] {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s ease, transform 0.6s ease;
}

[data-observe].is-visible {
    opacity: 1;
    transform: translateY(0);
}

[data-observe]:nth-child(1) { transition-delay: 0.1s; }
[data-observe]:nth-child(2) { transition-delay: 0.2s; }
[data-observe]:nth-child(3) { transition-delay: 0.3s; }
[data-observe]:nth-child(4) { transition-delay: 0.4s; }
[data-observe]:nth-child(5) { transition-delay: 0.5s; }
[data-observe]:nth-child(6) { transition-delay: 0.6s; }
[data-observe]:nth-child(7) { transition-delay: 0.7s; }
[data-observe]:nth-child(8) { transition-delay: 0.8s; }
[data-observe]:nth-child(9) { transition-delay: 0.9s; }

.highlight-new-review {
    animation: highlight-pulse 2s ease-in-out;
    border: 2px solid #10B981 !important;
    background-color: rgba(16, 185, 129, 0.1) !important;
}

@keyframes highlight-pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
    }
}
</style>