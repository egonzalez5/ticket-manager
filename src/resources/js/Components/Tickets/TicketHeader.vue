<script setup>
import { Link } from '@inertiajs/vue3'
import { statusFor, priorityFor } from '@/composables/useTicketHelpers'

defineProps({
    ticket: { type: Object, required: true },
})
</script>

<template>
    <header class="mb-7">
        <div class="flex items-center gap-3 mb-3">
            <Link
                href="/tickets"
                class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400
                       hover:text-gray-700 hover:bg-white/80 transition-colors
                       border border-transparent hover:border-gray-200"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </Link>

            <span class="text-xs font-mono text-gray-400 tracking-wide">TICK-{{ ticket.id }}</span>
            <span class="text-gray-300">·</span>

            <span v-if="ticket.status" :class="['badge', statusFor(ticket.status.slug).badge]">
                <span :class="['w-1.5 h-1.5 rounded-full', statusFor(ticket.status.slug).dot]" />
                {{ statusFor(ticket.status.slug).label }}
            </span>

            <span v-if="ticket.priority"
                  :class="['badge hidden sm:inline-flex', priorityFor(ticket.priority.level).badge]">
                <span class="font-bold text-[10px] leading-none">{{ priorityFor(ticket.priority.level).icon }}</span>
                {{ priorityFor(ticket.priority.level).label }}
            </span>

            <span v-if="ticket.is_overdue" class="badge badge-red">Overdue</span>
        </div>

        <h1 class="text-[22px] font-semibold text-gray-900 leading-snug">{{ ticket.title }}</h1>
    </header>
</template>
