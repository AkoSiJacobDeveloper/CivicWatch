<script setup>
import { onUnmounted, onMounted, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { ref } from 'vue';
import Swal from 'sweetalert2';

import AdminLayout from '@/Layouts/AdminLayout.vue';

const toast = useToast();
const props = defineProps({ 
    reviews: Object,
    filters: Object,
    currentStatus: {
        type: String,
        default: 'pending'
    },
    pending_count: {
        type: Number,
        default: 0
    },
    approved_count: {
        type: Number,
        default: 0
    },
    rejected_count: {
        type: Number,
        default: 0
    }
})

const sortOrder = ref(props.filters?.date || 'desc');
const statusFilter = ref(props.currentStatus || 'pending');
const lastUpdate = ref(new Date().toISOString()); 
let pollInterval = null;
const previousReviewsCount = ref(props.reviews.total || 0);
const currentReviewIds = ref(new Set());
const newReviewIds = ref(new Set());

const statusOptions = computed(() => {
    return [
        { 
            value: 'pending', 
            label: 'Pending Review', 
            count: typeof props.pending_count !== 'undefined' ? props.pending_count : 0 
        },
        { 
            value: 'approved', 
            label: 'Approved', 
            count: typeof props.approved_count !== 'undefined' ? props.approved_count : 0 
        },
        { 
            value: 'rejected', 
            label: 'Rejected', 
            count: typeof props.rejected_count !== 'undefined' ? props.rejected_count : 0 
        }
    ];
});
const debugProps = computed(() => {
    return {
        reviews: props.reviews,
        pending_count: props.pending_count,
        approved_count: props.approved_count,
        rejected_count: props.rejected_count,
        all_props: props
    };
});

function applyFilter(value) {
    statusFilter.value = value;
    
    router.get('/admin/reviews', { 
        sort: sortOrder.value,
        status: statusFilter.value 
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    })
}

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

            if (currentReviewsCount > previousReviewsCount.value && statusFilter.value === 'pending') {
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

function confirmApprove(review) {
    Swal.fire({
        title: 'Approve Review?',
        html: `Are you sure you want to approve the review from <strong>${review.name}</strong>?<br>
            <span class="text-sm text-gray-500">This review will be visible to the public.</span>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, approve it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-lg shadow-xl',
            confirmButton: 'px-4 py-2 rounded-lg',
            cancelButton: 'px-4 py-2 rounded-lg'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            approveReviewAction(review.id);
        }
    });
}

function confirmReject(review) {
    Swal.fire({
        title: 'Reject Review?',
        html: `Are you sure you want to reject the review from <strong>${review.name}</strong>?<br>
            <span class="text-sm text-gray-500">This review will not be visible to the public.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, reject it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-lg shadow-xl',
            confirmButton: 'px-4 py-2 rounded-lg',
            cancelButton: 'px-4 py-2 rounded-lg'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            rejectReviewAction(review.id);
        }
    });
}

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
            deleteReviewAction(review.id);
        }
    });
}

async function approveReviewAction(reviewId) {
    try {
        Swal.fire({
            title: 'Approving...',
            text: 'Please wait while we approve the review.',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        await router.post(route('admin.reviews.approve', { review: reviewId }), {
            preserveScroll: true,
        });

        Swal.close();
        
        toast.success('Review approved successfully!');
        
    } catch (error) {
        Swal.close();
        toast.error('Failed to approve the review. Please try again.');
    }
}

async function rejectReviewAction(reviewId) {
    try {
        Swal.fire({
            title: 'Rejecting...',
            text: 'Please wait while we reject the review.',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        await router.post(route('admin.reviews.reject', { review: reviewId }), {
            preserveScroll: true,
        });


        Swal.close();
        toast.success('Review rejected successfully!');
        
    } catch (error) {
        Swal.close();
        toast.error('Failed to reject the review. Please try again.');
    }
}

async function deleteReviewAction(reviewId) {
    try {
        Swal.fire({
            title: 'Deleting...',
            text: 'Please wait while we delete the review.',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        await router.post(route('admin.reviews.delete', { review: reviewId }), {
            preserveScroll: true,
        });

        Swal.close();
        toast.success('Review deleted successfully!');
        
    } catch (error) {
        Swal.close();
        toast.error('Failed to delete the review. Please try again.');
    }
}

function toggleSort() {
    sortOrder.value = sortOrder.value === 'desc' ? 'asc' : 'desc'
    
    router.get('/admin/reviews', { 
        sort: sortOrder.value,
        status: statusFilter.value 
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    })
}

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'approved':
            return 'bg-green-100 text-green-800 border-green-200';
        case 'rejected':
            return 'bg-red-100 text-red-800 border-red-200';
        default:
            return 'bg-gray-100 text-gray-800 border-gray-200';
    }
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
    <Head title="Manage Reviews" />

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
                        <h1 class="font-semibold text-2xl font-[Poppins]">Reviews</h1>
                        <p class="text-gray-600 text-sm">Manage and moderate user reviews</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 py-[5.2px]">
                    <!-- Sort Button -->
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
                <!-- Status Info -->
                <div class="mb-7 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-blue-800">
                                {{ statusFilter === 'pending' ? 'Pending Reviews' : 
                                    statusFilter === 'approved' ? 'Approved Reviews' : 
                                    'Rejected Reviews' }}
                            </h3>
                            <p class="text-blue-600 text-sm">
                                {{ statusFilter === 'pending' ? 'These reviews are waiting for your approval before being visible to the public.' : 
                                    statusFilter === 'approved' ? 'These reviews are live and visible to all users on the website.' : 
                                    'These reviews were rejected and are not visible to the public.' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tab Filter (like Achievements component) -->
                <div class="flex flex-col gap-2 mb-6">
                    <div class="">
                        <ul class="flex justify-between">
                            <div class="font-[Poppins] flex flex-wrap text-sm font-medium text-center border-b border-gray-200 dark:border-gray-700">
                                <li 
                                    v-for="(option, index) in statusOptions" 
                                    :key="index"
                                    class="me-2"
                                >
                                    <button
                                        @click="applyFilter(option.value)"
                                        :class="[
                                            'flex items-center gap-2 px-4 py-2 rounded-t-lg transition-all duration-300 border-b-2 font-medium',
                                            statusFilter === option.value 
                                                ? 'text-blue-700 bg-blue-50 border-blue-700' 
                                                : 'border-transparent hover:text-blue-600 hover:bg-blue-50 hover:border-blue-300'
                                        ]"
                                    >
                                        <div :class="[
                                            'w-2 h-2 rounded-full mr-1',
                                            option.value === 'pending' ? 'bg-yellow-500' :
                                            option.value === 'approved' ? 'bg-green-500' :
                                            'bg-red-500'
                                        ]"></div>
                                        
                                        <span>{{ option.label }}</span>
                                        <span class="bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded-full">
                                            {{ option.count }}
                                        </span>
                                    </button>
                                </li>
                            </div>
                        </ul>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div 
                        v-for="review in props.reviews.data" 
                        :key="review.id"
                        :class="[
                            'shadow-container p-8 bg-white flex flex-col gap-6 transition-all duration-500 rounded-lg border',
                            isNewReview(review.id) ? 'border-2 border-green-500 bg-green-50 animate-pulse' : ''
                        ]"
                    >

                        <div class="flex justify-between">
                            <div class="inline-flex gap-1">
                                <div>
                                    <img 
                                        :src="'/Images/SVG/user-circle-fill.svg'"
                                        :alt="Icon"
                                        :class="review.is_anonymous ? 'opacity-55': 'opacity-100'"
                                        class="h-16 w-16"
                                    />
                                </div>
                                <div class="flex flex-col">
                                    <div class="flex items-center justify-between gap-2">
                                        <h3 class="font-bold text-base font-[Poppins]">{{ review.name }}</h3>
                                        
                                    </div>
                                    <p class="text-gray-600 text-xs font-medium">{{ review.location }}</p>
                                    <p class="text-gray-600 text-xs">{{ review.created_at }}</p>
                                    
                                </div>
                            </div>

                            <div class="flex items-center space-x-1">
                                <span
                                    class="px-1 shadow rounded bg-blue-700"
                                    v-for="star in 5" :key="star" 
                                    :class="star <= review.rating ? 'text-yellow-400 text-xl' : 'text-gray-200 text-xl'">
                                ★
                                </span>
                            </div>
                            
                            
                        </div>
                        

                        <div class="flex flex-col gap-3">
                            <!-- Status Badge -->
                            <div class="flex items-center gap-1">
                                <span 
                                    :class="['px-3 py-1 text-xs font-medium rounded-full border capitalize', getStatusBadgeClass(review.status)]"
                                >
                                    {{ review.status }}
                                </span>
                                <span 
                                    v-if="review.is_anonymous"
                                    class="bg-gray-100 text-gray-800 px-3 py-1 text-xs font-medium rounded-full border capitalize border-gray-200"
                                >
                                    Anonymous
                                </span>
                            </div> 
                            
                            <div class="h-[145px] overflow-y-auto">
                                <p class="text-gray-600 text-base">{{ review.review_message }}</p>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex justify-end gap-2">
                            <template v-if="review.status === 'pending'">
                                <button 
                                    @click="confirmApprove(review)"
                                    class="p-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors duration-200"
                                    title="Approve Review"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </button>
                                <button 
                                    @click="confirmReject(review)"
                                    class="p-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200"
                                    title="Reject Review"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </template>
                            <button 
                                @click="confirmDelete(review)"
                                class="p-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200"
                                title="Delete Review"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>  
                
                <!-- Empty State -->
                <div v-if="!reviews.data || reviews.data.length === 0" class="mt-3 flex flex-col items-center justify-center h-64 p-10 mb-2">
                    <img 
                            :src="'/Images/SVG/not found.svg'" 
                            alt="SVG" 
                            class="h-44 flex items-center"
                        >
                    <h3 class="text-lg font-medium text-gray-500">No {{ statusFilter }} reviews</h3>
                    
                </div>
                
                <!-- Pagination -->
                <div v-if="reviews.data && reviews.data.length > 0" class="bg-gray-50 px-3 py-4 mt-6 rounded-lg">
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
                                    :href="link.url + (link.url.includes('?') ? '&' : '?') + `status=${statusFilter}&sort=${sortOrder}`"
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
            </section>
        </main>
    </AdminLayout>
</template>