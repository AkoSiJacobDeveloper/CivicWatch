<script setup>
import { computed } from 'vue';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement, Filler } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement, Filler)

const props = defineProps({
    monthlyReports: {
        type: Array,
        default: () => []
    }
});

const chartData = computed(() => ({
    labels: props.monthlyReports.map(item => item.month),
    datasets: [{
        label: 'Reports per Month',
        data: props.monthlyReports.map(item => item.count),
        backgroundColor: 'rgba(59, 130, 246, 0.1)', // Light blue fill
        borderColor: '#3B82F6', // Blue line
        borderWidth: 3,
        pointBackgroundColor: '#3B82F6',
        pointBorderColor: '#ffffff',
        pointBorderWidth: 2,
        pointRadius: 6,
        pointHoverRadius: 8,
        pointHoverBackgroundColor: '#1D4ED8',
        pointHoverBorderColor: '#ffffff',
        fill: true, // Fill area under line
        tension: 0.4 // Smooth curves
    }]
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'top',
            labels: {
                usePointStyle: true,
                padding: 20,
                font: {
                    size: 12,
                    weight: '500'
                }
            }
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleColor: 'white',
            bodyColor: 'white',
            borderColor: '#374151',
            borderWidth: 1,
            cornerRadius: 8,
            callbacks: {
                label: function(context) {
                    return `Reports: ${context.parsed.y}`;
                }
            }
        }
    },
    scales: {
        x: {
            grid: {
                display: false
            },
            ticks: {
                color: '#6B7280',
                font: {
                    size: 11,
                    weight: '500'
                }
            }
        },
        y: {
            beginAtZero: true,
            grid: {
                color: '#E5E7EB',
                borderDash: [2, 2]
            },
            ticks: {
                stepSize: 1,
                precision: 0,
                color: '#6B7280',
                font: {
                    size: 11
                },
                callback: function(value) {
                    return Number.isInteger(value) ? value : null;
                }
            }
        }
    },
    elements: {
        point: {
            hoverRadius: 8
        }
    },
    animation: {
        duration: 1200,
        easing: 'easeInOutQuart'
    }
};
</script>

<template>
    <div class="bg-white p-10 w-[50%] shadow-container">
        <div class="mb-5">
            <h2 class="text-lg font-semibold font-[Poppins]">Report Status Trend</h2>
            <p class="text-sm text-gray-700">Number of reports submitted each month</p>
        </div>
        <div class="h-64">
            <Line :data="chartData" :options="chartOptions" />
        </div>
    </div>
</template>