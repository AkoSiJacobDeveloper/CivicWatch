<script setup>
import { Head } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { text } from '@fortawesome/fontawesome-svg-core';

const informations = [
    { icon: '/Images/SVG/asterisk.svg', text: 'Sender Name' },
    { icon: '/Images/SVG/asterisk.svg', text: 'Contact Number' },
    { icon: '/Images/SVG/asterisk.svg', text: 'The specific Barangay (Cabulijan), Sitio, and Purok' },
    { icon: '/Images/SVG/asterisk.svg', text: 'Images of the issue you upload' },
]

const datas = [
    { icon: '/Images/SVG/asterisk.svg', text: 'Report Title and Description' },
    { icon: '/Images/SVG/asterisk.svg', text: 'Issue Type (e.g., waste management, drainage, streetlights)' },
    { icon: '/Images/SVG/asterisk.svg', text: 'Location Details' },
    { icon: '/Images/SVG/asterisk.svg', text: 'Additional Notes and Remarks' },
]

const threes = [
    { icon: '/Images/SVG/asterisk.svg', text: 'To process, validate, and track your submitted reports.' },
    { icon: '/Images/SVG/asterisk.svg', text: 'To send real-time alerts to Barangay officials via Firebase and IoT devices.' },
    { icon: '/Images/SVG/asterisk.svg', text: 'To provide you with status updates on your report using your tracking code.' },
    { icon: '/Images/SVG/asterisk.svg', text: 'To communicate with you if follow-up is necessary, using the contact number you provided.' },
    { icon: '/Images/SVG/asterisk.svg', text: 'To improve our services and user experience based on feedback and reviews.' },
    { icon: '/Images/SVG/asterisk.svg', text: 'To display announcements and achievements to the community.' }
]

const fource = [
    { icon: '/Images/SVG/asterisk.svg', htext: 'With Barangay Officials:', text: 'Your report details are shared with authorized administrators of Barangay Cabulijan for validation and action.'},
    { icon: '/Images/SVG/asterisk.svg', htext: 'With Local Agencies', text: 'Upon validation by barangay officials, your report may be forwarded to the concerned local government agency responsible for resolving the issue.'},
    { icon: '/Images/SVG/asterisk.svg', htext: 'Service Providers', text: 'We use third-party services like Firebase Firestore for real-time notifications and database management. These providers are bound by contractual obligations to protect your data.'},
    { icon: '/Images/SVG/asterisk.svg', htext: 'Legal Obligations', text: 'We may disclose information if required to do so by law or in response to valid requests by public authorities.'},
]

const rights = [
    { icon: '/Images/SVG/asterisk.svg', text: 'Access the personal information we hold about you by using your tracking code.'},
    { icon: '/Images/SVG/asterisk.svg', text: 'Request correction of any inaccurate or incomplete information.'},
    { icon: '/Images/SVG/asterisk.svg', text: 'Access the personal information we hold about you by using your tracking code.'},
]
</script>

<template>
    <Head title="Privacy Policy" />

    <GuestLayout>
        <main class="pt-[50px]">
            <section class="md:px-10 lg:px-32 py-20 flex flex-col gap-7">
                <div class="mb-5 flex items-center gap-2 border border-blue-200 rounded-md bg-blue-50 p-5">
                    <div class="bg-blue-100 flex items-center p-3 rounded">
                        <img :src="'/Images/SVG/shield-check.svg'" alt="Icon">
                    </div>
                    <div>
                        <h1 class="font-[Poppins] font-semibold text-4xl">Privacy Policy</h1>
                        <p class="text-gray-500 font-medium text-sm">Last Updated: November 2025</p>
                    </div>
                </div>

                <div>
                    <h1 class="font-medium text-2xl mb-1">1. Introduction</h1>
                    <p class="text-gray-500 font-light text-justify">Welcome to CivicWatch, a web-based platform developed by the students of Mater Dei College for Barangay Cabulijan, Tubigon, Bohol. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our service to report non-emergency community issues. By using CivicWatch, you consent to the data practices described in this policy.</p>
                </div>

                <div>
                    <h1 class="font-medium text-2xl mb-1">2. Information We Collect</h1>
                    <ul class="text-gray-500">
                        <!-- Personal Data -->
                        <p class="font-light"><strong class="font-semibold">Personal Data: </strong>When you submit a report, we collect the information you provide, which may include:</p>
                        
                        <ul class="pl-2 font-light light-disk mb-2">
                            <li
                                v-for="information in informations"
                                :key="information.id"
                                class="flex items-center gap-1"
                            >
                                <img :src="information.icon" alt="icon" class="h-3">
                                {{ information.text }}
                            </li>
                            
                        </ul>
                        
                        <!-- Report Data -->
                        <p class="font-light"><strong class="font-semibold">Report Data: </strong>We collect details about the issue you report, such as:</p>

                        <ul class="pl-2 font-light light-disk mb-2">
                            <li
                                v-for="data in datas"
                                :key="data.id"
                                class="flex items-center gap-1"
                            >
                                <img :src="data.icon" alt="icon" class="h-3">
                                {{ data.text }}
                            </li>
                        </ul>

                        <p class="font-light"><strong class="font-semibold">Technical Data: </strong>We may collect information about your location for system administration and to prevent abuse.</p>
                        <p class="font-light"><strong class="font-semibold">Feedback Data: </strong>If you submit a review, we collect your name, purok, and the feedback message.</p>
                    </ul>
                </div>
                
                <div>
                    <h1 class="font-medium text-2xl mb-1">3. How We Use Your Information</h1>
                    <p class="text-gray-500 font-light mb-1">We use the information we collect for the following purposes:</p>

                    <ul class="pl-2 font-light light-disk mb-2">
                        <li
                            v-for="three in threes"
                            :key="three.id"
                            class="flex items-center gap-1 text-gray-500"
                        >
                            <img :src="three.icon" alt="icon" class="h-3">
                            {{ three.text }}
                        </li>
                    </ul>
                </div>

                <div>
                    <h1 class="font-medium text-2xl mb-1">4. Sharing Your Information</h1>
                    <p class="text-gray-500 font-light mb-1">Your information is shared strictly for the purpose of resolving the reported issues:</p>

                    <li
                        v-for="four in fource"
                        :key="four.id"
                        class="flex items-center gap-1 text-gray-500"
                    >
                        <img :src="four.icon" alt="icon" class="h-3">
                        <p class="font-light"><strong class="font-semibold">{{ four.htext }} </strong> {{ four.text }}</p>
                    </li>
                </div>

                <div>
                    <h1 class="font-medium text-2xl mb-1">5. Data Retention</h1>
                    <p class="text-gray-500 font-light text-justify">We will retain your personal data only for as long as necessary to fulfill the purposes outlined in this Privacy Policy, including for legal, administrative, or accountability purposes. Report records may be kept for system analysis and improving barangay services.</p>
                </div>

                <div>
                    <h1 class="font-medium text-2xl mb-1">6. Your rights</h1>
                    <p class="text-gray-500 font-light mb-1">You have the right to:</p>

                    <li
                        v-for="right in rights"
                        :key="right.id"
                        class="flex items-center gap-1 text-gray-500"
                    >
                        <img :src="right.icon" alt="icon" class="h-3">
                        {{ right.text }}
                    </li>
                </div>

                <div>
                    <h1 class="font-medium text-2xl mb-1">7. Security of Your Information</h1>
                    <p class="text-gray-500 font-light text-justify">We implement appropriate technical and organizational security measures, including password protection and secure server hosting, to protect your personal data. However, no method of transmission over the Internet is 100% secure, and we cannot guarantee its absolute security.</p>
                </div>

                <div>
                    <h1 class="font-medium text-2xl mb-1">8. Changes to This Privacy Policy</h1>
                    <p class="text-gray-500 font-light text-justify">We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last Updated" date.</p>
                </div>

                <div>
                    <h1 class="font-medium text-2xl mb-1">9. Contact Us</h1>
                    <p class="text-gray-500 font-light text-justify">If you have any questions about this Privacy Policy, please contact Barangay Cabulijan Hall or the CivicWatch development team at Mater Dei College. Their contact information will be found on the About page.</p>
                </div>
                

            </section>
        </main>
    </GuestLayout>
</template>
