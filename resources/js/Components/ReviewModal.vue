<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { ref } from 'vue';
import Swal from 'sweetalert2';
import {
    Listbox,
    ListboxButton,
    ListboxOptions,
    ListboxOption,
} from '@headlessui/vue'

const emit = defineEmits(['close']); 
const toast = useToast();
const form = useForm({
    name: '',
    location: '',
    review_message: '',
    is_anonymous: false,
    rating: 0,
})

const locations = [
    {
        id: 1,
        place: 'Purok 1 - Sitio Ligua'
    },
    {
        id: 2,
        place: 'Purok 2 - Sitio Centro'
    },
    {
        id: 3,
        place: 'Purok 3 - Sitio Red Land'
    },
    {
        id: 4,
        place: 'Purok 4 - Sitio Sto.Nino'
    },
    {
        id: 5,
        place: 'Purok 5 - Sitio Sambag 1'
    },
    {
        id: 6,
        place: 'Purok 6 - Sitio Sambag 2'
    }
]

const selectedLocation = ref(null)

const hoverRating = ref(0)

const setRating = (stars) => {
    form.rating = stars
}

const setHoverRating = (stars) => {
    hoverRating.value = stars
}

const resetHoverRating = () => {
    hoverRating.value = 0
}

const submitReview = () => {
    // Show confirmation dialog before submitting
    Swal.fire({
        title: 'Ready to submit your review?',
        html: `
            <div class="text-left">
                <p class="mb-3 text-center font-[Poppins] text-base font-medium">Your review will be published and <strong>cannot be edited</strong> after submission.</p>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm">
                    <p class="font-medium text-blue-800">Please make sure:</p>
                    <ul class="list-disc list-inside mt-1 text-blue-700 space-y-1">
                        <li>Your review is accurate and respectful</li>
                        <li>You've selected the correct location</li>
                        <li>You're satisfied with your rating</li>
                    </ul>
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, submit my review',
        cancelButtonText: 'No, let me review it',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-lg shadow-xl',
            htmlContainer: 'text-left'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'info',
                title: 'Submitting...',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            form.post('/review', {
                onSuccess: () => {
                    Swal.close();
                    toast.success('Review submitted successfully!');
                    form.reset()
                    form.is_anonymous = false;
                    form.rating = 0;
                    selectedLocation.value = null;
                    emit('close')
                },
                onError: () => {
                    Swal.close();
                    toast.error('Failed to submit review. Please try again.');
                }
            })
        }
    })
}

defineProps({
    show: Boolean
})
</script>

<template>
    <div v-if="show" class="fixed z-[1000] inset-0 backdrop-blur-sm bg-white/20 flex justify-center items-center p-4">
        <div class="w-full max-w-md sm:max-w-lg md:max-w-xl lg:w-[35%] bg-white flex flex-col rounded-lg">
            <div class="flex justify-between p-4 sm:p-6 lg:p-8 bg-blue-700 rounded-t-md">
                <!-- Header -->
                <div class="flex-1">
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-white">Tell Us What You Think</h1>
                    <p class="text-white font-light text-sm sm:text-base">Every review helps us get better for you.</p>
                </div>
                <!-- Close Button -->
                <div class="flex justify-end ml-4">
                    <img @click="$emit('close')" :src="'/Images/SVG/x (white).svg'" alt="Close Icon" class="h-4 w-4 sm:h-5 sm:w-5 hover:cursor-pointer">
                </div>
            </div>
            
            <!-- Card Content -->
            <div class="flex flex-col px-4 sm:p-6 lg:px-8 pt-4 pb-6 sm:pb-8">
                <form @submit.prevent="submitReview" class="flex flex-col gap-3 sm:gap-4">
                    <!-- Name -->
                    <div class="relative">
                        <input
                            v-model="form.name"
                            type="text" 
                            id="floating_outlined" 
                            class="block p-3 sm:p-4 w-full text-sm sm:text-base bg-white text-gray-900 bg-transparent rounded-lg border-1 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                            placeholder=" " />
                        <label 
                            for="name" 
                            class="absolute text-sm sm:text-base text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white dark:bg-gray-900 px-2 sm:px-5 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">Full Name</label>
                    </div>

                    <!-- Location -->
                    <div class="relative">
                        <Listbox v-model="form.location">
                            <ListboxButton
                                class="flex justify-between items-center text-left p-3 sm:p-4 w-full text-sm sm:text-base bg-white text-gray-900 rounded-lg border border-gray-300 focus:outline-none focus:ring-0 focus:border-blue-600"
                            >
                                <span class="block truncate" :class="{ 'text-gray-500': !form.location }">
                                    {{ form.location || 'Select Location' }}
                                </span>
                                <span class="pointer-events-none flex items-center">
                                    <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </ListboxButton>

                            <ListboxOptions
                                class="absolute z-50 mt-1 w-full bg-white rounded-lg shadow-lg border border-gray-300 max-h-56 overflow-y-auto"
                            >
                                <ListboxOption
                                    v-for="location in locations"
                                    :key="location.id"
                                    :value="location.place"
                                    v-slot="{ active, selected }"
                                    class="cursor-pointer select-none relative py-2 pl-3 pr-9"
                                    :class="active ? 'bg-blue-100 text-blue-900' : 'text-gray-900'"
                                >
                                    <span class="block truncate text-sm sm:text-base" :class="selected ? 'font-medium' : 'font-normal'">
                                        {{ location.place }}
                                    </span>
                                    <span v-if="selected" class="absolute inset-y-0 right-0 flex items-center pr-4 text-blue-600">
                                        <svg class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </ListboxOption>
                            </ListboxOptions>
                        </Listbox>
                    </div>

                    <!-- Star Rating-->
                    <div class="">
                        <label class="block text-sm font-semibold font-[Poppins] mb-1">Rating <span class="text-blue-500 text-xs font-normal">(Optional)</span></label>
                        <div class="border rounded-lg p-3 sm:p-4">
                            <div class="flex items-center space-x-1">
                                <button
                                    v-for="star in 5"
                                    :key="star"
                                    type="button"
                                    @click="setRating(star)"
                                    @mouseenter="setHoverRating(star)"
                                    @mouseleave="resetHoverRating"
                                    class="text-xl sm:text-2xl focus:outline-none transition-transform hover:scale-110"
                                    :class="(hoverRating || form.rating) >= star ? 'text-yellow-400' : 'text-gray-300'"
                                >
                                    ★
                                </button>
                                <span class="ml-2 text-xs sm:text-sm text-gray-500">
                                    {{ form.rating ? `${form.rating}/5` : 'Click to rate' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Message or Description-->
                    <div>
                        <label class="block text-sm font-semibold font-[Poppins] mb-1" for="review_message">Review</label>
                        <textarea 
                            v-model="form.review_message" 
                            class="block p-2.5 h-32 sm:h-40 w-full text-sm sm:text-base bg-white text-gray-900 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 resize-none" 
                            id="message" 
                            name="message"  
                            placeholder="Type your review here"
                            >
                        </textarea>
                    </div>

                    <!-- Anonymous -->
                    <div class="flex items-center mt-2">
                        <input 
                            v-model="form.is_anonymous"
                            type="checkbox" 
                            id="is_anonymous"
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <label for="is_anonymous" class="ml-2 text-xs sm:text-sm text-gray-700">
                            <p>Post as anonymous? <span class="text-blue-500 text-xs">(Your name will be hidden from public)</span></p>
                        </label>
                    </div>
                    
                    <div class="">
                        <button class="w-full bg-blue-500 px-3 py-3 rounded text-[#FAF9F6] hover:bg-blue-600 transition-all duration-300 text-sm sm:text-base">Submit Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>