<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Bargraph from '@/Components/Analytics/Bargraph.vue'
import Linegraph from '@/Components/Analytics/Linegraph.vue'
import { ref, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
    totalReports: Number,
    pending: Number,
    in_progress: Number,
    resolved: Number,
    rejected: Number,
    reportCounts: Object,
    monthlyReports: Array
})

// Reactive data that will be updated via polling
const dashboardData = ref({
    totalReports: props.totalReports || 0,
    pending: props.pending || 0,
    in_progress: props.in_progress || 0,
    resolved: props.resolved || 0,
    rejected: props.rejected || 0,
    reportCounts: props.reportCounts || {},
    monthlyReports: props.monthlyReports || []
})

const connectionStatus = ref('connected')
const lastUpdated = ref(new Date().toLocaleTimeString())
const pollInterval = ref(null)
const isPolling = ref(true)

// Polling function
const fetchDashboardData = async () => {
    try {
        const response = await fetch('/api/dashboard-data')
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`)
        }
        
        const data = await response.json()
        
        // Update reactive data
        dashboardData.value = {
            ...dashboardData.value,
            ...data
        }
        lastUpdated.value = new Date().toLocaleTimeString()
        connectionStatus.value = 'connected'
        
        console.log('📊 Polling data received:', data)
        
    } catch (error) {
        console.error('❌ Polling error:', error)
        connectionStatus.value = 'disconnected'
    }
}

// Start polling
const startPolling = () => {
    isPolling.value = true
    console.log('🔄 Starting polling every 5 seconds')
    
    // Fetch immediately
    fetchDashboardData()
    
    // Then set up interval
    pollInterval.value = setInterval(fetchDashboardData, 3000) // 5 seconds
}

// Stop polling
const stopPolling = () => {
    isPolling.value = false
    if (pollInterval.value) {
        clearInterval(pollInterval.value)
        pollInterval.value = null
    }
    console.log('🛑 Polling stopped')
}

// Update overviews reactively based on live data
const overviews = ref([])

const updateOverviews = () => {
    overviews.value = [
        { 
            icon: '/Images/SVG/clipboard-text-black.svg', 
            text: 'Total Reports', 
            count: dashboardData.value.totalReports, 
            color: '#000000' 
        },
        { 
            icon: '/Images/SVG/hourglass-amber.svg', 
            text: 'Pending Reports', 
            count: dashboardData.value.pending, 
            color: '#F59E0B' 
        },
        { 
            icon: '/Images/SVG/spinner-gap-blue.svg', 
            text: 'In Progress Reports', 
            count: dashboardData.value.in_progress, 
            color: '#3482f7' 
        },
        { 
            icon: '/Images/SVG/check-circle-green.svg', 
            text: 'Resolved Reports', 
            count: dashboardData.value.resolved, 
            color: '#10B981' 
        },
        { 
            icon: '/Images/SVG/file-x-red.svg', 
            text: 'Rejected Reports', 
            count: dashboardData.value.rejected, 
            color: '#EF4444' 
        }
    ]
}

// Watch for changes in dashboardData and update overviews
watch(dashboardData, updateOverviews, { deep: true })

// Initialize when component mounts
onMounted(() => {
    updateOverviews() // Set initial overviews
    startPolling() // Start polling
    
    console.log('🏁 Dashboard component mounted with polling')
})

// Clean up when component unmounts
onUnmounted(() => {
    stopPolling()
})

// Manual refresh function
const manualRefresh = () => {
    console.log('🔄 Manual refresh triggered')
    fetchDashboardData()
}

// Toggle polling
const togglePolling = () => {
    if (isPolling.value) {
        stopPolling()
    } else {
        startPolling()
    }
}
</script>

<template>
    <AdminLayout>
        <main class="flex flex-col gap-4">
            <div class="flex gap-1 items-center">
                <img :src="'/Images/SVG/squares-four (black).svg'" 
                    alt="icon" 
                    class="h-8"
                >
                <h1 class="font-semibold text-3xl font-[Poppins] my-3">Dashboard</h1>
                
                <!-- Connection Status Indicator -->
                <div :class="[
                    'ml-4 px-3 py-1 rounded-full text-xs font-medium flex items-center gap-2 transition-colors duration-300',
                    connectionStatus === 'connected' ? 'bg-green-100 text-green-800 border border-green-300' :
                    'bg-red-100 text-red-800 border border-red-300'
                ]">
                    <span class="w-2 h-2 rounded-full animate-pulse" 
                        :class="connectionStatus === 'connected' ? 'bg-green-500' : 'bg-red-500'"></span>
                    {{ 
                        connectionStatus === 'connected' ? 'Live Updates' : 'Disconnected'
                    }}
                    <span class="text-xs">
                        ({{ isPolling ? 'Auto' : 'Paused' }})
                    </span>
                </div>
                
                <!-- Last Updated Time -->
                <div class="ml-auto text-sm text-gray-500 flex items-center gap-4">
                    <span>Updated: {{ lastUpdated }}</span>
                    <button 
                        @click="manualRefresh"
                        class="px-3 py-1 bg-blue-100 text-blue-700 rounded text-sm hover:bg-blue-200 transition-colors border border-blue-300"
                    >
                        Refresh Now
                    </button>
                    <button 
                        @click="togglePolling"
                        :class="[
                            'px-3 py-1 rounded text-sm transition-colors border',
                            isPolling ? 'bg-yellow-100 text-yellow-700 border-yellow-300 hover:bg-yellow-200' :
                            'bg-green-100 text-green-700 border-green-300 hover:bg-green-200'
                        ]"
                    >
                        {{ isPolling ? 'Pause Updates' : 'Resume Updates' }}
                    </button>
                </div>
            </div>
            
            <div class="flex flex-col gap-7">
                <!-- Overview Cards -->
                <div>
                    <div class="grid grid-cols-5 gap-2 bg-[#FAF9F6]">
                        <div v-for="(overview, index) in overviews" 
                            :key="index" 
                            class="bg-white border p-5 rounded border-b-4 shadow-container transition-all duration-500 hover:shadow-lg" 
                            :style="{ borderBottomColor: overview.color }">
                            <div class="flex justify-between items-center mb-5">
                                <p class="font-semibold font-[Poppins] text-sm">{{ overview.text }}</p>
                                <img :src="overview.icon" alt="Icon" class="h-8">
                            </div>
                            <p class="text-6xl font-bold transition-all duration-500 ease-in-out">
                                {{ overview.count }}
                            </p>
                        </div>
                    </div>
                </div>
            
                <!-- Charts -->
                <div class="flex gap-2">
                    <Bargraph :reportCounts="dashboardData.reportCounts" />
                    <Linegraph :monthlyReports="dashboardData.monthlyReports" />
                </div>
            </div>

            <!-- Debug Info - Remove in production -->
            <!-- <details class="mt-4 p-4 bg-gray-100 rounded text-xs">
                <summary class="cursor-pointer font-semibold">Debug Info (Click to expand)</summary>
                <div class="mt-2 space-y-1">
                    <p><strong>Polling Status:</strong> {{ isPolling ? 'Active' : 'Paused' }}</p>
                    <p><strong>Connection Status:</strong> {{ connectionStatus }}</p>
                    <p><strong>Last Updated:</strong> {{ lastUpdated }}</p>
                    <p><strong>Live Data:</strong> {{ dashboardData }}</p>
                </div>
            </details> -->
        </main>
    </AdminLayout>
</template>