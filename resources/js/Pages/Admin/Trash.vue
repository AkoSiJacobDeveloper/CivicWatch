<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2'

import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Result } from 'postcss';

const props = defineProps({
    reports: Object,
})

const urlParams = new URLSearchParams(window.location.search);
const fromParam = urlParams.get('from');
const toast = useToast();

const backUrl = computed(() => {
    switch(fromParam) {
        case 'pending':
            return '/admin/pending-reports'
        case 'in_progress':
            return '/admin/in-progress'
        case 'resolved':
            return '/admin/resolved-reports'
        case 'rejected':
            return '/admin/rejected-reports'
        case 'reports':
            return '/admin/reports'
        default:
            return '/admin/reports';
    }
})

const theads = [    
    { heading: 'Image', img: '/Images/SVG/image.svg' },
    { heading: 'Title', img: '/Images/SVG/text-t (1).svg' },
    { heading: 'Sender', img: '/Images/SVG/user.svg' },
    { heading: 'Type', img: '/Images/SVG/tag (1).svg' },
    { heading: 'Description', img: '/Images/SVG/align-left.svg' },
    { heading: 'Level', img: '/Images/SVG/flag.svg' },
    { heading: 'Status', img: '/Images/SVG/hourglass (1).svg' },
    { heading: 'Reason', img: '/Images/SVG/info.svg' },
    { heading: 'Action', img: '/Images/SVG/play.svg' }
];

const truncateText = (text, maxLength = 20) => {
    return text && text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
};

const capitalizeFirstLetter = (str) => {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
};

const viewDetails = (id) => {
    router.visit(`/admin/reports/${id}`);
};

const restoreReport = (id) => {
    if (!confirm('Restore this report?')) return;
    
    router.post(route('admin.reports.restore', { report: id }), {}, {
        onSuccess: () => {
            toast.success('Report Restored Successfully!');
        },
        onError: () => {
            toast.error('Problem Restoring Report');
        }
    });
};

const permanentDelete = async (id) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: 'Are you sure you want to delete this report? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel'
    });
    
    if(result.isConfirmed) {
        Swal.fire({
            title: 'Processing...',
            didOpen: () => Swal.showLoading(),
            allowOutsideClick: false,
            showConfirmButton: false,
            allowEscapeKey: false
        })
        router.delete(route('admin.reports.force-delete', { report: id}), {
            onSuccess: () => {
                Swal.close();
                toast.success('Report Permanently Deleted!');
            },
            onError: () => {
                toast.error('Problem Deleting Report');
            }
        })
    }
};
</script>

<template>
    <Head title="Manage Trash" />

    <AdminLayout>
        <main class="table-fixed flex flex-col gap-5 text-sm hide-scrollbar">
            <section class="flex  items-center gap-2">
                <div>
                    <Link :href="backUrl">
                        <img :src="'/Images/SVG/arrow-circle-left-fill (700).svg'" alt="Back Icon">
                    </Link>
                </div>
                <h1 class="font-semibold text-3xl font-[Poppins]">Trash</h1>
            </section>

            <section>
                <!-- Enhanced Table -->
                <div class="overflow-hidden shadow-container">
                    <!-- Table Header Section -->
                    <div class="px-4 py-4 border-b">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-800 font-[Poppins]">
                                Reports Overview
                            </h2>
                            <div class="text-sm text-gray-600">
                                Total: <span class="font-medium text-blue-600">{{ props.reports.total }}</span> reports
                            </div>
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div class="">
                        <table class="w-full">
                            <thead class="border-b-2 border-gray-200">
                                <tr class="grid grid-cols-[100px_1.5fr_1.5fr_1fr_1fr_1fr_1fr_1fr_1fr] items-center">
                                    <th v-for="(thead, index) in theads" :key="index" 
                                        class="px-3 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider font-[Poppins]">
                                        <div class="flex items-center gap-2">
                                            <img :src="thead.img" alt="Heading Icons" class="h-4 w-4 opacity-70">
                                            {{ thead.heading }}
                                        </div>
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="(report, index) in props.reports.data" :key="index" 
                                    class="grid grid-cols-[100px_1.5fr_1.5fr_1fr_1fr_1fr_1fr_1fr_1fr] hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300 group">
                
                                    <!-- Image Column -->
                                    <td class="px-3 py-4">
                                        <div class="relative w-16 h-16 rounded-lg overflow-hidden shadow-md group-hover:shadow-lg transition-shadow duration-200">
                                            <img :src="`/storage/${report.image}`" alt="Report Image" 
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200" />
                                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-200"></div>
                                        </div>
                                    </td>

                                    <!-- Title Column -->
                                    <td class="px-3 py-4">
                                        <div class="flex flex-col">
                                            <span
                                                :data-tooltip-target="`title-tooltip-${report.id}`"
                                                data-tooltip-placement="top"
                                                class="text-sm font-semibold text-gray-700 cursor-help group-hover:text-blue-500 transition-colors duration-200"
                                            >
                                                {{ truncateText(report.title) }}
                                            </span>
                                            <span class="text-xs text-gray-500 mt-1 group-hover:text-blue-500">ID: #{{ report.id }}</span>
                                        </div>

                                        <div
                                            :id="`title-tooltip-${report.id}`"
                                            role="tooltip"
                                            class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                                        >
                                            {{ report.title }}
                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>
                                    </td>

                                    <!-- Sender Column -->
                                    <td class="px-3 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-semibold text-gray-700 group-hover:text-blue-500">{{ report.sender_name }}</span>
                                            <span class="text-xs font-medium text-gray-500 group-hover:text-blue-500">{{ report.barangay_name }}, {{ report.sitio_name }}</span>
                                            <span class="text-xs text-gray-500 group-hover:text-blue-500">{{ report.contact_number}}</span>
                                        </div>
                                    </td>
                                    
                                    <!-- Type Column -->
                                    <td class="px-3 py-4">
                                        <span 
                                            :data-tooltip-target="`type-tooltip-${report.id}`"
                                            data-tooltip-placement="top"
                                            class="text-sm cursor-help transition-all duration-300 group-hover:text-blue-500"
                                        >
                                            {{ report.issue_type && report.issue_type.length > 30 ? report.issue_type.substring(0, 15) + '...' : report.issue_type }}                                     
                                        </span>
                                        <div 
                                            :id="`type-tooltip-${report.id}`" 
                                            role="tooltip" 
                                            class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                                        >
                                            {{ report.issue_type }}
                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>
                                    </td>

                                    <!-- Description Column -->
                                    <td class="px-3 py-4">
                                        <span 
                                            :data-tooltip-target="`desc-tooltip-${report.id}`"
                                            data-tooltip-placement="top"
                                            class="text-sm text-gray-900 cursor-help group-hover:text-blue-500 transition-colors duration-200"
                                        >
                                            {{  report.description.length > 30 ? report.description.substring(0, 30) + '...' : report.description    }}
                                        </span>
                                        <div 
                                            :id="`desc-tooltip-${report.id}`" 
                                            role="tooltip" 
                                            class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip w-48 text-center"
                                        >
                                            {{ report.description }}
                                            <div class="tooltip-arrow" data-popper-arrow></div>
                                        </div>
                                    </td>

                                    <!-- Priority Level -->
                                    <td class="px-3 py-4">
                                        <div class="flex items-center gap-2">
                                            <span
                                                :class="['text-xs font-semibold text-center rounded-full border py-2 px-4',
                                                    report.priority_level === 'low' ? 'text-green-800' : 
                                                    report.priority_level === 'medium' ? 'text-amber-800' :
                                                    report.priority_level === 'high' ? 'text-red-500' : '' 
                                            ]">
                                            
                                                {{ capitalizeFirstLetter(report.priority_level) }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Status Column -->
                                    <td class="px-3 py-4">
                                        <div class="flex justify-start">
                                            <span :class="[
                                                'inline-flex items-center px-4 py-2 rounded-full text-xs font-semibold border shadow-sm',
                                                report.status === 'Pending' ? 'bg-amber-100 text-amber-800 border-amber-200' :
                                                report.status === 'In Progress' ? 'bg-blue-100 text-blue-800 border-blue-200' :
                                                report.status === 'Resolved' ? 'bg-green-100 text-green-800 border-green-200' :
                                                report.status === 'Rejected' ? 'bg-red-100 text-red-800 border-red-200' : 
                                                report.status === 'Duplicate' ? 'bg-purple-100 text-purple-800 border-purple-200' : ''
                                            ]">
                                                <div :class="[
                                                    'w-1.5 h-1.5 rounded-full mr-2',
                                                    report.status === 'Pending' ? 'bg-amber-500 animate-pulse' :
                                                    report.status === 'In Progress' ? 'bg-blue-500 animate-pulse' :
                                                    report.status === 'Resolved' ? 'bg-green-500' :
                                                    report.status === 'Rejected' ? 'bg-red-500' : 
                                                    report.status === 'Duplicate' ? 'bg-purple-500' : ''
                                                ]"></div>
                                                {{ report.status }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-3 py-4">
                                        <span>{{ report.rejection_reason }}</span>
                                    </td>

                                    <!-- Note Column -->
                                    <!-- <td class="px-3 py-4">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="text-gray-500 text-xs font-semibold text-center rounded-full border py-2 px-4"
                                                v-if="report.status.toLowerCase() !== 'duplicate'"
                                            >
                                                Primary
                                            </span>

                                            <span
                                                class="text-gray-500 text-xs font-semibold text-center rounded-full border py-2 px-4"
                                                v-else 
                                            >
                                                Dup of #{{ report.duplicate_of_report_id }}
                                            </span>
                                        </div>
                                    </td> -->

                                    <!-- Action Column -->
                                    <td class="px-3 py-4">
                                        <div class="flex gap-2">
                                            <!-- Restore Button -->
                                            <button 
                                                @click="restoreReport(report.id)"
                                                class="flex items-center justify-center gap-2 py-2 text-xs font-medium text-green-700 bg-green-50 border border-green-300 rounded-full hover:bg-green-600 hover:text-white hover:border-green-600 focus:outline-none focus:ring-4 focus:ring-green-100 transition-all duration-200 shadow-sm hover:shadow-md group flex-grow"
                                            >
                                                <svg class="h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                            </button>
                                            
                                            <!-- Permanent Delete Button -->
                                            <button 
                                                @click="permanentDelete(report.id)"
                                                class="flex items-center justify-center gap-2 py-2 text-xs font-medium text-red-700 bg-red-50 border border-red-300 rounded-full hover:bg-red-600 hover:text-white hover:border-red-600 focus:outline-none focus:ring-4 focus:ring-red-100 transition-all duration-200 shadow-sm hover:shadow-md group flex-grow"
                                            >
                                                <svg class="h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Enhanced Pagination -->
                    <div class="bg-gray-50 px-3 py-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing <span class="font-medium">{{ props.reports.from || 0 }}</span> to 
                                <span class="font-medium">{{ props.reports.to || 0 }}</span> of 
                                <span class="font-medium">{{ props.reports.total || 0 }}</span> results
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <template v-for="link in props.reports.links" :key="link.label">
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
            </section>
        </main>
    </AdminLayout>
</template>