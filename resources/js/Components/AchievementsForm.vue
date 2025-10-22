<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { initTooltips } from 'flowbite';
import { useToast } from 'vue-toastification'
import { Listbox, ListboxButton, ListboxOption, ListboxOptions } from '@headlessui/vue';

const toast = useToast();

const props = defineProps({
    categories: Array,
    locations: Array,
})

const form = useForm({
    title: '',
    category_id: '',
    summary: '',
    featured_image: null,
    date_of_achievement: '',
    location_id: 1,
    document_attachments: [],
    gallery_images: [],
    content: '',
})

// Refs for file inputs
const imageInputRef = ref(null);
const attachmentsInputRef = ref(null);
const galleryInputRef = ref(null);  

// Add these refs for image previews
const featuredImagePreview = ref(null);
const galleryPreviews = ref([]);

const selectedLocationName = computed(() => {
    if (!form.location_id) return 'Cabulijan - All Purok'
    
    const location = props.locations.find(loc => loc.id === form.location_id)
    return location ? location.name : 'Cabulijan - All Purok'
})

const selectedCategory = computed(() => {
    if (!form.category_id) return 'Select a category'
    
    const category = props.categories.find(cat => cat.id === form.category_id)
    return category ? category.name : 'Select a category'
})

// Handle featured image with preview
const handleImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.featured_image = file;
        
        // Create preview for featured image
        const reader = new FileReader();
        reader.onload = (e) => {
            featuredImagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

// Remove featured image
const removeFeaturedImage = () => {
    form.featured_image = null;
    featuredImagePreview.value = null;
    if (imageInputRef.value) imageInputRef.value.value = '';
}

const handleGalleryImagesChange = (event) => {
    const files = Array.from(event.target.files);
    
    // Create preview URLs for each new file
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
            galleryPreviews.value.push({
                url: e.target.result,
                file: file
            });
        };
        reader.readAsDataURL(file);
    });
    
    // Add files to form
    form.gallery_images = [...form.gallery_images, ...files];
}

// Remove single gallery image
const removeGalleryImage = (index) => {
    form.gallery_images.splice(index, 1);
    galleryPreviews.value.splice(index, 1);
}

// Handle multiple attachments
const handleAttachmentsChange = (event) => {
    const files = Array.from(event.target.files);
    form.document_attachments = [...form.document_attachments, ...files];
}

// Remove single attachment
const removeAttachment = (index) => {
    form.document_attachments.splice(index, 1);
}

// Submit form
const submitForm = () => {
    form.post(route('admin.store.achievements'), {
        onSuccess: () => {
            toast.success('Achievement recorded successfully!');
            form.reset();
            featuredImagePreview.value = null;
            galleryPreviews.value = [];
            if (imageInputRef.value) imageInputRef.value.value = '';
            if (attachmentsInputRef.value) attachmentsInputRef.value.value = '';
            if (galleryInputRef.value) galleryInputRef.value.value = '';
        },
        preserveScroll: true,
    });
}

onMounted(() => {
    initTooltips();
});
</script>

<template>
    <form @submit.prevent="submitForm">
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
                        The title of the achievement
                        <div class="tooltip-arrow" data-popper-arrow></div>
                    </div>
                </div>

                <input 
                    v-model="form.title"
                    type="text"
                    placeholder="Successful Hall Constructions"
                    class="w-full p-3 bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline focus:ring-1 focus:border-blue-200 peer dark:bg-[#2c2c2c]"
                >
            </div>

            <!-- Category Dropdown -->
            <div>
                <div class="flex items-center gap-1">
                    <label for="title" class="block text-sm font-[Poppins] font-medium">Category</label>
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
                        Category of the achievement
                        <div class="tooltip-arrow" data-popper-arrow></div>
                    </div>
                </div>
                <Listbox as="div" class="relative" v-model="form.category_id">
                    <ListboxButton 
                        class="flex justify-between items-center text-left p-3 w-full text-base bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline-none focus:ring-0 focus:border-gray-200 peer dark:bg-[#2c2c2c]"
                    >
                        {{ selectedCategory }}

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
                            v-for="category in categories" 
                            :key="category.id" 
                            :value="category.id"
                            class="dark:bg-[#2c2c2c] px-4 py-2 hover:bg-blue-100 transition-all duration-100 cursor-pointer"
                        >
                            {{ category.name }}
                        </ListboxOption>
                    </ListboxOptions>
                </Listbox>
            </div>
        </div>

        <!-- Summary -->
        <div class="mb-4">
            <div class="flex items-center gap-1">
                <label for="title" class="block text-sm font-[Poppins] font-medium">Summary</label>
                <button 
                    data-tooltip-target="tooltip-info-summary"
                    type="button"
                    class="text-blue-500 hover:text-blue-700"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="blue" viewBox="0 0 256 256"><path d="M140,180a12,12,0,1,1-12-12A12,12,0,0,1,140,180ZM128,72c-22.06,0-40,16.15-40,36v4a8,8,0,0,0,16,0v-4c0-11,10.77-20,24-20s24,9,24,20-10.77,20-24,20a8,8,0,0,0-8,8v8a8,8,0,0,0,16,0v-.72c18.24-3.35,32-17.9,32-35.28C168,88.15,150.06,72,128,72Zm104,56A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path></svg>
                </button>

                <div 
                    id="tooltip-info-summary"
                    role="tooltip"
                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                >
                    Clear, short summary of the achievement
                    <div class="tooltip-arrow" data-popper-arrow></div>
                </div>
            </div>

            <textarea 
                v-model="form.summary"
                rows="3"
                placeholder="Write the achievement summary here..."
                class=" w-full text-base bg-white text-gray-900 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-200 dark:bg-[#2c2c2c] dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 resize-none"
                required
            ></textarea>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-5">
            <!-- Image -->
            <div>
                <div class="flex items-center gap-1">
                    <label for="category" class="block text-sm font-[Poppins] font-medium">Featured Image</label>
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
                    <!-- Featured Image Preview -->
                    <div v-if="featuredImagePreview" class="mt-2 relative">
                        <img 
                            :src="featuredImagePreview" 
                            alt="Featured image preview"
                            class="w-full h-48 object-cover rounded-lg border"
                        >
                        <button 
                            type="button"
                            @click="removeFeaturedImage"
                            class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm hover:bg-red-600"
                            title="Remove image"
                        >
                            ×
                        </button>
                    </div>
                </div>
            </div>

            <!-- Date of Achievement -->
            <div>
                <div class="flex items-center gap-1">
                    <label for="category" class="block text-sm font-[Poppins] font-medium">Date of Achievement</label>
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
                        Date when the achievement acquire
                        <div class="tooltip-arrow" data-popper-arrow></div>
                    </div>
                </div>
                <input 
                    v-model="form.date_of_achievement"
                    type="date"
                    class="w-full p-3 bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline focus:ring-1 focus:border-blue-200 peer dark:bg-[#2c2c2c]"
                    required
                >
            </div>

            <!-- Location -->
            <div>
                <div class="flex items-center gap-1">
                    <label class="block text-sm font-[Poppins] font-medium">Location</label>
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
                <Listbox as="div" class="relative" v-model="form.location_id">
                    <ListboxButton 
                        class="flex justify-between items-center text-left p-3 w-full text-base bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline-none focus:ring-0 focus:border-gray-200 peer dark:bg-[#2c2c2c]"
                    >
                        {{ selectedLocationName }}

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
                            v-for="location in locations" 
                            :key="location.id" 
                            :value="location.id"
                            class="dark:bg-[#2c2c2c] px-4 py-2 hover:bg-blue-100 transition-all duration-100 cursor-pointer"
                        >
                            {{ location.name }}
                        </ListboxOption>
                    </ListboxOptions>
                </Listbox>
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
                    <ul class="text-sm text-gray-600">
                        <li 
                            v-for="(file, index) in form.document_attachments" 
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

            <!-- Gallery Images -->
            <div>
                <div class="flex items-center gap-1">
                    <label for="category" class="block text-sm font-[Poppins] font-medium">
                        Gallery Images {{ galleryPreviews.length > 0 ? `(${galleryPreviews.length})` : '' }}
                    </label>
                    <button 
                        data-tooltip-target="tooltip-info-gallery"
                        type="button"
                        class="text-blue-500 hover:text-blue-700"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="blue" viewBox="0 0 256 256"><path d="M140,180a12,12,0,1,1-12-12A12,12,0,0,1,140,180ZM128,72c-22.06,0-40,16.15-40,36v4a8,8,0,0,0,16,0v-4c0-11,10.77-20,24-20s24,9,24,20-10.77,20-24,20a8,8,0,0,0-8,8v8a8,8,0,0,0,16,0v-.72c18.24-3.35,32-17.9,32-35.28C168,88.15,150.06,72,128,72Zm104,56A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path></svg>
                    </button>

                    <div 
                        id="tooltip-info-gallery"
                        role="tooltip"
                        class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                    >
                        Add multiple photos to create a gallery (optional)
                        <div class="tooltip-arrow" data-popper-arrow></div>
                    </div>
                </div>
                <div class="p-1 border bg-white rounded-lg">
                    <input 
                        ref="galleryInputRef"
                        type="file"
                        multiple
                        @change="handleGalleryImagesChange"
                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-500 file:text-white hover:file:bg-green-600 cursor-pointer"
                        accept="image/*"
                    >
                    <!-- Preview Grid -->
                    <div class="grid grid-cols-3 gap-2 mt-2" v-if="galleryPreviews.length > 0">
                        <div 
                            v-for="(preview, index) in galleryPreviews" 
                            :key="index"
                            class="relative border rounded-lg overflow-hidden"
                        >
                            <img 
                                :src="preview.url" 
                                :alt="'Gallery image ' + (index + 1)"
                                class="w-full h-24 object-cover"
                            >
                            <button 
                                type="button"
                                @click="removeGalleryImage(index)"
                                class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600"
                                title="Remove image"
                            >
                                ×
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="col-span-2">
                <div class="flex items-center gap-1">
                    <label for="category" class="block text-sm font-[Poppins] font-medium">Achievement Description</label>
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
                        Provide detailed information using the text editor about the achievement
                        <div class="tooltip-arrow" data-popper-arrow></div>
                    </div>
                </div>
                <div>
                    <textarea 
                        v-model="form.content"
                        rows="7"
                        placeholder="Write the achievement description here..."
                        class=" w-full text-base bg-white text-gray-900 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-200 dark:bg-[#2c2c2c] dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 resize-none"
                        required
                    ></textarea>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end mt-6">
            <button 
                type="submit" 
                :disabled="form.processing"
                class="mt-5 px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
                <span v-if="form.processing">Creating...</span>
                <span v-else>Create Achievement</span>
            </button>
        </div>
    </form>
</template>