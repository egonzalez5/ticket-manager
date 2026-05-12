<script setup>
import { computed } from 'vue'
import TicketReplyBox from '@/Components/Tickets/TicketReplyBox.vue'
import {
    initials, relDate, absDate,
    avatarColor, isAgentUser,
    formatSize, fileExt,
} from '@/composables/useTicketHelpers'

const props = defineProps({
    ticket:          { type: Object,  required: true },
    canInternalNote: { type: Boolean, default: false },
})

// ── Timeline: messages + history merged chronologically ───────────────────────
const timeline = computed(() => {
    const msgs = (props.ticket.messages ?? []).map(m => ({ ...m, _type: 'message' }))
    const hist = (props.ticket.history  ?? [])
        .filter(h => h.action !== 'created')
        .map(h => ({ ...h, _type: 'history' }))
    return [...msgs, ...hist].sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
})

const historyLabel = (item) => {
    if (item.action === 'status_changed') return item.description ?? 'Status changed'
    if (item.action === 'assigned')       return item.description ?? 'Ticket assigned'
    if (item.action === 'updated')        return 'Updated ticket details'
    return item.description ?? item.action
}
</script>

<template>
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">

        <!-- Card header -->
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-[13px] font-semibold text-gray-900">Activity</h2>
            <span class="text-xs text-gray-400">
                {{ timeline.length }} event{{ timeline.length === 1 ? '' : 's' }}
            </span>
        </div>

        <!-- Timeline feed -->
        <div class="px-6 py-6">

            <!-- Empty state -->
            <div v-if="!timeline.length" class="py-10 text-center">
                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5 text-gray-300" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <p class="text-[13px] font-medium text-gray-500">No activity yet</p>
                <p class="text-xs text-gray-400 mt-1">Be the first to reply below.</p>
            </div>

            <!-- Items -->
            <div v-else class="space-y-1">
                <div
                    v-for="(item, idx) in timeline"
                    :key="item.id ?? idx"
                    class="flex gap-4"
                >
                    <!-- Left: avatar + connector line -->
                    <div class="flex flex-col items-center flex-shrink-0 pt-0.5">

                        <!-- Message: colored avatar -->
                        <div
                            v-if="item._type === 'message'"
                            :class="[
                                'w-8 h-8 rounded-full flex items-center justify-center',
                                'text-white text-[10px] font-bold flex-shrink-0 shadow-sm',
                                item.user ? avatarColor(item.user.id) : 'bg-gray-400',
                            ]"
                        >{{ item.user ? initials(item.user.name) : '?' }}</div>

                        <!-- History: icon in gray circle -->
                        <div v-else class="w-8 h-8 rounded-full bg-gray-100 border border-gray-200
                                           flex items-center justify-center flex-shrink-0">
                            <svg v-if="item.action === 'status_changed'"
                                 class="w-3.5 h-3.5 text-gray-400" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <svg v-else-if="item.action === 'assigned'"
                                 class="w-3.5 h-3.5 text-gray-400" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <svg v-else class="w-3.5 h-3.5 text-gray-400" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>

                        <!-- Connector line -->
                        <div v-if="idx < timeline.length - 1"
                             class="w-px flex-1 mt-2 mb-1 bg-gray-100" />
                    </div>

                    <!-- Right: content -->
                    <div :class="['flex-1 min-w-0', idx < timeline.length - 1 ? 'pb-5' : 'pb-0']">

                        <!-- History event: compact inline -->
                        <div v-if="item._type === 'history'"
                             class="flex items-center gap-2 pt-1.5 flex-wrap">
                            <span class="text-[12.5px] font-medium text-gray-500">
                                {{ item.user?.name ?? 'System' }}
                            </span>
                            <span class="text-[12.5px] text-gray-400">{{ historyLabel(item) }}</span>
                            <span class="text-[11px] text-gray-300">·</span>
                            <span class="text-[11px] text-gray-400">{{ relDate(item.created_at) }}</span>
                        </div>

                        <!-- Message: card bubble -->
                        <div v-else>
                            <!-- Meta row -->
                            <div class="flex items-center gap-2 mb-2 flex-wrap">
                                <span class="text-[13px] font-semibold text-gray-900">
                                    {{ item.user?.name ?? '—' }}
                                </span>
                                <span v-if="isAgentUser(item) && !item.is_internal"
                                      class="text-[10px] font-medium text-indigo-500 bg-indigo-50
                                             px-1.5 py-0.5 rounded">Agent</span>
                                <span v-if="item.is_internal"
                                      class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded
                                             bg-amber-100 text-amber-700 text-[10px] font-semibold">
                                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                              d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                              clip-rule="evenodd" />
                                    </svg>
                                    Internal
                                </span>
                                <span class="text-[11.5px] text-gray-400">{{ absDate(item.created_at) }}</span>
                            </div>

                            <!-- Bubble: color by type -->
                            <div :class="[
                                'rounded-xl px-4 py-3 border text-sm leading-relaxed',
                                item.is_internal
                                    ? 'bg-amber-50 border-amber-200/70 text-amber-900'
                                    : isAgentUser(item)
                                        ? 'bg-indigo-50/60 border-indigo-200/60 text-gray-700'
                                        : 'bg-gray-50 border-gray-200/80 text-gray-700',
                            ]">
                                <p class="whitespace-pre-wrap">{{ item.message }}</p>

                                <!-- Inline attachments -->
                                <div v-if="item.attachments?.length"
                                     class="mt-3 pt-3 border-t space-y-1.5"
                                     :class="item.is_internal ? 'border-amber-200/60' : 'border-gray-200/60'">
                                    <a
                                        v-for="att in item.attachments"
                                        :key="att.id"
                                        :href="att.download_url"
                                        class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg
                                               bg-white/70 hover:bg-white border border-white/80
                                               transition-colors group w-fit max-w-full"
                                    >
                                        <span class="w-5 h-5 rounded bg-gray-100 flex items-center
                                                     justify-center text-[8px] font-bold text-gray-500 flex-shrink-0">
                                            {{ fileExt(att.file_name) }}
                                        </span>
                                        <span class="text-[12px] text-gray-600 truncate max-w-[180px]">
                                            {{ att.file_name }}
                                        </span>
                                        <span class="text-[11px] text-gray-400 flex-shrink-0">
                                            {{ formatSize(att.file_size) }}
                                        </span>
                                        <svg class="w-3 h-3 text-gray-300 group-hover:text-gray-600
                                                   flex-shrink-0 transition-colors"
                                             fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Reply box -->
        <TicketReplyBox :ticket-id="ticket.id" :can-internal-note="canInternalNote" />

    </div>
</template>
