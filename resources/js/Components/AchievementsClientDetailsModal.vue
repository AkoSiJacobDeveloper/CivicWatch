<script setup>
import { Link } from '@inertiajs/vue3'
import { onMounted, onUnmounted, watch, ref } from 'vue'
import { initTooltips } from 'flowbite';


const props = defineProps({
    show: {
        type: Boolean,
        required: true
    },
    achievements: {
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

// Watch for show prop changes to handle scroll properly
watch(() => props.show, (newValue) => {
    if (newValue) {
        // Modal opened - lock scroll
        document.body.style.overflow = 'hidden'
        document.addEventListener('keydown', handleEscape)
    } else {
        // Modal closed - restore scroll
        document.body.style.overflow = ''
        document.removeEventListener('keydown', handleEscape)
    }
})

const currentGalleryIndex = ref(0)

const nextGallery = () => {
    if (props.achievements?.galleries?.length) {
        currentGalleryIndex.value = (currentGalleryIndex.value + 1) % props.achievements.galleries.length
    }
}

const prevGallery = () => {
    if (props.achievements?.galleries?.length) {
        currentGalleryIndex.value = (currentGalleryIndex.value - 1 + props.achievements.galleries.length) % props.achievements.galleries.length
    }
}

const isImageFile = (filePath) => {
    const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.bmp', '.webp']
    return imageExtensions.some(ext => filePath.toLowerCase().includes(ext))
}

const getFileName = (attachment) => {
    // Handle both old string format and new object format
    if (typeof attachment === 'string') {
        const parts = attachment.split('/')
        return parts[parts.length - 1]
    } else if (attachment.original_name) {
        return attachment.original_name
    }
    return 'Unknown file'
}

const getFileType = (attachment) => {
    let fileName = ''
    if (typeof attachment === 'string') {
        fileName = attachment
    } else if (attachment.original_name) {
        fileName = attachment.original_name
    } else if (attachment.path) {
        fileName = attachment.path
    }
    
    const extension = fileName.split('.').pop()?.toUpperCase()
    return extension || 'File'
}

const getFileUrl = (attachment) => {
    if (typeof attachment === 'string') {
        return `/storage/${attachment}`
    } else if (attachment.path) {
        return `/storage/${attachment.path}`
    }
    return '#'
}

const getFileSize = (attachment) => {
    if (typeof attachment === 'string') {
        return 'Unknown size'
    } else if (attachment.file_size) {
        // Convert bytes to human readable format
        const bytes = attachment.file_size
        const sizes = ['Bytes', 'KB', 'MB', 'GB']
        if (bytes === 0) return '0 Bytes'
        const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)))
        return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i]
    }
    return 'Unknown size'
}

const getFileIcon = (attachment) => {
    const fileType = getFileType(attachment).toLowerCase();
    
    const iconMap = {
        'pdf': '/Images/SVG/file-pdf.svg',
        'doc': '/Images/SVG/file-doc.svg',
        'docx': '/Images/SVG/file-doc.svg',
        'xls': '/Images/SVG/file-xls.svg',
        'xlsx': '/Images/SVG/file-xls.svg',
        'ppt': '/Images/SVG/file-ppt.svg',
        'pptx': '/Images/SVG/file-ppt.svg',
        'txt': '/Images/SVG/file-text (500).svg',
    };
    
    return iconMap[fileType] || '/Images/SVG/file-text (500).svg';
}

// Fallback cleanup
onUnmounted(() => {
    document.body.style.overflow = ''
    document.removeEventListener('keydown', handleEscape)
})

onMounted(() => {
    initTooltips();
})
</script>

<template>
    <div 
        v-if="show" 
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-2 sm:p-4 z-[9999]"
        @click.self="handleBackdropClick"
    >
        <div 
            class="bg-white rounded-lg max-w-full sm:max-w-2xl lg:max-w-4xl w-full max-h-[90vh] overflow-y-auto mx-2 sm:mx-0"
            role="dialog"
            aria-labelledby="modal-title"
            aria-modal="true"
        >
            <!-- Modal Header -->
            <div class="flex justify-between items-start bg-blue-700 p-4 sm:p-6">
                <div class="flex-1">
                    <h2 id="modal-title" class="text-xl sm:text-2xl lg:text-3xl font-bold text-white mb-2 sm:mb-3">{{ achievements?.title }}</h2>
                    <div class="flex flex-col sm:flex-row sm:gap-5 gap-2">
                        <div class="flex gap-1">
                            <div class="flex justify-center items-center">
                                <img :src="'/Images/SVG/calendar (white).svg'" alt="Icon" class="h-3 w-3 sm:h-4 sm:w-4 flex">
                            </div>
                            <span class="text-white text-xs">{{ formatDate(achievements?.date_of_achievement) }}</span>
                        </div>
                        <div>
                            <div class="flex gap-1">
                                <div class="flex justify-center items-center">
                                    <img :src="'/Images/SVG/user (white).svg'" alt="Icon" class="h-3 w-3 sm:h-4 sm:w-4 flex">
                                </div>
                                <span class="text-white text-xs">System Administrator</span>
                            </div>
                        </div>
                        <div>
                            <div class="flex gap-1">
                                <div class="flex justify-center items-center">
                                    <img :src="'/Images/SVG/map-pin-area.svg'" alt="Icon" class="h-3 w-3 sm:h-4 sm:w-4 flex">
                                </div>
                                <span class="text-white text-xs">{{ achievements.location?.name || 'Not specified' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <button 
                    @click="close"
                    class="text-gray-400 hover:text-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 rounded ml-2"
                    aria-label="Close modal"
                >
                    <img :src="'/Images/SVG/x (white).svg'" alt="Close Icon" class="h-4 w-4 sm:h-5 sm:w-5">
                </button>
            </div>

            <div class="max-h-96 overflow-y-auto">
                <!-- Modal Content -->
                <div class="px-4 sm:px-6">
                    <!-- Content -->
                    <div class="my-3">
                        <p class="text-gray-600 whitespace-pre-line text-sm sm:text-base">{{ achievements?.content }}</p>
                    </div>

                    <!-- STATUS -->
                    <div class="border px-3 py-2 rounded-md mb-3">
                        <span class="font-medium text-gray-500 font-[Poppins] mb-1 text-xs">STATUS</span>
                        <p 
                            class="text-base sm:text-lg font-semibold"
                            :class="achievements?.status === 'published'
                                ? 'text-blue-500' : 'text-gray-700'"
                        >
                            {{ capitalizeFirstLetter(achievements?.status) }}
                        </p>
                    </div>

                    <!-- Category -->
                    <div class="border px-3 py-2 rounded-md mb-3">
                        <span class="font-medium text-gray-500 font-[Poppins] mb-1 text-xs">CATEGORY</span>
                        <p class="text-base sm:text-lg font-semibold text-blue-500">{{ achievements?.category.name || 'Uncategorized' }}</p>
                    </div>

                    <!-- Featured Image -->
                    <div v-if="achievements.featured_image" class="border px-3 py-2 rounded-md mb-3">
                        <span class="font-medium text-gray-500 font-[Poppins] mb-1 text-xs">FEATURED IMAGE</span>
                        <img 
                            :src="`/storage/${achievements.featured_image}`" 
                            :alt="achievements.title"
                            class="w-full rounded-lg mt-2"
                        >
                    </div>

                    <!-- IMAGE GALLERY -->
                    <div v-if="achievements.galleries && achievements.galleries.length > 0" class="border px-3 py-2 rounded-md mb-3">
                        <div class="flex items-center">
                            <span class="font-medium text-gray-500 font-[Poppins] mb-1 text-xs">GALLERY IMAGES</span>
                            <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 ml-2">
                                {{ achievements.galleries.length }} images
                            </span>
                        </div>

                        <!-- Simple Vue Carousel -->
                        <div class="relative w-full h-64 sm:h-80 mt-2">
                            <!-- Carousel wrapper -->
                            <div class="relative h-full overflow-hidden rounded-lg">
                                <img 
                                    v-for="(gallery, index) in achievements.galleries" 
                                    :key="gallery.id"
                                    :src="`/storage/${gallery.image_path}`" 
                                    :alt="gallery.caption || `Gallery image ${index + 1}`"
                                    class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500"
                                    :class="currentGalleryIndex === index ? 'opacity-100' : 'opacity-0'"
                                >
                            </div>
                            
                            <!-- Navigation buttons -->
                            <button 
                                v-if="achievements.galleries && achievements.galleries.length > 1"
                                @click="prevGallery"
                                class="absolute top-1/2 left-2 transform -translate-y-1/2 bg-white/80 hover:bg-white rounded-full p-1 sm:p-2 transition-colors"
                            >
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <button 
                                v-if="achievements.galleries && achievements.galleries.length > 1"
                                @click="nextGallery"
                                class="absolute top-1/2 right-2 transform -translate-y-1/2 bg-white/80 hover:bg-white rounded-full p-1 sm:p-2 transition-colors"
                            >
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>

                            <!-- Indicators -->
                            <div class="flex justify-center">
                                <div v-if="achievements.galleries && achievements.galleries.length > 1" class="flex items-center justify-center absolute bottom-1.5 space-x-2 px-3 py-1 glass-container">
                                    <button 
                                        v-for="(gallery, index) in achievements.galleries" 
                                        :key="gallery.id"
                                        @click="currentGalleryIndex = index"
                                        class="w-2 h-2 sm:w-3 sm:h-3 rounded-full transition-colors"
                                        :class="currentGalleryIndex === index ? 'bg-blue-600' : 'bg-gray-300'"
                                        :aria-label="`Go to slide ${index + 1}`"
                                    ></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ATTACHMENTS -->
                    <div v-if="achievements.document_attachments && achievements.document_attachments.length > 0" class="border px-3 py-2 rounded-md mb-3">
                        <div class="flex items-center">
                            <span class="font-medium text-gray-500 font-[Poppins] mb-1 text-xs">ATTACHMENTS</span>
                            <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800 ml-2">
                                {{ achievements.document_attachments.length }} files
                            </span>
                        </div>

                        <div class="mt-3 space-y-2">
                            <div 
                                v-for="(attachment, index) in achievements.document_attachments" 
                                :key="index"
                                class="flex flex-col sm:flex-row sm:items-center justify-between p-3 rounded-lg transition-colors border border-blue-200 bg-blue-50 gap-2"
                            >
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-100 rounded flex items-center justify-center">
                                            <!-- DYNAMIC FILE ICON BASED ON FILE TYPE -->
                                            <img 
                                                :src="getFileIcon(attachment)" 
                                                :alt="getFileType(attachment) + ' icon'" 
                                                class="h-4 w-4 sm:h-6 sm:w-6"
                                            >
                                        </div>
                                    </div>
                                    
                                    <div class="">
                                        <a 
                                            :href="getFileUrl(attachment)" 
                                            target="_blank"
                                            class="text-sm font-medium text-blue-500 hover:text-blue-700 hover:underline block"
                                            @click.stop
                                        >
                                            {{ getFileName(attachment) }}
                                        </a>
                                        <p class="text-xs text-gray-500">
                                            {{ getFileType(attachment) }} • {{ getFileSize(attachment) }}
                                        </p>
                                    </div>
                                </div>
                                
                                <a 
                                    :href="getFileUrl(attachment)" 
                                    target="_blank" 
                                    class="flex items-center justify-center space-x-1 px-2 sm:px-3 py-1 bg-blue-600 text-white text-xs sm:text-sm rounded-lg hover:bg-blue-700 transition-colors w-full sm:w-auto"
                                    :download="getFileName(attachment)"
                                >
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span>Download</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200 p-4 sm:p-6">
                <button 
                    @click="close"
                    class="px-3 sm:px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 text-sm sm:text-base"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.glass-container {
    background: rgba(255, 255, 255, 0.34);
    border-radius: 16px;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(6.6px);
    -webkit-backdrop-filter: blur(6.6px);
}
</style>