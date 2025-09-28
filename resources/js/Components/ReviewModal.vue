<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { ref } from 'vue';

const emit = defineEmits(['close']); // Move this BEFORE using emit
const toast = useToast();
const form = useForm({
    name: '',
    location: '',
    review_message: '',
})

const submitReview = () => {
    form.post('/review', {
        onSuccess: () => {
            toast.success('Review Submitted Successfully!');
            form.reset()
            emit('close')
        },
    })
}

defineProps({
    show: Boolean
})
</script>


<template>
    <div v-if="show" class="fixed z-[9999] inset-0 backdrop-blur-sm bg-white/20 flex justify-center items-center">
        <div class="w-[35%] p-10 shadow-lg border bg-white flex flex-col gap-5 rounded-lg">
            <div class="">
                <!-- Close Button -->
                <div class="flex justify-end">
                    <img @click="$emit('close')" :src="'/Images/SVG/x.svg'" alt="Close Icon" class="w-5 h-5 hover:cursor-pointer">
                </div>
            </div>
            
            <!-- Card Content -->
            <div class="flex flex-col gap-4">
                <div class="text-center my-5">
                    <h1 class="text-3xl font-bold text-blue-500">Tell Us What You Think</h1>
                    <p class="text-gray-500">Every review helps us get better for you.</p>
                </div>
                <form @submit.prevent="submitReview" class="flex flex-col gap-3">
                    <!-- <div>
                        <label class="block font-semibold font-[Poppins]" for="name">Name</label>
                        <input v-model="form.name" class="p-3 rounded w-full border bg-white border-b-2 border-[#3B82F6] px-3 py-3 dark:bg-[#2c2c2c]" type="text" id="name" placeholder="John Doe">
                    </div> -->
                    <div class="relative">
                        <input
                            v-model="form.name"
                            type="text" 
                            id="floating_outlined" 
                            class="block p-4 pt-4 w-full text-base bg-white text-gray-900 bg-transparent rounded-lg border-1 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                            placeholder=" " />
                        <label 
                            for="name" 
                            class="absolute text-base text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white dark:bg-gray-900 px-5 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">Full Name</label>
                    </div>

                    <div class="mb-5">
                        <!-- <label class="block font-semibold font-[Poppins]" for="location">Location</label> -->
                        <select 
                            v-model="form.location" 
                            class="block p-4 w-full text-base bg-white text-gray-500 bg-transparent rounded-lg border-1 border-gray-300 appearance-none dark:text-gray-400 dark:border-gray-700 focus:outline-none focus:ring-0 focus:border-gray-200 peer"
                            name="location" 
                            id="location">
                            <option value="">Select Your Location</option>
                            <option value="Purok 1 - Sitio Ligua">Purok 1 - Sitio Ligua</option>
                            <option value="Purok 2 - Sitio Centro">Purok 2 - Sitio Centro</option>
                            <option value="Purok 3 - Sitio Red Land">Purok 3 - Sitio Red Land</option>
                            <option value="Purok 4 - Sitio Sto.Nino">Purok 4 - Sitio Sto.Nino</option>
                            <option value="Purok 5 - Sitio Sambag 1">Purok 5 - Sitio Sambag 1</option>
                            <option value="Purok 6 - Sitio Sambag 2">Purok 6 - Sitio Sambag 2</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold font-[Poppins] mb-1" for="review_message">Review</label>
                        <textarea 
                            v-model="form.review_message" 
                            class="block p-2.5 h-40 w-full text-base bg-white text-gray-900 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 resize-none" 
                            id="message" 
                            name="message"  
                            placeholder="Type your review here"
                            >
                        </textarea>

                    </div>
                    
                    <div class="">
                        <button class="w-full bg-blue-500 px-3 py-3 rounded text-[#FAF9F6] hover:bg-blue-600 transition-all duration-300">Submit Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
     