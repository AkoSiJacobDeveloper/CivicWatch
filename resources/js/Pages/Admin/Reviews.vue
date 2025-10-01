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
                    <!-- Total: <span class="text-blue-500">{{ reviews.total }}</span><span> Reports</span> -->
                    <select 
                        :value="props.filters.date ?? ''"
                        @change="router.get(
                            route('system.review'),
                            { date: $event.target.value },
                            { preserveState: true, preserveScroll: true, replace: true }
                        )"
                        class="text-gray-600 font-medium text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-32 p-3 border-gray-300 shadow-sm dark:placeholder-gray-400  dark:focus:ring-blue-500 dark:focus:border-blue-500 cursor-pointer mb-2"
                    >
                        <div class="mt-2">
                            <option value="">Sort by Date</option>
                            <option value="newest">Newest</option>
                            <option value="oldest">Oldest</option>
                        </div>
                        
                    </select>
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
                                <p class=" text-gray-600">{{ review.created_at }}</p>
                            </div>
                            <div class="h-32 overflow-y-auto">
                                <p class="text-gray-600 text-base">{{ review.review_message }}</p>
                            </div>
                        </div>
                            
                        <div class="mt-3 flex justify-between items-center">
                            <div>
                                <h3 class="font-bold text-base font-[Poppins]">{{ review.name }}</h3>
                                <p class="text-gray-600 text-sm ">{{ review.location }}</p>
                            </div>
                            <div class="flex justify-center items-center p-3 rounded-full shadow-md hover:scale-110 transition-all duration-300">
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