<script setup>
import { ref, onMounted, computed, watch, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { initTooltips } from 'flowbite';
import { useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { Listbox, ListboxButton, ListboxOption, ListboxOptions } from '@headlessui/vue';
import Swal from 'sweetalert2'
import jsPDF from 'jspdf';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import FilterModal from '@/Components/FilterModal.vue';
import ReportsDetailsModal from '@/Components/ReportsDetailsModal.vue';
import ResolutionModal from '@/Components/ResolutionModal.vue';

// Props from Laravel
const props = defineProps({
    reports: Object,
    status: {
        type: String,
        default: 'all'
    },
    issueTypes: { 
        type: Array,
        default: () => []
    },
    barangays: {  
        type: Array,
        default: () => []
    },
    sitios: {  
        type: Array,
        default: () => []
    }
});

const toast = useToast();
const selectedReports = ref([]);
const showDuplicateModal = ref(false);
const primaryReportId = ref(null);

const showReportDetailsModal = ref(false);
const selectedReportDetails = ref(null);

const issueTypes = ref(props.issueTypes);
const barangays = ref(props.barangays);
const sitios = ref(props.sitios);

const showBulkResolutionModal = ref(false);

const handleBulkResolve = () => {
    showBulkResolutionModal.value = true;
};

const handleBulkResolutionConfirm = async (resolution) => {
    Swal.fire({
        title: 'Resolving Reports...',
        text: `Processing ${selectedReports.value.length} reports`,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
        showConfirmButton: false
    });

    router.post(route('admin.reports.bulk-resolve'), {
        report_ids: selectedReports.value,
        resolution: resolution
    }, {
        onSuccess: () => {
            Swal.close();
            toast.success(`${selectedReports.value.length} reports resolved successfully!`);
            selectedReports.value = [];
            selectedBulkAction.value = bulkActions.value[0];
        },
        onError: (errors) => {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Failed',
                text: 'Failed to resolve reports. Please try again.'
            });
        }
    });
};


const activeFilters = ref({
    barangay: '',
    sitio: '',
    issueType: '',
    priorityLevel: '',
    startDate: '',
    endDate: ''
});

const viewDetails = async (report) => {
    try {
        console.log('🔄 Fetching full report data for ID:', report.id);
    
        const response = await fetch(`/api/reports/${report.id}/details`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const reportData = await response.json();
        console.log('✅ Full report data fetched:', reportData);
        
        selectedReportDetails.value = reportData;
        showReportDetailsModal.value = true;
        
    } catch (error) {
        console.error('❌ Failed to fetch report details:', error);
    
        console.log('🔄 Using fallback table data');
        selectedReportDetails.value = report;
        showReportDetailsModal.value = true;
    }
};


const handleReportApproved = (reportId) => {
    router.reload({ preserveScroll: true });
};

const handleReportResolved = (reportId) => {
    router.reload({ preserveScroll: true });
};

const handleReportRejected = (reportId) => {
    router.reload({ preserveScroll: true });
};

const handleReportDeleted = (reportId) => {
    router.reload({ preserveScroll: true });
};

const hasPrimaryReports = computed(() => {
    return selectedReports.value.some(reportId => {
        const report = props.reports.data.find(r => r.id === reportId);
        return report?.status?.toLowerCase() !== 'duplicate';
    });
});

const hasOnlyDuplicates = computed(() => {
    return selectedReports.value.length > 0 && 
        selectedReports.value.every(reportId => {
            const report = props.reports.data.find(r => r.id === reportId);
            return report?.status?.toLowerCase() === 'duplicate';
        });
});

const canMarkAsDuplicate = computed(() => {
    if (selectedReports.value.length < 2) return false;
    
    // Check if we have at least one non-duplicate report to be primary
    const hasValidPrimary = selectedReports.value.some(reportId => {
        const report = props.reports.data.find(r => r.id === reportId);
        const status = report?.status?.toLowerCase();
        return status && status !== 'duplicate' && status !== 'resolved';
    });
    
    return hasValidPrimary && !hasResolvedReports.value;
});

const hasPendingReports = computed(() => {
    return selectedReports.value.some(reportId => {
        const report = props.reports.data.find(r => r.id === reportId);
        return report?.status?.toLowerCase() === 'pending';
    });
});

const hasInProgressReports = computed(() => {
    return selectedReports.value.some(reportId => {
        const report = props.reports.data.find(r => r.id === reportId);
        return report?.status?.toLowerCase() === 'in progress';
    });
});

const hasRejectedReports = computed(() => {
    return selectedReports.value.some(reportId => {
        const report = props.reports.data.find(r => r.id === reportId);
        return report?.status?.toLowerCase() === 'rejected';
    });
});

const hasNonRejectedReports = computed(() => {
    return selectedReports.value.some(reportId => {
        const report = props.reports.data.find(r => r.id === reportId);
        return report?.status?.toLowerCase() !== 'rejected';
    });
});

// Check if any selected reports are resolved
const hasResolvedReports = computed(() => {
    return selectedReports.value.some(reportId => {
        const report = props.reports.data.find(r => r.id === reportId);
        return report?.status?.toLowerCase() === 'resolved';
    });
});

// Check if any selected reports are NOT rejected (for trash)
const canRevertReports = computed(() => {
    return selectedReports.value.some(reportId => {
        const report = props.reports.data.find(r => r.id === reportId);
        const status = report?.status?.toLowerCase();
        return status && status !== 'pending' && status !== 'duplicate';
    });
});

// Bulk actions options with conditional disabling
const bulkActions = computed(() => {
    const actions = [
        { id: null, name: 'Actions', disabled: true },
        { id: 'mark-duplicate', name: 'Mark as Duplicate' },
        { id: 'bulk-approve', name: 'Approve Selected' },
        { id: 'bulk-resolved', name: 'Resolve Selected' },
        { id: 'bulk-delete', name: 'Move to Trash' },
        { id: 'revert', name: 'Revert Status' },
        { id: 'export-pdf', name: 'Export as PDF' }
    ];

    return actions.map(action => {
        if (action.disabled) return action;

        let disabled = false;
        let disabledReason = '';

        switch (action.id) {
            case 'mark-duplicate':
                disabled = !canMarkAsDuplicate.value;
                if (selectedReports.value.length < 2) {
                    disabledReason = 'Select at least 2 reports to mark as duplicates';
                } else if (!hasPrimaryReports.value) {
                    disabledReason = 'Need at least one non-duplicate report as primary';
                } else if (hasResolvedReports.value) {
                    disabledReason = 'Cannot mark resolved reports as duplicate';
                }
                break;

            case 'bulk-approve':
                disabled = !hasPendingReports.value || hasInProgressReports.value || hasResolvedReports.value || hasRejectedReports.value;
                disabledReason = 'Can only approve pending reports';
                break;

            case 'bulk-resolved':
                disabled = !hasInProgressReports.value || hasPendingReports.value || hasResolvedReports.value || hasRejectedReports.value;
                disabledReason = 'Can only resolve in progress reports';
                break;

            case 'bulk-delete':
                disabled = hasNonRejectedReports.value;
                disabledReason = 'Can only move rejected reports to trash';
                break;

            case 'revert':
                if (hasOnlyDuplicates.value) {
                    disabled = true;
                    disabledReason = 'Duplicate reports cannot be reverted';
                } else {
                    disabled = !canRevertReports.value;
                    disabledReason = 'Can only revert in progress, resolved, or rejected reports';
                }
                break;

            case 'export-pdf': 
                disabled = selectedReports.value.length === 0;
                disabledReason = 'Please select reports to export';
                break;
        }

        return { ...action, disabled, disabledReason };
    });
});

const exportSelectedReportsPDF = () => {
    if (selectedReports.value.length === 0) {
        toast.error('Please select reports to export');
        return;
    }

    Swal.fire({
        title: 'Generating PDF...',
        text: `Preparing ${selectedReports.value.length} report(s) for export`,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
        showConfirmButton: false
    });

    const selectedReportData = props.reports.data.filter(report => 
        selectedReports.value.includes(report.id)
    );

    generateMultipleReportsPDF(selectedReportData, 'selected-reports');
};

const generateMultipleReportsPDF = (reports, filename) => {
    const doc = new jsPDF();
    
    doc.setFont('helvetica');
    
    reports.forEach((report, index) => {
        if (index > 0) {
            doc.addPage();
        }
        
        let yPosition = 55; 
        const pageWidth = doc.internal.pageSize.width;
        const margin = 20;
        const contentWidth = pageWidth - (margin * 2);
        const pageHeight = doc.internal.pageSize.height;
        
        // ===== ENHANCED HEADER =====
        doc.setFillColor(59, 130, 246); 
        doc.rect(0, 0, pageWidth, 45, 'F');
        doc.setFillColor(37, 99, 235);
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
        
        // ===== SIMPLIFIED MIDDLE CONTENT (YOUR VERSION) =====
        
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
        
        // Page number with total
        doc.text(`Page ${index + 1} of ${reports.length}`, pageWidth / 2, pageHeight - 10, { align: 'center' });
        
        // Copyright
        doc.setFontSize(7);
        doc.text(`Copyright ${new Date().getFullYear()} CivicWatch. All rights reserved.`, margin, pageHeight - 5);
        
        // Security classification
        doc.setTextColor(239, 68, 68);
        doc.text('CLASSIFIED: INTERNAL USE ONLY', pageWidth - margin, pageHeight - 5, { align: 'right' });
    });
    
    doc.save(`${filename}-${new Date().toISOString().split('T')[0]}.pdf`);
    
    Swal.close();
    toast.success(`PDF with ${reports.length} report(s) downloaded successfully!`);
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

const selectedBulkAction = ref(bulkActions.value[0]);

function handleBulkAction(action) {
    if (!action || action.disabled) {
        if (action?.disabledReason) {
            Swal.fire({
                icon: 'warning',
                title: 'Action Not Available',
                text: action.disabledReason
            });
        }
        return;
    }

    // Common validations
    if (selectedReports.value.length === 0) {
        Swal.fire({
            icon: 'info',
            title: 'No Reports Selected',
            text: 'Please select at least 1 report.'
        });
        return;
    }

    // Action-specific handlers
    const actionHandlers = {
        'mark-duplicate': () => {
            if (selectedReports.value.length < 2) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selection Required',
                    text: 'Please select at least 2 reports to mark as duplicates.'
                });
                return;
            }
            showDuplicateModal.value = true;
        },

        'bulk-approve': () => {
            Swal.fire({
                title: `Approve ${selectedReports.value.length} pending report(s)?`,
                text: 'This will move the selected reports to "In Progress" status.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Approve',
                reverseButtons: true
            }).then(result => {
                if (result.isConfirmed) {
                    executeBulkAction('admin.reports.bulk-approve', 'Selected Reports Successfully Approved!');
                }
            });
        },

        'bulk-resolved': () => {
            handleBulkResolve();
        },

        'bulk-delete': () => {
            Swal.fire({
                title: `Move ${selectedReports.value.length} rejected report(s) to trash?`,
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Move to Trash',
                reverseButtons: true
            }).then(result => {
                if (result.isConfirmed) {
                    executeBulkAction('admin.reports.bulk-delete', 'Selected reports successfully moved to Trash!');
                }
            });
        },

        'revert': () => {
            Swal.fire({
                title: `Revert ${selectedReports.value.length} report(s) to previous status?`,
                text: 'This will change the status back to the previous state in the workflow.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Yes, Revert',
                reverseButtons: true
            }).then(result => {
                if (result.isConfirmed) {
                    executeBulkAction('admin.reports.bulk-revert', 'Selected reports successfully reverted!');
                }
            });
        },

        'export-pdf': () => { 
            exportSelectedReportsPDF();
        }
    };

    const handler = actionHandlers[action.id];
    if (handler) handler();
}

// Helper function for bulk actions
function executeBulkAction(routeName, successMessage) {
    Swal.fire({
        title: 'Processing...',
        didOpen: () => Swal.showLoading(),
        allowOutsideClick: false,
        showConfirmButton: false
    });

    router.post(route(routeName), {
        report_ids: selectedReports.value
    }, {
        onSuccess: () => {
            Swal.close();
            toast.success(successMessage);
            selectedReports.value = [];
            selectedBulkAction.value = bulkActions.value[0];
        },
        onError: (errors) => {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Failed',
                text: 'Action failed. Please try again.'
            });
            console.error('Bulk action failed:', errors);
        }
    });
}

function handleApplyFilters(filters) {
    console.log('Received filters:', filters);
    
    // Update active filters - include ALL filter types
    activeFilters.value = { 
        issueType: filters.issueType || '',
        priorityLevel: filters.priorityLevel || '',
        barangay: filters.barangay || '',
        sitio: filters.sitio || '',
        startDate: filters.startDate || '',
        endDate: filters.endDate || ''
    };
    
    // Build query parameters
    const params = {
        status: props.status || 'all',
        search: search.value
    };
    
    // Add filters to params if they have values
    if (filters.issueType) {
        params.issue_type = filters.issueType;
    }

    if (filters.priorityLevel) {
        params.priority_level = filters.priorityLevel; 
    }
    
    if (filters.barangay) {
        params.barangay = filters.barangay;
    }
    
    if (filters.sitio) {
        params.sitio = filters.sitio;
    }
    
    if (filters.startDate) {
        params.start_date = filters.startDate;
    }
    
    if (filters.endDate) {
        params.end_date = filters.endDate;
    }
    
    // Navigate with filters
    router.get('/admin/reports', params, {
        preserveState: true,
        preserveScroll: true
    });
}

function clearAllFilters() {
    activeFilters.value = {
        issueType: '',
        priorityLevel: '',
        barangay: '',
        sitio: '',
        startDate: '',
        endDate: ''
    };
    
    router.get('/admin/reports', {
        status: props.status || 'all',
        search: search.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
}

const hasActiveFilters = computed(() => {
    return activeFilters.value.issueType || 
        activeFilters.value.priorityLevel ||
        activeFilters.value.barangay ||
        activeFilters.value.sitio ||
        activeFilters.value.startDate || 
        activeFilters.value.endDate;
});

function clearFilter(filterType) {
    const params = {
        status: props.status || 'all',
        search: search.value
    };
    
    // Keep other filters but remove the specified one
    if (filterType !== 'issueType' && activeFilters.value.issueType) {
        params.issue_type = activeFilters.value.issueType;
    }

    if (filterType !== 'priorityLevel' && activeFilters.value.priorityLevel) {
        params.priority_level = activeFilters.value.priorityLevel; 
    }
    
    if (filterType !== 'barangay' && activeFilters.value.barangay) {
        params.barangay = activeFilters.value.barangay;
    }
    
    if (filterType !== 'sitio' && activeFilters.value.sitio) {
        params.sitio = activeFilters.value.sitio;
    }
    
    if (filterType !== 'dateRange') {
        if (activeFilters.value.startDate) {
            params.start_date = activeFilters.value.startDate;
        }
        if (activeFilters.value.endDate) {
            params.end_date = activeFilters.value.endDate;
        }
    }
    
    // Update local state
    if (filterType === 'issueType') {
        activeFilters.value.issueType = '';
    } else if (filterType === 'priorityLevel') {
        activeFilters.value.priorityLevel = '';
    } else if (filterType === 'barangay') {
        activeFilters.value.barangay = '';
        activeFilters.value.sitio = '';
    } else if (filterType === 'sitio') {
        activeFilters.value.sitio = '';
    } else if (filterType === 'dateRange') {
        activeFilters.value.startDate = '';
        activeFilters.value.endDate = '';
    }
    
    router.get('/admin/reports', params, {
        preserveState: true,
        preserveScroll: true
    });
}

const showfilterModal = ref(false)

function openFilterModal () {
    showfilterModal.value = true;
}

function closeFilterModal () {
    showfilterModal.value = false;
}

const activeStatus = ref(props.status);

onMounted(() => {
    initTooltips();
});

// Table headings
const theads = [    
    { heading: 'Image', img: '/Images/SVG/image.svg' },
    { heading: 'Title', img: '/Images/SVG/text-t (1).svg' },
    { heading: 'Sender', img: '/Images/SVG/user.svg' },
    { heading: 'Type', img: '/Images/SVG/tag (1).svg' },
    { heading: 'Description', img: '/Images/SVG/align-left.svg' },
    { heading: 'Level', img: '/Images/SVG/flag.svg' },
    { heading: 'Status', img: '/Images/SVG/hourglass (1).svg' },
    { heading: 'Action', img: '/Images/SVG/play.svg' }
];

// Filter options with additional styling for tabs
const filters = [
    { 
        text: 'All', 
        value: 'all', 
        icon: '/Images/SVG/clipboard-text-black.svg',
        count: props.reports?.total || 0
    },
    { 
        text: 'Pending', 
        value: 'pending', 
        icon: '/Images/SVG/hourglass (1).svg',
        count: 0 // You can pass this from your Laravel controller
    },
    { 
        text: 'In Progress', 
        value: 'in_progress', 
        icon: '/Images/SVG/play.svg',
        count: 0
    },
    { 
        text: 'Resolved', 
        value: 'resolved', 
        icon: '/Images/SVG/check-circle.svg',
        count: 0
    },
    { 
        text: 'Rejected', 
        value: 'rejected', 
        icon: '/Images/SVG/x-circle.svg',
        count: 0
    },
    {
        text: 'Duplicate',
        value: 'duplicate',
        icon : '/Images/SVG/copy.svg',
        count: 0
    }
];

// Check if a filter is active
const isActive = (value) => {
    return props.status?.toLowerCase() === value.toLowerCase();
};

// Debounce helper
function debounce(func, delay) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), delay);
    };
}

// Search
const search = ref('');
const debouncedSearch = debounce(() => {
    router.get('/admin/reports', { search: search.value, status: props.status || 'all' }, {
        preserveState: true,
        preserveScroll: true
    });
}, 300);

function fetchReports() {
    debouncedSearch();
}

// Apply filter
function applyFilter(value) {
    activeStatus.value = value;
    router.get('/admin/reports', { status: value, search: search.value }, {
        preserveState: true,
        preserveScroll: true
    });
}

// Helper function to truncate text
const truncateText = (text, maxLength = 20) => {
    return text && text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
};

const capitalizeFirstLetter = (str) => {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
};

onMounted(() => {
    initTooltips();
    
    // Initialize active filters from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    
    activeFilters.value = {
        issueType: urlParams.get('issue_type') || '',
        priorityLevel: urlParams.get('priority_level') || '',
        barangay: urlParams.get('barangay') || '',
        sitio: urlParams.get('sitio') || '',
        startDate: urlParams.get('start_date') || '',
        endDate: urlParams.get('end_date') || ''
    };

    setTimeout(() => {
        initTooltips();
    }, 100);
});

// Or create a separate initialization function
function initializeFiltersFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    
    activeFilters.value = {
        issueType: urlParams.get('issue_type') || '',
        barangay: urlParams.get('barangay') || '',
        sitio: urlParams.get('sitio') || '',
        startDate: urlParams.get('start_date') || '',
        endDate: urlParams.get('end_date') || ''
    };
}

const selectAllReports = (event) => {
    if (event.target.checked) {
        // Only select reports that are NOT duplicates
        selectedReports.value = props.reports.data
            .filter(report => report.status.toLowerCase() !== 'duplicate')
            .map(report => report.id);
    } else {
        selectedReports.value = [];
    }
};

const markAsDuplicate = () => {
    if (!primaryReportId.value) {
        // Auto-select the first non-duplicate report as primary if none selected
        const firstPrimary = selectedReports.value.find(reportId => {
            const report = props.reports.data.find(r => r.id === reportId);
            return report?.status?.toLowerCase() !== 'duplicate';
        });
        
        if (firstPrimary) {
            primaryReportId.value = firstPrimary;
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Primary Report Required',
                text: 'Please select which report should be the primary one.'
            });
            return;
        }
    }

    const primaryReport = props.reports.data.find(r => r.id === primaryReportId.value);
    const duplicateCount = selectedReports.value.length - 1;
    
    Swal.fire({
        title: 'Confirm Mark as Duplicate',
        html: `
            <div class="text-left">
                <p><strong>Primary Report:</strong> #${primaryReportId.value} - ${primaryReport?.title}</p>
                <p><strong>Duplicates:</strong> ${duplicateCount} report(s) will be marked as duplicates</p>
                <p class="text-sm text-gray-600 mt-2">The primary report will remain active, while duplicates will be consolidated under it.</p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancel',
        confirmButtonText: 'Mark as Duplicate',
        reverseButtons: true
    }).then(result => {
        if (result.isConfirmed) {
            router.post(route('admin.reports.mark-duplicate'), {
                primary_report_id: primaryReportId.value,
                duplicate_report_ids: selectedReports.value.filter(id => id !== primaryReportId.value)
            }, {
                onSuccess: () => {
                    toast.success(`Successfully marked ${duplicateCount} report(s) as duplicates!`);
                    showDuplicateModal.value = false;
                    selectedReports.value = [];
                    primaryReportId.value = null;
                    selectedBulkAction.value = bulkActions.value[0];
                },
                onError: (errors) => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: 'Failed to mark reports as duplicates. Please try again.'
                    });
                }
            });
        }
    });
};

watch(selectedReports, (newValue) => {
    console.log('Selected reports changed:', newValue);
    
    if (newValue.length === 0) {
        selectedBulkAction.value = bulkActions.value[0];
    } else {
        // Find the first available (not disabled) bulk action
        const availableAction = bulkActions.value.find(action => !action.disabled && action.id !== null);
        if (availableAction) {
            selectedBulkAction.value = availableAction;
        }
    }
}, { deep: true });

// Helper methods for duplicate handling
const isReportDuplicate = (reportId) => {
    const report = props.reports.data.find(r => r.id === reportId);
    return report?.status?.toLowerCase() === 'duplicate';
};

const getReportTitle = (reportId) => {
    const report = props.reports.data.find(r => r.id === reportId);
    return report?.title || 'Unknown Report';
};

const currentReportIds = computed(() => {
    return props.reports.data.map(report => report.id);
});
const previousReportIds = ref([]);

const newReportIds = ref(new Set());
const newReportTimeouts = ref({});

// Add this method to check if a report is new
const isNewReport = (reportId) => {
    return newReportIds.value.has(reportId);
};

const markAsNewReport = (reportId) => {
    newReportIds.value.add(reportId);
    
    // Remove the indicator after 8 seconds (you can adjust between 5-10 seconds)
    const timeoutId = setTimeout(() => {
        newReportIds.value.delete(reportId);
        delete newReportTimeouts.value[reportId];
    }, 8000);
    
    newReportTimeouts.value[reportId] = timeoutId;
};

const isPolling = ref(true)
const pollInterval = ref(null)
const newReportIndicator = ref(false)
const previousReportCount = ref(props.reports?.total || 0)

// Polling function for reports
const fetchReportsData = async () => {
    try {
        const response = await fetch('/api/reports-data')
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`)
        }
        
        const newData = await response.json()
        
        // Check if new reports arrived
        if (newData.totalReports > previousReportCount.value) {
            newReportIndicator.value = true
            
            const newReportsCount = newData.totalReports - previousReportCount.value
            
            // Store current IDs before refresh to compare after reload
            const currentIdsBeforeRefresh = [...currentReportIds.value];
            
            // Show toast immediately
            toast.info(`📨 ${newReportsCount} new report(s) received!`)
            
            // Only refresh if we're on the "All" tab or "Pending" tab where new reports would appear
            const shouldAutoRefresh = activeStatus.value === 'all' || activeStatus.value === 'pending'
            
            if (shouldAutoRefresh) {
                // Refresh immediately
                router.reload({ preserveScroll: true, preserveState: true })
                
                // After reload, compare and mark new reports
                setTimeout(() => {
                    const currentIdsAfterRefresh = currentReportIds.value;
                    const newReportIdsArray = currentIdsAfterRefresh.filter(id => !currentIdsBeforeRefresh.includes(id));
                    
                    newReportIdsArray.forEach(reportId => {
                        markAsNewReport(reportId);
                    });
                }, 1000); // Increased timeout to ensure page is fully loaded
            } else {
                // If not auto-refreshing, mark current reports as new
                if (props.reports.data && props.reports.data.length > 0) {
                    const newestReports = props.reports.data.slice(0, newReportsCount);
                    newestReports.forEach(report => {
                        markAsNewReport(report.id);
                    });
                }
            }
        }
        
        // Update previous count
        previousReportCount.value = newData.totalReports
        
    } catch (error) {
        console.error('❌ Reports polling error:', error)
    }
}

// Start polling
const startReportsPolling = () => {
    isPolling.value = true
    console.log('🔄 Starting reports polling every 3 seconds')
    
    // Fetch immediately
    fetchReportsData()
    
    // Then set up interval (10 seconds for reports page)
    pollInterval.value = setInterval(fetchReportsData, 3000)
}

// Stop polling
const stopReportsPolling = () => {
    isPolling.value = false
    if (pollInterval.value) {
        clearInterval(pollInterval.value)
        pollInterval.value = null
    }
    console.log('🛑 Reports polling stopped')
}

// Initialize polling when component mounts
onMounted(() => {
    // Store initial report IDs
    previousReportIds.value = [...currentReportIds.value];
    startReportsPolling()
})

onUnmounted(() => {
    stopReportsPolling();
    
    // Clear all new report timeouts
    Object.values(newReportTimeouts.value).forEach(timeoutId => {
        clearTimeout(timeoutId);
    });
    newReportTimeouts.value = {};
    newReportIds.value.clear();
    previousReportIds.value = [];
});

</script>

<template>
    <AdminLayout>
        <main class="table-fixed flex flex-col gap-5 text-sm hide-scrollbar"> 
            <div class="flex justify-between">
                <div class="flex gap-3 items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <img 
                            :src="'/Images/SVG/clipboard-text-black.svg'" 
                            alt="icon"
                            class="h-8"
                        >
                    </div>
                    <div>
                        <h1 class="font-semibold text-2xl font-[Poppins]">Reports</h1>
                        <p class="text-gray-600 text-sm">Data-driven insights at a glance</p>
                    </div>
                </div>

                <!-- Active Filter -->
                <div v-if="hasActiveFilters" class="bg-blue-50 border border-blue-200 rounded-lg p-2 mb-4">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <h3 class="text-sm font-medium text-blue-800">Active Filters:</h3>
                            <div class="flex gap-2 flex-wrap">
                                <!-- Issue Type Filter Badge -->
                                <span 
                                    v-if="activeFilters.issueType" 
                                    class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full"
                                >
                                    Type: {{ issueTypes.find(t => t.id == activeFilters.issueType)?.name }}
                                    <button @click="clearFilter('issueType')" class="hover:text-blue-900">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 8.586L2.929 1.515 1.515 2.929 8.586 10l-7.071 7.071 1.414 1.414L10 11.414l7.071 7.071 1.414-1.414L11.414 10l7.071-7.071-1.414-1.414L10 8.586z"/>
                                        </svg>
                                    </button>
                                </span>

                                <span 
                                    v-if="activeFilters.priorityLevel" 
                                    class="inline-flex items-center gap-1 px-2 py-1 bg-indigo-100 text-indigo-700 text-xs rounded-full"
                                >
                                    Priority: {{ capitalizeFirstLetter(activeFilters.priorityLevel) }}
                                    <button @click="clearFilter('priorityLevel')" class="hover:text-indigo-900">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 8.586L2.929 1.515 1.515 2.929 8.586 10l-7.071 7.071 1.414 1.414L10 11.414l7.071 7.071 1.414-1.414L11.414 10l7.071-7.071-1.414-1.414L10 8.586z"/>
                                        </svg>
                                    </button>
                                </span>

                                <!-- Barangay Filter Badge -->
                                <span 
                                    v-if="activeFilters.barangay" 
                                    class="inline-flex items-center gap-1 px-2 py-1 bg-purple-100 text-purple-700 text-xs rounded-full"
                                >
                                    Barangay: {{ barangays.find(b => b.id == activeFilters.barangay)?.name }}
                                    <button @click="clearFilter('barangay')" class="hover:text-purple-900">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 8.586L2.929 1.515 1.515 2.929 8.586 10l-7.071 7.071 1.414 1.414L10 11.414l7.071 7.071 1.414-1.414L11.414 10l7.071-7.071-1.414-1.414L10 8.586z"/>
                                        </svg>
                                    </button>
                                </span>

                                <!-- Sitio Filter Badge -->
                                <span 
                                    v-if="activeFilters.sitio" 
                                    class="inline-flex items-center gap-1 px-2 py-1 bg-orange-100 text-orange-700 text-xs rounded-full"
                                >
                                    Sitio: {{ sitios.find(s => s.id == activeFilters.sitio)?.name }}
                                    <button @click="clearFilter('sitio')" class="hover:text-orange-900">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 8.586L2.929 1.515 1.515 2.929 8.586 10l-7.071 7.071 1.414 1.414L10 11.414l7.071 7.071 1.414-1.414L11.414 10l7.071-7.071-1.414-1.414L10 8.586z"/>
                                        </svg>
                                    </button>
                                </span>

                                <!-- Date Range Filter Badge -->
                                <span 
                                    v-if="activeFilters.startDate || activeFilters.endDate" 
                                    class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full"
                                >
                                    <span v-if="activeFilters.startDate && activeFilters.endDate">
                                        {{ new Date(activeFilters.startDate).toLocaleDateString() }} - {{ new Date(activeFilters.endDate).toLocaleDateString() }}
                                    </span>
                                    <span v-else-if="activeFilters.startDate">
                                        From: {{ new Date(activeFilters.startDate).toLocaleDateString() }}
                                    </span>
                                    <span v-else>
                                        Until: {{ new Date(activeFilters.endDate).toLocaleDateString() }}
                                    </span>
                                    <button @click="clearFilter('dateRange')" class="hover:text-green-900">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 8.586L2.929 1.515 1.515 2.929 8.586 10l-7.071 7.071 1.414 1.414L10 11.414l7.071 7.071 1.414-1.414L11.414 10l7.071-7.071-1.414-1.414L10 8.586z"/>
                                        </svg>
                                    </button>
                                </span>
                            </div>
                        </div>
                        
                        <button 
                            @click="clearAllFilters"
                            class="text-sm text-blue-600 hover:text-blue-800 underline"
                        >
                            Clear All Filters
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-5 w-[30%]">
                    <div class="relative w-full flex items-center">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                            </svg>
                        </div>
                        <input 
                            type="text"
                            class="block w-full p-3 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Search reports title..."
                            v-model="search"
                            @input="fetchReports"
                        >
                        <button
                            @click="fetchReports"
                            class="text-white absolute right-2 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2"
                        >
                            Search
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-7">
                <!-- Tab Filters -->
                <div class="">
                    <!-- Tabs Navigation -->
                    <ul class="flex justify-between">
                        <div 
                            class="font-[Poppins] flex flex-wrap text-sm font-medium text-center border-b border-gray-200 dark:border-gray-700"
                        >
                            <li 
                                v-for="(filter, index) in filters" 
                                :key="index"
                                class="me-2"
                            >
                                <button
                                    @click="applyFilter(filter.value)"
                                    :class="[
                                        'flex items-center gap-2 px-4 py-2 rounded-t-lg transition-all duration-300 border-b-2 font-medium',
                                        // Active state with specific colors for each filter
                                        activeStatus === filter.value && filter.value === 'all' 
                                            ? 'text-blue-600 bg-blue-50 border-blue-600' 
                                            : activeStatus === filter.value && filter.value === 'pending'
                                            ? 'text-amber-600 bg-amber-50 border-amber-600'
                                            : activeStatus === filter.value && filter.value === 'in_progress'
                                            ? 'text-blue-600 bg-blue-50 border-blue-600'
                                            : activeStatus === filter.value && filter.value === 'resolved'
                                            ? 'text-green-600 bg-green-50 border-green-600'
                                            : activeStatus === filter.value && filter.value === 'rejected'
                                            ? 'text-red-600 bg-red-50 border-red-600'
                                            : activeStatus === filter.value && filter.value === 'duplicate'
                                            ? 'text-purple-600 bg-purple-50 border-purple-600'
                                            // Inactive state with hover effects
                                            : filter.value === 'pending'
                                            ? 'border-transparent hover:text-amber-600 hover:bg-amber-50 hover:border-amber-300'
                                            : filter.value === 'in_progress'
                                            ? 'border-transparent hover:text-blue-600 hover:bg-blue-50 hover:border-blue-300'
                                            : filter.value === 'resolved'
                                            ? 'border-transparent hover:text-green-600 hover:bg-green-50 hover:border-green-300'
                                            : filter.value === 'rejected'
                                            ? 'border-transparent hover:text-red-600 hover:bg-red-50 hover:border-red-300'
                                            : filter.value === 'duplicate'
                                            ? 'border-transparent hover:text-purple-600 hover:bg-purple-50 hover:border-purple-300'
                                            // Default for 'all'
                                            : 'border-transparent hover:text-blue-600 hover:bg-blue-50 hover:border-blue-300'
                                    ]"
                                >
                                    <!-- Color indicator dot -->
                                    <div :class="[
                                        'w-2 h-2 rounded-full mr-1',
                                        filter.value === 'all' ? 'bg-blue-500' :
                                        filter.value === 'pending' ? 'bg-amber-500' :
                                        filter.value === 'in_progress' ? 'bg-blue-500' :
                                        filter.value === 'resolved' ? 'bg-green-500' :
                                        filter.value === 'rejected' ? 'bg-red-500' : 
                                        filter.value === 'duplicate' ? 'bg-purple-500' : ''
                                    ]"></div>

                                    <!-- Optional: Add icons to tabs -->
                                    
                                    
                                    <span>{{ filter.text }}</span>
                                </button>
                            </li>
                        </div>

                        <div class="flex gap-2 w-[30%]">
                            <!-- Bulk action Listbox -->
                            <Listbox 
                                as="div" 
                                v-model="selectedBulkAction" 
                                @update:modelValue="handleBulkAction"
                                class="relative flex-grow"
                            >
                                <ListboxButton
                                    :class="[
                                        'w-full flex items-center justify-between px-4 py-2 text-sm font-medium rounded-lg border transition-all duration-300 mb-1',
                                        selectedReports.length === 0 || selectedBulkAction.disabled
                                            ? 'bg-gray-200 text-gray-500 cursor-not-allowed border-gray-200' 
                                            : 'bg-white text-gray-700 border-gray-300 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2'
                                    ]"
                                    :disabled="selectedReports.length === 0 || selectedBulkAction.disabled"
                                >
                                    <span class="truncate">{{ selectedBulkAction.name }}</span>
                                    <svg 
                                        class="w-4 h-4 ml-2 -mr-1 transition-transform duration-200"
                                        :class="{ 'rotate-180': open }"
                                        fill="none" 
                                        stroke="currentColor" 
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </ListboxButton>

                                <transition
                                    enter-active-class="transition duration-100 ease-out"
                                    enter-from-class="transform scale-95 opacity-0"
                                    enter-to-class="transform scale-100 opacity-100"
                                    leave-active-class="transition duration-75 ease-in"
                                    leave-from-class="transform scale-100 opacity-100"
                                    leave-to-class="transform scale-95 opacity-0"
                                >
                                    <ListboxOptions
                                        class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden focus:outline-none"
                                    >
                                        <ListboxOption
                                            v-for="action in bulkActions"
                                            :key="action.id"
                                            :value="action"
                                            :disabled="action.disabled || selectedReports.length === 0"
                                            v-slot="{ active, selected }"
                                        >
                                            <li
                                                :class="[
                                                    'px-4 py-2 text-sm cursor-pointer transition-colors duration-200',
                                                    action.disabled || selectedReports.length === 0
                                                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                                        : active
                                                        ? 'bg-blue-100 text-blue-900'
                                                        : 'text-gray-700',
                                                    selected ? 'bg-blue-50 text-blue-700' : ''
                                                ]"
                                            >
                                                {{ action.name }}
                                            </li>
                                        </ListboxOption>
                                    </ListboxOptions>
                                </transition>
                            </Listbox>

                            <!-- Filter -->
                            <button 
                                @click="openFilterModal"
                                class="flex items-center justify-center px-4 py-2 gap-2 rounded-lg border hover:bg-blue-700 group hover:text-white transition-all duration-300 mb-1 flex-grow"
                            >
                                <svg 
                                    class="w-4 h-4 group-hover:text-white transition-all duration-300 text-center" 
                                    fill="none" 
                                    stroke="currentColor" 
                                    viewBox="0 0 24 24" 
                                    xmlns="http://www.w3.org/2000/svg">

                                    <path 
                                        stroke-linecap="round" 
                                        stroke-linejoin="round" 
                                        stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
                                    ></path>
                                </svg>
                                <p class="text-sm font-medium text-center group-hover:text-white">Filter</p>
                            </button>

                            <!-- Trash -->
                            <Link 
                                :href="route('report.trash')"
                                class="flex items-center justify-center px-4 py-2 gap-2 rounded-lg border border-red-300 bg-red-50 text-red-700 hover:bg-red-100 transition-all duration-300 mb-1 flex-grow"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <p class="text-sm font-medium">Trash</p>
                            </Link>
                        </div>
                    </ul>
                </div>
                
                <div 
                    v-if="!props.reports.data || props.reports.data.length === 0"
                    class="flex justify-center items-center h-96"
                >
                    <div class="text-center flex flex-col gap-3">
                        <img :src="'/Images/SVG/not found.svg'" alt="SVG" class="h-44">
                        <p class="text-xl text-gray-500">No reports found!</p>
                    </div>
                </div>

                <!-- Enhanced Table -->
                <div v-else class="overflow-hidden shadow-container">
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
                                <tr class="grid grid-cols-[50px_100px_150px_3fr_3fr_3fr_2fr_2fr_2fr] items-center">
                                    <th class="px-3 py-4 text-center text-xs font-semibold text-gray-900 uppercase tracking-wider font-[Poppins]">
                                        <!-- ADD CHECKBOX COLUMN -->
                                        <input 
                                            type="checkbox" 
                                            :checked="selectedReports.length > 0 && 
                                            selectedReports.length === props.reports.data.filter(r => r.status.toLowerCase() !== 'duplicate').length"
                                            @change="selectAllReports"
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                                        >
                                    </th>
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
                                    class="grid grid-cols-[50px_100px_150px_3fr_3fr_3fr_2fr_2fr_2fr] hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300 group">

                                    <!-- Checkbox Column -->
                                    <td class="px-3 py-4 flex items-center justify-center">
                                        <div class="relative">
                                            <input 
                                                type="checkbox" 
                                                :value="report.id" 
                                                v-model="selectedReports"
                                                :disabled="report.status.toLowerCase() === 'duplicate'" 
                                                :class="[
                                                    'w-4 h-4 text-blue-600 rounded focus:ring-blue-500',
                                                    report.status.toLowerCase() === 'duplicate'
                                                        ? 'bg-gray-300 cursor-not-allowed' 
                                                        : 'bg-gray-100 border-gray-300'
                                                ]"
                                                :data-tooltip-target="`tooltip-${report.id}`"
                                                :data-tooltip-trigger="report.status.toLowerCase() === 'duplicate' ? 'hover' : 'none'"
                                            >

                                            <div 
                                                :id="`tooltip-${report.id}`" 
                                                role="tooltip" 
                                                class="absolute z-20 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg shadow-sm opacity-0 tooltip w-48"
                                            >
                                                <span v-if="report.status.toLowerCase() === 'duplicate'">
                                                    This report is already marked as a duplicate
                                                </span>
                                                <div 
                                                    class="tooltip-arrow" 
                                                    data-popper-arrow>
                                                </div>  
                                            </div>
                                        </div>
                                    </td>
                                                                
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
                                            <span class="text-sm font-semibold text-gray-700 group-hover:text-blue-500">{{ report.sender }}</span>
                                            <span class="text-xs font-medium text-gray-500 group-hover:text-blue-500">{{ report.location }}</span>
                                            <span class="text-xs text-gray-500 group-hover:text-blue-500">{{ report.contact }}</span>
                                        </div>
                                    </td>
                                    
                                    <!-- Type Column -->
                                    <td class="px-3 py-4">
                                        <span 
                                            :data-tooltip-target="`type-tooltip-${report.id}`"
                                            data-tooltip-placement="top"
                                            class="text-sm cursor-help transition-all duration-300 group-hover:text-blue-500"
                                        >
                                            {{ report.type.length > 30 ? report.type.substring(0, 15) + '...' : report.type }}
                                        </span>
                                        <div 
                                            :id="`type-tooltip-${report.id}`" 
                                            role="tooltip" 
                                            class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-blue-500 rounded-lg shadow-lg opacity-0 tooltip"
                                        >
                                            {{ report.type }}
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


                                    <!-- Contact Column -->
                                    <!-- <td class="px-3 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                            <span class="text-sm font-medium text-gray-700 group-hover:text-blue-500">{{ report.contact }}</span>
                                        </div>
                                    </td> -->

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
                                                    report.status === 'Resolved' ? 'bg-green-500 animate-pulse' :
                                                    report.status === 'Rejected' ? 'bg-red-500 animate-pulse' : 
                                                    report.status === 'Duplicate' ? 'bg-purple-500 animate-pulse' : ''
                                                ]"></div>
                                                {{ report.status }}
                                            </span>
                                        </div>
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
                                        <div class="flex items-center gap-2">
                                            <button 
                                                @click="viewDetails(report)"
                                                class="inline-flex items-center gap-2 px-4 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-full hover:bg-blue-600 hover:text-white hover:border-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-100 transition-all duration-200 shadow-sm hover:shadow-md group"
                                            >
                                                Details
                                                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </button>
                                            
                                            <!-- New Report Indicator - Moved to right side -->
                                            <span 
                                                v-if="isNewReport(report.id)"
                                                class="relative flex items-center justify-center"
                                            >
                                                <!-- Pulsing dot -->
                                                <span class="flex h-3 w-3">
                                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                                                </span>
                                            </span>
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
            </div>

            <FilterModal 
                :show="showfilterModal"
                @close="showfilterModal = false"
                :issueTypes="issueTypes"
                @apply-filters="handleApplyFilters"
                :barangays="barangays"
                :sitios="sitios"
            />

            <!-- Duplicate Modal -->
            <div v-if="showDuplicateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="rounded-lg shadow-lg bg-white w-full max-w-xl mx-4">
                    <!-- Header -->
                    <div class="flex justify-between items-center p-5 border-b bg-blue-700 rounded-t-lg">
                        <h3 class="text-lg font-semibold font-[Poppins] text-white">Mark as Duplicate</h3>
                        <button @click="showDuplicateModal = false" class="p-1 rounded hover:bg-blue-600 transition-colors">
                            <img :src="'/Images/SVG/x (white).svg'" alt="Close Icon" class="w-5 h-5">
                        </button>
                    </div>
                    
                    <!-- Body -->
                    <div class="p-5">
                        <p class="text-sm text-gray-600 mb-4">Select which report should be the <strong>primary</strong> one. All other selected reports will be marked as duplicates of this report.</p>
                        <div class="space-y-3 mb-4 max-h-60 overflow-y-auto">
                            <div v-for="reportId in selectedReports" :key="reportId" 
                                class="flex items-center p-2 rounded border hover:bg-gray-50 transition-colors">
                                <input 
                                    type="radio" 
                                    :id="`report-${reportId}`" 
                                    :value="reportId" 
                                    v-model="primaryReportId"
                                    class="w-4 h-4 text-blue-600 focus:ring-blue-500"
                                    :disabled="isReportDuplicate(reportId)"
                                >
                                <label :for="`report-${reportId}`" class="ml-2 text-sm flex-1">
                                    <div class="font-medium">Report #{{ reportId }}</div>
                                    <div class="text-xs text-gray-500">{{ getReportTitle(reportId) }}</div>
                                    <div v-if="isReportDuplicate(reportId)" class="text-xs text-orange-600 font-medium">
                                        (Already a duplicate)
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <div v-if="primaryReportId" class="bg-blue-50 border border-blue-200 rounded p-3 text-sm">
                            <strong>Primary Report:</strong> #{{ primaryReportId }} - {{ getReportTitle(primaryReportId) }}
                            <br>
                            <strong>Duplicates:</strong> {{ selectedReports.length - 1 }} report(s)
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="flex justify-end border-t p-5">
                        <div class="flex gap-3">
                            <button 
                                @click="showDuplicateModal = false" 
                                class="px-4 py-2 text-sm rounded-lg bg-gray-300 text-gray-600 hover:text-gray-800 transition-all duration-300"
                            >
                                Cancel
                            </button>
                            <button 
                                @click="markAsDuplicate" 
                                :disabled="!primaryReportId"
                                :class="[
                                    'px-4 py-2 text-sm rounded-lg transition-all duration-300',
                                    primaryReportId 
                                        ? 'bg-blue-600 text-white hover:bg-blue-700' 
                                        : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                ]"
                            >
                                Confirm
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <ReportsDetailsModal 
                :show="showReportDetailsModal"
                :report="selectedReportDetails"
                @close="showReportDetailsModal = false"
                @approved="handleReportApproved"
                @resolved="handleReportResolved"
                @rejected="handleReportRejected"
                @deleted="handleReportDeleted"
            />

            <ResolutionModal
                :show="showBulkResolutionModal"
                :isBulk="true"
                :reportCount="selectedReports.length"
                @close="showBulkResolutionModal = false"
                @confirm="handleBulkResolutionConfirm"
            />
        </main>
    </AdminLayout>
</template>