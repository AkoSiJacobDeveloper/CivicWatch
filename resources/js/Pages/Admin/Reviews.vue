<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { ref } from 'vue';

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
const showDeleteModal = ref(false);
const itemToDelete = ref(null);
const sortOrder = ref(props.filters?.date || 'desc');

function confirmDelete(id) {
    itemToDelete.value = id;
    showDeleteModal.value = true;
}

function deleteReview() {
    if (!itemToDelete.value) return;
    router.delete(route('reviews.delete', itemToDelete.value), {
        onSuccess: () => {
            toast.success('Review deleted successfully');
            showDeleteModal.value = false;
            itemToDelete.value = null;
        },
        onError: () => {
            toast.error('Failed to delete the review. Please try again.');
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
</script>

<template>
    <Head title="Manage Categories" />

    <AdminLayout>
        <main class="flex flex-col gap-7 text-sm">
            <div class="flex justify-between items-center">
                <div class="flex gap-1 items-center">
                    <img 
                        :src="'/Images/SVG/chat-circle-dots (black).svg'" 
                        alt="icon"
                        class="h-8"
                    >
                    <h1 class="font-semibold text-3xl font-[Poppins] my-3">Reviews</h1>
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
                        class="shadow-container p-8 bg-white flex flex-col gap-6"
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
                                <button @click="confirmDelete(review.id)">
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

                <DeleteReview
                    :show="showDeleteModal" 
                    @close="showDeleteModal = false" 
                    @delete="deleteReview"
                />
            </section>
        </main>
    </AdminLayout>
</template>