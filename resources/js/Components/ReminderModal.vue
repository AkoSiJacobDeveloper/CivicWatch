<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const showModal = ref(true)
const dontShowAgain = ref(false)

onMounted(() => {
    const hasOptedOut = localStorage.getItem('hideAdminModal')
    if (hasOptedOut === 'true') {
        showModal.value = false
    }
})

function closeModal() {
    if(dontShowAgain.value) {
        localStorage.setItem('hideAdminModal', 'true')
    }
    showModal.value = false
}
</script>

<template>
    <div v-if="showModal" class="fixed z-[9999] inset-0 backdrop-blur-sm bg-white/30 flex justify-center items-center">
        <div class="relative w-[90%] max-w-md bg-blue-500 p-10 rounded shadow-lg">
            <div class="absolute -top-12 left-1/2 -translate-x-1/2 bg-[#FAF9F6] text-center rounded-full w-24 h-24 p-5 shadow-lg z-10">
                <font-awesome-icon icon="lock" class="text-blue-500 text-5xl" />
            </div>

            <!-- Card Content -->
            <div class="flex flex-col gap-10 mt-10">
                <div class="text-center text-[#FAF9F6]">
                    <h1 class="font-bold text-3xl mb-5">Admin Access Only</h1>
                    <p class="text-sm">Only authorized personnel should proceed. Please log in with your assigned credentials.</p>
                </div>

                <div class="flex justify-between">
                    <form @submit.prevent class="flex justify-start items-center gap-2 text-sm text-[#FAF9F6] w-1/2">
                        <input v-model="dontShowAgain" type="checkbox" id="dont-show" class="accent-blue-600 w-4 h-4" />
                        <label for="dont-show" class="text-sm cursor-pointer select-none hover:text-blue-900 transition duration-300">Don't Show Again</label>
                    </form>

                    <div class="flex gap-1 text-[#FAF9F6] w-1/2">
                        <Link href="/" class="w-[50%] bg-gray-500 p-3 rounded hover:bg-gray-600 transition duration-300 text-center text-sm">Home</Link>
                        <button @click="closeModal" class="w-[50%] bg-blue-700 p-3 rounded hover:bg-blue-800 transition duration-300 text-sm">Okay</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>