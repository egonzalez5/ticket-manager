<script setup>
import { router } from '@inertiajs/vue3'
import { relDate } from '@/composables/useTicketHelpers'

defineProps({
    activity: { type: Array, default: () => [] },
})

const actionLabel = (action, description) => {
    if (description) return description
    const map = {
        created:        'created this ticket',
        updated:        'updated ticket details',
        status_changed: 'changed the status',
        assigned:       'reassigned the ticket',
        deleted:        'deleted the ticket',
    }
    return map[action] ?? action
}

const actionIcon = (action) => {
    if (action === 'status_changed') return 'status'
    if (action === 'assigned')       return 'assign'
    if (action === 'created')        return 'plus'
    if (action === 'deleted')        return 'trash'
    return 'edit'
}
</script>

<template>
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">

        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-[13px] font-semibold text-gray-900">Recent Activity</h3>
        </div>

        <div v-if="!activity.length" class="flex items-center justify-center py-14 text-[13px] text-gray-300">
            No activity yet
        </div>

        <ul v-else class="divide-y divide-gray-100">
            <li
                v-for="(item, idx) in activity"
                :key="idx"
                @click="router.visit(route('tickets.show', item.ticket_id))"
                class="flex items-start gap-3 px-5 py-3.5 hover:bg-gray-50 cursor-pointer transition-colors"
            >
                <!-- Icon -->
                <div class="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <!-- status_changed -->
                    <svg v-if="actionIcon(item.action) === 'status'"
                         class="w-3 h-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <!-- assigned -->
                    <svg v-else-if="actionIcon(item.action) === 'assign'"
                         class="w-3 h-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <!-- created -->
                    <svg v-else-if="actionIcon(item.action) === 'plus'"
                         class="w-3 h-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <!-- default edit -->
                    <svg v-else class="w-3 h-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <p class="text-[12.5px] text-gray-700 leading-snug">
                        <span class="font-medium text-gray-900">{{ item.user?.name ?? 'System' }}</span>
                        {{ ' ' }}{{ actionLabel(item.action, item.description) }}
                    </p>
                    <p class="text-[11.5px] text-indigo-500 font-medium mt-0.5 truncate">
                        {{ item.ticket_title }}
                    </p>
                </div>

                <!-- Time -->
                <span class="text-[11px] text-gray-300 flex-shrink-0 mt-0.5">
                    {{ relDate(item.created_at) }}
                </span>
            </li>
        </ul>
    </div>
</template>
