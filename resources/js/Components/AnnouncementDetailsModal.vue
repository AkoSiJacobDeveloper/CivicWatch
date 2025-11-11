<script setup>
import { Link, router } from '@inertiajs/vue3'
import { onMounted, onUnmounted } from 'vue'
import Swal from 'sweetalert2'
import { useToast } from 'vue-toastification'

const toast = useToast();

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

const emit = defineEmits(['close', 'deleted', 'archived', 'restored'])

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

const getFileIcon = (filePath) => {
    if (!filePath) return '/Images/SVG/file-text (500).svg';
    
    try {
        const fileName = filePath.split('/').pop() || '';
        const extension = fileName.split('.').pop()?.toLowerCase() || '';
        
        const iconMap = {
            'pdf': '/Images/SVG/file-pdf.svg',
            'doc': '/Images/SVG/file-doc.svg',
            'docx': '/Images/SVG/file-doc.svg',
            'xls': '/Images/SVG/file-xls.svg',
            'xlsx': '/Images/SVG/file-xls.svg',
            'ppt': '/Images/SVG/file-ppt.svg',
            'pptx': '/Images/SVG/file-ppt.svg',
            'txt': '/Images/SVG/file-text (500).svg',
            'zip': '/Images/SVG/file-archive.svg',
            'rar': '/Images/SVG/file-archive.svg',
            '7z': '/Images/SVG/file-archive.svg',
            'jpg': '/Images/SVG/file-image.svg',
            'jpeg': '/Images/SVG/file-image.svg',
            'png': '/Images/SVG/file-image.svg',
            'gif': '/Images/SVG/file-image.svg',
            'bmp': '/Images/SVG/file-image.svg',
            'svg': '/Images/SVG/file-image.svg',
        };
        
        return iconMap[extension] || '/Images/SVG/file-text (500).svg';
    } catch (error) {
        console.error('Error getting file icon:', error);
        return '/Images/SVG/file-text (500).svg';
    }
}

const getFileType = (filePath) => {
    if (!filePath) return 'File';
    
    try {
        const fileName = filePath.split('/').pop() || '';
        const extension = fileName.split('.').pop()?.toUpperCase();
        return extension || 'File';
    } catch (error) {
        console.error('Error getting file type:', error);
        return 'File';
    }
}

const getFileSize = (filePath) => {
    // Since we don't have file size stored, show placeholder
    return 'Unknown size';
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

const editAnnouncement = (id) => {
    router.get(`/admin/announcements/edit/${id}`)
}

const deleteAnnouncement = (id) => {
    Swal.fire({
        title: 'You are about to delete an announcement',
        text: 'Are you sure you want to delete this announcement? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        customClass: {
            confirmButton: 'swal2-confirm',
            cancelButton: 'swal2-cancel'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'info',
                title: 'Deleting...',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Then delete the announcement
            router.delete(route('admin.delete.announcement', { id: id }), {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.close();
                    emit('deleted', id)
                    toast.success('Announcement deleted successfully')
                },
                onError: () => {
                    Swal.close();
                    toast.error('Announcement failed to delete')
                }
            })
        }
    })
}

const archiveAnnouncement = (id) => {
    Swal.fire({
        title: 'Archive Announcement?',
        text: 'This announcement will be moved to the archive.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, archive it!',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'info',
                title: 'Archiving...',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            router.post(route('admin.archive.announcement', { id: id }), {}, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.close();
                    emit('archived', id)
                    toast.success('Announcement successfully archived');
                },
                onError: () => {
                    Swal.close();
                    toast.error('Announcement failed to archived');
                }
            })
        }
    })
}

// Restore announcement from modal
const restoreAnnouncement = (id) => {
    Swal.fire({
        title: 'Restore Announcement?',
        text: 'This announcement will be moved back to active announcements.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, restore it!',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'info',
                title: 'Restoring...',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            router.post(route('admin.restore.announcement', { id: id }), {}, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.close();
                    emit('restored', id)
                    toast.success('Announcement successfully restored');
                },
                onError: () => {
                    Swal.close();
                    toast.error('Announcement failed to restore');
                }
            })
        }
    })
}


onMounted(() => {
    initTooltips();
});

</script>

<template>
    <div 
        v-if="show" 
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
        @click.self="handleBackdropClick"
    >
        <div 
            class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto"
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
                                <span class="text-white text-xs">{{ announcement.venue || 'Location not specified' }}</span>
                            </div>
                        </div>
                    </div>
                    <div v-if="announcement?.archived_at" class="mt-2">
                        <span class="bg-gray-600 text-white text-xs font-semibold px-2 py-1 rounded inline-flex items-center gap-1">
                            <img :src="'/Images/SVG/archive.svg'" alt="Archive" class="h-3 w-3">
                            Archived on {{ formatDate(announcement.archived_at) }}
                        </span>
                    </div>
                </div>
                <button 
                    @click="close"
                    class="text-gray-400 hover:text-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 rounded "
                    aria-label="Close modal"
                >
                    <img :src="'/Images/SVG/x (white).svg'" alt="Close Icon" class="h-5 w-5">
                </button>
            </div>

            <div class="h-96 overflow-y-auto">
                <!-- Modal Content -->
                <div class="px-6 ">
                    <!-- Announcement -->
                    <div class="my-3">
                        <!-- <span class="font-medium text-gray-500 font-[Poppins] mb-1 text-xs">CONTENT</span> -->
                        <p class="text-gray-600 whitespace-pre-line">{{ announcement?.content }}</p>
                    </div>

                    <!-- Priority Level -->
                    <div class="border px-3 py-2 rounded-md mb-3">
                        <span class="font-medium text-gray-500 font-[Poppins] mb-1 text-xs">PRIORITY</span>
                        <p 
                            class="text-lg font-semibold"
                            :class="announcement?.level === 'urgent'
                                ? 'text-red-700' : announcement?.level === 'important'
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
                        <p class="text-lg font-semibold text-blue-500">{{ announcement?.contact_person || 'Not Specified' }}</p>
                    </div>

                    <!-- Contact Number -->
                    <div  class="border px-3 py-2 rounded-md mb-3">
                        <span class="font-medium text-gray-500 font-[Poppins] mb-1 text-xs">CONTACT NUMBER</span>
                        <p class="text-lg font-semibold text-blue-500">{{ announcement?.contact_number || 'Not Specified' }}</p>
                    </div>

                    <!-- LOCATIONS -->
                    <div class="border px-3 py-2 rounded-md mb-3">
                        <span class="font-medium text-gray-500 font-[Poppins] mb-1 text-xs">LOCATION</span>
                        <p class="text-lg font-semibold text-blue-500">
                            {{ announcement?.purok === 'all' ? 'All Purok/Sitio' : announcement?.specific_area }}
                        </p>
                    </div>

                    

                    <!-- Image -->
                    <div v-if="announcement.image" class="border px-3 py-2 rounded-md mb-3">
                        <span class="font-medium text-gray-500 font-[Poppins] mb-1 text-xs">IMAGE</span>
                        <img 
                            :src="`/storage/${announcement.image}`" 
                            :alt="announcement.title"
                            class="w-full rounded-lg"
                        >
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
                    <div class="border px-3 py-2 rounded-md mb-3" v-if="announcement.documents.length">
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

                    <!-- INSTRUCTIONS -->
                    <div class="border px-3 py-2 rounded-md mb-3">
                        <span class="font-medium text-gray-500 font-[Poppins] mb-1 text-xs">INSTRUCTIONS</span>
                        <p class="text-lg font-semibold text-blue-500">{{ announcement?.instructions }}</p>

                        <div v-if="!announcement?.instructions?.length" class="text-gray-500">
                            <span class="font-medium">No special instructions</span>
                        </div>
                    </div>
                </div>

                <!-- Attachments - UPDATED VERSION -->
                <div class="px-6" v-if="announcement.attachments.length">
                    <div v-if="announcement?.attachments?.length" class="border rounded-md px-3 py-2">
                        <div class="flex items-center">
                            <p class="font-medium text-gray-500 font-[Poppins]  text-xs">ATTACHMENTS</p>
                            <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800 ml-2">
                                {{ announcement.attachments.length }} files
                            </span>
                        </div>
                        

                        <div class="mt-3 space-y-2">
                            <div 
                                v-for="file in announcement.attachments" 
                                :key="file.id"
                                class="flex items-center justify-between p-3 rounded-lg transition-colors border border-blue-200 bg-blue-50"
                            >
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-gray-100 rounded flex items-center justify-center">
                                            <!-- DYNAMIC FILE ICON -->
                                            <img 
                                                :src="getFileIcon(file.file_path)" 
                                                :alt="getFileType(file.file_path) + ' icon'" 
                                                class="h-6 w-6"
                                            >
                                        </div>
                                    </div>
                                    
                                    <div class="">
                                        <a 
                                            :href="getFileUrl(file.file_path)"
                                            target="_blank"
                                            class="text-sm font-medium text-blue-500 hover:text-blue-700 hover:underline block"
                                            @click.stop
                                        >
                                            {{ file.file_name || file.file_path.split('/').pop() }}
                                        </a>
                                        <p class="text-xs text-gray-500">
                                            {{ getFileType(file.file_path) }} • {{ getFileSize(file.file_path) }}
                                        </p>
                                    </div>
                                </div>
                                
                                <a 
                                    :href="getFileUrl(file.file_path)"
                                    target="_blank" 
                                    class="flex items-center space-x-1 px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors"
                                    :download="file.file_name || file.file_path.split('/').pop()"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span>Download</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div v-else class="border rounded-md px-3 py-2 text-gray-500">
                        <p class="font-medium text-gray-500 font-[Poppins] mb-1 text-xs">ATTACHMENTS</p>
                        <span class="font-medium">No attachments available</span>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-between gap-3 mt-6 pt-4 border-t border-gray-200 p-6">
                <button 
                    @click="close"
                    class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500"
                >
                    Close
                </button>
                <div>
                    <template 
                        v-if="!announcement?.archived_at"
                    >
                        <div class="flex gap-1">
                            <!-- Archive -->
                            <button
                                @click="archiveAnnouncement(announcement.id)"
                                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 flex items-center justify-center gap-1 flex-1"
                            >
                                <img :src="'/Images/SVG/archive.svg'" alt="Icon" class="h-4 w-4">
                                Archive
                            </button>

                            <!-- Edit-->
                            <button
                                @click="editAnnouncement(announcement.id)"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 flex gap-1 items-center justify-center flex-1"
                            >
                                <img :src="'/Images/SVG/pencil-simple-line.svg'" alt="Icon" class="h-4 w-4">
                                Edit
                            </button>

                            <!-- Delete -->
                            <!-- <button
                                @click="deleteAnnouncement(announcement.id)"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-1 flex-1"
                            >
                                <img :src="'/Images/SVG/trash.svg'" alt="Icon" class="h-4 w-4">
                                Delete
                            </button> -->
                        </div>
                    </template>

                    <template v-else>
                        <div class="flex gap-2 justify-end">
                            <!-- Archived Announcement Buttons -->
                            <button
                                @click="restoreAnnouncement(announcement.id)"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 flex items-center gap-1"
                            >
                                <img :src="'/Images/SVG/arrows-counter-clockwise.svg'" alt="Icon" class="h-4 w-4">
                                Restore
                            </button>
                            <button
                                @click="deleteAnnouncement(announcement.id)"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 flex items-center gap-1"
                            >
                                <img :src="'/Images/SVG/trash.svg'" alt="Icon" class="h-4 w-4">
                                Delete
                            </button>
                        </div>
                    </template>
                </div>  
            </div>
        </div>
    </div>
</template>