<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useToast } from 'vue-toastification';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import DeleteModal from '@/Components/DeleteModal.vue';
import RejectingModal from '@/Components/RejectingModal.vue';
import ConfirmRejectModal from '@/Components/ConfirmRejectModal.vue';

const props = defineProps({ report: Object })
const toast = useToast();
const page = usePage();

const urlParams = new URLSearchParams(window.location.search);
const fromParam = urlParams.get('from');

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

const approveReport = (id) => {
    router.put(route('report.approve', id), {}, {
        onSuccess: () => {
            toast.success(page.props.flash.success || 'Report approved successfully!');
        }
    });
};

const resolveReport = (id) => {
    router.put(route('report.resolved', id), {}, {
        onSuccess: () => {
            toast.success(page.props.flash.success || 'Report resolved successfully!');
        }
    });
};

function capitalize(word) {
    if (!word) return '';
    return word.charAt(0).toUpperCase() + word.slice(1);
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}   

// const details = computed(() => {
//     if (!props.report) return []
//     return [
//         { icon: '/Images/SVG/tag.svg', label: 'Type', subtext: props.report.type },
//         { icon: '/Images/SVG/map-pin.svg', label: 'Location', subtext: props.report.location },
//         { icon: '/Images/SVG/paper-plane-tilt.svg', label: 'Submitted By', subtext: props.report.sender },
//         { icon: '/Images/SVG/phone.svg', label: 'Contact', subtext: props.report.contact || 'N/A' },
//         { icon: '/Images/SVG/calendar.svg', label: 'Date Submitted', subtext: formatDate(props.report.created_at) },
//         { icon: '/Images/SVG/warning-circle.svg', label: 'Rejection Message', subtext: props.report.rejection_reason || 'This report is not rejected' },
//         { icon: '/Images/SVG/chat-circle-text.svg', label: 'Remarks', subtext: props.report.remarks || 'User didnt add any remarks.'},
//         { 
//             icon: '/Images/SVG/copy.svg', 
//             label: 'Duplicate Status', 
//             // FIX: Check both the status AND the duplicate_of field
//             subtext: props.report.status.toLowerCase() === 'Duplicate' && props.report.duplicate_of_report_id
//                 ? `This is a duplicate of Report #${props.report.duplicate_of_report_id}` 
//                 : 'This is the primary report' 
//         }
//     ]
// })

console.log(props.report)


const showDeleteModal = ref(false);
const selectedId = ref(null);
const rejectionReason = ref('')

function openDeleteModal(id) {
    selectedId.value = id;
    showDeleteModal.value = true;
}

function confirmDelete() {
    if (selectedId.value) {
        router.delete(route('reports.destroy', selectedId.value), {
            onSuccess: () => {
                showDeleteModal.value = false;
                selectedId.value = null;
                toast.success('Report deleted successfully!');
            }
        })
    }
}

const showRejectModal = ref(false);
const rejectedReportId = ref(null);
const showToConfirm = ref(false);

function openRejectModal(id) {
    rejectedReportId.value = id;
    showRejectModal.value = true;
}

const handleReject = (reason) => {
    console.log('Reason from modal:', reason)
    rejectionReason.value = reason
    showRejectModal.value = false
    showToConfirm.value = true
}

const finalizeRejection = () => {
    router.put(route('report.rejected', rejectedReportId.value), 
        { reason: rejectionReason.value },
        {
            onSuccess: () => {
                toast.success(page.props.flash.success || 'Report rejected successfully!');
                showToConfirm.value = false; 
            }
        }
    );
};
</script>

<template>
    <AdminLayout>
        <main class="flex flex-col gap-5 text-sm">
            <section class="flex  items-center gap-2">
                <div>
                    <Link :href="backUrl">
                        <img :src="'/Images/SVG/arrow-circle-left-fill (700).svg'" alt="Back Icon">
                    </Link>
                </div>
                <h1 class="font-semibold text-2xl font-[Poppins] ">Report Details</h1>
            </section>

            <section class="bg-white shadow-lg rounded-lg">
                <!-- Header -->
                <header class="px-10 py-7 rounded-lg border-b border-gray-200 mb-5 shadow">
                    <div class="flex justify-between">
                        <div class="">
                            <span :class=" [
                                    'text-center border rounded-full font-semibold px-3 py-1' ,
                                    report.status === 'Pending' ? 'bg-amber-100 text-amber-800 border-amber-200 font-bold' :
                                    report.status === 'In Progress' ? 'bg-blue-100 text-[#3B82F6] border-blue-200 font-bold' :
                                    report.status === 'Resolved' ? 'bg-green-100 text-[#16A34A] border-green-200 font-bold' :
                                    report.status === 'Rejected' ? 'bg-red-100 text-[#EF4444] border-red-200 font-bold' : 
                                    report.status === 'Duplicate' ? 'bg-purple-100 text-purple-800 border-purple-200 font-bold' : ''
                                ]">
                                    {{ report.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                            </span>
                            <div class="mt-3">
                                <h3 class="font-bold text-3xl font-[Poppins] ">{{ report.title }}</h3>
                            </div>
                        </div>

                        <!-- Notes for Duplicates Reports -->
                        <div
                            v-if="report.status === 'Duplicate' && report.duplicate_of_report_id"
                            class="border-l-4 border-blue-800 bg-gradient-to-r from-blue-200 to-blue-100 w-[30%] p-2 rounded-lg"
                        >
                            <p 
                                class="ml-1">
                                This report has been identified as a duplicate and consolidated with Report ID: 
                                <Link 
                                    :href="`/admin/reports/${ report.duplicate_of_report_id }`" 
                                    class="font-semibold underline hover:text-blue-700 transition-all duration-300">#{{ report.duplicate_of_report_id }}
                                </Link> 
                                The primary issue is being tracked under the referenced report.
                            </p>
                        </div>

                        
                        <div
                            v-if="report.duplicates && report.duplicates.length > 0"
                            class="border-l-4 border-blue-800 bg-gradient-to-r from-blue-200 to-blue-100 w-[30%] p-2 rounded-lg"
                        >
                            <p 
                                class="ml-1">
                                This is the primary report for this issue with a Report ID:
                                <ul>
                                    <li v-for="dup in report.duplicates" :key="dup.id">
                                        <Link :href="`/admin/reports/${dup.id}`" class="font-semibold underline hover:text-blue-700 transition-all duration-300">
                                            #{{ dup.id }} 
                                        </Link>
                                        duplicate reports have been consolidated here for efficient resolution.
                                    </li>
                                    
                                </ul>
                                
                            </p>
                        </div>

                        <!-- Reason for Rejection -->
                        <div
                            v-if="report.status === 'Rejected'"
                            class="flex items-center border-l-4 border-red-800 bg-gradient-to-r from-red-200 to-red-100 w-[30%] p-2 rounded-lg"
                        >
                            <div class="flex flex-col ml-1">
                                <p>This report was rejected for the following reason:</p>
                                <p class="font-semibold">{{ report.rejection_reason }}</p>
                            </div>
                            
                        </div>
                    </div>
                </header>

                <!-- Middle Information -->
                <div class="px-10 mb-5">
                    <div class="border border-gray-200 grid grid-cols-4 rounded-lg shadow-lg">
                        <div class="p-5">
                            <div class="text-center">
                                <p class="text-sm text-gray-500">Report ID</p>
                                <span class="text-lg font-semibold mb-1 text-blue-500">#{{ report.id }}</span>
                                
                            </div>
                        </div>
                        <div class=" p-5">
                            <div class="text-center">
                                <p class="text-sm text-gray-500 mb-1">Location</p>
                                <span class="text-lg font-semibold text-blue-500">{{ report.location }}</span>
                            </div>
                            
                        </div>
                        <div class="p-5">
                            <div class="text-center">
                                <p class="text-sm text-gray-500 mb-1">Priority Level</p>
                                <span :class="[
                                    'text-lg font-semibold',
                                    report.priority_level === 'low' ? 'text-green-500' :
                                    report.priority_level === 'medium' ? 'text-amber-500' :
                                    report.priority_level === 'high' ? 'text-red-500' : ''
                                ]">
                                    {{ report.priority_level.charAt(0).toUpperCase() + report.priority_level.slice(1)  }}
                                </span>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="text-center">
                                <p class="text-sm text-gray-500 mb-1">Submitted At</p>
                                <span class="text-lg font-semibold text-blue-500">{{ formatDate(report.created_at) }}</span>  
                            </div>
                        </div>
                    </div>

                    <!-- Image -->
                    <div 
                        v-if="report"
                        class="my-10"
                    >
                        <div class="">
                            <img 
                                :src="`/storage/${report.image}`" 
                                :alt="report.title"
                                class="w-full h-[100vh] rounded-lg"
                            >
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-10">
                        <p class="text-lg mb-1 font-[Poppins] font-semibold">Description</p>
                        <p class="text-lg text-gray-500">{{ report.description }}</p>
                    </div>

                    <!-- User Details -->
                    <div>
                        <p class="text-lg mb-1 font-[Poppins] font-semibold">Additional Info</p>
                        <div class="grid grid-cols-4">
                            <div>
                                <span class="text-sm text-gray-500">Sender</span>
                                <p class="text-lg font-semibold mb-1 text-blue-500">{{ report.sender }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Contact Number</span>
                                <p class="text-lg font-semibold mb-1 text-blue-500">{{ report.contact }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Type of Issue</span>
                                <p class="text-lg font-semibold mb-1 text-blue-500">{{ report.type }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Updated At</span>
                                <p class="text-lg font-semibold mb-1 text-blue-500">{{ formatDate (report.updated_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div class="my-10">
                        <p class="text-lg mb-1 font-[Poppins] font-semibold">Remarks</p>
                        <p class="text-lg text-gray-500">{{ report.remarks || 'User didnt leave a remarks'}}</p>
                    </div>
                </div>

                <!-- Footer -->
                <footer 
                    class="px-10 py-7 rounded-lg border-t border-gray-200 shadow flex justify-between"
                >
                    <div class="flex gap-1 w-[20%] text-[#FAF9F6]">
                        <!-- Approve Button -->
                        <button
                            v-if="report.status === 'Pending'"
                            @click="approveReport(report.id)"
                            type="submit" 
                            class="bg-emerald-500 hover:bg-emerald-600 transition-all duration-300 p-3 flex-1 rounded flex items-center justify-center gap-1"
                        >
                            <img :src="'/Images/SVG/check-circle.svg'" alt="Approve Icon" class="w-5 h-5">
                            Approve
                        </button>

                        <!-- Reject Button -->
                        <button 
                            v-if="report.status === 'Pending'"
                            @click="openRejectModal(report.id)"
                            type="submit" 
                            class="bg-red-500 hover:bg-red-600 transition-all duration-300 p-3 flex-1 rounded flex items-center justify-center gap-1"
                        >
                            <img :src="'/Images/SVG/x-circle.svg'" alt="Reject Icon" class="w-5 h-5">
                            Reject
                        </button>

                        <!-- Resolved Button -->
                        <button
                            v-if="report.status === 'In Progress'"
                            @click="resolveReport(report.id)"
                            type="submit"
                            class="bg-[#2cc08f] hover:bg-[#25a87b] transition-all duration-300 p-3 rounded flex items-center jusify-center gap-1"
                        >
                            <img :src="'/Images/SVG/check-circle.svg'" alt="Approved Icon" class="w-5 h-5">
                            Resolved
                        </button>

                    </div>

                    <div class="flex gap-1 w-[10%] text-[#FAF9F6]">
                        <!-- Delete Button -->
                        <button 
                            @click="openDeleteModal(report.id)" 
                            class="bg-red-500 hover:bg-red-600 transition-all duration-300 p-3 flex-1 rounded flex items-center justify-center gap-1"
                        >
                            <img :src="'/Images/SVG/trash.svg'" alt="Delete Icon" class="w-5 h-5">
                            Delete
                        </button>
                    </div>
                </footer>
            </section>

            <DeleteModal 
                :show="showDeleteModal"
                @close="showDeleteModal = false"
                @delete="confirmDelete"
            />

            <RejectingModal
                :show="showRejectModal"
                @close="showRejectModal = false"
                @confirm="handleReject"
            />

            <ConfirmRejectModal
                :show="showToConfirm"
                @close="showToConfirm = false"
                @reject="finalizeRejection"
            />

        </main> 
    </AdminLayout>
</template>     