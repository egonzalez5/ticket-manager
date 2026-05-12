<script setup>
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import {
    Chart as ChartJS,
    CategoryScale, LinearScale,
    BarElement, Tooltip,
} from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip)

const props = defineProps({
    data: { type: Array, default: () => [] }, // [{ level, name, count }]
})

const COLORS = { 3: '#ef4444', 2: '#f97316', 1: '#10b981' }

const chartData = computed(() => ({
    labels:   props.data.map(d => d.name),
    datasets: [{
        data:            props.data.map(d => d.count),
        backgroundColor: props.data.map(d => COLORS[d.level] ?? '#d1d5db'),
        borderRadius:    6,
        barThickness:    28,
    }],
}))

const options = {
    indexAxis:           'y',
    responsive:          true,
    maintainAspectRatio: false,
    plugins: {
        legend:  { display: false },
        tooltip: {
            callbacks: {
                label: (ctx) => ` ${ctx.parsed.x} tickets`,
            },
        },
    },
    scales: {
        x: {
            beginAtZero: true,
            ticks: {
                color: '#9ca3af',
                font: { size: 11 },
                stepSize: 1,
                precision: 0,
            },
            grid:   { color: '#f3f4f6' },
            border: { display: false },
        },
        y: {
            grid:   { display: false },
            ticks:  { color: '#6b7280', font: { size: 12, weight: '500' } },
            border: { display: false },
        },
    },
}
</script>

<template>
    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
        <h3 class="text-[13px] font-semibold text-gray-900 mb-5">Tickets by Priority</h3>

        <div v-if="!data.length" class="flex items-center justify-center h-36 text-[13px] text-gray-300">
            No data
        </div>
        <div v-else :style="{ height: `${Math.max(data.length * 56, 120)}px` }">
            <Bar :data="chartData" :options="options" />
        </div>
    </div>
</template>
