<script setup>
import { Link } from '@inertiajs/vue3';

const actions = [
    { url: '/reports-details', urlName: 'See Details' }
]

// const props = defineProps(['show'])
// const emit = defineEmits(['close'])
</script>

<template>
    <div class="text-center">
        <div class="py-1">
            <Link 
                v-for="(action, index) in actions" 
                :key="index"
                :href="action.url" 
                class="px-4 py-2 border border-[#000] rounded text-sm hover:bg-blue-600 transition-all duration-300 hover:text-[#FAF9F6] hover:border-hidden"
                @click="$emit('close')"
            >
                {{ action.urlName }}
            </Link>
        </div>
        <!-- <button type="submit" class="bg-[#10b980] hover:bg-[#0e7a5c] transition-all duration-300 w-full px-4 py-2 rounded">Approve</button> -->
    </div>
</template>