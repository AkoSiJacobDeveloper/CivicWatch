<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { useToast } from 'vue-toastification';
import { initTooltips } from 'flowbite';
import Swal from 'sweetalert2';
import jsPDF from 'jspdf';

import RejectingModal from '@/Components/RejectingModal.vue';
import ReportMap from '@/Components/ReportMap.vue';
import ResolutionModal from '@/Components/ResolutionModal.vue';

const props = defineProps({
    show: {
        type: Boolean,
        required: true
    },
    report: {
        type: Object,
        default: null
    },
    closeOnBackdrop: {
        type: Boolean,
        default: true
    }
})

const showResolutionModal = ref(false);

const resolveReport = () => {
    showResolutionModal.value = true;
};

const emit = defineEmits(['close', 'approved', 'resolved', 'rejected', 'deleted'])

const toast = useToast();
const page = usePage();
const reportMapRef = ref(null);
const mapKey = ref(0);

watch(() => props.show, (newValue) => {
    if (newValue) {
        nextTick(() => {
            setTimeout(() => {
                console.log('🔴 Attempting to refresh map...');
                if (reportMapRef.value?.refreshMap) {
                    reportMapRef.value.refreshMap();
                } else {
                    console.log('❌ Map ref not ready yet');
                }
            }, 100);
            
            setTimeout(() => {
                console.log('🔴 Second attempt to refresh map...');
                if (reportMapRef.value?.refreshMap) {
                    reportMapRef.value.refreshMap();
                }
            }, 500);

            setTimeout(() => {
                console.log('🔴 Third attempt to refresh map...');
                if (reportMapRef.value?.refreshMap) {
                    reportMapRef.value.refreshMap();
                }
            }, 1000);
        });
    }
});

// Also watch for report changes
watch(() => props.report, (newReport) => {
    console.log('🔴 FULL REPORT DATA:', newReport);
    if (newReport) {
        console.log('🔴 Coordinates check:', {
            hasLatitude: !!newReport.latitude,
            hasLongitude: !!newReport.longitude,
            latitude: newReport.latitude,
            longitude: newReport.longitude,
            gps_accuracy: newReport.gps_accuracy
        });
    }
}, { immediate: true, deep: true });


const rejectionReason = ref('')
const showRejectModal = ref(false);
const rejectedReportId = ref(null);

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
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

const capitalizeFirstLetter = (str) => {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

watch(() => props.show, (newValue) => {
    if (newValue) {
        document.body.style.overflow = 'hidden'
        document.addEventListener('keydown', handleEscape)
    } else {
        document.body.style.overflow = ''
        document.removeEventListener('keydown', handleEscape)
    }
})

const approveReport = async (id) => {
    // Confirmation dialog
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "You are about to approve this report.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        cancelButtonText: 'Cancel',
        confirmButtonText: 'Yes, approve it!',
        reverseButtons: true
    });

    if (!result.isConfirmed) {
        return;
    }

    // Show loading indicator
    Swal.fire({
        title: 'Approving Report...',
        text: 'Please wait while we process your request',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {
        await router.post(route('report.approve', { report: id }), {}, {
            onSuccess: () => {
                Swal.close();
                toast.success('Report approved successfully!');
                emit('approved', id);
                close();
            },
            onError: (errors) => {
                Swal.close();
                let errorMessage = 'Failed to approve report';
                
                if (errors.message) {
                    errorMessage = errors.message;
                } else if (typeof errors === 'string') {
                    errorMessage = errors;
                }
                
                Swal.fire({
                    title: 'Error!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
            }
        });
    } catch (error) {
        Swal.close();
        Swal.fire({
            title: 'Error!',
            text: 'An unexpected error occurred',
            icon: 'error',
            confirmButtonColor: '#ef4444'
        });
    }
};

const handleResolutionConfirm = async (resolution) => {
    // Show loading
    Swal.fire({
        title: 'Resolving Report...',
        text: 'Please wait while we process your request',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {
        await router.post(route('report.resolved', props.report.id), 
            { resolution: resolution },
            {
                onSuccess: () => {
                    Swal.close();
                    toast.success('Report resolved successfully!');
                    emit('resolved', props.report.id);
                    close();
                },
                onError: (errors) => {
                    Swal.close();
                    let errorMessage = 'Failed to resolve report';
                    
                    if (errors.message) {
                        errorMessage = errors.message;
                    } else if (typeof errors === 'string') {
                        errorMessage = errors;
                    } else if (errors.resolution) {
                        errorMessage = errors.resolution[0];
                    }
                    
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonColor: '#ef4444'
                    });
                }
            }
        );
    } catch (error) {
        Swal.close();
        Swal.fire({
            title: 'Error!',
            text: 'An unexpected error occurred',
            icon: 'error',
            confirmButtonColor: '#ef4444'
        });
    }
};

const deleteReport = async (id) => {
    const result = await Swal.fire({
        title: 'Delete Report?',
        html: `
            <div class="text-left">
                <p class="mb-3">You are about to permanently delete this report. This action cannot be undone.</p>
                <div class="bg-red-50 border border-red-200 rounded p-3 mb-3">
                    <p class="text-red-800 text-sm font-semibold">Report #${id}: ${props.report?.title}</p>
                </div>
                <p class="text-sm text-gray-600">All report data including images and location information will be permanently removed.</p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        cancelButtonText: 'Cancel',
        confirmButtonText: 'Yes, delete report',
        reverseButtons: true
    });

    if (!result.isConfirmed) {
        return;
    }

    // Show loading
    Swal.fire({
        title: 'Deleting Report...',
        text: 'Please wait while we remove the report',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {
        // Use POST method with _method spoofing
        await router.post(route('reports.destroy', id), {
            _method: 'DELETE'
        }, {
            onSuccess: () => {
                Swal.close();
                toast.success('Report deleted successfully!');
                emit('deleted', id);
                close();
            },
            onError: (errors) => {
                Swal.close();
                let errorMessage = 'Failed to delete report';
                
                if (errors.message) {
                    errorMessage = errors.message;
                } else if (typeof errors === 'string') {
                    errorMessage = errors;
                }
                
                Swal.fire({
                    title: 'Error!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
            }
        });
    } catch (error) {
        Swal.close();
        Swal.fire({
            title: 'Error!',
            text: 'An unexpected error occurred',
            icon: 'error',
            confirmButtonColor: '#ef4444'
        });
    }
};

function openRejectModal(id) {
    rejectedReportId.value = id;
    showRejectModal.value = true;
}

const handleReject = async (reason) => {
    console.log('Reason from modal:', reason)
    rejectionReason.value = reason
    showRejectModal.value = false
    
    // SweetAlert2 confirmation instead of ConfirmRejectModal
    const result = await Swal.fire({
        title: 'Confirm Rejection',
        html: `
            <div class="text-left">
                <p class="mb-3">You are about to reject this report with the following reason:</p>
                <div class="bg-red-50 border border-red-200 rounded p-3 mb-3">
                    <p class="text-red-800 text-sm">${reason}</p>
                </div>
                <p class="text-sm text-gray-600">This action cannot be undone.</p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        cancelButtonText: 'Cancel',
        confirmButtonText: 'Yes, reject report',
        reverseButtons: true
    });

    if (result.isConfirmed) {
        // Show loading
        Swal.fire({
            title: 'Rejecting Report...',
            text: 'Please wait while we process your request',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            await router.post(route('report.rejected', rejectedReportId.value), 
                { reason: rejectionReason.value },
                {
                    onSuccess: () => {
                        Swal.close();
                        toast.success('Report rejected successfully!');
                        emit('rejected', rejectedReportId.value);
                        close();
                    },
                    onError: (errors) => {
                        Swal.close();
                        let errorMessage = 'Failed to reject report';
                        
                        if (errors.message) {
                            errorMessage = errors.message;
                        } else if (typeof errors === 'string') {
                            errorMessage = errors;
                        } else if (errors.reason) {
                            errorMessage = errors.reason[0];
                        }
                        
                        Swal.fire({
                            title: 'Error!',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonColor: '#ef4444'
                        });
                    }
                }
            );
        } catch (error) {
            Swal.close();
            Swal.fire({
                title: 'Error!',
                text: 'An unexpected error occurred',
                icon: 'error',
                confirmButtonColor: '#ef4444'
            });
        }
    }
};

// PDF
const exportReportPDF = () => {
    if (!props.report) return;

    Swal.fire({
        title: 'Generating PDF...',
        text: 'Please wait while we generate your PDF',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
        showConfirmButton: false
    });

    // Create PDF instance
    const doc = new jsPDF();
    
    const report = props.report;
    let yPosition = 55;
    const pageWidth = doc.internal.pageSize.width;
    const margin = 20;
    const contentWidth = pageWidth - (margin * 2);
    const pageHeight = doc.internal.pageSize.height;
    
    // ===== ENHANCED HEADER =====
    doc.setFillColor(59, 130, 246); // Classic blue
    doc.rect(0, 0, pageWidth, 45, 'F');
    doc.setFillColor(37, 99, 235); // Darker blue for depth
    doc.rect(0, 35, pageWidth, 10, 'F');
    
    // Main title
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(24);
    doc.setFont('helvetica', 'bold');
    doc.text('DETAILED REPORT', pageWidth / 2, 18, { align: 'center' });
    
    // Subtitle
    doc.setFontSize(11);
    doc.setFont('helvetica', 'normal');
    doc.text(`COMPREHENSIVE ANALYSIS - REPORT #${report.id}`, pageWidth / 2, 26, { align: 'center' });
    
    // Status badge in header
    const statusColor = getStatusColor(report.status);
    doc.setFillColor(statusColor.background[0], statusColor.background[1], statusColor.background[2]);
    doc.rect(pageWidth / 2 - 40, 30, 80, 8, 'F');
    doc.setFontSize(9);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(statusColor.text[0], statusColor.text[1], statusColor.text[2]);
    doc.text(`STATUS: ${report.status.toUpperCase()}`, pageWidth / 2, 35, { align: 'center' });
    
    // Generation info
    doc.setTextColor(255, 255, 255, 180);
    doc.setFontSize(8);
    doc.text(`Generated: ${new Date().toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })}`, pageWidth / 2, 42, { align: 'center' });
    
    // Reset for content
    doc.setTextColor(0, 0, 0);
    
    // ===== CONTENT MATCHING MULTI-REPORT STYLE =====
    
    // Report basic info
    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    doc.text(`Report #${report.id}: ${report.title}`, margin, yPosition);
    yPosition += 8;
    
    // Status and priority
    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');
    doc.text(`Status: ${report.status} | Priority: ${capitalizeFirstLetter(report.priority_level)}`, margin, yPosition);
    yPosition += 15;
    
    // Sender information
    doc.setFont('helvetica', 'bold');
    doc.text('Sender Information:', margin, yPosition);
    yPosition += 6;
    
    doc.setFont('helvetica', 'normal');
    doc.text(`Name: ${report.sender}`, margin, yPosition);
    yPosition += 5;
    doc.text(`Contact: ${report.contact}`, margin, yPosition);
    yPosition += 5;
    doc.text(`Location: ${report.location}`, margin, yPosition);
    yPosition += 5;
    doc.text(`Submitted: ${new Date(report.created_at).toLocaleString()}`, margin, yPosition);
    yPosition += 15;
    
    // Description
    if (report.description) {
        doc.setFont('helvetica', 'bold');
        doc.text('Description:', margin, yPosition);
        yPosition += 6;
        
        doc.setFont('helvetica', 'normal');
        const splitDescription = doc.splitTextToSize(report.description, contentWidth);
        doc.text(splitDescription, margin, yPosition);
        yPosition += (splitDescription.length * 5) + 10;
    }
    
    // Remarks
    doc.setFont('helvetica', 'bold');
    doc.text('Remarks:', margin, yPosition);
    yPosition += 6;
    
    doc.setFont('helvetica', 'normal');
    const remarks = report.remarks || 'No additional remarks provided.';
    const splitRemarks = doc.splitTextToSize(remarks, contentWidth);
    doc.text(splitRemarks, margin, yPosition);
    yPosition += (splitRemarks.length * 5) + 10;
    
    // Technical details
    doc.setFont('helvetica', 'bold');
    doc.text('Technical Details:', margin, yPosition);
    yPosition += 6;
    
    doc.setFont('helvetica', 'normal');
    
    // GPS coordinates
    if (report.latitude && report.longitude) {
        doc.text(`Coordinates: ${report.latitude.toFixed(6)}, ${report.longitude.toFixed(6)}`, margin, yPosition);
        yPosition += 5;
        
        if (report.gps_accuracy) {
            doc.text(`GPS Accuracy: ${report.gps_accuracy.toFixed(0)} meters`, margin, yPosition);
            yPosition += 5;
        }
    }
    
    // Image info
    doc.text(`Image: ${report.image ? 'Attached' : 'Not attached'}`, margin, yPosition);
    yPosition += 5;
    
    // Report type
    doc.text(`Issue Type: ${report.type}`, margin, yPosition);
    yPosition += 10;
    
    // Rejection reason (if applicable)
    if (report.rejection_reason) {
        doc.setFont('helvetica', 'bold');
        doc.text('Rejection Reason:', margin, yPosition);
        yPosition += 6;
        
        doc.setFont('helvetica', 'normal');
        const splitReason = doc.splitTextToSize(report.rejection_reason, contentWidth);
        doc.text(splitReason, margin, yPosition);
        yPosition += (splitReason.length * 5) + 10;
    }
    
    // ===== ENHANCED FOOTER =====
    
    // Footer background
    doc.setFillColor(249, 250, 251);
    doc.rect(0, pageHeight - 30, pageWidth, 30, 'F');
    
    // Footer separator
    doc.setDrawColor(229, 231, 235);
    doc.line(margin, pageHeight - 30, pageWidth - margin, pageHeight - 30);
    
    // Footer content
    doc.setFontSize(8);
    doc.setTextColor(107, 114, 128);
    doc.text('CivicWatch - Official Document', pageWidth / 2, pageHeight - 20, { align: 'center' });
    doc.text(`Document ID: RMS-${report.id}-${Date.now().toString().slice(-6)}`, pageWidth / 2, pageHeight - 15, { align: 'center' });
    
    // Page number
    doc.text(`Page 1 of 1`, pageWidth / 2, pageHeight - 10, { align: 'center' });
    
    // Copyright
    doc.setFontSize(7);
    doc.text(`Copyright ${new Date().getFullYear()} CivicWatch. All rights reserved.`, margin, pageHeight - 5);
    
    // Security classification
    doc.setTextColor(239, 68, 68);
    doc.text('CLASSIFIED: INTERNAL USE ONLY', pageWidth - margin, pageHeight - 5, { align: 'right' });
    
    // Save the PDF
    doc.save(`report-${report.id}-${new Date().toISOString().split('T')[0]}.pdf`);
    
    Swal.close();
    toast.success('PDF downloaded successfully!');
};

const getStatusColor = (status) => {
    const statusMap = {
        'pending': { background: [253, 230, 138], text: [146, 64, 14] },
        'in progress': { background: [219, 234, 254], text: [30, 64, 175] },
        'resolved': { background: [220, 252, 231], text: [22, 101, 52] },
        'rejected': { background: [254, 226, 226], text: [185, 28, 28] },
        'duplicate': { background: [233, 213, 255], text: [126, 34, 206] }
    };
    
    const lowerStatus = status.toLowerCase();
    return statusMap[lowerStatus] || { background: [243, 244, 246], text: [55, 65, 81] };
};

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
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-[60]"
        @click.self="handleBackdropClick"
    >
        <div 
            class="bg-white rounded-lg max-w-4xl w-full "
            role="dialog"
            aria-labelledby="modal-title"
            aria-modal="true"
        >
            <!-- Modal Header -->
            <div class="flex justify-between items-start bg-blue-700 p-6 rounded-t-lg">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-3">
                        <span :class="[
                            'text-center border rounded-full font-semibold px-3 py-1 text-sm',
                            report?.status === 'Pending' ? 'bg-amber-100 text-amber-800 border-amber-200 font-bold' :
                            report?.status === 'In Progress' ? 'bg-blue-100 text-[#3B82F6] border-blue-200 font-bold' :
                            report?.status === 'Resolved' ? 'bg-green-100 text-[#16A34A] border-green-200 font-bold' :
                            report?.status === 'Rejected' ? 'bg-red-100 text-[#EF4444] border-red-200 font-bold' : 
                            report?.status === 'Duplicate' ? 'bg-purple-100 text-purple-800 border-purple-200 font-bold' : ''
                        ]">
                            {{ report?.status?.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                        </span>
                    </div>

                    <h2 id="modal-title" class="text-2xl font-bold text-white">{{ report?.title }}</h2>
                    <div class="flex gap-5 mt-2">
                        <div class="flex gap-1 items-center">
                            <img :src="'/Images/SVG/user (white).svg'" alt="Icon" class="h-4 w-4">
                            <span class="text-white text-xs">{{ report?.sender }}</span>
                        </div>
                        <div class="flex gap-1 items-center">
                            <img :src="'/Images/SVG/calendar (white).svg'" alt="Icon" class="h-4 w-4">
                            <span class="text-white text-xs">{{ formatDate(report?.created_at) }}</span>
                        </div>
                        <div class="flex gap-1 items-center">
                            <img :src="'/Images/SVG/map-pin-area.svg'" alt="Icon" class="h-4 w-4">
                            <span class="text-white text-xs">{{ report?.location }}</span>
                        </div>
                    </div>
                </div>

                <button 
                    @click="close"
                    class="text-gray-400 hover:text-gray-200 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 rounded ml-4"
                    aria-label="Close modal"
                >
                    <img :src="'/Images/SVG/x (white).svg'" alt="Close Icon" class="h-6 w-6">
                </button>
            </div>

            <!-- Modal Content -->
            <div class="h-96 overflow-y-auto">
                <div class="px-6 py-4">
                    <!-- Notes for Duplicates Reports -->
                    <div class="mb-4">
                        <div
                            v-if="report?.status === 'Duplicate' && report?.duplicate_of_report_id"
                            class="flex items-center border-l-4 border-blue-800 bg-gradient-to-r from-blue-200 to-blue-100 p-3 rounded-lg flex-1"
                        >
                            <img :src="'/Images/SVG/info (blue).svg'" alt="Icon" class="h-5 w-5">
                            <p class="ml-2 text-sm">
                                This report has been identified as a duplicate and consolidated with Report ID: 
                                <span class="font-semibold">#{{ report.duplicate_of_report_id }}</span>.
                            </p>
                        </div>

                        <div
                            v-if="report?.duplicates && report.duplicates.length > 0"
                            class="mb-2 flex items-center border-l-4 border-blue-800 bg-gradient-to-r from-blue-200 to-blue-100 p-3 rounded-lg flex-1"
                        >
                            <img :src="'/Images/SVG/info (blue).svg'" alt="Icon" class="h-5 w-5">
                            <p class="ml-2 text-sm">
                                This is the primary report for this issue with a Report ID of 
                                <span class="font-semibold" v-for="(dup, index) in report.duplicates" :key="dup.id">
                                    #{{ dup.id }}.
                                    <span v-if="index < report.duplicates.length - 1">, </span>
                                </span>
                                Duplicate reports consolidated here.
                            </p>
                        </div>

                        <!-- Reason for Rejection -->
                        <div
                            v-if="report?.status === 'Rejected'"
                            class="flex items-center border-l-4 border-red-800 bg-gradient-to-r from-red-200 to-red-100 p-3 rounded-lg flex-1"
                        >
                            <img :src="'/Images/SVG/warning.svg'" alt="Warning" class="h-5 w-5">
                            <div class="flex flex-col ml-2 text-sm">
                                <p>Rejection reason: <span class="font-semibold">{{ report.rejection_reason }}</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="border border-gray-200 grid grid-cols-4 rounded-lg shadow-lg mb-6">
                        <div class="p-4">
                            <div class="text-center">
                                <p class="text-sm text-gray-500">Report ID</p>
                                <span class="text-base font-semibold mb-1 text-blue-500">#{{ report?.id }}</span>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="text-center">
                                <p class="text-sm text-gray-500 mb-1">Location</p>
                                <span class="text-xs font-semibold text-blue-500">{{ report?.location }}</span>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="text-center">
                                <p class="text-sm text-gray-500 mb-1">Priority Level</p>
                                <span :class="[
                                    'text-base font-semibold',
                                    report?.priority_level === 'low' ? 'text-green-500' :
                                    report?.priority_level === 'medium' ? 'text-amber-500' :
                                    report?.priority_level === 'high' ? 'text-red-500' : ''
                                ]">
                                    {{ report?.priority_level?.charAt(0).toUpperCase() + report?.priority_level?.slice(1)  }}
                                </span>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="text-center">
                                <p class="text-sm text-gray-500 mb-1">Updated At</p>
                                <span class="text-xs font-semibold text-blue-500">{{ formatDate(report?.updated_at) }}</span>  
                            </div>
                        </div>
                    </div>

                    <!-- Image -->
                    <div v-if="report?.image" class="mb-6">
                        <div class="rounded-lg overflow-hidden">
                            <img 
                                :src="`/storage/${report.image}`" 
                                :alt="report.title"
                                class="w-full h-96 object-cover rounded-lg"
                            >
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <p class="text-sm mb-2 font-[Poppins] font-semibold">Description</p>
                        <p class="leading-relaxed text-lg text-blue-500">{{ report?.description }}</p>
                    </div>

                    <!-- Additional Info -->
                    <div class="mb-6">
                        <p class="text-sm mb-3 font-[Poppins] font-semibold">Additional Info</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="border rounded-lg p-4">
                                <span class="text-xs text-gray-500 block mb-1">Sender</span>
                                <p class="text-lg font-semibold text-blue-500">{{ report?.sender }}</p>
                            </div>
                            <div class="border rounded-lg p-4">
                                <span class="text-xs text-gray-500 block mb-1">Contact Number</span>
                                <p class="text-lg font-semibold text-blue-500">{{ report?.contact }}</p>
                            </div>
                            <div class="border rounded-lg p-4">
                                <span class="text-xs text-gray-500 block mb-1">Type of Issue</span>
                                <p class="text-lg font-semibold text-blue-500">{{ report?.type }}</p>
                            </div>
                            <div class="border rounded-lg p-4">
                                <span class="text-xs text-gray-500 block mb-1">Submitted At</span>
                                <p class="text-lg font-semibold text-blue-500">{{ formatDate(report?.created_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div class="mb-6">
                        <p class="text-sm mb-2 font-[Poppins] font-semibold">Remarks</p>
                        <p class="text-gray-600 bg-gray-50 border rounded-lg p-4">
                            {{ report?.remarks || 'User didn\'t leave any remarks' }}
                        </p>
                    </div>

                    <!-- Map -->
                    <div v-if="report?.latitude && report?.longitude" class="mb-6">
                        <div class="flex items-center gap-2 mb-3">
                            <p class="font-semibold text-sm font-[Poppins]">Reported Location</p>
                        </div>
                        
                        <ReportMap 
                            ref="reportMapRef"
                            :key="`map-${report.id}-${mapKey}`"
                            :latitude="report.latitude"
                            :longitude="report.longitude"
                            :accuracy="report.gps_accuracy"
                            :report-location="report.location"
                            class="w-full h-64 rounded-lg"
                        />
                        
                        <div class="mt-3 text-sm text-gray-600">
                            <p v-if="report.gps_accuracy" class="mb-1">
                                <strong>GPS Accuracy:</strong> {{ report.gps_accuracy.toFixed(0) }} meters
                            </p>
                            <p class="mb-1"><strong>Coordinates:</strong> {{ report.latitude.toFixed(6) }}, {{ report.longitude.toFixed(6) }}</p>
                            <p class="text-xs text-gray-500">This shows the exact location where the report was submitted from.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-between items-center gap-3 mt-6 pt-4 border-t border-gray-200 p-6">
                <button 
                    @click="close"
                    class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500"
                >
                    Close
                </button>
                
                <div class="flex gap-2">
                    <button 
                        @click="exportReportPDF"
                        class="flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-all duration-300"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export PDF
                    </button>
                    
                    <!-- Action Buttons -->
                    <template v-if="report?.status === 'Pending'">
                        <button
                            @click="approveReport(report.id)"
                            class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition-all duration-300 flex items-center gap-2"
                        >
                            <img :src="'/Images/SVG/check-circle.svg'" alt="Approve Icon" class="w-4 h-4">
                            Approve
                        </button>
                        <button 
                            @click="openRejectModal(report.id)"
                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-all duration-300 flex items-center gap-2"
                        >
                            <img :src="'/Images/SVG/x-circle.svg'" alt="Reject Icon" class="w-4 h-4">
                            Reject
                        </button>
                    </template>

                    <button
                        v-if="report?.status === 'In Progress'"
                        @click="resolveReport"
                        class="px-4 py-2 bg-[#2cc08f] text-white rounded-lg hover:bg-[#25a87b] transition-all duration-300 flex items-center gap-2"
                    >
                        <img :src="'/Images/SVG/check-circle.svg'" alt="Resolved Icon" class="w-4 h-4">
                        Mark as Resolved
                    </button>

                    <!-- Delete Button (always visible for admins) -->
                    <button
                        v-if="report.status === 'Rejected'"
                        @click="deleteReport(report.id)"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all duration-300 flex items-center gap-2"
                    >
                        <img :src="'/Images/SVG/trash.svg'" alt="Delete Icon" class="w-4 h-4">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Only RejectingModal remains -->
    <RejectingModal
        :show="showRejectModal"
        @close="showRejectModal = false"
        @confirm="handleReject"
    />

    <ResolutionModal
        :show="showResolutionModal"
        @close="showResolutionModal = false"
        @confirm="handleResolutionConfirm"
    />

</template>