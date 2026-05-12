<script setup>
import { computed } from 'vue'
import { Doughnut } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'

ChartJS.register(ArcElement, Tooltip, Legend)

const props = defineProps({
    data: { type: Array, default: () => [] }, // [{ slug, name, count }]
})

const COLORS = {
    open:        '#6366f1',
    in_progress: '#f59e0b',
    pending:     '#8b5cf6',
    resolved:    '#10b981',
    closed:      '#d1d5db',
}

const chartData = computed(() => ({
    labels:   props.data.map(d => d.name),
    datasets: [{
        data:            props.data.map(d => d.count),
        backgroundColor: props.data.map(d => COLORS[d.slug] ?? '#e5e7eb'),
        borderWidth:     0,
        hoverOffset:     4,
    }],
}))

const options = {
    responsive:          true,
    maintainAspectRatio: true,
    cutout:              '68%',
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                padding:     16,
                boxWidth:    10,
                boxHeight:   10,
                borderRadius: 3,
                font: { size: 12 },
                color: '#6b7280',
            },
        },
        tooltip: {
            callbacks: {
                label: (ctx) => ` ${ctx.label}: ${ctx.parsed}`,
            },
        },
    },
}

const total = computed(() => props.data.reduce((acc, d) => acc + d.count, 0))
</script>

<template>
    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
        <h3 class="text-[13px] font-semibold text-gray-900 mb-5">Tickets by Status</h3>

        <div v-if="!data.length" class="flex items-center justify-center h-52 text-[13px] text-gray-300">
            No data
        </div>

        <div v-else class="relative">
            <Doughnut :data="chartData" :options="options" class="max-h-64" />
            <!-- Center label -->
            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none" style="bottom: 36px">
                <span class="text-[22px] font-bold text-gray-900 leading-none">{{ total }}</span>
                <span class="text-[11px] text-gray-400 mt-1">total</span>
            </div>
        </div>
    </div>
</template>
