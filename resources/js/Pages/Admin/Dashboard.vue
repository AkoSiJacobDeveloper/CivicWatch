<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Bargraph from '@/Components/Analytics/Bargraph.vue'
import Linegraph from '@/Components/Analytics/Linegraph.vue'

const props = defineProps({
    totalReports: Number,
    pending: Number,
    in_progress: Number,
    resolved: Number,
    rejected: Number,
    reportCounts: Object,
    monthlyReports: Array
})
// console.log('Total Reports:', totalReports)
const overviews = [
    { icon: '/Images/SVG/clipboard-text-black.svg', text: 'Total Reports', count: props.totalReports, color: '#000000' },
    { icon: '/Images/SVG/hourglass-amber.svg', text: 'Pending Reports', count: props.pending, color: '#F59E0B' },
    { icon: '/Images/SVG/spinner-gap-blue.svg', text: 'In Progress Reports', count: props.in_progress, color: '#3482f7' },
    { icon: '/Images/SVG/check-circle-green.svg', text: 'Resolved Reports', count: props.resolved, color: '#10B981' },
    { icon: '/Images/SVG/file-x-red.svg', text: 'Rejected Reports', count: props.rejected, color: '#EF4444' }
]

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
            </div>
            <div class="flex flex-col gap-7 ">
                <div>
                    <div class="grid grid-cols-5 gap-2 bg-[#FAF9F6]">
                        <div v-for="(overview,index) in overviews" :key="index" class="bg-[#fff] border-t border-l border-r p-5 rounded border-b-4 border-b-[#3B82F6] shadow-container" :style="{ borderBottomColor: overview.color }">
                            <div class="flex justify-between items-center mb-5">
                                <p class="font-semibold font-[Poppins] text-sm">{{ overview.text }}</p>
                                <img :src="overview.icon" alt="Icon" class="h-8">
                            </div>
                            <p class="text-6xl font-bold">{{ overview.count }}</p>
                        </div>
                    </div>
                </div>
            
                <div class="flex gap-2">
                    <Bargraph :reportCounts="reportCounts" />
                    <Linegraph :monthlyReports="monthlyReports" />
                </div>
            </div>
            
        </main>
    </AdminLayout>
</template>
