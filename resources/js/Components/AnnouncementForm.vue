<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { useToast } from 'vue-toastification';
import { initTooltips } from 'flowbite';
import { Listbox, ListboxButton, ListboxOption, ListboxOptions } from '@headlessui/vue';
// import Multiselect from 'vue-multiselect'
import CustomMultiselect from './CustomMultiselect.vue';

const props = defineProps({
    announcementCategories: Array,
    audiences: Array,
    activePuroks: Array,
    offices: Array,
    documents: Array,
})

const toast = useToast();
const levels = ['high', 'medium', 'low'];
const isFeatureds = ['yes', 'no']

const form = useForm({
    title: '',
    type: '',
    level: '',
    is_pinned: '',
    content: '',
    image: null,
    publish_at: '',
    event_date: '',
    expiry_date: '',
    contact_person: '',
    contact_number: '',
    purok: 'all',
    attachments: [],
    audiences: [], 
    departments: [],
    requirements: [],
    instructions: '',
    counts: '',
    reg_deadline: '',
    other_document: '',
    
    // category_id: '',
    
})

const purokOptions = [
    { id: 'all', name: 'All Purok' },
    ...props.activePuroks
]

const getPurokName = (id) => {
    if (id === 'all') return 'All Purok'
    const purok = props.activePuroks.find(p => p.id === id)
    return purok ? purok.name : 'Select purok area'
}

const handleImageChange = (e) => {
    form.image = e.target.files[0]
}

const handleAttachmentsChange = (e) => {
    form.attachments = Array.from(e.target.files)
}

const getCategoryName = (id) => {
    const category = props.announcementCategories.find(cat => cat.id === id)
    return category ? category.name : 'Select an issue type'
}

const imageInputRef = ref(null)
const attachmentsInputRef = ref(null)

const submitForm = () => {
    form.transform((data) => {
        // Helper function to extract IDs
        const extractIds = (array) => {
            if (!array) return [];
            return array.map(item => item?.id || item).filter(id => id && id !== 'other');
        };

        return {
            ...data,
            audiences: extractIds(data.audiences),
            departments: extractIds(data.departments),
            requirements: extractIds(data.requirements),
            other_document: data.other_document,
            image: data.image
        };
    }).post(route('admin.announcement.create'), {
        preserveScroll: true,
        onSuccess: () => {
            // Clear file inputs using refs
            if (imageInputRef.value) imageInputRef.value.value = ''
            if (attachmentsInputRef.value) attachmentsInputRef.value.value = ''
            
            // Clear form data
            form.image = null
            form.attachments = []
            form.reset()
            toast.success('Announcement created successfully!');
        },
        onError: (errors) => {
            console.log('Errors:', errors);
        }
    });
}
const removeAttachment = (index) => {
    // Remove the file from the attachments array
    form.attachments.splice(index, 1)
    
    // If you want to also update the file input to reflect the changes
    // This is optional but provides better UX
    updateFileInput()
}

const updateFileInput = () => {
    // This creates a new FileList (read-only, so we can't modify directly)
    // But the form.attachments array is what gets sent to the server
    if (attachmentsInputRef.value) {
        // Reset the input to clear visual state
        attachmentsInputRef.value.value = ''
    }
}

const requirementCategories = [
    'Education & Youth', 'Social Services', 'Community Events', 'Business & Livelihood',
    'Health Services', 'Senior Citizen Programs', 'PWD Programs', 'Sports Tournaments', 
    'Training & Workshops'
]

const showRequirements = computed(() => {
    const category = props.announcementCategories.find(cat => cat.id === form.type);
    return category && requirementCategories.includes(category.name);
});

const enhancedDocuments = computed(() => {
    // Combine predefined documents with "Other" option
    return [
        ...props.documents, // Your existing documents from backend
        { id: 'other', name: 'Other (Specify below)', document_type: 'Custom' }
    ];
});
const hasOtherDocument = computed(() => {
    console.log('Checking hasOtherDocument...');
    
    if (!form.requirements || form.requirements.length === 0) {
        return false;
    }
    
    // Try multiple ways to detect "Other"
    const hasOther = form.requirements.some(req => {
        // Method 1: Check by ID
        if (req?.id === 'other') return true;
        
        // Method 2: Check by name
        if (req?.name === 'Other (Specify below)') return true;
        
        // Method 3: Check if it's a string
        if (typeof req === 'string' && req === 'other') return true;
        
        // Method 4: Check any property that might indicate "Other"
        if (req && typeof req === 'object') {
            return Object.values(req).some(val => 
                val === 'other' || val === 'Other (Specify below)'
            );
        }
        
        return false;
    });
    
    console.log('hasOtherDocument final result:', hasOther);
    return hasOther;
});
onMounted(() => {
    initTooltips();
});
</script>

<template>
    <form @submit.prevent="submitForm">
        <h1 class="font-bold text-2xl mb-2 text-blue-500">Basic Information</h1>
        <div class="grid grid-cols-2 gap-4 mb-5">
            <!-- Title -->
            <div>
                <div  class="flex items-center gap-1">
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
                    class="w-full p-3 bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline-none focus:ring-0 focus:border-gray-200 peer dark:bg-[#2c2c2c]"
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
                    class="relative"
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
                        class=" w-full text-base bg-white text-gray-900 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-[#2c2c2c] dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 resize-none"
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
                        class="w-full  text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-500 file:text-white hover:file:bg-blue-600 cursor-pointer"
                        accept="image/*"
                    >
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
        
        <h1 class="font-bold text-2xl mb-2 text-blue-500">Schedule & Dates</h1>
        <div class="grid grid-cols-2 gap-4 mb-5">
            <!-- Publish Date and Time -->
            <div>
                <div class="flex items-center gap-1">
                    <label for="category" class="block text-sm font-[Poppins] font-medium">Publish Date & Time</label>
                    <button 
                        data-tooltip-target="tooltip-info-publish"
                        type="button"
                        class="text-blue-500 hover:text-blue-700"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="blue" viewBox="0 0 256 256"><path d="M140,180a12,12,0,1,1-12-12A12,12,0,0,1,140,180ZM128,72c-22.06,0-40,16.15-40,36v4a8,8,0,0,0,16,0v-4c0-11,10.77-20,24-20s24,9,24,20-10.77,20-24,20a8,8,0,0,0-8,8v8a8,8,0,0,0,16,0v-.72c18.24-3.35,32-17.9,32-35.28C168,88.15,150.06,72,128,72Zm104,56A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path></svg>
                    </button>

                    <div 
                        id="tooltip-info-publish"
                        role="tooltip"
                        class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                    >
                        When the announcement becomes visible to residents
                        <div class="tooltip-arrow" data-popper-arrow></div>
                    </div>
                </div>
                <input
                    v-model="form.publish_at"
                    :min="today"
                    type="datetime-local"
                    class="w-full p-3 bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline-none focus:ring-0 focus:border-gray-200 peer dark:bg-[#2c2c2c]"
                    required
                    
                >
            </div>

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
                    v-model="form.event_date"
                    type="datetime-local"
                    class="w-full p-3 bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline-none focus:ring-0 focus:border-gray-200 peer dark:bg-[#2c2c2c]"
                    required
                >
            </div>

            <!-- Expiry Date -->
            <div>
                <div class="flex items-center gap-1">
                    <label for="category" class="block text-sm font-[Poppins] font-medium">Expiry Date</label>
                    <button 
                        data-tooltip-target="tooltip-info-expiry"
                        type="button"
                        class="text-blue-500 hover:text-blue-700"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="blue" viewBox="0 0 256 256"><path d="M140,180a12,12,0,1,1-12-12A12,12,0,0,1,140,180ZM128,72c-22.06,0-40,16.15-40,36v4a8,8,0,0,0,16,0v-4c0-11,10.77-20,24-20s24,9,24,20-10.77,20-24,20a8,8,0,0,0-8,8v8a8,8,0,0,0,16,0v-.72c18.24-3.35,32-17.9,32-35.28C168,88.15,150.06,72,128,72Zm104,56A104,104,0,1,1,128,24,104.11,104.11,0,0,1,232,128Zm-16,0a88,88,0,1,0-88,88A88.1,88.1,0,0,0,216,128Z"></path></svg>
                    </button>

                    <div 
                        id="tooltip-info-expiry"
                        role="tooltip"
                        class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                    >
                        When the announcement automatically archives (optional)
                        <div class="tooltip-arrow" data-popper-arrow></div>
                    </div>
                </div>
                <input 
                    v-model="form.expiry_date"
                    type="datetime-local"
                    class="w-full p-3 bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline-none focus:ring-0 focus:border-gray-200 peer dark:bg-[#2c2c2c]"
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

            <!--- Specific Areas/Purok (Optional)-->
            <div>
                <div class="flex items-center gap-1">
                    <label for="category" class="block text-sm font-[Poppins] font-medium">Specific Areas/Purok</label>
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
                <Listbox
                    v-model="form.purok"
                    as="div"
                    class="relative"
                >
                    <ListboxButton
                        class="flex justify-between items-center text-left p-3 w-full text-base bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline-none focus:ring-0 focus:border-gray-200 peer dark:bg-[#2c2c2c]"
                        required
                    >
                        {{ form.purok ? (form.purok === 'all' ? 'All Purok' : purokOptions.find(p => p.id === form.purok)?.name) : 'Select purok area' }}


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
                            {{ purok.name}}

                            
                        </ListboxOption>
                    </ListboxOptions>
                </Listbox>
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
                    class="w-full p-3 bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline-none focus:ring-0 focus:border-gray-200 peer dark:bg-[#2c2c2c]"
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
                    class="w-full p-3 bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline-none focus:ring-0 focus:border-gray-200 peer dark:bg-[#2c2c2c]"
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
                        class="w-full p-3 bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline-none focus:ring-0 focus:border-gray-200 peer dark:bg-[#2c2c2c]"
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
                        v-model="form.reg_deadline"
                        type="date"
                        class="w-full p-3 bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline-none focus:ring-0 focus:border-gray-200 peer dark:bg-[#2c2c2c]"
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
                        placeholder="E.g., Bring original and 2 photocopies, Special instructions..."
                        class="w-full p-3 bg-white text-gray-500 rounded-lg border border-gray-300 dark:text-gray-400 dark:border-gray-700 focus:outline-none focus:ring-0 focus:border-gray-200 peer dark:bg-[#2c2c2c]"
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
                <span v-if="form.processing">Creating Announcement...</span>
                <span v-else>Create Announcement</span>
            </button>
        </div>
    </form>
</template>
