<script setup>
import { router } from '@inertiajs/vue3'
import { statusFor, priorityFor, relDate } from '@/composables/useTicketHelpers'

defineProps({
    tickets: { type: Array, default: () => [] },
})
</script>

<template>
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">

        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-[13px] font-semibold text-gray-900">Recent Tickets</h3>
            <button
                @click="router.visit(route('tickets.index'))"
                class="text-[12px] text-indigo-600 hover:text-indigo-800 font-medium transition-colors"
            >
                View all →
            </button>
        </div>

        <div v-if="!tickets.length" class="flex items-center justify-center py-14 text-[13px] text-gray-300">
            No tickets yet
        </div>

        <ul v-else class="divide-y divide-gray-100">
            <li
                v-for="ticket in tickets"
                :key="ticket.id"
                @click="router.visit(route('tickets.show', ticket.id))"
                class="flex items-center gap-3 px-5 py-3.5 hover:bg-gray-50 cursor-pointer transition-colors"
            >
                <!-- Priority dot -->
                <span
                    v-if="ticket.priority"
                    :class="['w-1.5 h-1.5 rounded-full flex-shrink-0 mt-0.5',
                             ticket.priority.level === 3 ? 'bg-red-500' :
                             ticket.priority.level === 2 ? 'bg-orange-400' : 'bg-emerald-500']"
                />

                <!-- Title + meta -->
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-medium text-gray-800 truncate">{{ ticket.title }}</p>
                    <p class="text-[11.5px] text-gray-400 mt-0.5">
                        #{{ ticket.id }}
                        <span v-if="ticket.user"> · {{ ticket.user.name }}</span>
                        <span v-if="ticket.assigned_to" class="text-indigo-400"> → {{ ticket.assigned_to.name }}</span>
                    </p>
                </div>

                <!-- Status badge -->
                <span
                    v-if="ticket.status"
                    :class="['badge flex-shrink-0', statusFor(ticket.status.slug).badge]"
                >
                    <span :class="['w-1.5 h-1.5 rounded-full', statusFor(ticket.status.slug).dot]" />
                    {{ statusFor(ticket.status.slug).label }}
                </span>

                <!-- Time -->
                <span class="text-[11px] text-gray-300 flex-shrink-0 hidden sm:block">
                    {{ relDate(ticket.created_at) }}
                </span>
            </li>
        </ul>
    </div>
</template>
