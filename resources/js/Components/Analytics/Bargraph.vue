<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const props = defineProps({
    reportCounts: {
        type: Object,
        default: () => ({
            pending: 0,
            in_progress: 0,
            resolved: 0,
            rejected: 0
        })
    }
})

const chartData = computed(() => ({
    labels: ['Pending', 'In Progress', 'Resolved', 'Rejected'],
    datasets: [{
        label: 'Reports',
        backgroundColor: ['#fef3c7', '#dbeafe', '#d1fae5', '#fee2e2'],
        borderColor: ['#f59f0a', '#3583f7', '#11b980', '#ef4545'],
        borderWidth: 2,
        borderRadius: 3,
        data: [
            props.reportCounts.pending || 0,
            props.reportCounts.in_progress || 0,
            props.reportCounts.resolved || 0,
            props.reportCounts.rejected || 0
        ]
    }]
}));

const chartOptions = {
    responsive: true, 
    plugins: { legend: { position: 'top' } }
};

console.log('Report counts:', props.reportCounts);

</script> 

<template>
    <section class="w-[50%] bg-white p-10 shadow-container">
        <div class="mb-5">
            <h1 class="text-lg font-[Poppins] font-semibold ">Reports Breakdown</h1>
            <p class="text-sm text-gray-700">Breakdown of Pending, In Progress, Resolved, and Rejected Reports</p>
        </div>
        <div class="h-64">
            <Bar :data="chartData" :options="chartOptions" />
        </div>
    </section>
</template>