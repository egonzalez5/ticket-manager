<script setup>
import { computed, ref, watchEffect } from 'vue'
import { router } from '@inertiajs/vue3'
import {
    initials, absDate, formatMinutes,
    avatarColor, statusFor, priorityFor,
} from '@/composables/useTicketHelpers'

const props = defineProps({
    ticket:     { type: Object, required: true },
    statuses:   { type: Array,  default: () => [] },
    priorities: { type: Array,  default: () => [] },
    agents:     { type: Array,  default: () => [] },
    can:        { type: Object, default: () => ({}) },
})

// ── Refs locales para actualización optimista ─────────────────────────────────
// watchEffect: corre inmediatamente al montar Y cada vez que props.ticket
// cambia (post-redirect de Inertia). Esto evita dos problemas:
// 1. El select revierta a Unassigned mientras el request está en vuelo.
// 2. Al recargar la página el select no refleja el valor guardado.
const localStatusId   = ref(null)
const localPriorityId = ref(null)
const localAssignedTo = ref('')

watchEffect(() => {
    localStatusId.value   = props.ticket.status?.id      ?? null
    localPriorityId.value = props.ticket.priority?.id    ?? null
    localAssignedTo.value = props.ticket.assigned_to?.id ?? ''
})

const slaWarning = computed(() => {
    if (!props.ticket.due_date) return null
    const msLeft = new Date(props.ticket.due_date) - Date.now()
    if (msLeft < 0) return {
        type: 'overdue',
        label: 'SLA Breached',
        sub: `Overdue by ${formatMinutes(Math.abs(Math.ceil(msLeft / 60000)))}`,
    }
    if (msLeft < 4 * 3600 * 1000) return {
        type: 'warning',
        label: 'SLA Warning',
        sub: `${formatMinutes(Math.ceil(msLeft / 60000))} remaining until breach`,
    }
    return null
})

const updateField = (data) =>
    router.patch(route('tickets.update', props.ticket.id), data, { preserveScroll: true })

// v-model ya actualizó el ref antes de que este handler corra
const onStatusChange   = () => updateField({ status_id:   localStatusId.value })
const onPriorityChange = () => updateField({ priority_id: localPriorityId.value })
const onAssigneeChange = () => updateField({ assigned_to: localAssignedTo.value || null })
</script>

<template>
    <div class="w-72 xl:w-80 flex-none sticky top-6 space-y-4">

        <!-- ── Details card ──────────────────────────────────────────── -->
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">
            <h2 class="text-[13px] font-semibold text-gray-900 mb-4">Details</h2>
            <div class="space-y-4">
           <!--      {{ ticket }} -->
                <!-- Status -->
                <div>
                    <label class="block text-[11px] text-gray-400 font-medium mb-1.5">Status</label>
                    <div class="relative">
                        <select
                            v-if="can.changeStatus"
                            v-model="localStatusId"
                            @change="onStatusChange"
                            class="w-full appearance-none border border-gray-200 rounded-lg
                                   px-3 py-2 text-[13px] text-gray-800 bg-white cursor-pointer pr-8
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500/20
                                   focus:border-indigo-300 transition-colors"
                        >
                            <option v-for="s in statuses" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                        <svg v-if="can.changeStatus"
                             class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5
                                    text-gray-400 pointer-events-none"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                        <span v-if="!can.changeStatus && ticket.status"
                              :class="['badge', statusFor(ticket.status.slug).badge]">
                            <span :class="['w-1.5 h-1.5 rounded-full', statusFor(ticket.status.slug).dot]" />
                            {{ statusFor(ticket.status.slug).label }}
                        </span>
                    </div>
                </div>

                <!-- Priority -->
                <div>
                    <label class="block text-[11px] text-gray-400 font-medium mb-1.5">Priority</label>
                    <div class="relative">
                        <select
                            v-if="can.changePriority"
                            v-model="localPriorityId"
                            @change="onPriorityChange"
                            class="w-full appearance-none border border-gray-200 rounded-lg
                                   px-3 py-2 text-[13px] text-gray-800 bg-white cursor-pointer pr-8
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500/20
                                   focus:border-indigo-300 transition-colors"
                        >
                            <option v-for="p in priorities" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                        <svg v-if="can.changePriority"
                             class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5
                                    text-gray-400 pointer-events-none"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                        <span v-if="!can.changePriority && ticket.priority"
                              :class="['badge', priorityFor(ticket.priority.level).badge]">
                            <span class="font-bold text-[10px] leading-none">
                                {{ priorityFor(ticket.priority.level).icon }}
                            </span>
                            {{ priorityFor(ticket.priority.level).label }}
                        </span>
                    </div>
                </div>

                <!-- Assignee -->
                <div>
<!--                     {{ can }}
                    {{ agents }} -->
                    {{ ticket.assigned_to }}
                    <label class="block text-[11px] text-gray-400 font-medium mb-1.5">Assignee</label>
                    <div class="relative">
                        <select
                            v-if="can.assign"
                            v-model="localAssignedTo"
                            @change="onAssigneeChange"
                            class="w-full appearance-none border border-gray-200 rounded-lg
                                   px-3 py-2 text-[13px] text-gray-800 bg-white cursor-pointer pr-8
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500/20
                                   focus:border-indigo-300 transition-colors"
                        >
                            <option value="">Unassigned</option>
                           
                            <option v-for="a in agents" :key="a.id" :value="a.id">{{ a.name }}</option>
                        </select>
                        <svg v-if="can.assign"
                             class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5
                                    text-gray-400 pointer-events-none"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                        <div v-if="!can.assign" class="flex items-center gap-2">
                            <div v-if="ticket.assigned_to"
                                 :class="['w-5 h-5 rounded-full flex items-center justify-center',
                                          'text-white text-[9px] font-bold',
                                          avatarColor(ticket.assigned_to.id)]">
                                {{ initials(ticket.assigned_to.name) }}
                            </div>
                            <div v-else class="w-5 h-5 rounded-full border-2 border-dashed border-gray-200" />
                            <span class="text-[13px] text-gray-700">
                                {{ ticket.assigned_to?.name ?? 'Unassigned' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Category -->
                <div v-if="ticket.category">
                    <label class="block text-[11px] text-gray-400 font-medium mb-1">Category</label>
                    <span class="text-[13px] text-gray-700">{{ ticket.category.name }}</span>
                </div>

            </div>
        </div>

        <!-- ── SLA Warning card (conditional) ────────────────────────── -->
        <div
            v-if="slaWarning"
            :class="[
                'rounded-2xl border p-4',
                slaWarning.type === 'overdue'
                    ? 'bg-red-50 border-red-200'
                    : 'bg-amber-50 border-amber-200',
            ]"
        >
            <div class="flex items-start gap-3">
                <div :class="[
                    'w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5',
                    slaWarning.type === 'overdue' ? 'bg-red-100' : 'bg-amber-100',
                ]">
                    <svg class="w-3.5 h-3.5"
                         :class="slaWarning.type === 'overdue' ? 'text-red-600' : 'text-amber-600'"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p :class="['text-[13px] font-semibold',
                                slaWarning.type === 'overdue' ? 'text-red-700' : 'text-amber-700']">
                        {{ slaWarning.label }}
                    </p>
                    <p :class="['text-[12px] mt-0.5',
                                slaWarning.type === 'overdue' ? 'text-red-500' : 'text-amber-600']">
                        {{ slaWarning.sub }}
                    </p>
                </div>
            </div>
        </div>

        <!-- ── SLA info card (no warning active) ─────────────────────── -->
        <div v-if="ticket.sla && !slaWarning"
             class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">
            <h2 class="text-[13px] font-semibold text-gray-900 mb-3">SLA</h2>
            <dl class="space-y-2">
                <div class="flex justify-between">
                    <dt class="text-[11.5px] text-gray-400">Plan</dt>
                    <dd class="text-[13px] text-gray-700 font-medium">{{ ticket.sla.name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-[11.5px] text-gray-400">Response</dt>
                    <dd class="text-[13px] text-gray-700">{{ formatMinutes(ticket.sla.response_time) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-[11.5px] text-gray-400">Resolution</dt>
                    <dd class="text-[13px] text-gray-700">{{ formatMinutes(ticket.sla.resolution_time) }}</dd>
                </div>
            </dl>
        </div>

        <!-- ── Information card ───────────────────────────────────────── -->
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">
            <h2 class="text-[13px] font-semibold text-gray-900 mb-4">Information</h2>
            <div class="space-y-4">

                <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 text-gray-300 flex-shrink-0 mt-0.5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <div>
                        <p class="text-[10.5px] text-gray-400 mb-0.5">Reporter</p>
                        <p class="text-[13px] text-gray-800 font-medium">{{ ticket.user?.name ?? '—' }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 text-gray-300 flex-shrink-0 mt-0.5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <div>
                        <p class="text-[10.5px] text-gray-400 mb-0.5">Created</p>
                        <p class="text-[13px] text-gray-700">{{ absDate(ticket.created_at) }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 text-gray-300 flex-shrink-0 mt-0.5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="text-[10.5px] text-gray-400 mb-0.5">Last Updated</p>
                        <p class="text-[13px] text-gray-700">{{ absDate(ticket.updated_at) }}</p>
                    </div>
                </div>

                <div v-if="ticket.due_date" class="flex items-start gap-3">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5"
                         :class="ticket.is_overdue ? 'text-red-400' : 'text-gray-300'"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <div>
                        <p class="text-[10.5px] text-gray-400 mb-0.5">Due Date</p>
                        <p :class="['text-[13px] font-medium',
                                   ticket.is_overdue ? 'text-red-600' : 'text-gray-700']">
                            {{ absDate(ticket.due_date) }}
                        </p>
                    </div>
                </div>

            </div>
        </div>

    </div>
</template>
