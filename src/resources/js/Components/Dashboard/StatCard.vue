<script setup>
defineProps({
    label:     { type: String,           required: true },
    value:     { type: [String, Number], required: true },
    sub:       { type: String,           default: null  },
    subType:   { type: String,           default: 'neutral' }, // 'up' | 'down' | 'neutral' | 'warn'
    iconBg:    { type: String,           default: 'bg-gray-100' },
    iconColor: { type: String,           default: 'text-gray-500' },
})
</script>

<template>
    <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm flex flex-col gap-4">

        <!-- Icon + label -->
        <div class="flex items-center justify-between">
            <span class="text-[12px] font-medium text-gray-400 uppercase tracking-wide">{{ label }}</span>
            <div :class="['w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0', iconBg]">
                <span :class="['w-4 h-4', iconColor]">
                    <slot name="icon" />
                </span>
            </div>
        </div>

        <!-- Value -->
        <div class="flex items-end justify-between gap-2">
            <span class="text-[28px] font-bold text-gray-900 leading-none tabular-nums">
                {{ value ?? '—' }}
            </span>

            <!-- Sub text / delta -->
            <span
                v-if="sub"
                :class="[
                    'text-[11.5px] font-medium mb-0.5 px-1.5 py-0.5 rounded-md',
                    subType === 'up'      && 'text-emerald-700 bg-emerald-50',
                    subType === 'down'    && 'text-red-600 bg-red-50',
                    subType === 'warn'    && 'text-amber-700 bg-amber-50',
                    subType === 'neutral' && 'text-gray-400 bg-gray-50',
                ]"
            >
                {{ sub }}
            </span>
        </div>
    </div>
</template>
