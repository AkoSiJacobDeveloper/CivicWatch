<script setup>
import { ref, watch, onMounted } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import ReminderModal from '@/Components/ReminderModal.vue'

const form = useForm({
    email: '',
    password: '',
    remember: false
})

const countdown = ref(null)
const emailFocused = ref(false)
const passwordFocused = ref(false)
const isLoading = ref(false)
let timer = null

function setCountdown(seconds) {
    const endTime = Date.now() + seconds * 1000
    localStorage.setItem('rateLimitEndTime', endTime)
}

function startTimer(duration) {
    countdown.value = duration
    clearInterval(timer)

    timer = setInterval(() => {
        if (countdown.value > 0) {
            countdown.value--
        } else {
            clearInterval(timer)
            countdown.value = null
            localStorage.removeItem('rateLimitEndTime')
            form.clearErrors('email')
        }
    }, 1000)
}

watch(() => form.errors.email, (newVal) => {
    if (newVal && newVal.includes('Too many login attempts')) {
        const match = newVal.match(/(\d+)\s*seconds?/)
        if (match) {
            const seconds = parseInt(match[1])
            setCountdown(seconds)
            startTimer(seconds)
        }
    }
})

onMounted(() => {
    const endTime = localStorage.getItem('rateLimitEndTime')
    if (endTime) {
        const now = Date.now()
        const remaining = Math.floor((endTime - now) / 1000)
        if (remaining > 0) {
            startTimer(remaining)
        } else {
            localStorage.removeItem('rateLimitEndTime')
        }
    }
})

function submit() {
    isLoading.value = true
    form.post('/admin/login', {
        onFinish: () => {
            form.reset('password')
            isLoading.value = false
        },
        onSuccess: () => {
            router.visit('/admin/dashboard')
        },
    });
}
</script>

<template>
    <main class="flex items-center justify-center parent-container">
        <div class="flex w-[70%] gap-1">
            <!-- Left Section - Logo -->
            <section class="hidden md:flex w-3/5  h-auto items-center justify-center relative border bg-white p-5 rounded">
                <!-- Back Button -->
                <Link href="/" class="absolute top-8 left-8 group">
                    <img 
                        :src="'/Images/SVG/arrow-circle-left-fill (700).svg'" 
                        alt="Back Icon"
                        class="h-10 w-10 transition-transform duration-300 group-hover:scale-110"
                    >
                    
                </Link>
                
                <div class="transform transition-transform duration-500 hover:scale-105">
                    <img 
                        :src="'/Images/Cabulijan/erasebg-transformed.webp'" 
                        alt="Barangay Seal" 
                        class="w-full h-full max-w-xs object-contain opacity-90 relative"
                    >
                    <div class="absolute top-96 w-full flex gap-2 items-center justify-center">
                        <img :src="'/Images/SVG/map-pin-fill.svg'" alt="Location" class="h-4 w-4">
                        <span class="text-xs text-gray-500">Cabulijan, Tubigon, Bohol</span>
                    </div>
                </div>
            </section>
        
            <!-- Right Section - Login Form -->
            <section class="w-2/5 flex items-center justify-center p-8 border bg-white rounded">
                <div class="w-full max-w-sm space-y-8">
                    <!-- Header -->
                    <div class="space-y-2">
                        <h1 class="text-2xl font-semibold text-gray-900 font-[Poppins]">
                            Welcome Back
                        </h1>
                        <p class="text-sm text-gray-500">
                            <p class="text-xs text-gray-500">Your role matters. Let’s get things done for the community.</p>
                        </p>
                    </div>

                    <!-- Error Messages -->
                    <div v-if="form.errors.credentials" class="p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600">{{ form.errors.credentials }}</p>
                    </div>

                    <div v-if="countdown !== null" class="p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600">
                            Too many attempts. Try again in {{ countdown }}s
                        </p>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Email Field -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Email
                            </label>
                            <div class="relative">
                                <div class="absolute left-3 top-1/2 -translate-y-1/2">
                                    <img 
                                        :src="'/Images/SVG/envelope-simple.svg'" 
                                        alt="Email" 
                                        class="h-5 w-5 opacity-40"
                                    >
                                </div>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    placeholder="admin@example.com"
                                    @focus="emailFocused = true"
                                    @blur="emailFocused = false"
                                    :class="[
                                        'w-full pl-11 pr-4 py-3 bg-white border rounded-lg transition-all duration-200 focus:outline-none',
                                        emailFocused 
                                            ? 'border-gray-900 ring-1 ring-gray-900' 
                                            : 'border-gray-300 hover:border-gray-400'
                                    ]"
                                >
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Password
                            </label>
                            <div class="relative">
                                <div class="absolute left-3 top-1/2 -translate-y-1/2">
                                    <img 
                                        :src="'/Images/SVG/lock.svg'" 
                                        alt="Password" 
                                        class="h-5 w-5 opacity-40"
                                    >
                                </div>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    placeholder="••••••••"
                                    @focus="passwordFocused = true"
                                    @blur="passwordFocused = false"
                                    :class="[
                                        'w-full pl-11 pr-4 py-3 bg-white border rounded-lg transition-all duration-200 focus:outline-none',
                                        passwordFocused 
                                            ? 'border-gray-900 ring-1 ring-gray-900' 
                                            : 'border-gray-300 hover:border-gray-400'
                                    ]"
                                >
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            :disabled="countdown !== null || isLoading"
                            :class="[
                                'w-full py-3 rounded-lg font-medium text-white transition-all duration-200',
                                countdown === null && !isLoading
                                    ? 'bg-blue-500 hover:bg-blue-700 active:scale-[0.99]'
                                    : 'bg-blue-300 cursor-not-allowed'
                            ]"
                        >
                            <span v-if="isLoading" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Signing in...
                            </span>
                            <span v-else-if="countdown === null" class="flex items-center justify-center gap-2">
                                <img :src="'/Images/SVG/sign-in.svg'" alt="Signin" class="h-5 w-5">
                                Sign In
                            </span>
                            <span v-else>Locked ({{ countdown }}s)</span>
                        </button>
                    </form>

                    <!-- Back to Home Link -->
                    <div class="text-center md:hidden">
                        <Link 
                            class="text-sm text-gray-600 hover:text-gray-900 transition-colors inline-flex items-center gap-1.5"
                            href="/"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to Home
                        </Link>
                    </div>
                </div>
            </section>
        </div>

        <ReminderModal />
    </main>
</template>

<style scoped>
.parent-container {
    background-image: url('/Images/vector-line.jpg');
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover; /* ✅ this makes it fill the screen properly */
    height: 100vh;
    width: 100vw;

}
/* Smooth transitions */
* {
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Remove default focus rings and add custom ones */
input:focus {
    outline: none;
}
</style>