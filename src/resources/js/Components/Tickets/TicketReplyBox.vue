<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    ticketId:        { type: Number,  required: true },
    canInternalNote: { type: Boolean, default: false },
})

const fileInputRef  = ref(null)
const selectedFiles = ref([])

const form = useForm({
    message:     '',
    is_internal: false,
    attachments: [],
})

const onFileChange = (e) => {
    selectedFiles.value = Array.from(e.target.files)
    form.attachments    = Array.from(e.target.files)
}

const removeFile = (idx) => {
    selectedFiles.value.splice(idx, 1)
    form.attachments = [...selectedFiles.value]
    if (selectedFiles.value.length === 0 && fileInputRef.value) fileInputRef.value.value = ''
}

const submit = () => {
    if (!form.message.trim()) return
    form.post(`/tickets/${props.ticketId}/messages`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            selectedFiles.value = []
            if (fileInputRef.value) fileInputRef.value.value = ''
        },
    })
}
</script>

<template>
    <div class="border-t border-gray-100">

        <!-- Tabs: Reply / Internal Note (staff only) -->
        <div v-if="canInternalNote" class="flex border-b border-gray-100 px-6 pt-1">
            <button
                @click="form.is_internal = false"
                :class="[
                    'px-3 py-2.5 text-xs font-medium border-b-2 -mb-px transition-colors',
                    !form.is_internal
                        ? 'border-indigo-500 text-indigo-600'
                        : 'border-transparent text-gray-400 hover:text-gray-600',
                ]"
            >Reply</button>
            <button
                @click="form.is_internal = true"
                :class="[
                    'px-3 py-2.5 text-xs font-medium border-b-2 -mb-px transition-colors flex items-center gap-1.5',
                    form.is_internal
                        ? 'border-amber-500 text-amber-600'
                        : 'border-transparent text-gray-400 hover:text-gray-600',
                ]"
            >
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                          d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                          clip-rule="evenodd" />
                </svg>
                Internal Note
            </button>
        </div>

        <!-- Textarea area -->
        <div :class="['px-6 pt-5 pb-3 transition-colors', form.is_internal ? 'bg-amber-50/40' : '']">
            <textarea
                v-model="form.message"
                rows="3"
                :placeholder="form.is_internal
                    ? 'Write an internal note (only visible to agents)…'
                    : 'Add a comment…'"
                class="w-full text-[13.5px] text-gray-700 placeholder-gray-300
                       resize-none focus:outline-none bg-transparent leading-relaxed"
            />

            <p v-if="form.errors.message" class="mt-1 text-xs text-red-500">
                {{ form.errors.message }}
            </p>

            <!-- Selected files -->
            <div v-if="selectedFiles.length" class="flex flex-wrap gap-1.5 mt-2">
                <span
                    v-for="(f, i) in selectedFiles"
                    :key="i"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full
                           bg-gray-100 text-[11px] text-gray-600"
                >
                    <span class="max-w-[120px] truncate">{{ f.name }}</span>
                    <button @click="removeFile(i)" type="button"
                            class="text-gray-400 hover:text-gray-700">×</button>
                </span>
            </div>
        </div>

        <!-- Toolbar: attach + send -->
        <div :class="[
            'flex items-center justify-between px-5 py-3 border-t border-gray-100',
            form.is_internal ? 'bg-amber-50/40' : 'bg-gray-50/60',
        ]">
            <button
                @click="fileInputRef.click()"
                type="button"
                class="inline-flex items-center gap-1.5 text-[12.5px] text-gray-400
                       hover:text-gray-600 font-medium transition-colors"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                </svg>
                Attach file
            </button>

            <input ref="fileInputRef" type="file" multiple class="hidden"
                   accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.zip"
                   @change="onFileChange" />

            <button
                @click="submit"
                :disabled="!form.message.trim() || form.processing"
                type="button"
                :class="[
                    'inline-flex items-center gap-1.5 px-4 py-2 rounded-lg',
                    'text-[13px] font-medium transition-all',
                    'disabled:opacity-40 disabled:cursor-not-allowed',
                    form.is_internal
                        ? 'bg-amber-500 hover:bg-amber-600 text-white'
                        : 'bg-indigo-600 hover:bg-indigo-700 text-white',
                ]"
            >
                <svg v-if="form.processing" class="w-3.5 h-3.5 animate-spin"
                     fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10"
                            stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor"
                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <template v-else>
                    {{ form.is_internal ? 'Save note' : 'Add comment' }}
                </template>
            </button>
        </div>

    </div>
</template>
