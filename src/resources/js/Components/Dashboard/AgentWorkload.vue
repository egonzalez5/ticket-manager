<script setup>
import { computed } from 'vue'
import { initials, avatarColor } from '@/composables/useTicketHelpers'

const props = defineProps({
    agents: { type: Array, default: () => [] }, // [{ id, name, open_count, in_progress_count }]
})

const maxTotal = computed(() =>
    Math.max(...props.agents.map(a => a.open_count + a.in_progress_count), 1)
)
</script>

<template>
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">

        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-[13px] font-semibold text-gray-900">Agent Workload</h3>
        </div>

        <div v-if="!agents.length" class="flex items-center justify-center py-14 text-[13px] text-gray-300">
            No agents found
        </div>

        <ul v-else class="divide-y divide-gray-100">
            <li
                v-for="agent in agents"
                :key="agent.id"
                class="flex items-center gap-3 px-5 py-3.5"
            >
                <!-- Avatar -->
                <div :class="[
                    'w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0',
                    'text-white text-[10px] font-bold',
                    avatarColor(agent.id),
                ]">
                    {{ initials(agent.name) }}
                </div>

                <!-- Name + bars -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[13px] font-medium text-gray-700 truncate">{{ agent.name }}</span>
                        <span class="text-[11.5px] text-gray-400 ml-2 flex-shrink-0">
                            {{ agent.open_count + agent.in_progress_count }} active
                        </span>
                    </div>

                    <!-- Stacked progress bar: open + in_progress -->
                    <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden flex">
                        <div
                            class="h-full bg-indigo-500 rounded-l-full transition-all"
                            :style="{ width: `${(agent.open_count / maxTotal) * 100}%` }"
                        />
                        <div
                            class="h-full bg-amber-400 transition-all"
                            :style="{ width: `${(agent.in_progress_count / maxTotal) * 100}%` }"
                        />
                    </div>

                    <!-- Legend -->
                    <div class="flex items-center gap-3 mt-1">
                        <span class="flex items-center gap-1 text-[10.5px] text-gray-400">
                            <span class="w-2 h-2 rounded-sm bg-indigo-500 inline-block" />
                            {{ agent.open_count }} open
                        </span>
                        <span class="flex items-center gap-1 text-[10.5px] text-gray-400">
                            <span class="w-2 h-2 rounded-sm bg-amber-400 inline-block" />
                            {{ agent.in_progress_count }} in progress
                        </span>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template>
