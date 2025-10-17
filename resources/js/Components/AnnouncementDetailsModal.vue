<script setup>
import { Link } from '@inertiajs/vue3'
import { onMounted, onUnmounted } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        required: true
    },
    announcement: {
        type: Object,
        default: null
    },
    closeOnBackdrop: {
        type: Boolean,
        default: true
    }
})

const emit = defineEmits(['close'])

const close = () => {
    emit('close')
}

const handleBackdropClick = () => {
    if (props.closeOnBackdrop) {
        close()
    }
}

const handleEscape = (event) => {
    if (event.key === 'Escape') {
        close()
    }
}

// Simple file URL helper
const getFileUrl = (filePath) => {
    return `/storage/${filePath}`
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleString('en-US', {
        month: 'long',
        day: 'numeric', 
        year: 'numeric', 
        hour: 'numeric',
        minute: '2-digit',
    })
}

const capitalizeFirstLetter = (str) => {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

onMounted(() => {
    document.addEventListener('keydown', handleEscape)
    document.body.style.overflow = 'hidden'
})

onUnmounted(() => {
    document.removeEventListener('keydown', handleEscape)
    document.body.style.overflow = ''
})
</script>

<template>
    <div 
        v-if="show" 
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
        @click.self="handleBackdropClick"
    >
        <div 
            class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto"
            role="dialog"
            aria-labelledby="modal-title"
            aria-modal="true"
        >
            <!-- Modal Header -->
            <div class="flex justify-between items-start bg-blue-700 p-6">
                <div>
                    <h2 id="modal-title" class="text-3xl font-bold text-white mb-3">{{ announcement?.title }}</h2>
                    <div class="flex gap-5">
                        <div class="flex gap-1">
                            <div class="flex justify-center items-center">
                                <img :src="'/Images/SVG/calendar (white).svg'" alt="Icon" class="h-4 w-4 flex">
                            </div>
                            <span class="text-white text-xs">{{ formatDate(announcement?.event_date) }}</span>
                        </div>
                        <div>
                            <div class="flex gap-1">
                                <div class="flex justify-center items-center">
                                    <img :src="'/Images/SVG/user (white).svg'" alt="Icon" class="h-4 w-4 flex">
                                </div>
                                <span class="text-white text-xs">System Administrator</span>
                            </div>
                        </div>
                        <div>
                            <div class="flex gap-1">
                                <div class="flex justify-center items-center">
                                    <img :src="'/Images/SVG/map-pin-area.svg'" alt="Icon" class="h-4 w-4 flex">
                                </div>
                                <span class="text-white text-xs">{{ announcement.venue || 'Not Specified' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <button 
                    @click="close"
                    class="text-gray-400 hover:text-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 rounded"
                    aria-label="Close modal"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="h-96 overflow-y-auto">
                <!-- Modal Content -->
                <div class="px-6 ">
                    <!-- Announcement -->
                    <div class="py-5">
                        <p class="text-gray-600 whitespace-pre-line">{{ announcement?.content }}</p>
                    </div>

                    <!-- Priority Level -->
                    <div class="border px-3 py-2 rounded-md mb-3">
                        <span class="font-medium text-gray-500 font-[Poppins] mb-1 text-xs">PRIORITY</span>
                        <p 
                            class="text-lg font-semibold"
                            :class="announcement?.level === 'high'
                                ? 'text-red-700' : announcement?.level === 'medium'
                                ? 'text-amber-500' : 'text-green-700'"
                        >
                            {{ capitalizeFirstLetter(announcement?.level) }}
                        </p>
                    </div>

                    <!-- Category -->
                    <div class="border px-3 py-2 rounded-md mb-3">
                        <span class="font-medium text-gray-500 font-[Poppins] mb-1 text-xs">CATEGORY</span>
                        <p class="text-lg font-semibold text-blue-500">{{ announcement?.category.name }}</p>
                    </div>

                    <!-- Contact Person -->
                    <div class="border px-3 py-2 rounded-md mb-3">
                        <span class="font-medium text-gray-500 font-[Poppins] mb-1 text-xs">CONTACT PERSON</span>
                        <p class="text-lg font-semibold text-blue-500">{{ announcement?.contact_person }}</p>
                    </div>

                    <!-- Contact Number -->
                    <div class="border px-3 py-2 rounded-md mb-3">
                        <span class="font-medium text-gray-500 font-[Poppins] mb-1 text-xs">CONTACT NUMBER</span>
                        <p class="text-lg font-semibold text-blue-500">{{ announcement?.contact_number }}</p>
                    </div>

                    <!-- LOCATIONS -->
                    <div class="border px-3 py-2 rounded-md mb-3">
                        <span class="font-medium text-gray-500 font-[Poppins] mb-1 text-xs">LOCATION</span>
                        <p class="text-lg font-semibold text-blue-500">
                            {{ announcement?.purok === 'all' ? 'All Purok/Sitio' : announcement?.specific_area }}
                        </p>
                    </div>

                    <!-- Audiences -->
                    <div class="border px-3 py-2 rounded-md mb-3">
                        <span class="font-medium text-gray-500 font-[Poppins] mb-1 text-xs">TARGET AUDIENCES</span>
                        <div v-if="announcement?.audiences?.length" class="">
                            <div class="space-y-2">
                                <div 
                                    v-for="audience in announcement.audiences" 
                                    :key="audience.id"
                                    class="flex items-center gap-3 hover:bg-gray-50 transition-colors"
                                >
                                
                                    <div class="flex-1 border border-blue-200 bg-blue-50 px-3 py-3 rounded-md">
                                        <span class="text-blue-500 font-semibold">{{ audience.name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DEPARTMENTs -->
                    <div class="border px-3 py-2 rounded-md mb-3">
                        <span class="font-medium text-gray-500 font-[Poppins] mb-1 text-xs">RESPONSIBLE DEPARTMENTS</span>
                        <div v-if="announcement?.departments?.length" class="">
                            <div class="space-y-2">
                                <div 
                                    v-for="department in announcement.departments" 
                                    :key="department.id"
                                    class="flex items-center gap-3 hover:bg-gray-50 transition-colors"
                                >
                                
                                    <div class="flex-1 border border-blue-200 bg-blue-50 px-3 py-3 rounded-md">
                                        <span class="text-blue-500 font-semibold">{{ department.name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="!announcement?.departments?.length" class="text-gray-500">
                            <span class="font-medium">No departments specified for this announcement</span>
                        </div>
                    </div>

                    <!-- DOCUMENTS -->
                    <div class="border px-3 py-2 rounded-md mb-3">
                        <span class="font-medium text-gray-500 font-[Poppins] mb-1 text-xs">REQUIRED DOCUMENTS</span>
                        <div v-if="announcement?.documents?.length" class="">
                            <div class="space-y-2">
                                <div 
                                    v-for="document in announcement.documents" 
                                    :key="document.id"
                                    class="flex items-center gap-3 hover:bg-gray-50 transition-colors"
                                >
                                
                                    <div class="flex-1 border border-blue-200 bg-blue-50 px-3 py-1 rounded-md">
                                        <span class="text-blue-500 font-semibold">{{ document.name }}</span>
                                        <span class="text-xs text-gray-500 block capitalize">{{ document.document_type?.toLowerCase() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Custom "Other" Document -->
                        <div v-if="announcement?.other_document" class="mt-3">
                            <p class="font-medium text-gray-500 font-[Poppins] mb-1 text-xs">ADDITIONAL REQUIREMENTS</p>
                            <div class="p-3 border rounded-lg bg-blue-50 border-blue-200">
                                <span class="text-blue-500 font-medium">{{ announcement.other_document }}</span>
                            </div>
                        </div>

                        <!-- No Documents Message -->
                        <div v-if="!announcement?.documents?.length && !announcement?.other_document" class="text-gray-500">
                            <span class="font-medium">No documents required for this announcement</span>
                        </div>
                    </div>
                </div>

                <!-- Attachments - SIMPLIFIED VERSION -->
                <div class="px-6">
                    <div v-if="announcement?.attachments?.length" class=" border rounded-md px-3 py-2">
                        <p class="font-medium text-gray-500 font-[Poppins] mb-1 text-xs">ATTACHMENTS</p>
                        <div 
                            v-for="file in announcement.attachments" 
                            :key="file.id"
                            class="flex items-center gap-3 bg-blue-50 px-3 py-1 rounded-md border border-blue-200 hover:bg-gray-50 transition-colors mb-2"
                        >
                            <div class="flex-1 min-w-0">
                                <a 
                                    :href="getFileUrl(file.file_path)"
                                    target="_blank"
                                    class="text-blue-500 hover:text-blue-700 hover:underline truncate block"
                                    @click.stop
                                >
                                    {{ file.file_name || file.file_path.split('/').pop() }}
                                </a>
                            </div>
                            <a 
                                :href="getFileUrl(file.file_path)"
                                :download="file.file_name || file.file_path.split('/').pop()"
                                class="p-2 text-gray-500 hover:text-blue-600 transition-colors"
                                @click.stop
                            >
                                <img :src="'/Images/SVG/file-arrow-down-fill.svg'" alt="Download" class="h-8 w-8">
                            </a>
                        </div>
                    </div>
                    <div v-else class="border rounded-md px-3 py-2 text-gray-500">
                        <p class="font-medium text-gray-500 font-[Poppins] mb-1 text-xs">ATTACHMENTS</p>
                        <span class="font-medium">No attachments available</span>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200 p-6">
                <button 
                    @click="close"
                    class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500"
                >
                    Close
                </button>
                <Link
                    :href="`/admin/announcements/${announcement?.id}/edit`"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    Edit Announcement
                </Link>
            </div>
        </div>
    </div>
</template>