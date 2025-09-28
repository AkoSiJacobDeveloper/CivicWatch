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
    form.post('/admin/login', {
        onFinish: () => form.reset('password'),
        onSuccess: () => {
            router.visit('/admin/dashboard')
        },
    });
}
</script>

<template>
    <main class=" flex justify-center items-center h-screen w-screen main-container" style="background-image: url('/Images/stacked.svg');">
        <div class="h-[70%] w-[70%] flex z-[9999] rounded-md shadow-lg">
            <section class="w-3/5 left-section rounded hidden md:block" style="background-image: url('/Images/stacked.svg');" >
                <Link href="/">
                    <font-awesome-icon icon="circle-arrow-left" class="text-[#FAF9F6] p-5 text-2xl" />
                </Link>
                <div class="flex justify-center items-center h-[70%]">
                    <img :src="'/Images/CivicWatch(1).png'" alt="CivicWatch">
                </div>
                <div class="text-[#FAF9F6] text-xs text-center">
                    <p>&copy; {{ new Date().getFullYear() }} CivicWatch. All rights reserved.</p>
                </div>
            </section>
            
            <section class="w-full md:w-2/5 bg-[#FAF9F6] rounded md:p-5 flex justify-center items-center">
                <div class="px-5 flex flex-col gap-8">
                    <div class="">
                        <h1 class="font-semibold font-[Poppins] text-3xl">Welcome Back!</h1>
                        <p class="text-sm text-gray-500">Your role matters. Let’s get things done for the community.</p>
                    </div>

                    <div v-if="form.errors.credentials" class="bg-[#F2DEDE] border-red-400 text-[#A94442] text-center text-xs font-semibold py-4 rounded">{{ form.errors.credentials }}</div>

                    <div v-if="countdown !== null" class="bg-[#F2DEDE] border-red-400 text-[#A94442] text-center text-xs font-semibold py-4 rounded">
                        Too many login attempts. Please try again in {{ countdown }} second<span v-if="countdown !== 1">s</span>.
                    </div>

                    <div>
                        <form @submit.prevent="submit" class="flex flex-col md:gap-1 ">
                            <!-- Email Address -->
                            <label class="block text-sm font-semibold font-[Poppins] mb-1" for="review_message">Email</label>
                            <div class="flex border bg-gray-100 rounded-full mb-4">
                                <div class="p-3 flex justify-center items-center ml-2">
                                    <img 
                                        :src="'/Images/SVG/envelope-simple.svg'" 
                                        alt="Icon" 
                                        class="h-6"
                                    >
                                </div>
                
                                <input 
                                    v-model="form.email"
                                    type="email" 
                                    placeholder="Email"
                                    class="w-full border-0 bg-gray-100 rounded-full focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                >
                            </div>

                            <!-- Passowrd -->
                            <label class="block text-sm font-semibold font-[Poppins] mb-1" for="review_message">Password</label>
                            <div class="flex border bg-gray-100 rounded-full mb-4">
                                <div class="p-3 flex justify-center items-center ml-2">
                                    <img 
                                        :src="'/Images/SVG/lock.svg'" 
                                        alt="Icon" 
                                        class="h-6"
                                    >
                                </div>
                
                                <input 
                                    v-model="form.password"
                                    type="password" 
                                    placeholder="Password"
                                    class="w-full border-0 bg-gray-100 rounded-full focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                >
                            </div>
                    
                            <button
                                type="submit"
                                :disabled="countdown !== null"
                                class="p-3 w-full rounded text-[#FAF9F6] transition-all duration-200"
                                :class="{
                                    'bg-blue-600 hover:bg-blue-700 cursor-pointer': countdown === null,
                                    'bg-gray-400 cursor-not-allowed': countdown !== null
                                }"
                                >
                                    <span v-if="countdown === null">Login</span>
                                    <span v-else>Locked ({{ countdown }}s)</span>
                            </button>
                        </form>
                        <div class="text-center mt-5">
                            <Link class="text-blue-600 text-xs md:hidden" href="/">Back to Home</Link>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <ReminderModal />
    </main>
</template>

<style scoped>
.main-container {
    background-image: url('Images/stacked.svg');
}
.left-section {
    background-image: url('Images/stacked.svg');
    background-position: center;
    background-repeat: no-repeat;

}
</style>