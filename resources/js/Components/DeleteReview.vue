<script setup>

defineEmits(['close']);
defineProps({
    show: Boolean,
});
</script>

<template>
    <div v-if="show" class="fixed z-[9999] inset-0 backdrop-blur-sm bg-white/20 flex justify-center items-center">
        <div class="w-[35%] p-10 rounded shadow-lg border bg-white">
            <!-- Close Button --> 
            <div class="flex justify-end">
                <img @click="$emit('close')" :src="'/Images/SVG/x.svg'" alt="Close Icon" class="w-5 h-5 hover:cursor-pointer">
            </div>
            
            <div class="flex flex-col gap-10">
                <div>
                    <!-- Icon -->
                    <div class="flex justify-center mb-5">
                        <div class="w-16 h-16 rounded-full flex items-center justify-center bg-red-100">
                            <img :src="'/Images/SVG/warning.svg'" alt="Warning Icon">
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="text-center">
                        <h5 class="font-bold text-2xl">You are about to delete a review</h5>
                        <p>Are you sure you want to delete this review? This action cannot be undone.</p>
                    </div>
                </div>
                

                <!-- Action Buttons -->
                <div class="flex justify-center gap-3 w-full">
                    <button 
                        @click="$emit('close')" 
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition-all duration-300 flex-1"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="$emit('delete')" 
                        class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition-all duration-300 flex-1"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>>
</template>