<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';
import {
    Listbox,
    ListboxButton,
    ListboxOptions,
    ListboxOption,
} from '@headlessui/vue'

const props = defineProps({
    issue_type: Object,
});

const priority_levels = [
    { id: 1, level: 'High Priority', place: 'high' },
    { id: 2, level: 'Medium Priority', place: 'medium' },
    { id: 3, level: 'Low Priority', place: 'low' },
];

const displayPriority = computed(() => {
    if (!createForm.priority_level) return 'Select Priority Level';

    return createForm.priority_level
    .split(' ')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join(' ');
});

const headings = [
    { title: 'Issue Name', icon: '/Images/SVG/folders.svg' },
    { title: 'Reports Count', icon: '/Images/SVG/hash.svg' },
    { title: 'Status', icon: '/Images/SVG/hourglass (1).svg' },
    { title: 'Level', icon: '/Images/SVG/flag.svg' },
    { title: 'Action', icon: '/Images/SVG/play.svg' }
]

const toast = useToast();
const showCreateModal = ref(false);
const editingCategory = ref(null);
const showEditModal = ref(false);
const selectedPriority = ref('all');

const createForm = useForm({
    name: '',
    priority_level: '', // Add priority level to create form
});

const editForm = useForm({
    name: '',
    active: true,
    priority_level: '',
});

function openCreateModal() {
    createForm.reset();
    createForm.priority_level = ''; 
    showCreateModal.value = true;
}

function openEditModal(issueType) {
    editingCategory.value = issueType;
    editForm.name = issueType.name;
    editForm.priority_level = issueType.priority_level;
    editForm.active = issueType.active;
    showEditModal.value = true;
}

function createIssueType() {
    Swal.fire({
        icon: 'info',
        title: 'Creating...',
        text: 'Please wait while we create the new issue type.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    createForm.post('/admin/issue-type', {
        preserveScroll: true,
        onSuccess: () => {
            Swal.close();
            showCreateModal.value = false;
            createForm.reset();
            toast.success('Issue created successfully!');
        },
        onError: (errors) => {
            Swal.close();
            let errorMessage = 'Something went wrong while creating the issue type.';
            
            if (errors.message) {
                errorMessage = errors.message;
            } else if (errors.name) {
                errorMessage = errors.name;
            }
            
            Swal.fire({
                title: 'Error!',
                text: errorMessage,
                icon: 'error',
                confirmButtonColor: '#d33'
            });
        }
    });
}

function updateIssueType() {
    Swal.fire({
        icon: 'info',
        title: 'Updating...',
        text: 'Please wait while we update the issue type.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    editForm.put(`/admin/issue-type/${editingCategory.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            Swal.close();
            showEditModal.value = false;
            editForm.reset();
            editingCategory.value = null;
            toast.success('Issue edited successfully!');
        },
        onError: (errors) => {
            Swal.close();
            let errorMessage = 'Something went wrong while updating the issue type.';
            
            if (errors.message) {
                errorMessage = errors.message;
            } else if (errors.name) {
                errorMessage = errors.name;
            } else if (errors.priority_level) {
                errorMessage = errors.priority_level;
            }
            
            Swal.fire({
                title: 'Error!',
                text: errorMessage,
                icon: 'error',
                confirmButtonColor: '#d33'
            });
        }
    });
}

function deleteCategory(issueType) {
    Swal.fire({
        title: 'Deactivate Category?',
        text: `Are you sure you want to deactivate this issue?
               This will hide it from the report form but keep existing reports intact.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, deactivate it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-lg shadow-xl',
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'info',
                title: 'Deactivating...',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const form = useForm();
            form.delete(`/admin/issue-type/${issueType.id}`, {
                preserveScroll: true,
                onFinish: () => {
                    Swal.close();
                },
                onSuccess: () => {
                    toast.success('Issue deactivated successfully!');
                },
                onError: () => {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Something went wrong while deactivating the category.',
                        icon: 'error',
                        confirmButtonColor: '#d33'
                    });
                }
            });
        }
    });
}
</script>

<template>
    <Head title="Manage Categories" />

    <AdminLayout>
        <main class="flex flex-col gap-7 text-sm">
            <div class="flex justify-between items-center">
                <div class="flex gap-3 items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <img 
                            :src="'/Images/SVG/circles-four (black).svg'" 
                            alt="icon"
                            class="h-8"
                        >
                    </div>
                    <div>
                        <h1 class="font-semibold text-3xl font-[Poppins]">Issues</h1>
                        <p class="text-gray-600 text-sm">Categorize and resolve what matters</p>
                    </div>
                </div>

                <div>
                    <button 
                        @click="openCreateModal"
                        class="group flex gap-2 text-gray-600 font-medium p-3 rounded-lg hover:bg-blue-700 hover:text-white transition-all duration-300 border border-gray-300 shadow-sm"
                    >
                        <img 
                            :src="'/Images/SVG/plus-circle.svg'" 
                            alt="Icon" 
                            class="h-5 block group-hover:hidden">
                        <img 
                            :src="'/Images/SVG/plus-circle (white).svg'" 
                            alt="Icon White" 
                            class="h-5 hidden group-hover:block">
                        Add New Issue
                    </button>
                </div>
            </div>

            <!-- Issue Type Table -->
            <div class="shadow-container px-5 py-5">
                <table class="w-full">
                    <thead class="">
                        <tr class="grid grid-cols-5">
                            <th v-for="heading in headings" :key="heading.id" class="text-left py-3 px-4 text-xs font-medium text-gray-600 uppercase tracking-wider font-[Poppins]">
                                <img :src="heading.icon" alt="Icon" class="h-4 w-4 inline-block mr-1">
                                {{ heading.title }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="issueType in props.issue_type.data" :key="issueType.id" class="grid grid-cols-5">
                            <td class="py-3 px-4 text-sm font-medium text-gray-700 flex items-center ">
                                {{ issueType.name }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-700 flex items-center">
                                {{ issueType.reports_count || 0 }} reports
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap flex items-center">
                                <span 
                                    :class="issueType.active ? 'bg-green-100 text-green-800 border-green-200' : 'bg-red-100 text-red-800'"
                                    class="inline-flex items-center px-4 py-2 rounded-full text-xs font-semibold border shadow-sm"
                                >
                                    {{ issueType.active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-700 flex items-center capitalize">
                                <span
                                    :class="[
                                        'text-xs font-semibold text-center rounded-full border py-2 px-4',
                                        issueType.priority_level === 'high' ? 'text-red-500' : 
                                        issueType.priority_level === 'medium' ? 'text-amber-800' :
                                        issueType.priority_level === 'low' ? 'text-green-800' : ''
                                    ]"
                                >
                                    {{ issueType.priority_level }}
                                </span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-sm font-medium flex gap-2 text-[#FAF9F6]">
                                <button 
                                    @click="openEditModal(issueType)"
                                    class="flex items-center bg-blue-500 p-3 rounded-md hover:bg-blue-700 transition-all duration-300"
                                >
                                    <img :src="'/Images/SVG/pen.svg'" alt="Icon" class="h-5 w-5">
                                </button>

                                <button 
                                    v-if="issueType.active"
                                    @click="deleteCategory(issueType)"
                                    class="bg-[#ef4545] hover:bg-[#b23333] transition-all duration-300 rounded flex justify-center items-center p-3"
                                >
                                    <img :src="'/Images/SVG/prohibit.svg'" alt="Icon" class="h-5 w-5">
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination (keep your existing pagination code) -->
                <div class="bg-gray-50 px-3 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing <span class="font-medium">{{ props.issue_type.from || 0 }}</span> to 
                            <span class="font-medium">{{ props.issue_type.to || 0 }}</span> of 
                            <span class="font-medium">{{ props.issue_type.total || 0 }}</span> results
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <template v-for="link in props.issue_type.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    :class="[
                                        'flex items-center justify-center w-10 h-10 text-sm font-medium rounded-lg border transition-all duration-200',
                                        link.active 
                                            ? 'bg-blue-600 text-white border-blue-600 shadow-lg' 
                                            : 'bg-white text-gray-500 border-gray-300 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-300'
                                    ]"
                                >
                                    <span v-if="link.label.includes('Previous')" class="transform">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 256 256">
                                            <path d="M165.66,202.34a8,8,0,0,1-11.32,11.32l-80-80a8,8,0,0,1,0-11.32l80-80a8,8,0,0,1,11.32,11.32L91.31,128Z"></path>
                                        </svg>
                                    </span>
                                    <span v-else-if="link.label.includes('Next')" class="transform">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 256 256">
                                            <path d="M181.66,133.66l-80,80a8,8,0,0,1-11.32-11.32L164.69,128,90.34,53.66a8,8,0,0,1,11.32-11.32l80,80A8,8,0,0,1,181.66,133.66Z"></path>
                                        </svg>
                                    </span>
                                    <span v-else v-html="link.label"></span>
                                </Link>
                                <span
                                    v-else
                                    class="flex items-center justify-center w-10 h-10 text-sm text-gray-400 cursor-not-allowed"
                                >
                                    <span v-if="link.label.includes('Previous')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 256 256">
                                            <path d="M165.66,202.34a8,8,0,0,1-11.32,11.32l-80-80a8,8,0,0,1,0-11.32l80-80a8,8,0,0,1,11.32,11.32L91.31,128Z"></path>
                                        </svg>
                                    </span>
                                    <span v-else-if="link.label.includes('Next')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 256 256">
                                            <path d="M181.66,133.66l-80,80a8,8,0,0,1-11.32-11.32L164.69,128,90.34,53.66a8,8,0,0,1,11.32-11.32l80,80A8,8,0,0,1,181.66,133.66Z"></path>
                                        </svg>
                                    </span>
                                    <span v-else v-html="link.label"></span>
                                </span>
                            </template> 
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create Modal - Updated with Priority Level -->
            <div v-if="showCreateModal" class="fixed z-[50] inset-0 backdrop-blur-sm bg-white/20 flex justify-center items-center">
                <div class="w-full max-w-xl mx-4 rounded-lg shadow-lg bg-white">
                    <!-- Header -->
                    <div class="flex justify-between items-center p-5 border-b bg-blue-700 rounded-t-lg">
                        <div>
                            <h3 class="text-lg font-semibold font-[Poppins] text-white">Add New Issue</h3>
                        </div>
                        <!-- Close Button --> 
                        <div class="flex justify-end items-center">
                            <img 
                                @click="showCreateModal = false" 
                                :src="'/Images/SVG/x (white).svg'" 
                                alt="Close Icon" 
                                class="w-5 h-5 hover:cursor-pointer">
                        </div>
                    </div>
                    <div class="p-5">
                        <form @submit.prevent="createIssueType">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Category Name</label>
                                <input 
                                    v-model="createForm.name"
                                    type="text"
                                    placeholder="e.g., Water Supply Issues"
                                    class="w-full border border-gray-300 rounded px-3 py-2"
                                    required
                                />
                                <div v-if="createForm.errors.name" class="text-red-500 text-sm mt-1">
                                    {{ createForm.errors.name }}
                                </div>
                            </div>
                            <div class="mb-4 relative">
                                <Listbox v-model="createForm.priority_level">
                                    <ListboxButton
                                class="flex justify-between items-center text-left p-4 w-full text-base bg-white text-gray-900 rounded-lg border border-gray-300 focus:outline-none focus:ring-0 focus:border-blue-600"
                                    >
                                        <span class="block truncate" :class="{ 'text-gray-500': !createForm.priority_level }">
                                            {{ displayPriority }}
                                        </span>
                                        <span class="pointer-events-none flex items-center">
                                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </ListboxButton>

                                    <ListboxOptions
                                        class="absolute z-50 mt-1 w-full bg-white rounded-lg shadow-lg border border-gray-300 max-h-56 overflow-y-auto"
                                    >
                                        <ListboxOption
                                            v-for="priority_level in priority_levels"
                                            :key="priority_level.id"
                                            :value="priority_level.place"
                                            v-slot="{ active, selected }"
                                            class="cursor-pointer select-none relative py-2 pl-3 pr-9"
                                            :class="active ? 'bg-blue-100 text-blue-900' : 'text-gray-900'"
                                        >
                                            <span class="block truncate" :class="selected ? 'font-medium' : 'font-normal'">
                                                {{ priority_level.level }}
                                            </span>
                                            <span v-if="selected" class="absolute inset-y-0 right-0 flex items-center pr-4 text-blue-600">
                                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </ListboxOption> 
                                    </ListboxOptions>
                                </Listbox>

                                <!-- <label class="block text-sm font-medium text-gray-700 mb-2">Priority Level</label>
                                <select 
                                    v-model="createForm.priority_level"
                                    class="w-full border border-gray-300 rounded px-3 py-2"
                                    required
                                >
                                    <option value="high">High Priority</option>
                                    <option value="medium">Medium Priority</option>
                                    <option value="low">Low Priority</option>
                                </select> -->
                                <div v-if="createForm.errors.priority_level" class="text-red-500 text-sm mt-1">
                                    {{ createForm.errors.priority_level }}
                                </div>
                            </div>
                            <div class="flex justify-end space-x-2">
                                <button 
                                    type="button"
                                    @click="showCreateModal = false"
                                    class="px-4 py-2 text-gray-600 border border-gray-300 rounded hover:bg-gray-50"
                                >
                                    Cancel
                                </button>
                                <button 
                                    type="submit"
                                    :disabled="createForm.processing"
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                                >
                                    Create
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Modal (keep your existing edit modal) -->
            <div v-if="showEditModal" class="fixed z-[] inset-0 backdrop-blur-sm bg-white/20 flex justify-center items-center">
                <div class="w-full max-w-xl mx-4 rounded-lg shadow-lg bg-white">
                    <!-- Header -->
                    <div class="flex justify-between items-center p-5 border-b bg-blue-700 rounded-t-lg">
                        <div>
                            <h3 class="text-lg font-semibold font-[Poppins] text-white">Edit Issue</h3>
                        </div>
                        <!-- Close Button --> 
                        <div class="flex justify-end items-center">
                            <img 
                                @click="showEditModal = false" 
                                :src="'/Images/SVG/x (white).svg'" 
                                alt="Close Icon" 
                                class="w-5 h-5 hover:cursor-pointer">
                        </div>
                    </div>
                    
                    <div class="p-5">
                        <form @submit.prevent="updateIssueType">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Category Name</label>
                                <input 
                                    v-model="editForm.name"
                                    type="text"
                                    class="w-full border border-gray-300 rounded px-3 py-2"
                                    required
                                />
                                <div v-if="editForm.errors.name" class="text-red-500 text-sm mt-1">
                                    {{ editForm.errors.name }}
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Priority Level</label>
                                <select 
                                    v-model="editForm.priority_level"
                                    class="w-full border border-gray-300 rounded px-3 py-2"
                                    required
                                >
                                    <option value="high">High Priority</option>
                                    <option value="medium">Medium Priority</option>
                                    <option value="low">Low Priority</option>
                                </select>
                                <div v-if="editForm.errors.priority_level" class="text-red-500 text-sm mt-1">
                                    {{ editForm.errors.priority_level }}
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input 
                                        v-model="editForm.active"
                                        type="checkbox"
                                        class="mr-2"
                                    />
                                    <span class="text-sm text-gray-700">Active (visible in report form)</span>
                                </label>
                            </div>
                            <div class="flex justify-end space-x-2">
                                <button 
                                    type="button"
                                    @click="showEditModal = false"
                                    class="px-4 py-2 text-gray-600 border border-gray-300 rounded hover:bg-gray-50"
                                >
                                    Cancel
                                </button>
                                <button 
                                    type="submit"
                                    :disabled="editForm.processing"
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                                >
                                    Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </AdminLayout>
</template>