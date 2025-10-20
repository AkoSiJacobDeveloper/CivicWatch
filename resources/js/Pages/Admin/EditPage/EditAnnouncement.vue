<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { useToast } from 'vue-toastification';
import { initTooltips } from 'flowbite';
import { Listbox, ListboxButton, ListboxOption, ListboxOptions } from '@headlessui/vue';
import CustomMultiselect from '@/Components/CustomMultiselect.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

// Simple props definition
const props = defineProps({
    announcement: Object,
    announcementCategories: Array,
    audiences: Array,
    offices: Array,
    documents: Array,
    activePuroks: Array
});

const urlParams = new URLSearchParams(window.location.search);
const fromParam = urlParams.get('from');

const backUrl = computed(() => {
    switch(fromParam) {
        case 'announcement':
            return '/admin/announcements'
        default:
            return '/admin/announcements';
    }
})

const toast = useToast();

// Simple form initialization
const form = useForm({
    title: props.announcement?.title || '',
    type: props.announcement?.type || '',
    level: props.announcement?.level || '',
    is_pinned: props.announcement?.is_pinned || 'no',
    content: props.announcement?.content || '',
    image: null,
    existing_image: props.announcement?.image || null,
    publish_at: props.announcement?.publish_at || null,
    event_date: props.announcement?.event_date || '',
    venue: props.announcement?.venue || '',
    expiry_date: props.announcement?.expiry_date || null,
    contact_person: props.announcement?.contact_person || '',
    contact_number: props.announcement?.contact_number || '',
    purok: props.announcement?.purok || 'all',
    attachments: [], // For NEW file uploads
    existing_attachments: props.announcement?.attachments || [], // For existing attachments from DB
    audiences: props.announcement?.audiences || [],
    departments: props.announcement?.departments || [],
    requirements: props.announcement?.requirements || [],
    instructions: props.announcement?.instructions || '',
    counts: props.announcement?.counts || '',
    reg_deadline: props.announcement?.reg_deadline || '',
    other_document: props.announcement?.other_document || '',
    specific_area: props.announcement?.specific_area || '',
});

const submitForm = () => {
    console.log('Submitting form with files...');
    
    // Transform form data before sending
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
        existing_attachments: JSON.stringify(data.existing_attachments || [])
    }))
    .post(route('admin.update.announcement', props.announcement.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            if (imageInputRef.value) imageInputRef.value.value = '';
            if (attachmentsInputRef.value) attachmentsInputRef.value.value = '';
            toast.success('Announcement updated successfully!');
        },
        onError: (errors) => {
            toast.error('Failed to update announcement');
            console.log('VALIDATION ERRORS:', errors);
        }
    });
}

// Basic variables
const levels = ['high', 'medium', 'low'];
const isFeatureds = ['yes', 'no'];
const today = ref(new Date().toISOString().slice(0, 16)); // This is correct for datetime-local
const imageInputRef = ref(null);
const attachmentsInputRef = ref(null);

const purokOptions = [
    { id: 'all', name: 'All Purok' },
    { id: 'specific', name: 'Specific Area' },
];

// Simple handlers
const handleImageChange = (e) => {
    form.image = e.target.files[0];
}

const handleAttachmentsChange = (e) => {
    // Add new files to attachments array
    const newFiles = Array.from(e.target.files);
    form.attachments = [...form.attachments, ...newFiles];
}

const removeAttachment = (index) => {
    form.attachments.splice(index, 1);
    updateFileInput();
}

const removeExistingAttachment = (index) => {
    form.existing_attachments.splice(index, 1);
}

const updateFileInput = () => {
    if (attachmentsInputRef.value) {
        attachmentsInputRef.value.value = '';
    }
}

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

const showRequirements = computed(() => false); 
const getCategoryName = (id) => {
    const category = props.announcementCategories?.find(cat => cat.id === id);
    return category ? category.name : 'Select type';
}

// Simple date formatters
const formattedPublishAt = computed({
    get: () => form.publish_at ? form.publish_at.replace(/\.\d+Z$/, '').slice(0, 16) : '',
    set: (value) => { form.publish_at = value; }
});

const formattedEventDate = computed({
    get: () => form.event_date ? form.event_date.split('T')[0] : '',
    set: (value) => { form.event_date = value; }
});

const formattedExpiryDate = computed({
    get: () => form.expiry_date ? form.expiry_date.replace(/\.\d+Z$/, '').slice(0, 16) : '',
    set: (value) => { form.expiry_date = value; }
});

const formattedRegDeadline = computed({
    get: () => form.reg_deadline ? form.reg_deadline.split('T')[0] : '',
    set: (value) => { form.reg_deadline = value; }
});

const enhancedDocuments = computed(() => {
    return [
        ...(props.documents || []),
        { id: 'other', name: 'Other (Specify below)', document_type: 'Custom' }
    ];
});

onMounted(() => {
    initTooltips();
});

const minPublishDate = computed(() => {
    if (props.announcement?.id) {
        return null; 
    }
    return new Date().toISOString().slice(0, 16);
});

// Helper method to check if it's a valid File object
const isValidFile = (file) => {
    return file && typeof file === 'object' && file instanceof File;
}

// Safe method to create object URL
const getImagePreview = (file) => {
    if (isValidFile(file)) {
        return URL.createObjectURL(file);
    }
    return '';
}

// Handle image load errors
const onImageLoad = (event) => {
    console.log('Image loaded successfully');
}

const showImageSection = computed(() => form.image || form.existing_image);
const hasNewImage = computed(() => form.image && typeof form.image === 'object');
const hasExistingImage = computed(() => form.existing_image);
const newImagePreview = computed(() => {
    if (hasNewImage.value) {
        return URL.createObjectURL(form.image);
    }
    return '';
});
const existingImageUrl = computed(() => {
    return `/storage/${form.existing_image}`;
});

const handlePreviewError = (event) => {
    console.error('Failed to load image preview');
    // Optionally show a placeholder or hide the image
};

const handleExistingImageError = (event) => {
    console.error('Failed to load existing image');
    // Optionally show a placeholder
};
</script>

<template>
    <Head title="Edit Announcement"/>

    <AdminLayout>
        <main class="flex flex-col gap-7 text-sm">
            <section class="flex justify-between items-center">
                <div class="flex gap-1 items-center">
                    <div>
                        <Link :href="backUrl">
                            <img :src="'/Images/SVG/arrow-circle-left-fill (700).svg'" alt="Back Icon">
                        </Link>
                    </div>
                    <h1 class="font-semibold text-3xl font-[Poppins] my-3">Edit Announcement</h1>
                </div>
            </section>

            <section>
                <form @submit.prevent="submitForm">
                    <h1 class="font-bold text-2xl mb-2 text-blue-500">Basic Information</h1>
                    <div class="grid grid-cols-2 gap-4 mb-5">
                        <!-- Title -->
                        <div>
                            <div class="flex items-center gap-1">
                                <label for="title" class="block text-sm font-[Poppins] font-medium">Title</label>
                                <button 
                                    data-tooltip-target="tooltip-info-title"
                                    type="button"
                                    class="text-blue-500 hover:text-blue-700"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="blue" viewBox="0 0 256 256"><path d="M140,180a12,12,0,1,1-12-12A12,12,0,0,1,140,180ZM128,72c-22.06,0-40,16.15-40,36v4a8,8,0,0,0,16,0v-4c0-11,10.77-20,24-20s24,9,24,20-10.77,20-24,20a8,8,0,0,0-8,8v8a8,8,0,0,0,16,0v-.72c18.24-3.35,32-17.9,32-35.28C168,88.15,150.06,72,128,72Zm104,56A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path></svg>
                                </button>

                                <div 
                                    id="tooltip-info-title"
                                    role="tooltip"
                                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                                >
                                    Clear, concise title that summarizes the announcement
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                            </div>

                            <input 
                                v-model="form.title"
                                type="text"
                                placeholder="Assembly of Barangay Officials"
                                class="w-full p-3 bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline focus:ring-1 focus:border-blue-200 peer dark:bg-[#2c2c2c]"
                            >
                        </div>

                        <!-- Category-->
                        <div>
                            <div class="flex items-center gap-1">
                                <label for="category" class="block text-sm font-[Poppins] font-medium">Category</label>
                                <button 
                                    data-tooltip-target="tooltip-info-category"
                                    type="button"
                                    class="text-blue-500 hover:text-blue-700"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="blue" viewBox="0 0 256 256"><path d="M140,180a12,12,0,1,1-12-12A12,12,0,0,1,140,180ZM128,72c-22.06,0-40,16.15-40,36v4a8,8,0,0,0,16,0v-4c0-11,10.77-20,24-20s24,9,24,20-10.77,20-24,20a8,8,0,0,0-8,8v8a8,8,0,0,0,16,0v-.72c18.24-3.35,32-17.9,32-35.28C168,88.15,150.06,72,128,72Zm104,56A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path></svg>
                                </button>

                                <div 
                                    id="tooltip-info-category"
                                    role="tooltip"
                                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                                >
                                    Select the most relevant category for proper organization
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                            </div>

                            <Listbox
                                v-model="form.type"
                                as="div"
                                class="relative "
                            >
                                <ListboxButton
                                    class="flex justify-between items-center text-left p-3 w-full text-base bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline-none focus:ring-0 focus:border-gray-200 peer dark:bg-[#2c2c2c]"
                                    required
                                >
                                    {{ form.type ? getCategoryName(form.type) : 'Select an announcement type' }}

                                    <svg
                                        class="w-5 h-5 text-gray-400"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                        >
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.23 7.21a.75.75 0 011.06.02L10 11.292l3.71-4.06a.75.75 0 111.08 1.04l-4.25 4.65a.75.75 0 01-1.08 0l-4.25-4.65a.75.75 0 01.02-1.06z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </ListboxButton>

                                <ListboxOptions
                                    class="absolute z-50  w-full bg-white rounded-lg shadow-lg border border-gray-300 dark:bg-[#2c2c2c] max-h-56 overflow-y-auto" 
                                >
                                    <ListboxOption
                                        v-for="announcementCategorie in props.announcementCategories"
                                        :key="announcementCategorie.id"
                                        :value="announcementCategorie.id"
                                        class="dark:bg-[#2c2c2c] px-4 py-2 hover:bg-blue-100 transition-all duration-100"
                                    >
                                        {{ announcementCategorie.name}}

                                        
                                    </ListboxOption>
                                </ListboxOptions>
                            </Listbox>
                        </div>

                        <!-- Priority Level -->
                        <div>
                            <div class="flex items-center gap-1">
                                <label for="category" class="block text-sm font-[Poppins] font-medium">Priority Level</label>
                                <button 
                                    data-tooltip-target="tooltip-info-priority"
                                    type="button"
                                    class="text-blue-500 hover:text-blue-700"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="blue" viewBox="0 0 256 256"><path d="M140,180a12,12,0,1,1-12-12A12,12,0,0,1,140,180ZM128,72c-22.06,0-40,16.15-40,36v4a8,8,0,0,0,16,0v-4c0-11,10.77-20,24-20s24,9,24,20-10.77,20-24,20a8,8,0,0,0-8,8v8a8,8,0,0,0,16,0v-.72c18.24-3.35,32-17.9,32-35.28C168,88.15,150.06,72,128,72Zm104,56A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path></svg>
                                </button>

                                <div 
                                    id="tooltip-info-priority"
                                    role="tooltip"
                                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                                >
                                    <ul>
                                        <li><span class="text-red-700 font-semibold">High</span>: For emergency alerts and urgent matters</li>
                                        <li><span class="text-amber-700 font-semibold">Medium</span>: For important but non-critical announcements</li>
                                        <li><span class="text-green-700 font-semibold">Low</span>: For general information and routine updates</li>
                                    </ul>
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-3">
                                <div
                                    v-for="level in levels" 
                                    :key="level"
                                    class="flex items-center gap-2 border p-3 bg-white rounded-lg"
                                >
                                    <input 
                                        v-model="form.level"
                                        type="radio"
                                        :id="level"
                                        name="level"
                                        :value="level"

                                    >
                                    <label 
                                        :for="level"
                                        class="capitalize"
                                    >
                                        {{ level }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Featured Announcement -->
                        <div>
                            <div class="flex items-center gap-1">
                                <label for="category" class="block text-sm font-[Poppins] font-medium">Pin Announcement</label>
                                <button 
                                    data-tooltip-target="tooltip-info-pin"
                                    type="button"
                                    class="text-blue-500 hover:text-blue-700"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="blue" viewBox="0 0 256 256"><path d="M140,180a12,12,0,1,1-12-12A12,12,0,0,1,140,180ZM128,72c-22.06,0-40,16.15-40,36v4a8,8,0,0,0,16,0v-4c0-11,10.77-20,24-20s24,9,24,20-10.77,20-24,20a8,8,0,0,0-8,8v8a8,8,0,0,0,16,0v-.72c18.24-3.35,32-17.9,32-35.28C168,88.15,150.06,72,128,72Zm104,56A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path></svg>
                                </button>

                                <div 
                                    id="tooltip-info-pin"
                                    role="tooltip"
                                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                                >
                                    <ul>
                                        <li><span class="font-semibold">Yes</span>: Pin this announcement to the top of the page</li>
                                        <li><span class="font-semibold">No</span>: Display in chronological order with other announcements</li>
                                    </ul>
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div
                                    v-for="isFeatured in isFeatureds" 
                                    :key="isFeatured"
                                    class="flex items-center gap-2 border p-3 bg-white rounded-lg"
                                >
                                    <input 
                                        v-model="form.is_pinned"
                                        type="radio"
                                        :id="isFeatured"
                                        name="isFeatured"
                                        :value="isFeatured"

                                    >
                                    <label 
                                        :for="isFeatured"
                                        class="capitalize"
                                    >
                                        {{ isFeatured }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Venue -->
                        <div>
                            <div class="flex items-center gap-1">
                                <label for="category" class="block text-sm font-[Poppins] font-medium">Venue</label>
                                <button 
                                    data-tooltip-target="tooltip-info-venue"
                                    type="button"
                                    class="text-blue-500 hover:text-blue-700"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="blue" viewBox="0 0 256 256"><path d="M140,180a12,12,0,1,1-12-12A12,12,0,0,1,140,180ZM128,72c-22.06,0-40,16.15-40,36v4a8,8,0,0,0,16,0v-4c0-11,10.77-20,24-20s24,9,24,20-10.77,20-24,20a8,8,0,0,0-8,8v8a8,8,0,0,0,16,0v-.72c18.24-3.35,32-17.9,32-35.28C168,88.15,150.06,72,128,72Zm104,56A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path></svg>
                                </button>

                                <div 
                                    id="tooltip-info-venue"
                                    role="tooltip"
                                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                                >
                                    Venue where the events will takes place
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                            </div>

                            <input 
                                v-model="form.venue"
                                type="text"
                                placeholder="e.g., Barangay Hall, Community Center, Cabulijan Basketball Court"
                                class="w-full p-3 bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline focus:ring-1 focus:border-blue-200 peer dark:bg-[#2c2c2c]"
                            >
                        </div>
                    </div>

                    <h1 class="font-bold text-2xl mb-2 text-blue-500">Content & Media</h1>
                    <div class="grid grid-cols-2 gap-4 mb-5">
                        <!-- Content -->
                        <div class="col-span-2">
                            <div class="flex items-center gap-1">
                                <label for="category" class="block text-sm font-[Poppins] font-medium">Announcement Description</label>
                                <button 
                                    data-tooltip-target="tooltip-info-description"
                                    type="button"
                                    class="text-blue-500 hover:text-blue-700"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="blue" viewBox="0 0 256 256"><path d="M140,180a12,12,0,1,1-12-12A12,12,0,0,1,140,180ZM128,72c-22.06,0-40,16.15-40,36v4a8,8,0,0,0,16,0v-4c0-11,10.77-20,24-20s24,9,24,20-10.77,20-24,20a8,8,0,0,0-8,8v8a8,8,0,0,0,16,0v-.72c18.24-3.35,32-17.9,32-35.28C168,88.15,150.06,72,128,72Zm104,56A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path></svg>
                                </button>

                                <div 
                                    id="tooltip-info-description"
                                    role="tooltip"
                                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                                >
                                    Provide detailed information using the text editor about the announcement
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                            </div>
                            <div>
                                <textarea 
                                    v-model="form.content"
                                    id="description"
                                    rows="7"
                                    placeholder="Write the announcement description here..."
                                    class=" w-full text-base bg-white text-gray-900 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-200 dark:bg-[#2c2c2c] dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 resize-none"
                                    required
                                ></textarea>
                            </div>
                        </div>

                        <!-- Image -->
                        <div>
                            <div class="flex items-center gap-1">
                                <label for="category" class="block text-sm font-[Poppins] font-medium">Image</label>
                                <button 
                                    data-tooltip-target="tooltip-info-image"
                                    type="button"
                                    class="text-blue-500 hover:text-blue-700"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="blue" viewBox="0 0 256 256"><path d="M140,180a12,12,0,1,1-12-12A12,12,0,0,1,140,180ZM128,72c-22.06,0-40,16.15-40,36v4a8,8,0,0,0,16,0v-4c0-11,10.77-20,24-20s24,9,24,20-10.77,20-24,20a8,8,0,0,0-8,8v8a8,8,0,0,0,16,0v-.72c18.24-3.35,32-17.9,32-35.28C168,88.15,150.06,72,128,72Zm104,56A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path></svg>
                                </button>

                                <div 
                                    id="tooltip-info-image"
                                    role="tooltip"
                                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                                >
                                    Upload a relevant photo or banner (JPG, PNG)
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                            </div>
                            <div class="p-1 border bg-white rounded-lg">
                                <input 
                                    ref="imageInputRef"  
                                    type="file"
                                    @change="handleImageChange"
                                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-500 file:text-white hover:file:bg-blue-600 cursor-pointer "
                                    accept="image/*"
                                >
                            </div>
                            <div v-if="showImageSection" class="mt-3">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Current Image:</h4>
                                
                                <!-- New image preview -->
                                <img 
                                    v-if="hasNewImage"
                                    :src="newImagePreview" 
                                    :alt="form.title"
                                    class="w-64 h-48 object-cover rounded-lg border"
                                    @error="handlePreviewError"
                                />
                                
                                <!-- Existing image display -->
                                <img 
                                    v-else-if="hasExistingImage"
                                    :src="existingImageUrl" 
                                    :alt="props.announcement?.title"
                                    class="w-64 h-48 object-cover rounded-lg border"
                                    @error="handleExistingImageError"
                                />
                                
                                <p class="text-xs text-gray-500 mt-1">
                                    <span v-if="hasNewImage">New image preview - will replace current image</span>
                                    <span v-else-if="hasExistingImage">Current image - upload new one to replace</span>
                                </p>
                            </div>
                        </div>

                        <!-- Attachments -->
                        <div>
                            <div class="flex items-center gap-1">
                                <label for="category" class="block text-sm font-[Poppins] font-medium">Attachments</label>
                                <button 
                                    data-tooltip-target="tooltip-info-attachments"
                                    type="button"
                                    class="text-blue-500 hover:text-blue-700"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="blue" viewBox="0 0 256 256"><path d="M140,180a12,12,0,1,1-12-12A12,12,0,0,1,140,180ZM128,72c-22.06,0-40,16.15-40,36v4a8,8,0,0,0,16,0v-4c0-11,10.77-20,24-20s24,9,24,20-10.77,20-24,20a8,8,0,0,0-8,8v8a8,8,0,0,0,16,0v-.72c18.24-3.35,32-17.9,32-35.28C168,88.15,150.06,72,128,72Zm104,56A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path></svg>
                                </button>

                                <div 
                                    id="tooltip-info-attachments"
                                    role="tooltip"
                                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                                >
                                    Add supporting documents (PDF, Word, Excel files)
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                            </div>
                            <div class="p-1 border bg-white rounded-lg">
                                <input 
                                    ref="attachmentsInputRef"
                                    type="file"
                                    multiple
                                    @change="handleAttachmentsChange"
                                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-500 file:text-white hover:file:bg-blue-600 cursor-pointer"
                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt"
                                >
                                
                                <!-- Existing Attachments -->
                                <div v-if="form.existing_attachments && form.existing_attachments.length > 0" class="mt-3">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Existing Attachments:</h4>
                                    <ul class="text-sm text-gray-600">
                                        <li 
                                            v-for="(attachment, index) in form.existing_attachments" 
                                            :key="attachment.id"
                                            class="flex items-center justify-between border p-2 my-1 rounded bg-green-50 border-green-200"
                                        >
                                            <div>
                                                <span class="text-green-700">{{ attachment.file_name }}</span>
                                                <span class="text-xs text-gray-500 ml-2">({{ formatFileSize(attachment.file_size) }})</span>
                                            </div>
                                            <button 
                                                type="button"
                                                @click="removeExistingAttachment(index)"
                                                class="text-red-500 hover:text-red-700 hover:bg-red-100 p-1 rounded transition-colors duration-200"
                                                title="Remove file"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </li>
                                    </ul>
                                </div>

                                <!-- New Attachments -->
                                <div v-if="form.attachments && form.attachments.length > 0" class="mt-3">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">New Attachments:</h4>
                                    <ul class="text-sm text-gray-600">
                                        <li 
                                            v-for="(file, index) in form.attachments" 
                                            :key="index"
                                            class="flex items-center justify-between border p-2 my-1 rounded bg-blue-50 border-blue-200"
                                        >
                                            <span class="text-blue-700">{{ file.name }}</span>
                                            <button 
                                                type="button"
                                                @click="removeAttachment(index)"
                                                class="text-red-500 hover:text-red-700 hover:bg-red-100 p-1 rounded transition-colors duration-200"
                                                title="Remove file"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h1 class="font-bold text-2xl mb-2 text-blue-500">Schedule & Dates</h1>
                    <div class="grid grid-cols-2 gap-4 mb-5">
                        <!-- Event Date -->
                        <div>
                            <div class="flex items-center gap-1">
                                <label for="category" class="block text-sm font-[Poppins] font-medium">Event Date</label>
                                <button 
                                    data-tooltip-target="tooltip-info-event"
                                    type="button"
                                    class="text-blue-500 hover:text-blue-700"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="blue" viewBox="0 0 256 256"><path d="M140,180a12,12,0,1,1-12-12A12,12,0,0,1,140,180ZM128,72c-22.06,0-40,16.15-40,36v4a8,8,0,0,0,16,0v-4c0-11,10.77-20,24-20s24,9,24,20-10.77,20-24,20a8,8,0,0,0-8,8v8a8,8,0,0,0,16,0v-.72c18.24-3.35,32-17.9,32-35.28C168,88.15,150.06,72,128,72Zm104,56A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path></svg>
                                </button>

                                <div 
                                    id="tooltip-info-event"
                                    role="tooltip"
                                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                                >
                                    For time-sensitive events only (leave empty if not applicable)
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                            </div>
                            <input 
                                v-model="formattedEventDate"
                                type="date"
                                class="w-full p-3 bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline focus:ring-1 focus:border-blue-200 peer dark:bg-[#2c2c2c]"
                                required
                            >
                        </div>
                    </div>

                    <h1 class="font-bold text-2xl mb-2 text-blue-500">Target Audience</h1>
                    <div class="grid grid-cols-2 gap-4 mb-5">
                        <!-- Target Audience -->
                        <div>
                            <div class="flex items-center gap-1">
                                <label for="category" class="block text-sm font-[Poppins] font-medium">Audience</label>
                                <button 
                                    data-tooltip-target="tooltip-info-audience"
                                    type="button"
                                    class="text-blue-500 hover:text-blue-700"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="blue" viewBox="0 0 256 256"><path d="M140,180a12,12,0,1,1-12-12A12,12,0,0,1,140,180ZM128,72c-22.06,0-40,16.15-40,36v4a8,8,0,0,0,16,0v-4c0-11,10.77-20,24-20s24,9,24,20-10.77,20-24,20a8,8,0,0,0-8,8v8a8,8,0,0,0,16,0v-.72c18.24-3.35,32-17.9,32-35.28C168,88.15,150.06,72,128,72Zm104,56A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path></svg>
                                </button>

                                <div 
                                    id="tooltip-info-audience"
                                    role="tooltip"
                                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                                >
                                    Select specific groups who should see this announcement
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                            </div>
                            <CustomMultiselect
                                v-model="form.audiences"
                                :options="props.audiences"
                                placeholder="Select target audiences"
                                label="name"
                                track-by="id"
                            />
                        </div>

                        <div>
                            <div class="flex items-center gap-1">
                                <label class="block text-sm font-[Poppins] font-medium">Target Area Scope</label>
                                <button 
                                    data-tooltip-target="tooltip-info-areas"
                                    type="button"
                                    class="text-blue-500 hover:text-blue-700"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="blue" viewBox="0 0 256 256"><path d="M140,180a12,12,0,1,1-12-12A12,12,0,0,1,140,180ZM128,72c-22.06,0-40,16.15-40,36v4a8,8,0,0,0,16,0v-4c0-11,10.77-20,24-20s24,9,24,20-10.77,20-24,20a8,8,0,0,0-8,8v8a8,8,0,0,0,16,0v-.72c18.24-3.35,32-17.9,32-35.28C168,88.15,150.06,72,128,72Zm104,56A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path></svg>
                                </button>

                                <div 
                                    id="tooltip-info-areas"
                                    role="tooltip"
                                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                                >
                                    Target specific puroks or select 'All Purok' for everyone
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                            </div>
                            <Listbox v-model="form.purok" as="div" class="relative">
                                <ListboxButton 
                                    class="flex justify-between items-center text-left p-3 w-full text-base bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline-none focus:ring-0 focus:border-gray-200 peer dark:bg-[#2c2c2c]"
                                    required
                                >
                                    {{ form.purok === 'all' ? 'All Purok/Sitio (Recommended)' : 'Specific Area' }}

                                    <svg
                                        class="w-5 h-5 text-gray-400"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                        >
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.23 7.21a.75.75 0 011.06.02L10 11.292l3.71-4.06a.75.75 0 111.08 1.04l-4.25 4.65a.75.75 0 01-1.08 0l-4.25-4.65a.75.75 0 01.02-1.06z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </ListboxButton>
                                <ListboxOptions 
                                    class="absolute z-50 w-full bg-white rounded-lg shadow-lg border border-gray-300 dark:bg-[#2c2c2c] max-h-56 overflow-y-auto"
                                >
                                    <ListboxOption 
                                        v-for="purok in purokOptions" 
                                        :key="purok.id" 
                                        :value="purok.id"
                                        class="dark:bg-[#2c2c2c] px-4 py-2 hover:bg-blue-100 transition-all duration-100"
                                    >
                                        {{ purok.name }}
                                    </ListboxOption>
                                </ListboxOptions>
                            </Listbox>
                        </div>

                        <!-- Specific Area -->
                        <div v-if="form.purok === 'specific'">
                            <div class="flex items-center gap-1">
                                <label class="block text-sm font-[Poppins] font-medium">Specific Area</label>
                                <!-- Tooltip... -->
                            </div>
                            <input 
                                v-model="form.specific_area"
                                type="text"
                                placeholder="e.g., Purok 5, Sitio Centro, Near Barangay Hall"
                                class="w-full p-3 bg-white text-gray-500 rounded-lg border border-gray-300 focus:outline focus:ring-1 focus:border-blue-200 peer"
                            >
                        </div>
                    </div>

                    <h1 class="font-bold text-2xl mb-2 text-blue-500">Contact Information</h1>
                    <div class="grid grid-cols-2 gap-4 mb-5">
                        <!-- Contact Person -->
                        <div>
                            <div class="flex items-center gap-1">
                                <label for="category" class="block text-sm font-[Poppins] font-medium">Contact Person</label>
                                <button 
                                    data-tooltip-target="tooltip-info-person"
                                    type="button"
                                    class="text-blue-500 hover:text-blue-700"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="blue" viewBox="0 0 256 256"><path d="M140,180a12,12,0,1,1-12-12A12,12,0,0,1,140,180ZM128,72c-22.06,0-40,16.15-40,36v4a8,8,0,0,0,16,0v-4c0-11,10.77-20,24-20s24,9,24,20-10.77,20-24,20a8,8,0,0,0-8,8v8a8,8,0,0,0,16,0v-.72c18.24-3.35,32-17.9,32-35.28C168,88.15,150.06,72,128,72Zm104,56A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path></svg>
                                </button>

                                <div 
                                    id="tooltip-info-person"
                                    role="tooltip"
                                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                                >
                                    Official name of the person to contact
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                            </div>
                            <input 
                                v-model="form.contact_person"
                                type="text"
                                placeholder="Brgy. Capt. Jarred Ruba"
                                class="w-full p-3 bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline focus:ring-1 focus:border-blue-200 peer dark:bg-[#2c2c2c]"
                                required
                            >
                        </div>

                        <!-- Contact Number -->
                        <div>
                            <div class="flex items-center gap-1">
                                <label for="category" class="block text-sm font-[Poppins] font-medium">Contact Number</label>
                                <button 
                                    data-tooltip-target="tooltip-info-number"
                                    type="button"
                                    class="text-blue-500 hover:text-blue-700"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="blue" viewBox="0 0 256 256"><path d="M140,180a12,12,0,1,1-12-12A12,12,0,0,1,140,180ZM128,72c-22.06,0-40,16.15-40,36v4a8,8,0,0,0,16,0v-4c0-11,10.77-20,24-20s24,9,24,20-10.77,20-24,20a8,8,0,0,0-8,8v8a8,8,0,0,0,16,0v-.72c18.24-3.35,32-17.9,32-35.28C168,88.15,150.06,72,128,72Zm104,56A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path></svg>
                                </button>

                                <div 
                                    id="tooltip-info-number"
                                    role="tooltip"
                                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                                >
                                    Official contact number for inquiries
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                            </div>
                            <input 
                                v-model="form.contact_number"
                                type="tel"
                                placeholder="+63 912 345 6789"
                                class="w-full p-3 bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline focus:ring-1 focus:border-blue-200 peer dark:bg-[#2c2c2c]"
                                required
                            >
                        </div>

                        <!-- Office Department -->
                        <div>
                            <div class="flex items-center gap-1">
                                <label for="category" class="block text-sm font-[Poppins] font-medium">Office/Department</label>
                                <button 
                                    data-tooltip-target="tooltip-info-office"
                                    type="button"
                                    class="text-blue-500 hover:text-blue-700"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="blue" viewBox="0 0 256 256"><path d="M140,180a12,12,0,1,1-12-12A12,12,0,0,1,140,180ZM128,72c-22.06,0-40,16.15-40,36v4a8,8,0,0,0,16,0v-4c0-11,10.77-20,24-20s24,9,24,20-10.77,20-24,20a8,8,0,0,0-8,8v8a8,8,0,0,0,16,0v-.72c18.24-3.35,32-17.9,32-35.28C168,88.15,150.06,72,128,72Zm104,56A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path></svg>
                                </button>

                                <div 
                                    id="tooltip-info-office"
                                    role="tooltip"
                                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                                >
                                    Select the relevant barangay office handling this matter
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                            </div>
                            <CustomMultiselect
                                v-model="form.departments"
                                :options="props.offices"
                                placeholder="Select offices/departments"
                                label="name"
                                track-by="id"
                            />
                        </div>
                    </div>

                    <!-- Conditional Category -->
                    <div v-if="showRequirements">
                        <h1 class="font-bold text-2xl mb-2 text-blue-500">Requirements</h1>
                        <div class="grid grid-cols-2 gap-4 mb-5">
                            <!-- Documents -->
                            <div>
                                <div class="flex items-center gap-1">
                                    <label for="category" class="block text-sm font-[Poppins] font-medium">Documents Needed</label>
                                    <button 
                                        data-tooltip-target="tooltip-info-documents"
                                        type="button"
                                        class="text-blue-500 hover:text-blue-700"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="blue" viewBox="0 0 256 256"><path d="M140,180a12,12,0,1,1-12-12A12,12,0,0,1,140,180ZM128,72c-22.06,0-40,16.15-40,36v4a8,8,0,0,0,16,0v-4c0-11,10.77-20,24-20s24,9,24,20-10.77,20-24,20a8,8,0,0,0-8,8v8a8,8,0,0,0,16,0v-.72c18.24-3.35,32-17.9,32-35.28C168,88.15,150.06,72,128,72Zm104,56A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path></svg>
                                    </button>

                                    <div 
                                        id="tooltip-info-documents"
                                        role="tooltip"
                                        class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                                    >
                                        Select documents needed for the event. Use "Other" for custom documents.
                                        <div class="tooltip-arrow" data-popper-arrow></div>
                                    </div>
                                </div>
                
                                <!-- Documents Multiselect -->
                                <CustomMultiselect
                                    v-model="form.requirements"
                                    :options="enhancedDocuments"
                                    placeholder="Select required documents"
                                    label="name"
                                    track-by="id"
                                    :multiple="true"
                                    :close-on-select="false"
                                    class="border-0 bg-inherit p-0"
                                />
                                
                                <!-- Other Document Input (Shows only when "Other" is selected) -->
                                <div v-if="hasOtherDocument" class="mt-3 transition-all duration-300">
                                    <label class="block text-sm font-[Poppins] font-medium text-gray-700 mb-1">
                                        Specify Other Document
                                    </label>
                                    <input 
                                        v-model="form.other_document"
                                        type="text"
                                        placeholder="Enter custom document name (e.g., Voter's ID, NSO Certificate, etc.)"
                                        class="w-full p-3 bg-white text-gray-500 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                        maxlength="100"
                                    >
                                    <p class="text-xs text-gray-500 mt-1">Enter the name of the custom document required</p>
                                </div>
                            </div>

                            <!--Participants Slots -->
                            <div>
                                <div class="flex items-center gap-1">
                                    <label for="category" class="block text-sm font-[Poppins] font-medium">Participants Slots</label>
                                    <button 
                                        data-tooltip-target="tooltip-info-participants"
                                        type="button"
                                        class="text-blue-500 hover:text-blue-700"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="blue" viewBox="0 0 256 256"><path d="M140,180a12,12,0,1,1-12-12A12,12,0,0,1,140,180ZM128,72c-22.06,0-40,16.15-40,36v4a8,8,0,0,0,16,0v-4c0-11,10.77-20,24-20s24,9,24,20-10.77,20-24,20a8,8,0,0,0-8,8v8a8,8,0,0,0,16,0v-.72c18.24-3.35,32-17.9,32-35.28C168,88.15,150.06,72,128,72Zm104,56A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path></svg>
                                    </button>

                                    <div 
                                        id="tooltip-info-participants"
                                        role="tooltip"
                                        class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                                    >
                                        Maximum number of participants allowed (leave empty for unlimited)
                                        <div class="tooltip-arrow" data-popper-arrow></div>
                                    </div>
                                </div>
                                <input 
                                    v-model="form.counts"
                                    type="number"
                                    min="1"
                                    placeholder="50"
                                    class="w-full p-3 bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline focus:ring-1 focus:border-blue-200 peer dark:bg-[#2c2c2c]"
                                >
                            </div>

                            <!-- Registration Deadline -->
                            <div>
                                <div class="flex items-center gap-1">
                                    <label for="category" class="block text-sm font-[Poppins] font-medium">Registration Deadline</label>
                                    <button 
                                        data-tooltip-target="tooltip-info-deadline"
                                        type="button"
                                        class="text-blue-500 hover:text-blue-700"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="blue" viewBox="0 0 256 256"><path d="M140,180a12,12,0,1,1-12-12A12,12,0,0,1,140,180ZM128,72c-22.06,0-40,16.15-40,36v4a8,8,0,0,0,16,0v-4c0-11,10.77-20,24-20s24,9,24,20-10.77,20-24,20a8,8,0,0,0-8,8v8a8,8,0,0,0,16,0v-.72c18.24-3.35,32-17.9,32-35.28C168,88.15,150.06,72,128,72Zm104,56A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path></svg>
                                    </button>

                                    <div 
                                        id="tooltip-info-deadline"
                                        role="tooltip"
                                        class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                                    >
                                        Last day to register for this program/event
                                        <div class="tooltip-arrow" data-popper-arrow></div>
                                    </div>
                                </div>
                                <input 
                                    v-model="formattedRegDeadline"
                                    type="date"
                                    class="w-full p-3 bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline focus:ring-1 focus:border-blue-200 peer dark:bg-[#2c2c2c]"
                                >
                            </div>

                            <!-- Additional Instructions -->
                            <div class="col-span-2">
                                <div class="flex items-center gap-1">
                                    <label for="category" class="block text-sm font-[Poppins] font-medium">Additional Instructions</label>
                                    <button 
                                        data-tooltip-target="tooltip-info-instruction"
                                        type="button"
                                        class="text-blue-500 hover:text-blue-700"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="blue" viewBox="0 0 256 256"><path d="M140,180a12,12,0,1,1-12-12A12,12,0,0,1,140,180ZM128,72c-22.06,0-40,16.15-40,36v4a8,8,0,0,0,16,0v-4c0-11,10.77-20,24-20s24,9,24,20-10.77,20-24,20a8,8,0,0,0-8,8v8a8,8,0,0,0,16,0v-.72c18.24-3.35,32-17.9,32-35.28C168,88.15,150.06,72,128,72Zm104,56A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path></svg>
                                    </button>

                                    <div 
                                        id="tooltip-info-instruction"
                                        role="tooltip"
                                        class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                                    >
                                        Addtional instruction for this program/event
                                        <div class="tooltip-arrow" data-popper-arrow></div>
                                    </div>
                                </div>
                                <textarea 
                                    v-model="form.instructions"
                                    rows="7"
                                    placeholder="Put additional instructions or reminders here..."
                                    class="w-full p-3 bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline focus:ring-1 focus:border-blue-200 peer dark:bg-[#2c2c2c]"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="mt-5 px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing">Updating Announcement...</span>
                            <span v-else>Update Announcement</span>
                        </button>
                    </div>
                </form>
            </section>
        </main>
    </AdminLayout>
</template>