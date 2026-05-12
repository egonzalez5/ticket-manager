<script setup>
import { router } from '@inertiajs/vue3'

defineProps({
    tickets: { type: Array, default: () => [] },
})

const timeLeft = (isoDate) => {
    const diff = new Date(isoDate) - Date.now()
    if (diff <= 0) return null
    const h = Math.floor(diff / 3_600_000)
    const m = Math.floor((diff % 3_600_000) / 60_000)
    return h > 0 ? `${h}h ${m}m` : `${m}m`
}
</script>

<template>
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">

        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse" />
            <h3 class="text-[13px] font-semibold text-gray-900">SLA Warnings</h3>
            <span v-if="tickets.length" class="ml-auto text-[11px] font-medium px-1.5 py-0.5 rounded-full bg-red-50 text-red-600">
                {{ tickets.length }}
            </span>
        </div>

        <div v-if="!tickets.length"
             class="flex flex-col items-center justify-center py-14 gap-2">
            <svg class="w-8 h-8 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
            </svg>
            <p class="text-[13px] font-medium text-gray-400">All SLAs on track</p>
        </div>

        <ul v-else class="divide-y divide-gray-100">
            <li
                v-for="ticket in tickets"
                :key="ticket.id"
                @click="router.visit(route('tickets.show', ticket.id))"
                class="flex items-start gap-3 px-5 py-3.5 hover:bg-gray-50 cursor-pointer transition-colors"
            >
                <!-- Overdue icon -->
                <div :class="[
                    'w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5',
                    ticket.is_overdue ? 'bg-red-100' : 'bg-amber-100',
                ]">
                    <svg :class="['w-3.5 h-3.5', ticket.is_overdue ? 'text-red-600' : 'text-amber-600']"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-medium text-gray-800 truncate">{{ ticket.title }}</p>
                    <p class="text-[11.5px] mt-0.5"
                       :class="ticket.is_overdue ? 'text-red-500 font-medium' : 'text-amber-600'">
                        <template v-if="ticket.is_overdue">SLA breached</template>
                        <template v-else>{{ timeLeft(ticket.due_date) }} remaining</template>
                    </p>
                </div>

                <!-- Assignee -->
                <span v-if="ticket.assigned_to" class="text-[11.5px] text-gray-400 flex-shrink-0 hidden sm:block mt-1">
                    {{ ticket.assigned_to.name }}
                </span>
            </li>
        </ul>
    </div>
</template>
