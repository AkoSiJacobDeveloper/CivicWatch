<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

import GuestLayout from '@/Layouts/GuestLayout.vue';
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
<!--pt-[80px] md:pt-[120px]  -->
    <GuestLayout>
        <main class=" dark:text-[#FAF9F6] ">
            <section class="hero-section h-screen text-[#000] md:px-10 lg:px-32 flex ">
                <div class="w-full h-full md:w-1/2 md:flex justify-center items-center">
                    <div class="">
                        <h1 class="text-6xl font-bold font-[Poppins] mb-5 text-blue-700">About Us</h1>
                        <p class="text-justify dark:text-white">CivicWatch is a community-driven reporting platform designed to help residents easily report local issues such as uncollected garbage, broken streetlights, potholes, and more. It empowers citizens to actively participate in improving their surroundings by providing a simple and accessible way to communicate problems to barangay officials. With the use of modern tools like mobile-friendly forms, image uploads, and geolocation, CivicWatch bridges the gap between the community and local authorities.<br>
                        <br>
                        The system is not just a reporting tool it is a digital initiative aimed at promoting transparency, faster response times, and improved accountability in local governance. Whether it's a small nuisance or a major concern, CivicWatch ensures that every report counts and is tracked properly. It encourages collaboration and responsiveness, building a stronger, cleaner, and safer community for everyone.</p>
                    </div>
                </div>
                <div class="hidden md:w-1/2 h-full md:flex justify-center items-center">
                    <div class="hidden md:flex justify-end">
                        <!-- <img :src="'/Images/Team spirit-pana.svg'" alt="Community" class="w-[30rem]"> -->
                        <img :src="'/Images/team spirit.svg'" alt="Community" class="w-[30rem]">
                    </div>
                </div>
            </section>

            <section class="md:px-10 lg:px-32 py-20">
                <div class="mb-10 px-3 flex gap-2 py-5 rounded ">
                    <div class="w-1/2 flex justify-center items-center ">
                        <img :src="'/Images/CivicWatch(2).png'" alt="Logo">
                    </div>

                    <div class="w-1/2 h-auto">
                        <div class="flex flex-col gap-5">
                            <div class="flex flex-col border-l-4 border-[#3B82F6] p-5 bg-white shadow-lg rounded-lg dark:bg-[#2c2c2c]">
                                <div class="">
                                    <font-awesome-icon icon="compass" class="text-2xl text-[#FAF9F6] bg-blue-500 p-2 rounded-full "/>
                                    <h3 class="font-bold text-2xl font-[Poppins] mb-2">Our Mission</h3>
                                </div>
                                <p class="text-gray-700 text-justify dark:text-gray-500">CivicWatch is committed to empowering local communities by providing a reliable, accessible, and user-friendly platform that allows residents to report everyday issues affecting their surroundings. By bridging the gap between the public and barangay officials, CivicWatch aims to foster a culture of transparency, accountability, and collaboration. The platform ensures that every concern, no matter how small, is heard and acted upon efficiently contributing to the overall well-being and order of the community.</p>
                            </div>
                            <div class="flex flex-col border-l-4 border-[#3B82F6] p-5 bg-white shadow-lg rounded-lg dark:bg-[#2c2c2c]">
                                <div class="">
                                    <font-awesome-icon icon="eye" class="text-2xl text-[#FAF9F6] bg-blue-500 p-2 rounded-full" />
                                    <h3 class="font-bold text-2xl font-[Poppins] mb-2">Our Vision</h3>
                                </div>
                                <p class="text-gray-700 text-justify dark:text-gray-500">
                                    CivicWatch envisions a proactive and digitally connected community where every resident becomes a key player in maintaining peace, cleanliness, safety, and progress. Through the use of innovative technology, the platform aims to transform how citizens and barangay officials communicate and collaborate making local governance more efficient, transparent, and inclusive. It aspires to be the standard solution in every barangay for handling reports swiftly and responsibly, contributing to a cleaner, safer, and more organized community for all.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="md:px-10 lg:px-32 py-20 flex flex-col gap-10">
                <div class="">
                    <h2 class="text-2xl lg:text-4xl font-bold font-[Poppins] dark:text-[#FAF9F6] ">Supported Issues</h2>
                    <p class="text-sm md:text-base text-gray-500 dark:text-gray-300">Below are the categories or problems<span class="font-bold"> CivicWatch</span> is designed to receive and process.</p>
                </div>

                <div class="grid grid-cols-4 gap-4">
                    <div v-for="(container, index) in containers" :key="index" class="p-5 h-[250px] hover:bg-blue-600 group hover:text-white transition-all duration-300 hover:-translate-y-1 rounded-[20px] bg-white shadow-[5px_5px_16px_#bdbdbd,-5px_-5px_16px_#ffffff] dark:bg-[#2c2c2c] dark:shadow-none dark:hover:bg-blue-600">
                        <div class="bg-[#3B82F6] h-12 w-12 flex justify-center items-center rounded-full mb-3">
                            <font-awesome-icon :icon="container.icon" class="text-[#FAF9F6]"/>
                        </div>
                        <h3 class="font-bold font-[Poppins] mb-1">{{ container.heading }}</h3>
                        <ul class="">
                            <li 
                                v-for="(issue, i) in container.issues" 
                                :key="index"
                                
                            >
                                <p class="text-base text-gray-500 group-hover:text-white transition-all duration-300">- {{ issue.text }}</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="md:px-10 lg:px-32 py-20 flex flex-col gap-10 team-container rounded-t-[10%]">
                <div class="text-center">
                    <h2 class="text-2xl lg:text-4xl text-[#FAF9F6] font-bold font-[Poppins] dark:text-[#FAF9F6]">Meet The Team</h2>
                    <p class="text-sm md:text-base  dark:text-gray-300 text-[#FAF9F6]">The passionate minds behind<span class="text-blue-700 font-bold"> CivicWatch</span>.</p>
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div v-for="(team, i) in teams" :key="i" class=" h-[300px] flex glass-card hover:-translate-y-1">
                        <div class="w-1/2 flex justify-center items-center">
                            <div class="h-[80%] w-[80%]">
                                <img :src="team.img" alt="CivicWatch Team Images" class="object-cover h-full w-full rounded-full">
                            </div>
                        </div>
                        <div class="w-1/2 text-[#FAF9F6] p-5 flex flex-col justify-center">
                            <div class=" w-full h-1/2 flex flex-col justify-center ">
                                <h3 class="font-bold text-lg">{{ team.name }}</h3>
                                <p class="text-sm">{{ team.role }}</p>
                            </div>
                            <hr class="border-gray-400">
                            <div class="w-full h-1/2 flex flex-col justify-center">
                                <p class="text-sm mb-5">{{ team.course }}</p>
                                <div class="">
                                    <ul class="flex gap-2">
                                        <li v-for="(social, i) in team.socials" :key="i">
                                            <Link :href="social.link">
                                                <font-awesome-icon :icon="['fab', social.icon]" class="text-xl" />
                                            </Link>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="md:px-10 lg:px-32 py-20">
                <div class="flex h-96 justify-center items-center border p-5 bg-white rounded-lg dark:bg-[#1e1e1e] dark:border-none">
                    <div class="w-full flex flex-col gap-5">
                        <div class="flex justify-start">
                            <img :src="'/Images/quotation.svg'" alt="Quotation Icon" class="h-24">
                        </div>
                        <div class="flex justify-center items-center">
                            <p class="text-5xl font-[Helvetica] font-bold">A strong community starts with a single voice willing to speak up.</p>
                        </div>
                        <div class="flex justify-end pr-5">
                            <p class="text-blue-500 font-bold">- CivicWatch Team</p>
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



/* .glass-card {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(11px);
    -webkit-backdrop-filter: blur(11px);
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 
        0 8px 32px rgba(0, 0, 0, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.5),
        inset 0 -1px 0 rgba(255, 255, 255, 0.1),
        inset 0 0 0px 0px rgba(255, 255, 255, 0);
    position: relative;
    overflow: hidden;
}

.glass-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.8),
        transparent
    );
}

.glass-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 1px;
    height: 100%;
    background: linear-gradient(
        180deg,
        rgba(255, 255, 255, 0.8),
        transparent,
        rgba(255, 255, 255, 0.3)
    );
} */
</style>
<!-- 
<style scoped>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style> -->

<!-- <div class="flex flex-col justify-between">
                    <div class="flex justify-start items-start">
                        <img :src="'/Images/quote.svg'" alt="Quote svg" class="h-16">
                    </div>
                    <div class="flex w-full justify-between">
                        <div class="flex flex-col justify-start w-1/2">
                            <h1 class="font-[Poppins] text-2xl font-bold text-blue-600">
                            Building Better Communities Through Action
                            </h1>
                            <p class="italic text-xs">CivicWatch empowers residents to speak up, report issues, and create real change in their baranggay.</p> 
                        </div>
                        <AboutCTA class="w-1/2 flex justify-end"/>
                    </div>
                </div>
                <div class="flex items-center gap-3 relative my-5">
                    <div class="absolute left-3 flex justify-center items-center h-full z-[8000]">
                        <button class="bg-white/5 p-4 rounded-full backdrop-blur" @click="scrollLeft">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black" class="size-6">
                                <path fill-rule="evenodd" d="M7.72 12.53a.75.75 0 0 1 0-1.06l7.5-7.5a.75.75 0 1 1 1.06 1.06L9.31 12l6.97 6.97a.75.75 0 1 1-1.06 1.06l-7.5-7.5Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    
                    <div ref="scrollContainer"
                        class="flex gap-4 overflow-x-auto overflow-y-hidden scrollbar-hide"
                    >
                        <div 
                            v-for="(image, index) in images" 
                            :key="index" 
                            class="w-[500px] flex-shrink-0 rounded"
                        >
                            <img 
                                :src="image.img" 
                                alt="Image"
                                class="w-full h-full rounded"
                            >
                        </div>
                    </div>
                    
                    <div class="absolute right-3  flex justify-center items-center h-full z-[8000]">
                        <button class="bg-white/5 rounded-full p-4  backdrop-blur" @click="scrollRight">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black" class="size-6">
                                <path fill-rule="evenodd" d="M16.28 11.47a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 0 1-1.06-1.06L14.69 12 7.72 5.03a.75.75 0 0 1 1.06-1.06l7.5 7.5Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div> -->