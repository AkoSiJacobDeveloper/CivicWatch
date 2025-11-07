<script setup>
import { onUnmounted, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { ref } from 'vue';
import Swal from 'sweetalert2';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import DeleteReview from '@/Components/DeleteReview.vue';

const toast = useToast();
const props = defineProps({ 
    reviews: Object,
    filters: Object,
    status : {
        type: String,
        default: 'all'
    }
})

const sortOrder = ref(props.filters?.date || 'desc');
const lastUpdate = ref(new Date().toISOString()); 
let pollInterval = null;
const previousReviewsCount = ref(props.reviews.total || 0);
const currentReviewIds = ref(new Set());
const newReviewIds = ref(new Set());

// Polling function
const pollForUpdates = () => {
    router.reload({
        preserveState: true,
        preserveScroll: true,
        only: ['reviews'], 
        onSuccess: (page) => {
            const currentReviewsCount = page.props.reviews.total || 0;

            setTimeout(() => {
                newReviewIds.value.clear();
            }, 5000);

            if (currentReviewsCount > previousReviewsCount.value) {
                const newReviewsCount = currentReviewsCount - previousReviewsCount.value;
                
                toast.info('New review submitted!');
                
                page.props.reviews.data.forEach(review => {
                    if (!currentReviewIds.value.has(review.id)) {
                        newReviewIds.value.add(review.id);
                        currentReviewIds.value.add(review.id);
                    }
                });
                
                previousReviewsCount.value = currentReviewsCount;
            }

            if (page.props.reviews.data.length > 0) {
                lastUpdate.value = new Date().toISOString();
            }
        }
    });
};


function confirmDelete(review) {
    Swal.fire({
        title: 'Delete Review?',
        html: `Are you sure you want to delete the review from <strong>${review.name}</strong>?<br>
            <span class="text-sm text-gray-500">This action cannot be undone.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-lg shadow-xl',
            confirmButton: 'px-4 py-2 rounded-lg',
            cancelButton: 'px-4 py-2 rounded-lg'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            deleteReview(review.id);
        }
    });
}

function deleteReview(review) {
    Swal.fire({
        title: 'Deleting...',
        text: 'Please wait while we delete the review.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    router.delete(route('reviews.delete', review), {
        preserveScroll: true,
        onSuccess: () => {
            Swal.close();
            toast.success('Review deleted successfully');
        },
        onError: () => {
            Swal.close();
            Swal.fire({
                title: 'Error!',
                text: 'Failed to delete the review. Please try again.',
                icon: 'error',
                confirmButtonColor: '#d33'
            });
        },
    })
}


function toggleSort() {
    sortOrder.value = sortOrder.value === 'desc' ? 'asc' : 'desc'
    
    router.get('/admin/reviews', { sort: sortOrder.value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    })
}

const isNewReview = (reviewId) => {
    return newReviewIds.value.has(reviewId);
};

onMounted(() => {
    props.reviews.data.forEach(review => {
        currentReviewIds.value.add(review.id);
    });

    pollInterval = setInterval(pollForUpdates, 5000);
});

onUnmounted(() => {
    if (pollInterval) {
        clearInterval(pollInterval);
    }
});
</script>

<template>
    <Head title="Manage Categories" />

    <AdminLayout>
        <main class="flex flex-col gap-7 text-sm">
            <div class="flex justify-between items-center">
                <div class="flex gap-3 items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <img 
                            :src="'/Images/SVG/chat-circle-dots (black).svg'" 
                            alt="icon"
                            class="h-8"
                        >
                    </div>
                    <div>
                        <h1 class="font-semibold text-2xl font-[Poppins]3">Reviews</h1>
                        <p class="text-gray-600 text-sm">Feedback and progress, centralized</p>
                    </div>
                    
                </div>
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

            <section>
                <div class="grid grid-cols-3 gap-4">
                    <div 
                        v-for="review in props.reviews.data" 
                        :key="review.id"
                        :class="[
                            'shadow-container p-8 bg-white flex flex-col gap-6 transition-all duration-500',
                            isNewReview(review.id) ? 'border-2 border-green-500 bg-green-50 animate-pulse' : ''
                        ]"
                    >
                        <div class="flex flex-col gap-3">
                            <div class="flex justify-between items-center">
                                <img :src="'/Images/SVG/quote-30-double-open.svg'" alt="Quotation Icon" class="h-12">
                                <p class="text-gray-600">{{ review.created_at }}</p>
                            </div>
                            <div class="h-[145px] overflow-y-auto">
                                <p class="text-gray-600 text-base">{{ review.review_message }}</p>
                            </div>
                        </div>
                        
                        <div class="flex justify-between">
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
                                    <!-- Show real name with anonymous badge -->
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-bold text-base font-[Poppins]">{{ review.name }}</h3>
                                        <span 
                                            v-if="review.is_anonymous"
                                            class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded border border-gray-200"
                                        >
                                            Anonymous
                                        </span>
                                    </div>
                                    <p class="text-gray-600 text-xs font-medium">{{ review.location }}</p>
                                    <div class="flex items-center space-x-1">
                                        <span v-for="star in 5" :key="star" 
                                            :class="star <= review.rating ? 'text-yellow-400' : 'text-gray-300'">
                                            ★
                                        </span>
                                        <span v-if="review.rating" class="ml-1 text-xs text-gray-600">
                                            ({{ review.rating }}/5)
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-center items-center hover:scale-110 transition-all duration-300">
                                <button @click="confirmDelete(review)">
                                    <img :src="'/Images/SVG/trash (red).svg'" alt="Icon" class="h-6 cursor-pointer">
                                </button>
                            </div>
                        </div>
                    </div>
                </div>  
                
                <!-- Pagination -->
                <div class="bg-gray-50 px-3 py-4">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing <span class="font-medium">{{ props.reviews.from || 0 }}</span> to 
                            <span class="font-medium">{{ props.reviews.to || 0 }}</span> of 
                            <span class="font-medium">{{ props.reviews.total || 0 }}</span> results
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <template v-for="link in props.reviews.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    :class="[
                                        'flex items-center justify-center w-10 h-10 text-sm font-medium rounded-lg border transition-all duration-200',
                                        link.active 
                                            ? 'bg-blue-600 text-white border-blue-600 shadow-lg' 
                                            : 'bg-white text-gray-500 border-gray-300 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-300'
                                    ]"
                                >
                                    <span v-if="link.label.includes('Previous')" class="transform">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 256 256">
                                            <path d="M165.66,202.34a8,8,0,0,1-11.32,11.32l-80-80a8,8,0,0,1,0-11.32l80-80a8,8,0,0,1,11.32,11.32L91.31,128Z"></path>
                                        </svg>
                                    </span>
                                    <span v-else-if="link.label.includes('Next')" class="transform">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 256 256">
                                            <path d="M181.66,133.66l-80,80a8,8,0,0,1-11.32-11.32L164.69,128,90.34,53.66a8,8,0,0,1,11.32-11.32l80,80A8,8,0,0,1,181.66,133.66Z"></path>
                                        </svg>
                                    </span>
                                    <span v-else v-html="link.label"></span>
                                </Link>
                                <span
                                    v-else
                                    class="flex items-center justify-center w-10 h-10 text-sm text-gray-400 cursor-not-allowed"
                                >
                                    <span v-if="link.label.includes('Previous')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 256 256">
                                            <path d="M165.66,202.34a8,8,0,0,1-11.32,11.32l-80-80a8,8,0,0,1,0-11.32l80-80a8,8,0,0,1,11.32,11.32L91.31,128Z"></path>
                                        </svg>
                                    </span>
                                    <span v-else-if="link.label.includes('Next')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 256 256">
                                            <path d="M181.66,133.66l-80,80a8,8,0,0,1-11.32-11.32L164.69,128,90.34,53.66a8,8,0,0,1,11.32-11.32l80,80A8,8,0,0,1,181.66,133.66Z"></path>
                                        </svg>
                                    </span>
                                    <span v-else v-html="link.label"></span>
                                </span>
                            </template> 
                        </div>
                    </div>
                </div>

                <!-- <DeleteReview
                    :show="showDeleteModal" 
                    @close="showDeleteModal = false" 
                    @delete="deleteReview"
                /> -->
            </section>
        </main>
    </AdminLayout>
</template>