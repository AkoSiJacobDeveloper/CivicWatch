<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    issue_type: Object,
});

const headings = [
    { title: 'Issue Name', icon: '/Images/SVG/folders.svg' },
    { title: 'Reports Count', icon: '/Images/SVG/hash.svg' },
    { title: 'Status', icon: '/Images/SVG/hourglass (1).svg' },
    { title: 'Action', icon: '/Images/SVG/play.svg' }
]

const showCreateModal = ref(false);
const editingCategory = ref(null);
const showEditModal = ref(false);

const createForm = useForm({
    name: '',
});

const editForm = useForm({
    name: '',
    active: true,
});

function openCreateModal() {
    createForm.reset();
    showCreateModal.value = true;
}

function openEditModal(issueType) {
    editingCategory.value = issueType;
    editForm.name = issueType.name;
    editForm.active = issueType.active;
    showEditModal.value = true;
}

function createIssueType() {
    createForm.post('/admin/issue-type', {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        }
    });
}

function updateIssueType() {
    editForm.put(`/admin/issue-type/${editingCategory.value.id}`, {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
            editingCategory.value = null;
        }
    });
}

function deleteCategory(issueType) {
    if (confirm(`Are you sure you want to deactivate "${issueType.name}"? This will hide it from the report form but keep existing reports intact.`)) {
        useForm().delete(`/admin/issue-type/${issueType.id}`);
    }
}
</script>

<template>
    <Head title="Manage Categories" />

    <AdminLayout>
        <main class="flex flex-col gap-7 text-sm">
            <div class="flex justify-between items-center">
                <div class="flex gap-1 items-center">
                    <img 
                        :src="'/Images/SVG/circles-four (black).svg'" 
                        alt="icon"
                        class="h-8"
                    >
                    <h1 class="font-semibold text-3xl font-[Poppins] my-3">Issues</h1>
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
                        Add New Category
                    </button>
                </div>
            </div>

            <!-- Issue Type Table -->
            <div class="shadow-container px-5 py-5">
                <table class="w-full">
                    <thead class="">
                        <tr class="grid grid-cols-4">
                            <th v-for="heading in headings" :key="heading.id" class="text-start py-3 px-4 text-xs font-medium text-gray-600 uppercase tracking-wider font-[Poppins]">
                                <img :src="heading.icon" alt="Icon" class="h-4 w-4 inline-block mr-1">
                                {{ heading.title }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="issueType in props.issue_type.data" :key="issueType.id" class="grid grid-cols-4">
                            <td class="py-3 px-4 text-sm font-medium text-gray-700 flex items-center">
                                {{ issueType.name }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-700 flex items-center">
                                {{ issueType.reports_count || 0 }} reports
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap flex items-center">
                                <span 
                                    :class="issueType.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                >
                                    {{ issueType.active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-sm font-medium flex gap-2 text-[#FAF9F6]">
                                <button 
                                    @click="openEditModal(issueType)" 
                                    class="bg-blue-500 hover:bg-blue-700 flex-1 p-2 transition-all duration-300 rounded flex justify-center items-center gap-1"
                                >
                                    <img :src="'/Images/SVG/pen.svg'" alt="Icon" class="h-4 w-4">
                                    Edit
                                </button>
                                <button 
                                    v-if="issueType.active"
                                    @click="deleteCategory(issueType)"
                                    class="bg-[#ef4545] hover:bg-[#b23333] flex-1 p-1 transition-all duration-300 rounded flex justify-center items-center gap-1"
                                >
                                    <img :src="'/Images/SVG/prohibit.svg'" alt="Icon" class="h-4 w-4 inline-block">
                                    Deactivate
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
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

            <!-- Create Modal -->
            <div v-if="showCreateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white p-6 rounded-lg w-96">
                    <h2 class="text-lg font-bold mb-4">Add New Category</h2>
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

            <!-- Edit Modal -->
            <div v-if="showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white p-6 rounded-lg w-96">
                    <h2 class="text-lg font-bold mb-4">Edit Category</h2>
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
        </main>
    </AdminLayout>
</template>