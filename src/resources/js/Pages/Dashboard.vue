<script setup>
import { computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatCard              from '@/Components/Dashboard/StatCard.vue'
import TicketsByStatusChart  from '@/Components/Dashboard/TicketsByStatusChart.vue'
import TicketsOverTimeChart  from '@/Components/Dashboard/TicketsOverTimeChart.vue'
import TicketsByPriorityChart from '@/Components/Dashboard/TicketsByPriorityChart.vue'
import RecentTickets         from '@/Components/Dashboard/RecentTickets.vue'
import SlaWarnings           from '@/Components/Dashboard/SlaWarnings.vue'
import AgentWorkload         from '@/Components/Dashboard/AgentWorkload.vue'
import RecentActivity        from '@/Components/Dashboard/RecentActivity.vue'

const props = defineProps({
    kpis:           { type: Object, default: () => ({}) },
    byStatus:       { type: Array,  default: () => [] },
    overTime:       { type: Array,  default: () => [] },
    byPriority:     { type: Array,  default: () => [] },
    recentTickets:  { type: Array,  default: () => [] },
    slaWarnings:    { type: Array,  default: () => [] },
    agentWorkload:  { type: Array,  default: () => [] },
    recentActivity: { type: Array,  default: () => [] },
})

const isStaff = computed(() => usePage().props.auth.is_staff)

const today = new Date().toLocaleDateString('en-US', {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
})
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout title="Dashboard">
        <div class="bg-gray-50 min-h-full px-6 py-7 space-y-5">

            <!-- ── Page header ──────────────────────────────────────────── -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-[17px] font-semibold text-gray-900">Dashboard</h1>
                    <p class="text-[12.5px] text-gray-400 mt-0.5">{{ today }}</p>
                </div>
            </div>

            <!-- ── KPI cards ────────────────────────────────────────────── -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">

                <!-- Open -->
                <StatCard
                    label="Open"
                    :value="kpis.open ?? 0"
                    icon-bg="bg-indigo-50"
                    icon-color="text-indigo-600"
                >
                    <template #icon>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </template>
                </StatCard>

                <!-- In Progress -->
                <StatCard
                    label="In Progress"
                    :value="kpis.inProgress ?? 0"
                    icon-bg="bg-amber-50"
                    icon-color="text-amber-600"
                >
                    <template #icon>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </template>
                </StatCard>

                <!-- Resolved Today -->
                <StatCard
                    label="Resolved Today"
                    :value="kpis.resolvedToday ?? 0"
                    :sub="kpis.resolvedToday > 0 ? 'today' : null"
                    sub-type="up"
                    icon-bg="bg-emerald-50"
                    icon-color="text-emerald-600"
                >
                    <template #icon>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </template>
                </StatCard>

                <!-- Overdue SLA -->
                <StatCard
                    label="Overdue SLA"
                    :value="kpis.overdue ?? 0"
                    :sub="kpis.overdue > 0 ? 'needs attention' : null"
                    sub-type="warn"
                    icon-bg="bg-red-50"
                    icon-color="text-red-500"
                >
                    <template #icon>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </template>
                </StatCard>

                <!-- SLA Compliance -->
                <StatCard
                    label="SLA Compliance"
                    :value="kpis.slaCompliance !== null && kpis.slaCompliance !== undefined ? `${kpis.slaCompliance}%` : '—'"
                    sub="this month"
                    sub-type="neutral"
                    icon-bg="bg-violet-50"
                    icon-color="text-violet-600"
                >
                    <template #icon>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                    </template>
                </StatCard>
            </div>

            <!-- ── Charts row ────────────────────────────────────────────── -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <TicketsByStatusChart  :data="byStatus"  />
                <TicketsOverTimeChart  :data="overTime"  />
            </div>

            <!-- ── Priority chart (full width on small, half on large) ──── -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <TicketsByPriorityChart :data="byPriority" />

                <!-- SLA Warnings pinned next to priority chart -->
                <SlaWarnings :tickets="slaWarnings" />
            </div>

            <!-- ── Lists row ─────────────────────────────────────────────── -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <RecentTickets  :tickets="recentTickets"  />
                <RecentActivity :activity="recentActivity" />
            </div>

            <!-- ── Agent workload (staff only) ──────────────────────────── -->
            <div v-if="isStaff">
                <AgentWorkload :agents="agentWorkload" />
            </div>

        </div>
    </AuthenticatedLayout>
</template>
