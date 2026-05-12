<script setup>
import { reactive, computed } from 'vue'
import { router, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import DataTable from 'primevue/datatable'
import Column   from 'primevue/column'

// ─── Props (server renders data, no axios needed) ────────────────────────────
const props = defineProps({
    tickets: Object,
    filters: Object,
})

// ─── Local filter state (initialized from server) ────────────────────────────
const form = reactive({
    search:   props.filters?.search   ?? '',
    status:   props.filters?.status   ?? '',
    priority: props.filters?.priority ?? '',
    mine:     props.filters?.mine     ?? false,
    overdue:  props.filters?.overdue  ?? false,
})

// ─── Inertia navigation ──────────────────────────────────────────────────────
const applyFilters = (page = 1) => {
    const params = {}
    if (form.search)   params.search   = form.search
    if (form.status)   params.status   = form.status
    if (form.priority) params.priority = form.priority
    if (form.mine)     params.mine     = 1
    if (form.overdue)  params.overdue  = 1
    if (page > 1)      params.page     = page

    router.get(route('tickets.index'), params, {
        preserveState:  true,
        preserveScroll: true,
        replace:        true,
        only:           ['tickets', 'filters'],
    })
}

let searchTimer = null
const onSearch = () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => applyFilters(1), 350)
}

const clearAll = () => {
    form.search   = ''
    form.status   = ''
    form.priority = ''
    form.mine     = false
    form.overdue  = false
}

// ─── Computed ────────────────────────────────────────────────────────────────
const rows       = computed(() => props.tickets?.data ?? [])
const meta       = computed(() => props.tickets?.meta ?? {})
const hasFilters = computed(() =>
    form.search || form.status || form.priority || form.mine || form.overdue
)
const showingText = computed(() => {
    const m = meta.value
    if (!m.total) return ''
    if (m.from && m.to) return `${m.from}–${m.to} of ${m.total}`
    return `${m.total} tickets`
})

// ─── Badge maps ──────────────────────────────────────────────────────────────
const STATUS_MAP = {
    open:        { label: 'Open',        badge: 'badge-blue',    dot: 'bg-blue-500'    },
    in_progress: { label: 'In Progress', badge: 'badge-amber',   dot: 'bg-amber-500'   },
    pending:     { label: 'Pending',     badge: 'badge-violet',  dot: 'bg-violet-500'  },
    resolved:    { label: 'Resolved',    badge: 'badge-emerald', dot: 'bg-emerald-500' },
    closed:      { label: 'Closed',      badge: 'badge-gray',    dot: 'bg-gray-400'    },
}
const PRIORITY_MAP = {
    3: { label: 'High',   badge: 'badge-red',     icon: '↑' },
    2: { label: 'Medium', badge: 'badge-orange',  icon: '→' },
    1: { label: 'Low',    badge: 'badge-emerald', icon: '↓' },
}

// ─── Helpers ─────────────────────────────────────────────────────────────────
const statusFor   = (s) => STATUS_MAP[s]   ?? { label: s ?? '—',  badge: 'badge-gray', dot: 'bg-gray-400' }
const priorityFor = (l) => PRIORITY_MAP[l] ?? { label: '—',        badge: 'badge-gray', icon: '•'         }

const initials = (name) =>
    name ? name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase() : '?'

const relDate = (iso) => {
    if (!iso) return '—'
    const d    = new Date(iso)
    const days = Math.floor((Date.now() - d) / 86_400_000)
    if (days === 0) return 'Today'
    if (days === 1) return 'Yesterday'
    if (days  <  7) return `${days}d ago`
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}
</script>

<template>
    <Head title="Tickets" />

    <AuthenticatedLayout title="Tickets">
        <div class="p-6 space-y-4">

            <!-- ── Header ──────────────────────────────────────────── -->
            <div class="flex items-center justify-between">
                <p v-if="meta.total" class="text-[13px] text-gray-400">
                    {{ showingText }}
                </p>
                <div v-else />
                <button
                    @click="router.visit(route('tickets.create'))"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gray-900 hover:bg-gray-700 text-white text-[13px] font-medium rounded-lg transition-colors shadow-sm"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    New Ticket
                </button>
            </div>

            <!-- ── Filters ──────────────────────────────────────────── -->
            <div class="flex items-center gap-2 flex-wrap">

                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1110.5 18a7.5 7.5 0 006.15-3.35z" />
                    </svg>
                    <input
                        v-model="form.search"
                        @input="onSearch"
                        type="text"
                        placeholder="Search tickets…"
                        class="pl-9 pr-3 py-[7px] text-[13px] bg-white border border-gray-200 rounded-lg placeholder-gray-400 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/25 focus:border-indigo-400 w-52 transition-all"
                    />
                </div>

                <div class="relative">
                    <select
                        v-model="form.status"
                        @change="applyFilters(1)"
                        class="py-[7px] pl-3 pr-7 text-[13px] bg-white border border-gray-200 rounded-lg text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/25 focus:border-indigo-400 cursor-pointer appearance-none"
                    >
                        <option value="">All Statuses</option>
                        <option value="open">Open</option>
                        <option value="in_progress">In Progress</option>
                        <option value="pending">Pending</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                    <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>

                <div class="relative">
                    <select
                        v-model="form.priority"
                        @change="applyFilters(1)"
                        class="py-[7px] pl-3 pr-7 text-[13px] bg-white border border-gray-200 rounded-lg text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/25 focus:border-indigo-400 cursor-pointer appearance-none"
                    >
                        <option value="">All Priorities</option>
                        <option value="3">High</option>
                        <option value="2">Medium</option>
                        <option value="1">Low</option>
                    </select>
                    <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>

                <button
                    @click="form.mine = !form.mine; applyFilters(1)"
                    :class="[
                        'inline-flex items-center gap-1.5 px-3 py-[7px] text-[13px] font-medium rounded-lg border transition-all',
                        form.mine
                            ? 'bg-indigo-50 border-indigo-300 text-indigo-700'
                            : 'bg-white border-gray-200 text-gray-600 hover:border-gray-300',
                    ]"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    My Tickets
                </button>

                <button
                    @click="form.overdue = !form.overdue; applyFilters(1)"
                    :class="[
                        'inline-flex items-center gap-1.5 px-3 py-[7px] text-[13px] font-medium rounded-lg border transition-all',
                        form.overdue
                            ? 'bg-red-50 border-red-300 text-red-700'
                            : 'bg-white border-gray-200 text-gray-600 hover:border-gray-300',
                    ]"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Overdue
                </button>

                <button
                    v-if="hasFilters"
                    @click="clearAll"
                    class="px-2 py-[7px] text-[13px] text-gray-400 hover:text-gray-600 transition-colors"
                >
                    Clear
                </button>
            </div>

            <!-- ── Table ────────────────────────────────────────────── -->
            <div class="tm-table bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
                <DataTable
                    :value="rows"
                    data-key="id"
                    @row-click="(e) => router.visit(route('tickets.show', e.data.id))"
                    :pt="{ root: { class: 'w-full' }, table: { class: 'w-full' } }"
                >
                    <template #empty>
                        <div class="flex flex-col items-center justify-center py-16">
                            <div class="w-11 h-11 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                            </div>
                            <p class="text-[13.5px] font-semibold text-gray-700">No tickets found</p>
                            <p class="text-xs text-gray-400 mt-1">
                                {{ hasFilters ? 'Try adjusting your filters' : 'Create your first ticket to get started' }}
                            </p>
                        </div>
                    </template>

                    <Column header="#" :style="{ width: '56px' }">
                        <template #body="{ data }">
                            <span class="text-[12px] font-mono text-gray-400">#{{ data.id }}</span>
                        </template>
                    </Column>

                    <Column header="Title">
                        <template #body="{ data }">
                            <div class="flex flex-col min-w-0 max-w-xs lg:max-w-md">
                                <span class="text-[13.5px] font-medium text-gray-800 truncate leading-snug">{{ data.title }}</span>
                                <span v-if="data.category" class="text-[11.5px] text-gray-400 mt-0.5">{{ data.category.name }}</span>
                            </div>
                        </template>
                    </Column>

                    <Column header="Status" :style="{ width: '136px' }">
                        <template #body="{ data }">
                            <span v-if="data.status" :class="['badge', statusFor(data.status.slug).badge]">
                                <span :class="['w-1.5 h-1.5 rounded-full flex-shrink-0', statusFor(data.status.slug).dot]" />
                                {{ statusFor(data.status.slug).label }}
                            </span>
                        </template>
                    </Column>

                    <Column header="Priority" :style="{ width: '112px' }">
                        <template #body="{ data }">
                            <span v-if="data.priority" :class="['badge', priorityFor(data.priority.level).badge]">
                                <span class="font-bold text-[10px] leading-none">{{ priorityFor(data.priority.level).icon }}</span>
                                {{ priorityFor(data.priority.level).label }}
                            </span>
                        </template>
                    </Column>

                    <Column header="Assignee" :style="{ width: '148px' }" class="hidden md:table-cell">
                        <template #body="{ data }">
                            <div v-if="data.assigned_to" class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-indigo-500 flex items-center justify-center flex-shrink-0">
                                    <span class="text-white text-[9px] font-bold leading-none">{{ initials(data.assigned_to.name) }}</span>
                                </div>
                                <span class="text-[12.5px] text-gray-600 truncate">{{ data.assigned_to.name }}</span>
                            </div>
                            <span v-else class="text-[12.5px] text-gray-300">—</span>
                        </template>
                    </Column>

                    <Column header="Due" :style="{ width: '96px' }" class="hidden lg:table-cell">
                        <template #body="{ data }">
                            <span v-if="data.due_date" :class="['text-[12.5px] font-medium', data.is_overdue ? 'text-red-600' : 'text-gray-400']">
                                {{ relDate(data.due_date) }}
                            </span>
                            <span v-else class="text-[12.5px] text-gray-300">—</span>
                        </template>
                    </Column>

                    <Column header="Created" :style="{ width: '96px' }" class="hidden lg:table-cell">
                        <template #body="{ data }">
                            <span class="text-[12.5px] text-gray-400">{{ relDate(data.created_at) }}</span>
                        </template>
                    </Column>
                </DataTable>

                <!-- ── Pagination ───────────────────────────────────── -->
                <div
                    v-if="meta.last_page > 1"
                    class="flex items-center justify-between px-4 py-3 border-t border-gray-200/80 bg-white"
                >
                    <span class="text-[12.5px] text-gray-400">
                        Page {{ meta.current_page }} of {{ meta.last_page }}
                    </span>
                    <div class="flex items-center gap-1">
                        <button
                            :disabled="meta.current_page <= 1"
                            @click="applyFilters(meta.current_page - 1)"
                            class="px-3 py-1.5 text-[13px] rounded-md border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        >
                            ← Prev
                        </button>
                        <button
                            :disabled="meta.current_page >= meta.last_page"
                            @click="applyFilters(meta.current_page + 1)"
                            class="px-3 py-1.5 text-[13px] rounded-md border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        >
                            Next →
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
