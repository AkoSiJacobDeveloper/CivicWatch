<script setup>
import { Head } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const threes = [
    { icon: '/Images/SVG/asterisk.svg', htext: 'Accurate Information:', text: 'You agree to provide true, accurate, and complete information when submitting a report.'},
    { icon: '/Images/SVG/asterisk.svg', htext: 'Appropriate Use:', text: 'You agree to use CivicWatch only for lawful purposes and not to submit false, misleading, or malicious reports.'},
    { icon: '/Images/SVG/asterisk.svg', htext: 'Non-Emergency Use:', text: 'You acknowledge that CivicWatch is for NON-EMERGENCY issues only. For life-threatening situations, fires, or crimes in progress, you must immediately contact the proper emergency authorities.'},
    { icon: '/Images/SVG/asterisk.svg', htext: 'Sole Responsibility:', text: 'You are responsible for maintaining the confidentiality of your report tracking code and for all activities that occur under its use.'},
]

</script>

<template>
    <Head title="Terms and Conditon" />

    <GuestLayout>
        <main class="pt-[50px]">
            <section class="md:px-10 lg:px-32 py-20 flex flex-col gap-7">
                <div class="mb-5 flex items-center gap-2 border border-blue-200 rounded-md bg-blue-50 p-5">
                    <div class="bg-blue-100 flex items-center p-3 rounded">
                        <img :src="'/Images/SVG/file-text (blue).svg'" alt="Icon">
                    </div>
                    <div class="">
                        <h1 class="font-[Poppins] font-semibold text-4xl">Terms and Conditions</h1>
                        <p class="text-gray-500 font-medium text-sm">Last Updated: November 2025</p>
                    </div>
                </div>
                

                <div>
                    <h1 class="font-medium text-2xl mb-1">1. Acceptance of Terms</h1>
                    <p class="text-gray-500 font-light text-justify">By accessing and using the CivicWatch website, you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to these terms, please do not use our service.</p>
                </div>

                <div>
                    <h1 class="font-medium text-2xl mb-1">2. Description of Service</h1>
                    <p class="text-gray-500 font-light text-justify">CivicWatch is a capstone project providing a web-based platform for residents of Barangay Cabulijan, Tubigon, Bohol, to report and track non-emergency community issues such as damaged roads, broken streetlights, and unmanaged waste.</p>
                </div>
                
                <div>
                    <h1 class="font-medium text-2xl mb-1">3. User Responsibilities</h1>

                    <ul class="pl-2 font-light light-disk mb-2">
                        <li
                            v-for="three in threes"
                            :key="three.id"
                            class="flex items-center gap-1 text-gray-500"
                        >
                            <img :src="three.icon" alt="icon" class="h-3">
                            <p class="font-light"><strong class="font-semibold">{{ three.htext }} </strong> {{ three.text }}:</p>
                        </li>
                    </ul>
                </div>

                <div>
                    <h1 class="font-medium text-2xl mb-1">4. Intellectual Property</h1>
                    <p class="text-gray-500 font-light mb-1">The CivicWatch platform, including its design, logo, source code, and all content, is the intellectual property of the development group and Mater Dei College. You may not reproduce, distribute, or create derivative works without permission.</p>
                </div>

                <div>
                    <h1 class="font-medium text-2xl mb-1">5. Disclaimer of Warranties</h1>
                    <p class="text-gray-500 font-light mb-1">CivicWatch is provided on an "AS IS" and "AS AVAILABLE" basis. We do not warrant that the service will be uninterrupted, timely, secure, or error-free. The resolution of reported issues is the responsibility of the Barangay officials and concerned agencies, not the CivicWatch development team.</p>
                </div>

                <div>
                    <h1 class="font-medium text-2xl mb-1">6. Limitation of Liabilitiy</h1>
                    <p class="text-gray-500 font-light text-justify">The developers, advisors, and Mater Dei College shall not be held liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use of or inability to use the service, or the actions/inactions of the barangay officials based on the reports submitted.</p>
                </div>

                <div>
                    <h1 class="font-medium text-2xl mb-1">7. Governing Law</h1>
                    <p class="text-gray-500 font-light text-justify">These Terms shall be governed and construed in accordance with the laws of the Republic of the Philippines.</p>
                </div>
            </section>
        </main>
    </GuestLayout>
</template>
