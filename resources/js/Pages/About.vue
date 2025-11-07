<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

import GuestLayout from '@/Layouts/GuestLayout.vue';
import ApplicationImage from '@/Components/ApplicationImage.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import AboutCTA from '@/Components/AboutCTA.vue';
import { text } from '@fortawesome/fontawesome-svg-core';

// const scrollContainer = ref(null);

// Left and Right scroll functionality
// const scrollLeft = () => {  
//     scrollContainer.value.scrollBy({
//         left: -400,
//         behavior: 'smooth'
//     })
// }

// const scrollRight = () => {
//     scrollContainer.value.scrollBy({
//         left: 400,
//         behavior: 'smooth'
//     })  
// }

const containers = [
    { 
        icon: 'road', 
        heading: 'Road & Sidewalk Issues',
        issues: [
            { text: 'Potholes' },
            { text: 'Cracked or uneven pavement' },
            { text: 'Blocked or obstructed sidewalks' },
            { text: 'Damaged speed bumps or humps'}
        ]
    },
    {
        icon: 'lightbulb',
        heading: 'Streetlight & Electrical Issues',
        issues: [
            { text: 'Broken or non-functioning streetlights' },
            { text: 'Flickering or dim lights' },
            { text: 'Leaning/damaged electric poles' },
            { text: 'Exposed electric wires' }
        ]
    },
    {
        icon: 'trash',
        heading: 'Waste Management',
        issues: [
            { text: 'Uncollected garbage' },
            { text: 'Illegal dumping' },
            { text: 'Overflowing trash bins' },
            { text: 'Animal carcasses not removed' },
        ]
    }, 
    {
        icon: 'droplet',
        heading: 'Drainage & Water',
        issues: [
            { text: 'Clogged canals or drainages' },
            { text: 'Stagnant water (dengue risk)' },
            { text: 'Broken drainage covers' }
        ]
    },
    {
        icon: 'chair',
        heading: 'Public Infrastructure',
        issues: [
            { text: 'Damaged benches/waiting sheds' },
            { text: 'Vandalized walls or facilities' },
            { text: 'Broken barangay signage' }
        ]
    }, 
    {
        icon: 'tree',
        heading: 'Environment and Public Spaces',
        issues: [
            { text: 'Overgrown weeds or vegetation' },
            { text: 'Fallen trees blocking paths' },
            { text: 'Untended plazas or mini-parks' }
        ]
    },
    {
        icon: 'dog',
        heading: 'Animal Related',
        issues: [
            { text: 'Stray animals (non-aggressive)' },
            { text: 'Animal waste in public places' }
        ]
    }, 
    {
        icon: 'person-digging',
        heading: 'Construction-Related',
        issues: [
            { text: 'Road obstructions due to informal construction' },
            { text: 'Unsecured construction materials' }
        ]
    }, 
    {
        icon: 'volume-high',
        heading: 'Noise & Disturbance',
        issues: [
            { text: 'Loud sounds from establishments at restricted hours' },
            { text: 'Public karaoke complaints (non-urgent)' }
        ]
    }
]

const teams = [
    { 
        img: '/Images/Team/dan.jpg', 
        name: 'Dan Christian Aguillon', 
        role: '4th Year - Web Developer', 
        course: 'Bachelor of Science in Information Technology',
        socials: [
            { link: '#', icon: 'facebook' },
            { link: '#', icon: 'instagram' },
            { link: '#', icon: 'twitter' },
        ]
    },
    { 
        img: '/Images/Team/jacob.jpg', 
        name: 'Paul Jacob Tocmo', 
        role: '4th Year - Web Developer', 
        course: 'Bachelor of Science in Information Technology',
        socials: [
            { link: '#', icon: 'facebook' },
            { link: '#', icon: 'instagram' },
            { link: '#', icon: 'twitter' },
        ]
    },
    { 
        img: '/Images/Team/jarred.jpg', 
        name: 'Jarred Ruba', 
        role: '4th Year - Web Developer', 
        course: 'Bachelor of Science in Information Technology',
        socials: [
            { link: '#', icon: 'facebook' },
            { link: '#', icon: 'instagram' },
            { link: '#', icon: 'twitter' },
        ]
    },
    { 
        img: '/Images/Team/makoy.jpg', 
        name: 'Mark Lester Jumao-as', 
        role: '4th Year - Web Developer', 
        course: 'Bachelor of Science in Information Technology',
        socials: [
            { link: '#', icon: 'facebook' },
            { link: '#', icon: 'instagram' },
            { link: '#', icon: 'twitter' },
        ]
    },
]
</script>

<template>
    <Head title="About"/>
    <GuestLayout>
        <main class="dark:text-[#FAF9F6]">
            <section class="pt-32 lg:pt-0 hero-section min-h-screen text-[#000] px-4 md:px-10 lg:px-32 flex flex-col lg:flex-row items-center">
                <div class="w-full lg:w-1/2 flex justify-center items-center py-10 lg:py-0">
                    <div class="text-left">
                        <h1 class="text-3xl sm:text-4xl lg:text-6xl font-bold font-[Poppins] mb-5 text-blue-700">About Us</h1>
                        <p class="text-justify lg:text-left dark:text-white text-sm sm:text-base">CivicWatch is a community-driven reporting platform designed to help residents easily report local issues such as uncollected garbage, broken streetlights, potholes, and more. It empowers citizens to actively participate in improving their surroundings by providing a simple and accessible way to communicate problems to barangay officials. With the use of modern tools like mobile-friendly forms, image uploads, and geolocation, CivicWatch bridges the gap between the community and local authorities.<br>
                        <br>
                        The system is not just a reporting tool it is a digital initiative aimed at promoting transparency, faster response times, and improved accountability in local governance. Whether it's a small nuisance or a major concern, CivicWatch ensures that every report counts and is tracked properly. It encourages collaboration and responsiveness, building a stronger, cleaner, and safer community for everyone.</p>
                    </div>
                </div>
                <div class="hidden lg:flex w-full lg:w-1/2 justify-center items-center">
                    <div class="flex justify-center lg:justify-end">
                        <img :src="'/Images/team spirit.svg'" alt="Community" class="w-64 sm:w-80 lg:w-[30rem]">
                    </div>
                </div>
            </section>

            <section class="px-4 md:px-10 lg:px-32 py-10 lg:py-20">
                <div class="mb-10 flex flex-col lg:flex-row gap-6 py-5 rounded">
                    <div class="w-full lg:w-1/2 flex justify-center items-center">
                        <div class="flex flex-col sm:flex-row items-center gap-4 text-center sm:text-left">
                            <img :src="'/Images/Cabulijan/Official Cabulijan Logo.png'" alt="Cabulijan Logo" class="h-40 sm:h-60">
                            <div class="flex flex-col">
                                <div class="flex flex-col leading-none">
                                    <div class="border-b-2 border-black">
                                        <p class="text-3xl sm:text-4xl lg:text-5xl font-bold font-[Poppins] text-blue-950">CivicWatch</p>
                                    </div>
                                    <p class="text-xl sm:text-2xl mt-1 font-semibold text-blue-900">REPORT.ACT.RESOLVE</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-full lg:w-1/2 h-auto">
                        <div class="flex flex-col gap-4 sm:gap-5">
                            <div class="flex flex-col border-l-4 border-[#3B82F6] p-4 sm:p-5 bg-white shadow-lg rounded-lg dark:bg-[#2c2c2c]">
                                <div class="">
                                    <font-awesome-icon icon="compass" class="text-xl sm:text-2xl text-[#FAF9F6] bg-blue-500 p-2 rounded-full"/>
                                    <h3 class="font-bold text-xl sm:text-2xl font-[Poppins] mb-2">Our Mission</h3>
                                </div>
                                <p class="text-gray-700 text-justify dark:text-gray-500 text-sm sm:text-base">CivicWatch is committed to empowering local communities by providing a reliable, accessible, and user-friendly platform that allows residents to report everyday issues affecting their surroundings. By bridging the gap between the public and barangay officials, CivicWatch aims to foster a culture of transparency, accountability, and collaboration. The platform ensures that every concern, no matter how small, is heard and acted upon efficiently contributing to the overall well-being and order of the community.</p>
                            </div>
                            <div class="flex flex-col border-l-4 border-[#3B82F6] p-4 sm:p-5 bg-white shadow-lg rounded-lg dark:bg-[#2c2c2c]">
                                <div class="">
                                    <font-awesome-icon icon="eye" class="text-xl sm:text-2xl text-[#FAF9F6] bg-blue-500 p-2 rounded-full" />
                                    <h3 class="font-bold text-xl sm:text-2xl font-[Poppins] mb-2">Our Vision</h3>
                                </div>
                                <p class="text-gray-700 text-justify dark:text-gray-500 text-sm sm:text-base">
                                    CivicWatch envisions a proactive and digitally connected community where every resident becomes a key player in maintaining peace, cleanliness, safety, and progress. Through the use of innovative technology, the platform aims to transform how citizens and barangay officials communicate and collaborate making local governance more efficient, transparent, and inclusive. It aspires to be the standard solution in every barangay for handling reports swiftly and responsibly, contributing to a cleaner, safer, and more organized community for all.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="px-4 md:px-10 lg:px-32 py-10 lg:py-20 flex flex-col gap-6 lg:gap-10">
                <div class="text-left">
                    <h2 class="text-2xl lg:text-4xl font-bold font-[Poppins] dark:text-[#FAF9F6]">Supported Issues</h2>
                    <p class="text-sm md:text-base text-gray-500 dark:text-gray-300">Below are the categories or problems<span class="font-bold"> CivicWatch</span> is designed to receive and process.</p>
                </div>

                <!-- Supported Issues - Horizontal scroll on mobile, grid on larger screens -->
                <div class="lg:hidden">
                    <!-- Mobile: Horizontal Scroll -->
                    <div class="flex overflow-x-auto pb-4 space-x-4 scrollbar-hide -mx-2 px-2">
                        <div 
                            v-for="(container, index) in containers" 
                            :key="index" 
                            class="flex-shrink-0 w-64 p-4 h-[250px] hover:bg-blue-600 group hover:text-white transition-all duration-300 hover:-translate-y-1 rounded-[20px] bg-white shadow-[5px_5px_16px_#bdbdbd,-5px_-5px_16px_#ffffff] dark:bg-[#2c2c2c] dark:shadow-none dark:hover:bg-blue-600"
                        >
                            <div class="bg-[#3B82F6] h-10 w-10 flex justify-center items-center rounded-full mb-3">
                                <font-awesome-icon :icon="container.icon" class="text-[#FAF9F6]"/>
                            </div>
                            <h3 class="font-bold font-[Poppins] mb-1 text-sm">{{ container.heading }}</h3>
                            <ul class="">
                                <li v-for="(issue, i) in container.issues" :key="i">
                                    <p class="text-xs text-gray-500 group-hover:text-white transition-all duration-300">- {{ issue.text }}</p>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="hidden lg:block">
                    <!-- Desktop: Original Grid Layout -->
                    <div class="grid grid-cols-4 gap-4">
                        <div 
                            v-for="(container, index) in containers" 
                            :key="index" 
                            class="p-5 h-[250px] hover:bg-blue-600 group hover:text-white transition-all duration-300 hover:-translate-y-1 rounded-[20px] bg-white shadow-[5px_5px_16px_#bdbdbd,-5px_-5px_16px_#ffffff] dark:bg-[#2c2c2c] dark:shadow-none dark:hover:bg-blue-600"
                        >
                            <div class="bg-[#3B82F6] h-12 w-12 flex justify-center items-center rounded-full mb-3">
                                <font-awesome-icon :icon="container.icon" class="text-[#FAF9F6]"/>
                            </div>
                            <h3 class="font-bold font-[Poppins] mb-1">{{ container.heading }}</h3>
                            <ul class="">
                                <li v-for="(issue, i) in container.issues" :key="i">
                                    <p class="text-sm text-gray-500 group-hover:text-white transition-all duration-300">- {{ issue.text }}</p>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <section class="px-4 md:px-10 lg:px-32 py-10 lg:py-20 flex flex-col gap-6 lg:gap-10 team-container rounded-t-[10%]">
                <div class="text-center">
                    <h2 class="text-2xl lg:text-4xl text-[#FAF9F6] font-bold font-[Poppins]">Meet The Team</h2>
                    <p class="text-sm md:text-base dark:text-gray-300 text-[#FAF9F6]">The passionate minds behind<span class="text-blue-700 font-bold"> CivicWatch</span>.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <div v-for="(team, i) in teams" :key="i" class="h-auto min-h-[300px] lg:h-[300px] flex flex-col lg:flex-row glass-card hover:-translate-y-1">
                        <div class="w-full lg:w-1/2 flex justify-center items-center p-4 lg:p-0">
                            <div class="h-32 w-32 lg:h-[80%] lg:w-[80%]">
                                <img :src="team.img" alt="CivicWatch Team Images" class="object-cover h-full w-full rounded-full">
                            </div>
                        </div>
                        <div class="w-full lg:w-1/2 text-[#FAF9F6] p-4 lg:p-5 flex flex-col justify-center">
                            <div class="w-full h-1/2 flex flex-col justify-center">
                                <h3 class="font-bold text-lg">{{ team.name }}</h3>
                                <p class="text-sm">{{ team.role }}</p>
                            </div>
                            <hr class="border-gray-400 my-2">
                            <div class="w-full h-1/2 flex flex-col justify-center">
                                <p class="text-sm mb-3 lg:mb-5">{{ team.course }}</p>
                                <div class="">
                                    <ul class="flex gap-2">
                                        <li v-for="(social, i) in team.socials" :key="i">
                                            <Link :href="social.link">
                                                <font-awesome-icon :icon="['fab', social.icon]" class="text-lg lg:text-xl" />
                                            </Link>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="px-4 md:px-10 lg:px-32 py-10 lg:py-20">
                <div class="flex min-h-96 justify-center items-center border p-4 sm:p-5 bg-white rounded-lg dark:bg-[#1e1e1e] dark:border-none">
                    <div class="w-full flex flex-col gap-4 sm:gap-5">
                        <div class="flex justify-start">
                            <img :src="'/Images/quotation.svg'" alt="Quotation Icon" class="h-16 sm:h-20 lg:h-24">
                        </div>
                        <div class="flex justify-center items-center">
                            <p class="text-2xl sm:text-3xl lg:text-5xl font-[Helvetica] font-bold text-center">A strong community starts with a single voice willing to speak up.</p>
                        </div>
                        <div class="flex justify-end pr-0 sm:pr-5">
                            <p class="text-blue-500 font-bold text-sm sm:text-base">- CivicWatch Team</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </GuestLayout>
</template>

<style scoped>
.team-container {
    background: url('/Images/wave-haikei (1).svg');
    height: 100%;
    width: 100%;
    background-size: cover;
}

.glass-card {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 16px;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(4.2px);
    -webkit-backdrop-filter: blur(4.2px);
    border: 1px solid rgba(255, 255, 255, 0.21);
}

.glass-card:hover {
    background: rgba(255, 255, 255, 0.33);
    transition: all 0.3s;
    transform: translateY(-0.25rem);
}

/* Hide scrollbar for horizontal scrolling */
.scrollbar-hide {
    -ms-overflow-style: none;  /* Internet Explorer 10+ */
    scrollbar-width: none;  /* Firefox */
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;  /* Safari and Chrome */
}
</style>