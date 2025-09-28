<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue';

const types = [
    {
        icon: 'thumbtack',
        headline: 'General Usage',
        items: [
            {
                question: 'What is CivicWatch and how does it work?',
                answer: 'CivicWatch is a web-based reporting platform that allows citizens to submit local community issues such as garbage collection delays, broken streetlights, and other concerns. Users can report an issue by filling out a simple form, attaching an image, selecting their location, and submitting it for review by barangay officials.',
            },
            {
                question: 'Who can submit a report?',
                answer: 'Any resident or concerned individual within the barangay can submit a report using CivicWatch. No login is required.',
            },
            {
                question: 'Is using CivicWatch free?',
                answer: 'Yes, using CivicWatch is completely free for all users. The goal is to improve community service by encouraging public participation.',
            },
            {
                question: 'Do I need to register to submit a report?',
                answer: 'No registration is needed. CivicWatch allows you to submit reports without creating an account.',
            },
            {
                question: 'Can I submit a report anonymously?',
                answer: 'While CivicWatch requires a name and contact number for follow-up, this information is not made public and is only used by barangay officials for verification if needed.',
            },
        ],
    },
    {
        icon: 'camera',
        headline: 'Submitting Reports',
        items: [
            {
                question: 'What kind of issues can I report?',
                answer: 'You can report local issues like uncollected garbage, potholes, broken streetlights, illegal dumping, clogged drainages, and more. A full list is available in the report form.',
            },
            {
                question: 'How do I upload a photo when reporting?',
                answer: 'Simply click the “Upload Photo” button in the report form and select an image from your device. Only image files are accepted.',
            },
            {
                question: 'What locations are supported for reporting?',
                answer: 'Reports are currently supported for locations within Brgy. Cabulijan. You can select your specific purok or sitio from a dropdown in the form.',
            },
            {
                question: 'Can I submit a report without a photo?',
                answer: 'Yes, but attaching a photo helps barangay officials assess and verify the issue more effectively.',
            },
            {
                question: 'Why do I need to complete a CAPTCHA when submitting a report?',
                answer: 'The CAPTCHA is used to prevent spam and ensure that submissions come from real people, not bots.',
            },
        ],
    },
    {
        icon: 'location-dot',
        headline: 'Tracking Reports',
        items: [
            {
                question: 'How can I check the status of my report?',
                answer: 'After submission, you’ll receive a tracking code. You can use this code on the “Track Reports” page to view the status of your report.',
            },
            {
                question: 'Where can I use my tracking code?',
                answer: 'Enter your tracking code on the Track Reports page to see the current status, remarks, and updates from officials.',
            },
            {
                question: 'What does it mean if my report is “Pending” or “Resolved”?',
                answer: 'Pending - Your report has been submitted and is waiting for review and Resolved - The reported issue has been addressed or action has been taken.',
            },
            {
                question: 'How long does it take for an issue to be reviewed?',
                answer: 'This depends on the nature and urgency of the issue. Barangay officials aim to review and respond as soon as possible.',
            },
        ],
    },
    {
        icon: 'gear',
        headline: 'Technical Questions',
        items: [
            {
                question: 'Is CivicWatch available on mobile devices?',
                answer: 'Yes! CivicWatch is fully responsive and works on phones, tablets, and desktops.',
            },
            {
                question: 'Why is my image not uploading?',
                answer: 'Make sure your image is in JPEG or PNG format and does not exceed the size limit. Also check your internet connection.',
            },
            {
                question: 'I forgot my tracking code. What should I do?',
                answer: 'Unfortunately, CivicWatch does not store personal tracking codes. Please make sure to copy and save it after submission.',
            },
            {
                question: 'Why is the CAPTCHA not showing up on my browser?',
                answer: 'This might be caused by browser settings or ad blockers. Try refreshing the page or disabling browser extensions temporarily.',
            },
        ],
    },
    {
        icon: 'lock',
        headline: 'Privacy and Security',
        items: [
            {
                question: 'What happens to my data after I submit a report?',
                answer: 'Your data is stored securely and used only for the purpose of processing and resolving your report.',
            },
            {
                question: 'Is my contact number visible to the public?',
                answer: 'No. Your contact number is kept private and is only accessible to authorized barangay staff.',
            },
            {
                question: 'How is my privacy protected?',
                answer: 'CivicWatch follows strict data handling practices. All personal details are encrypted and not shared publicly.',
            },
        ],
    },
    {
        icon: 'comment',
        headline: 'Support',
        items: [
            {
                question: 'Who reviews my report after submission?',
                answer: 'Reports are reviewed by authorized barangay officials or designated community staff responsible for public concerns.',
            },
            {
                question: 'How do I contact the support team?',
                answer: 'You can use the contact form on the website or visit your local barangay office for support regarding your report.',
            },
            {
                question: 'What if I submitted the wrong information?',
                answer: 'You cannot edit a report after submission. If you made a mistake, you may submit a new report or contact the barangay directly.',
            },
            {
                question: 'Can I edit or delete a report after submitting?',
                answer: 'No. To maintain report integrity, edits and deletions are not allowed. Make sure to review your report before submitting.',
            },
        ],
    },
];

</script>

<template>
    <Head title="FAQ" />

    <GuestLayout>
        <main class="dark:text-[#FAF9F6]">
            <section class="hero-section h-screen text-[#000] md:px-10 lg:px-32 flex ">
                <div class="w-full h-full md:w-1/2 md:flex justify-center items-center">
                    <div class="">
                        <h1 class="text-6xl font-bold font-[Poppins] mb-5 text-blue-700">Have Questions? We’ve Got Answers!</h1>
                        <p class="text-justify text-lg dark:text-[#faf9f6]">CivicWatch is built to be simple, but we understand that you might have questions about how it all works. Whether you're unsure about how to report a local issue, track your submission, or understand the process behind the scenes, this page is here to help. Browse through the frequently asked questions to get clear answers and make the most out of your experience as an active community participant.</p>
                    </div>
                </div>
                <div class="hidden md:w-1/2 h-full md:flex justify-center items-center">
                    <div class="hidden md:flex justify-end">
                        <!-- <img :src="'/Images/Shrug-bro.svg'" alt="Community" class="w-[30rem]"> -->
                        <img :src="'/Images/questions.svg'" alt="Questions" class="w-[30rem]">
                    </div>
                </div>
            </section>

            <section class="text-[#000] md:px-10 lg:px-32 flex flex-col gap-10">
                <div class="">
                    <h2 class="text-2xl lg:text-4xl font-bold font-[Poppins] dark:text-white ">Frequently Ask Question</h2>
                    <p class="text-sm md:text-base text-gray-500 dark:text-[#FAF9F6]">See how <span class="font-bold">CivicWatch</span> improves community engagement, transparency, and response time.</p>
                </div>
                <div v-for="(type, typeIndex) in types" :key="typeIndex" class="flex flex-col gap-2">
                    <div class="flex gap-2">
                        <div class="bg-blue-500 rounded-full h-10 w-10 flex justify-center items-center">
                            <font-awesome-icon :icon="type.icon" class="text-[#FAF9F6] p-3 text-lg " />
                        </div>
                        <h3 class="text-lg font-bold flex justify-center items-center font-[Poppins] dark:text-[#FAF9F6]">{{ type.headline }}</h3>
                    </div>
                    
                    <div class="flex flex-col gap-4">
                        <Disclosure v-for="(item, index) in type.items" :key="index" as="div" class="">
                            <template v-slot="{ open }">
                                <DisclosureButton
                                    class="w-full text-left bg-white p-4 rounded-md flex justify-between items-center border-l-4 border-[#2b6cb0] shadow-md hover:bg-blue-100 hover:translate-x-2 transition-all duration-300 dark:bg-[#2c2c2c] ">
                                    <span class="text-black dark:text-white">{{ item.question }}</span>
                                    <span class="text-black dark:text-white">{{ open ? '−' : '+' }}</span>
                                </DisclosureButton>
                                <transition
                                    enter-active-class="transition duration-300 ease-in-out"
                                    enter-from-class="transform scale-95 opacity-0"
                                    enter-to-class="transform scale-100 opacity-100"
                                    leave-active-class="transition duration-300 ease-in-out"
                                    leave-from-class="transform scale-100 opacity-100"
                                    leave-to-class="transform scale-95 opacity-0"
                                >
                                    <DisclosurePanel class="p-4  rounded-md ">
                                        <p class="text-gray-700 dark:text-white"> {{ item.answer }} </p>
                                        
                                    </DisclosurePanel>
                                </transition>
                            </template>
                        </Disclosure>
                    </div>
                </div>
            </section>
        </main>
    </GuestLayout>
</template>