<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

import GuestLayout from '@/Layouts/GuestLayout.vue';

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

const toast = useToast()

const submit = () => {
    form.post(route('contact.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            toast.success('Your concern was submitted successfully!');
        },
        onError: (errors) => {
            toast.error('Your concern didnt submitted successfully!');
        }
    })
}

</script>

<template>
    <Head title="Contact Us" />

    <GuestLayout>
        <main class="dark:text-[#FAF9F6]">
            <!-- Hero Section -->
            <section class="hero-section h-screen text-[#000] md:px-10 lg:px-32 flex ">
                <div class="w-full h-full md:w-1/2 md:flex justify-center items-center">
                    <div class="">
                        <h1 class="text-6xl font-bold font-[Poppins] mb-5 text-blue-700">We're Here to Listen</h1>
                        <p class="text-justify text-lg dark:text-white">Have a specific question, suggestion, or concern that’s not covered in our FAQ? Reach out to us directly your voice helps shape a better community. The CivicWatch team is ready to assist you.</p>
                    </div>
                </div>
                <div class="hidden md:w-1/2 h-full md:flex justify-center items-center">
                    <div class="hidden md:flex justify-end">
                        <img :src="'/Images/email.svg'" alt="Community" class="w-[30rem]">
                    </div>
                </div>
            </section>

            <section class="text-[#000] mb-20 md:px-10 lg:px-32 flex gap-1">
                <div class="w-1/2 px-5 py-10 flex flex-col gap-5">
                    <div class="flex flex-col mb-5">
                        <p class="text-lg font-semibold dark:text-white">Contact Us</p>
                        <h1 class="font-bold text-6xl text-blue-700 font-[Poppins]">Get In Touch</h1>
                    </div>
                    <div>
                        <form @submit.prevent="submit">
                            <!-- Name -->
                            <div class="relative">
                                <input 
                                    v-model="form.name"
                                    type="text" 
                                    class="block p-4 pt-4 w-full text-base bg-white text-gray-900 bg-transparent rounded-lg border-1 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer dark:bg-[#2c2c2c]"
                                    placeholder=" "
                                    required
                                    />
                                <label 
                                    for="name" id="name" 
                                    class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1 dark:bg-[#2c2c2c]">Name</label>
                            </div>

                            <!-- Email -->
                            <div class="relative my-3">
                                <input 
                                    v-model="form.email"
                                    type="email" 
                                    class="block p-4 pt-4 w-full text-base bg-white text-gray-900 bg-transparent rounded-lg border-1 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer dark:bg-[#2c2c2c]" 
                                    placeholder=" " 
                                    required
                                    />
                                <label 
                                    for="email" id="email" 
                                    class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1 dark:bg-[#2c2c2c]">Email</label>
                            </div>

                            <!-- Subject -->
                            <div class="relative mb-5">
                                <input 
                                    v-model="form.subject"
                                    type="text" 
                                    class="block p-4 pt-4 w-full text-base bg-white text-gray-900 bg-transparent rounded-lg border-1 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer dark:bg-[#2c2c2c]" 
                                    placeholder=" " 
                                    required
                                    />
                                <label 
                                    for="subject" id="subject" 
                                    class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1 dark:bg-[#2c2c2c]">Subject</label>
                            </div>
                            
                            <!-- Message -->
                            <div class="flex flex-col mb-3">
                                <label class="block text-sm font-semibold font-[Poppins] mb-1 dark:text-white" for="review_message">Message</label>
                                <textarea 
                                    v-model="form.message" 
                                    name="message" 
                                    id="message"
                                    class="block p-4 h-40 w-full text-base bg-white text-gray-900 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 resize-none dark:bg-[#2c2c2c]"
                                    placeholder="Your message here..." 
                                    required>
                                </textarea>
                            </div>

                            <button :disabled="form.processing" :class="{ 'opacity-50': form.processing }" class="mt-3 w-full rounded text-[#FAF9F6] p-3 bg-blue-600 hover:bg-blue-700 transition-all duration-300" type="submit">Send Now</button>
                        </form>
                    </div>
                </div>
                <div class="w-1/2 px-5 py-10 flex flex-col gap-5 border rounded">
                    <div class="w-full h-full grid grid-cols-2 gap-2">
                        <div class="flex justify-center items-center bg-white shadow-md rounded-lg dark:bg-[#2c2c2c]">
                            <img :src="'/Images/CivicWatch(2).png'" alt="CivicWatch logo" class="h-14">
                        </div>
                        <div v-for="(contact, index) in contacts" :key="index" class="bg-white flex justify-center items-center shadow-md rounded-lg dark:bg-[#2c2c2c]">
                            <div class="text-center">
                                <font-awesome-icon :icon="contact.icon" class="text-4xl mb-2 text-blue-500" />
                                <h5 class="text-lg font-semibold font-[Poppins] text-blue-500">{{ contact.headline }}</h5>
                                <p class="text-sm dark:text-white">{{ contact.data }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="w-full h-full">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d676.6085748364573!2d123.97895532282458!3d9.949343210871389!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33aa258643482e4b%3A0x49f5907dab5a3d07!2sCabulijan%20Barangay%20Basketball%20Court%2C%20Tagbilaran%20North%20Road%2C%20Tubigon%2C%20Bohol!5e1!3m2!1sen!2sph!4v1753534615304!5m2!1sen!2sph" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>  
            </section>
        </main>
    </GuestLayout>
</template>
