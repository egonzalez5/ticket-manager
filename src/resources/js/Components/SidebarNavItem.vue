<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href:   { type: String,           required: true },
    active: { type: Boolean,          default: false },
    badge:  { type: [String, Number], default: null  },
});
</script>

<template>
    <Link
        :href="href"
        :class="[
            'group flex items-center gap-2.5 px-3 py-[7px] rounded-md text-[13px] font-medium transition-all duration-100 w-full',
            active
                ? 'bg-zinc-800 text-white'
                : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-zinc-200',
        ]"
    >
        <span class="flex-shrink-0 w-4 h-4 flex items-center justify-center opacity-80 group-hover:opacity-100"
              :class="active && 'opacity-100'">
            <slot name="icon" />
        </span>
        <span class="flex-1 truncate leading-none">
            <slot />
        </span>
        <span
            v-if="badge"
            class="ml-auto text-[11px] font-medium px-1.5 py-0.5 rounded-full tabular-nums"
            :class="active ? 'bg-zinc-700 text-zinc-300' : 'bg-zinc-800 text-zinc-500'"
        >
            {{ badge }}
        </span>
    </Link>
</template>
