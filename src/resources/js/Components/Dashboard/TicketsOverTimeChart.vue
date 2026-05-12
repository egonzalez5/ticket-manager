<script setup>
import { computed } from 'vue'
import { Line } from 'vue-chartjs'
import {
    Chart as ChartJS,
    CategoryScale, LinearScale,
    PointElement, LineElement,
    Tooltip, Filler,
} from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Tooltip, Filler)

const props = defineProps({
    data: { type: Array, default: () => [] }, // [{ date: 'YYYY-MM-DD', count: number }]
})

const formatLabel = (dateStr) => {
    const d = new Date(dateStr + 'T00:00:00')
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const chartData = computed(() => ({
    labels:   props.data.map(d => formatLabel(d.date)),
    datasets: [{
        label:           'Tickets created',
        data:            props.data.map(d => d.count),
        borderColor:     '#6366f1',
        backgroundColor: 'rgba(99,102,241,0.08)',
        borderWidth:     2,
        pointRadius:     3,
        pointHoverRadius: 5,
        pointBackgroundColor: '#6366f1',
        tension:         0.35,
        fill:            true,
    }],
}))

const options = {
    responsive:          true,
    maintainAspectRatio: false,
    interaction:  { mode: 'index', intersect: false },
    plugins: {
        legend:  { display: false },
        tooltip: {
            callbacks: {
                label: (ctx) => ` ${ctx.parsed.y} tickets`,
            },
        },
    },
    scales: {
        x: {
            grid:  { display: false },
            ticks: { color: '#9ca3af', font: { size: 11 }, maxTicksLimit: 7 },
            border: { display: false },
        },
        y: {
            beginAtZero: true,
            ticks: {
                color: '#9ca3af',
                font: { size: 11 },
                stepSize: 1,
                precision: 0,
            },
            grid:  { color: '#f3f4f6' },
            border: { display: false },
        },
    },
}
</script>

<template>
    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-[13px] font-semibold text-gray-900">Volume — last 14 days</h3>
            <span class="text-[11.5px] text-gray-400">tickets created</span>
        </div>

        <div v-if="!data.length" class="flex items-center justify-center h-52 text-[13px] text-gray-300">
            No data
        </div>
        <div v-else class="h-52">
            <Line :data="chartData" :options="options" />
        </div>
    </div>
</template>
