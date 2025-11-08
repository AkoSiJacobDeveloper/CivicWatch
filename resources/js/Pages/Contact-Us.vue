<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';
import { ref, onMounted, onUnmounted } from 'vue';

import GuestLayout from '@/Layouts/GuestLayout.vue';
import ApplicationImage from '@/Components/ApplicationImage.vue';

// Intersection Observer setup
const observer = ref(null);
const observedElements = ref([]);

onMounted(() => {
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
    const elements = document.querySelectorAll('[data-observe]');
    elements.forEach((el) => {
        observer.value.observe(el);
        observedElements.value.push(el);
    });
});

onUnmounted(() => {
    // Cleanup observer
    if (observer.value) {
        observedElements.value.forEach((el) => {
            observer.value.unobserve(el);
        });
        observer.value.disconnect();
    }
});

const contacts = [
    { icon: 'phone', headline: 'Phone', data: '09123456789' },
    { icon: 'location', headline: 'Location', data: 'Cabulijan, Tubigon, Bohol' },
    { icon: 'envelope', headline: 'Email Address', data: 'civicwatch.brgy@gmail.com' },
]

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: ''
})

const toast = useToast();

const submit = () => {
    // Show loading SweetAlert
    Swal.fire({
        title: 'Submitting your concern...',
        text: 'Please wait while we process your message',
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });

    form.post(route('contact.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            Swal.close(); 
            toast.success('Your concern has been submitted successfully.');
        },
        onError: (errors) => {
            Swal.fire({
                title: 'Error!',
                text: 'There was a problem submitting your concern. Please try again.',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#EF4444'
            });
        }
    })
}

</script>

<template>
    <Head title="Contact Us" />

    <GuestLayout>
        <main class="dark:text-[#FAF9F6]">
            <!-- Hero Section -->
            <section class="pt-56 lg:pt-0 hero-section min-h-screen text-[#000] px-4 md:px-10 lg:px-32 flex flex-col lg:flex-row items-center" data-observe>
                <div class="w-full lg:w-1/2 flex justify-center items-center py-10 lg:py-0">
                    <div class="text-left">
                        <h1 class="text-3xl sm:text-4xl lg:text-6xl font-bold font-[Poppins] mb-5 text-blue-700">We're Here to Listen</h1>
                        <p class="text-justify text-base sm:text-lg dark:text-[#faf9f6]">Have a specific question, suggestion, or concern that's not covered in our FAQ? Reach out to us directly your voice helps shape a better community. The CivicWatch team is ready to assist you.</p>
                    </div>
                </div>
                <div class="hidden lg:flex w-full lg:w-1/2 justify-center items-center">
                    <div class="flex justify-center lg:justify-end">
                        <img :src="'/Images/email.svg'" alt="Community" class="w-64 sm:w-80 lg:w-[30rem]">
                    </div>
                </div>
            </section>

            <section class="text-[#000] mb-10 lg:mb-20 px-4 md:px-10 lg:px-32 flex flex-col lg:flex-row gap-6 lg:gap-1">
                <!-- Contact Form -->
                <div class="w-full lg:w-1/2 px-4 sm:px-5 py-8 lg:py-10 flex flex-col gap-4 lg:gap-5" data-observe>
                    <div class="flex flex-col mb-4 lg:mb-5 text-left">
                        <p class="text-base lg:text-lg font-semibold dark:text-white">Contact Us</p>
                        <h1 class="font-bold text-3xl sm:text-4xl lg:text-6xl text-blue-700 font-[Poppins]">Get In Touch</h1>
                    </div>
                    <div>
                        <form @submit.prevent="submit">
                            <!-- Name -->
                            <div class="relative">
                                <label
                                    for="name"
                                    class="block mb-2 text-xs md:text-sm font-medium text-gray-900 dark:text-white">Name
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    id="title"
                                    class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-3 md:p-4 dark:bg-[#2c2c2c] dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="John Doe"
                                    required
                                    :disabled="form.processing"
                                />
                            </div>

                            <!-- Email -->
                            <div class="relative my-3">
                                <label
                                    for="email"
                                    class="block mb-2 text-xs md:text-sm font-medium text-gray-900 dark:text-white">Email
                                </label>
                                <input
                                    v-model="form.email"
                                    type="text"
                                    id="title"
                                    class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-3 md:p-4 dark:bg-[#2c2c2c] dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="johndoe@gmail.com"
                                    required
                                    :disabled="form.processing"
                                />
                            </div>

                            <!-- Subject -->
                            <div class="relative mb-4 lg:mb-5">
                                <label
                                    for="subject"
                                    class="block mb-2 text-xs md:text-sm font-medium text-gray-900 dark:text-white">Subject
                                </label>
                                <input
                                    v-model="form.subject"
                                    type="text"
                                    id="title"
                                    class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-3 md:p-4 dark:bg-[#2c2c2c] dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Subject of your concern"
                                    required
                                    :disabled="form.processing"
                                />
                            </div>
                            
                            <!-- Message -->
                            <div class="flex flex-col mb-3">
                                <label
                                    for="subject"
                                    class="block mb-2 text-xs md:text-sm font-medium text-gray-900 dark:text-white">Message
                                </label>
                                <textarea 
                                    v-model="form.message" 
                                    name="message" 
                                    id="message"
                                    class="block p-3 sm:p-4 h-32 sm:h-40 w-full text-sm bg-white text-gray-900 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 resize-none dark:bg-[#2c2c2c]"
                                    placeholder="Your message here..." 
                                    required
                                    :disabled="form.processing"
                                >
                                </textarea>
                            </div>

                            <button 
                                :disabled="form.processing" 
                                :class="{ 'opacity-50 cursor-not-allowed': form.processing }" 
                                class="mt-3 w-full rounded text-[#FAF9F6] p-3 bg-blue-600 hover:bg-blue-700 transition-all duration-300 text-sm sm:text-base flex items-center justify-center gap-2"
                                type="submit"
                            >
                                <span v-if="form.processing" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
                                <span>{{ form.processing ? 'Submitting...' : 'Send Now' }}</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Contact Info & Map -->
                <div class="w-full lg:w-1/2 px-4 sm:px-5 py-8 lg:py-10 flex flex-col gap-4 lg:gap-5 border rounded" data-observe>
                    <div class="w-full h-auto grid grid-cols-1 sm:grid-cols-2 gap-3 lg:gap-2">
                        <div class="flex items-center gap-1 justify-center py-5 lg:py-0 bg-white shadow-md rounded-lg dark:bg-[#2c2c2c]" data-observe>
                            <ApplicationImage />
                            <div class="flex flex-col">
                                <div class="flex flex-col leading-none">
                                    <div class="border-b-2 border-blue-700">
                                        <p class="text-2xl font-bold font-[Poppins] text-blue-500">CivicWatch</p>
                                    </div>
                                    <p class="text-xs mt-1 font-semibold text-blue-900">REPORT.ACT.RESOLVE</p>
                                </div>
                            </div>
                        </div>  
                        
                        <div v-for="(contact, index) in contacts" :key="index" class="bg-white flex justify-center items-center shadow-md rounded-lg dark:bg-[#2c2c2c] p-4" data-observe>
                            <div class="text-center">
                                <font-awesome-icon :icon="contact.icon" class="text-2xl sm:text-3xl lg:text-4xl mb-1 sm:mb-2 text-blue-500" />
                                <h5 class="text-sm sm:text-base lg:text-lg font-semibold font-[Poppins] text-blue-500">{{ contact.headline }}</h5>
                                <p class="text-xs sm:text-sm dark:text-white">{{ contact.data }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="w-full h-64 sm:h-80 lg:h-full" data-observe>
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d676.6085748364573!2d123.97895532282458!3d9.949343210871389!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33aa258643482e4b%3A0x49f5907dab5a3d07!2sCabulijan%20Barangay%20Basketball%20Court%2C%20Tagbilaran%20North%20Road%2C%20Tubigon%2C%20Bohol!5e1!3m2!1sen!2sph!4v1753534615304!5m2!1sen!2sph" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>  
            </section>
        </main>
    </GuestLayout>
</template>

<style scoped>
/* Add smooth transition for observed elements */
[data-observe] {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s ease, transform 0.6s ease;
}

[data-observe].is-visible {
    opacity: 1;
    transform: translateY(0);
}

/* Staggered animation delays for multiple elements */
[data-observe]:nth-child(1) { transition-delay: 0.1s; }
[data-observe]:nth-child(2) { transition-delay: 0.2s; }
[data-observe]:nth-child(3) { transition-delay: 0.3s; }
[data-observe]:nth-child(4) { transition-delay: 0.4s; }
[data-observe]:nth-child(5) { transition-delay: 0.5s; }
[data-observe]:nth-child(6) { transition-delay: 0.6s; }
[data-observe]:nth-child(7) { transition-delay: 0.7s; }
[data-observe]:nth-child(8) { transition-delay: 0.8s; }
[data-observe]:nth-child(9) { transition-delay: 0.9s; }
</style>