<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import TicketHeader   from '@/Components/Tickets/TicketHeader.vue'
import TicketActivity from '@/Components/Tickets/TicketActivity.vue'
import TicketSidebar  from '@/Components/Tickets/TicketSidebar.vue'
import { formatSize, fileExt } from '@/composables/useTicketHelpers'

const props = defineProps({
    ticket:     { type: Object, required: true },
    statuses:   { type: Array,  default: () => [] },
    priorities: { type: Array,  default: () => [] },
    agents:     { type: Array,  default: () => [] },
    can:        { type: Object, default: () => ({}) },
})

const attachments = computed(() => props.ticket.attachments ?? [])
</script>

<template>
    <Head :title="`#${ticket.id} – ${ticket.title}`" />

    <AuthenticatedLayout title="Tickets">
        <div class="bg-gray-50 min-h-full">
            <div class="px-6 py-7">

                <!-- Page header -->
                <TicketHeader :ticket="ticket" />

                <!-- Two-column layout -->
                <div class="flex gap-6 items-start">

                    <!-- Main column -->
                    <div class="flex-1 min-w-0 space-y-5">

                        <!-- Description card (inline: small, no dedicated component needed) -->
                        <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                            <h2 class="text-[13px] font-semibold text-gray-900 mb-3">Description</h2>
                            <p class="text-sm text-gray-500 leading-relaxed whitespace-pre-wrap">
                                {{ ticket.description }}
                            </p>

                            <!-- Tags -->
                            <div v-if="ticket.tags?.length" class="flex flex-wrap gap-2 mt-4">
                                <span
                                    v-for="tag in ticket.tags"
                                    :key="tag.id"
                                    class="px-2.5 py-0.5 border border-gray-200 rounded-full text-xs text-gray-500"
                                >{{ tag.name }}</span>
                            </div>

                            <!-- Ticket-level attachments -->
                            <div v-if="attachments.length" class="mt-4 pt-4 border-t border-gray-100 space-y-1.5">
                                <a
                                    v-for="att in attachments"
                                    :key="att.id"
                                    :href="att.download_url"
                                    class="flex items-center gap-3 px-3 py-2 rounded-lg
                                           border border-gray-100 hover:bg-gray-50 transition-colors group"
                                >
                                    <span class="w-7 h-7 rounded-md bg-gray-100 flex items-center justify-center
                                                 text-[9px] font-bold text-gray-500 flex-shrink-0">
                                        {{ fileExt(att.file_name) }}
                                    </span>
                                    <span class="text-[12.5px] text-gray-600 flex-1 truncate">{{ att.file_name }}</span>
                                    <span class="text-[11px] text-gray-400">{{ formatSize(att.file_size) }}</span>
                                    <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-gray-500
                                               flex-shrink-0 transition-colors"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <!-- Activity feed + Reply box -->
                        <TicketActivity :ticket="ticket" :can-internal-note="can.internalNote ?? false" />

                    </div>

                    <!-- Sidebar -->
                    <TicketSidebar
                        :ticket="ticket"
                        :statuses="statuses"
                        :priorities="priorities"
                        :agents="agents"
                        :can="can"
                    />

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
