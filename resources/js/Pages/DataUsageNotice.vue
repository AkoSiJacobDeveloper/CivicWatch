<script setup>
import { Head } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const usages = [
    { icon: '/Images/SVG/asterisk.svg', htext: 'Purpose:', text: 'The personal data you provide (Name, Contact Number, Location) is used exclusively for processing your community issue report, and enabling Barangay Cabulijan officials to address the problem.'},
    { icon: '/Images/SVG/asterisk.svg', htext: 'Sharing:', text: 'Your report details are shared only with authorized Barangay personnel and, when necessary, the specific local agency tasked with the resolution (e.g., public works department).'},
    { icon: '/Images/SVG/asterisk.svg', htext: 'Retention:', text: 'Your data is stored securely and retained for as long as needed for service fulfillment, accountability, and system improvement.'},
    { icon: '/Images/SVG/asterisk.svg', htext: 'Your Control:', text: 'You can access your report data at any time using your unique tracking code.'},
]

</script>

<template>
    <Head title="Terms and Conditon" />

    <GuestLayout>
        <main class="pt-[50px]">
            <section class="md:px-10 lg:px-32 py-20 flex flex-col gap-7">
                <div class="mb-5 flex items-center gap-2 border border-blue-200 rounded-md bg-blue-50 p-5">
                    <div class="bg-blue-100 flex items-center p-3 rounded">
                        <img :src="'/Images/SVG/database.svg'" alt="Icon">
                    </div>
                    <div class="">
                        <h1 class="font-[Poppins] font-semibold text-4xl">Data Usage Notice</h1>
                        <p class="text-gray-500 font-medium text-sm">Last Updated: November 2025</p>
                    </div>
                </div>
                
                <div>
                    <h1 class="font-semibold text-2xl mb-1">Your Data, Our Responsibility</h1>
                    <p class="text-gray-500 font-light text-justify">This notice explains how your data is used within the CivicWatch system.</p>
                </div>
                
                <div>
                    <ul class="pl-2 font-light light-disk mb-2">
                        <li
                            v-for="usage in usages"
                            :key="usage.id"
                            class="flex items-center gap-1 text-gray-500"
                        >
                            <img :src="usage.icon" alt="icon" class="h-3">
                            <p class="font-light"><strong class="font-semibold">{{ usage.htext }} </strong> {{ usage.text }}</p>
                        </li>
                    </ul>
                </div>

                <div>
                    <p class="text-gray-500 font-light text-justify">By submitting a report, you acknowledge that you have read and understood this Data Usage Notice.</p>
                </div>
            </section>
        </main>
    </GuestLayout>
</template>
