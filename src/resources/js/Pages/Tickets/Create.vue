<script setup>
import { ref } from 'vue'
import { useForm, Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    categories: { type: Array, default: () => [] },
    priorities:  { type: Array, default: () => [] },
    tags:        { type: Array, default: () => [] },
    slas:        { type: Array, default: () => [] },
})

const ALLOWED_MIME_TYPES = [
    'image/jpeg', 'image/png', 'image/gif', 'image/webp',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'text/csv', 'text/plain',
    'application/zip',
]
const MAX_FILE_SIZE  = 10 * 1024 * 1024   // 10 MB
const MAX_FILE_COUNT = 5

const form = useForm({
    title:       '',
    description: '',
    category_id: '',
    priority_id: '',
    sla_id:      '',
    tags:        [],
    attachments: [],
})

// ── Tags ─────────────────────────────────────────────────────────────────────
const toggleTag = (id) => {
    const idx = form.tags.indexOf(id)
    idx === -1 ? form.tags.push(id) : form.tags.splice(idx, 1)
}

// ── Attachments ──────────────────────────────────────────────────────────────
const fileInputRef   = ref(null)
const dragActive     = ref(false)
const fileErrors     = ref([])

const formatSize = (bytes) => {
    if (bytes < 1024)        return `${bytes} B`
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

const fileExt = (name) => (name.split('.').pop() ?? '').toUpperCase().slice(0, 4)

const validateFiles = (incoming) => {
    const errs = []
    const remaining = MAX_FILE_COUNT - form.attachments.length

    if (incoming.length > remaining) {
        errs.push(`Máximo ${MAX_FILE_COUNT} archivos. Solo se agregarán los primeros ${remaining}.`)
        incoming = incoming.slice(0, remaining)
    }

    const valid = []
    for (const f of incoming) {
        if (f.size > MAX_FILE_SIZE) {
            errs.push(`"${f.name}" supera 10 MB.`)
            continue
        }
        if (!ALLOWED_MIME_TYPES.includes(f.type)) {
            errs.push(`"${f.name}" tipo no permitido.`)
            continue
        }
        if (form.attachments.some(a => a.name === f.name && a.size === f.size)) {
            continue  // duplicado silencioso
        }
        valid.push(f)
    }

    fileErrors.value = errs
    return valid
}

const addFiles = (rawFiles) => {
    const valid = validateFiles(Array.from(rawFiles))
    form.attachments = [...form.attachments, ...valid]
}

const onFileInput = (e) => {
    addFiles(e.target.files)
    e.target.value = ''
}

const onDrop = (e) => {
    dragActive.value = false
    addFiles(e.dataTransfer.files)
}

const removeFile = (idx) => {
    form.attachments = form.attachments.filter((_, i) => i !== idx)
    fileErrors.value = []
}

// ── Submit ────────────────────────────────────────────────────────────────────
const submit = () => {
    fileErrors.value = []
    form.post(route('tickets.store'))
}
</script>

<template>
    <Head title="New Ticket" />

    <AuthenticatedLayout title="Tickets">
        <div class="bg-gray-50 min-h-full px-6 py-7">

            <!-- Page header -->
            <div class="flex items-center gap-3 mb-6">
                <button
                    @click="router.visit(route('tickets.index'))"
                    class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <h1 class="text-[15px] font-semibold text-gray-900">New Ticket</h1>
            </div>

            <form @submit.prevent="submit" class="max-w-2xl space-y-5">

                <!-- ── Title + Description ──────────────────────────────── -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-5">

                    <div>
                        <label class="block text-[12.5px] font-medium text-gray-700 mb-1.5">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            placeholder="Brief description of the issue…"
                            class="w-full px-3 py-2 text-[13.5px] border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/25 focus:border-indigo-400 transition-all placeholder-gray-300"
                            :class="form.errors.title ? 'border-red-300' : 'border-gray-200'"
                        />
                        <p v-if="form.errors.title" class="mt-1 text-[11.5px] text-red-500">{{ form.errors.title }}</p>
                    </div>

                    <div>
                        <label class="block text-[12.5px] font-medium text-gray-700 mb-1.5">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="6"
                            placeholder="Describe the issue in detail…"
                            class="w-full px-3 py-2 text-[13.5px] border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/25 focus:border-indigo-400 transition-all placeholder-gray-300 resize-none"
                            :class="form.errors.description ? 'border-red-300' : 'border-gray-200'"
                        />
                        <p v-if="form.errors.description" class="mt-1 text-[11.5px] text-red-500">{{ form.errors.description }}</p>
                    </div>
                </div>

                <!-- ── Category / Priority / SLA / Tags ────────────────── -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-5">

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Category -->
                        <div>
                            <label class="block text-[12.5px] font-medium text-gray-700 mb-1.5">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select
                                    v-model="form.category_id"
                                    class="w-full pl-3 pr-7 py-2 text-[13.5px] border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/25 focus:border-indigo-400 transition-all appearance-none"
                                    :class="form.errors.category_id ? 'border-red-300' : 'border-gray-200'"
                                >
                                    <option value="">Select category…</option>
                                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                                <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                            <p v-if="form.errors.category_id" class="mt-1 text-[11.5px] text-red-500">{{ form.errors.category_id }}</p>
                        </div>

                        <!-- Priority -->
                        <div>
                            <label class="block text-[12.5px] font-medium text-gray-700 mb-1.5">
                                Priority <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select
                                    v-model="form.priority_id"
                                    class="w-full pl-3 pr-7 py-2 text-[13.5px] border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/25 focus:border-indigo-400 transition-all appearance-none"
                                    :class="form.errors.priority_id ? 'border-red-300' : 'border-gray-200'"
                                >
                                    <option value="">Select priority…</option>
                                    <option v-for="p in priorities" :key="p.id" :value="p.id">{{ p.name }}</option>
                                </select>
                                <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                            <p v-if="form.errors.priority_id" class="mt-1 text-[11.5px] text-red-500">{{ form.errors.priority_id }}</p>
                        </div>
                    </div>

                    <!-- SLA (optional) -->
                    <div v-if="slas.length">
                        <label class="block text-[12.5px] font-medium text-gray-700 mb-1.5">SLA</label>
                        <div class="relative">
                            <select
                                v-model="form.sla_id"
                                class="w-full pl-3 pr-7 py-2 text-[13.5px] border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/25 focus:border-indigo-400 transition-all appearance-none"
                            >
                                <option value="">No SLA</option>
                                <option v-for="s in slas" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                            <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <!-- Tags -->
                    <div v-if="tags.length">
                        <label class="block text-[12.5px] font-medium text-gray-700 mb-2">Tags</label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="tag in tags"
                                :key="tag.id"
                                type="button"
                                @click="toggleTag(tag.id)"
                                :class="[
                                    'px-2.5 py-1 rounded-full text-xs border transition-all',
                                    form.tags.includes(tag.id)
                                        ? 'bg-indigo-50 border-indigo-300 text-indigo-700 font-medium'
                                        : 'bg-white border-gray-200 text-gray-500 hover:border-gray-300',
                                ]"
                            >
                                {{ tag.name }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ── Attachments ──────────────────────────────────────── -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="text-[12.5px] font-medium text-gray-700">
                            Attachments
                            <span class="ml-1 text-gray-400 font-normal">(max {{ MAX_FILE_COUNT }} files · 10 MB each)</span>
                        </label>
                        <span v-if="form.attachments.length" class="text-[11.5px] text-gray-400">
                            {{ form.attachments.length }}/{{ MAX_FILE_COUNT }}
                        </span>
                    </div>

                    <!-- Drop zone -->
                    <div
                        v-if="form.attachments.length < MAX_FILE_COUNT"
                        @dragover.prevent="dragActive = true"
                        @dragleave.prevent="dragActive = false"
                        @drop.prevent="onDrop"
                        @click="fileInputRef.click()"
                        :class="[
                            'flex flex-col items-center justify-center gap-2 py-8 border-2 border-dashed rounded-xl cursor-pointer transition-all select-none',
                            dragActive
                                ? 'border-indigo-400 bg-indigo-50'
                                : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50',
                        ]"
                    >
                        <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                        </svg>
                        <p class="text-[13px] text-gray-400">
                            Drop files here or <span class="text-indigo-600 font-medium">browse</span>
                        </p>
                        <p class="text-[11.5px] text-gray-300">
                            PDF, images, Office, ZIP — up to 10 MB each
                        </p>
                        <input
                            ref="fileInputRef"
                            type="file"
                            multiple
                            class="hidden"
                            accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.zip"
                            @change="onFileInput"
                        />
                    </div>

                    <!-- Client-side errors -->
                    <div v-if="fileErrors.length" class="space-y-1">
                        <p v-for="(err, i) in fileErrors" :key="i" class="text-[11.5px] text-amber-600">
                            {{ err }}
                        </p>
                    </div>

                    <!-- Server-side errors -->
                    <p v-if="form.errors.attachments" class="text-[11.5px] text-red-500">
                        {{ form.errors.attachments }}
                    </p>

                    <!-- File list -->
                    <div v-if="form.attachments.length" class="space-y-2">
                        <div
                            v-for="(file, idx) in form.attachments"
                            :key="idx"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-gray-100 bg-gray-50"
                        >
                            <span class="w-7 h-7 rounded-md bg-white border border-gray-200 flex items-center justify-center text-[9px] font-bold text-gray-500 flex-shrink-0">
                                {{ fileExt(file.name) }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-[12.5px] text-gray-700 truncate font-medium">{{ file.name }}</p>
                                <p class="text-[11px] text-gray-400">{{ formatSize(file.size) }}</p>
                            </div>
                            <button
                                type="button"
                                @click="removeFile(idx)"
                                class="p-1 rounded text-gray-300 hover:text-red-400 hover:bg-red-50 transition-colors flex-shrink-0"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Upload progress -->
                    <div v-if="form.progress" class="space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-[11.5px] text-gray-500">Uploading…</span>
                            <span class="text-[11.5px] text-gray-500">{{ form.progress.percentage }}%</span>
                        </div>
                        <div class="h-1 bg-gray-100 rounded-full overflow-hidden">
                            <div
                                class="h-full bg-indigo-500 rounded-full transition-all duration-150"
                                :style="{ width: `${form.progress.percentage}%` }"
                            />
                        </div>
                    </div>
                </div>

                <!-- ── Actions ──────────────────────────────────────────── -->
                <div class="flex items-center gap-3 justify-end">
                    <button
                        type="button"
                        @click="router.visit(route('tickets.index'))"
                        class="px-4 py-2 text-[13px] font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 text-[13px] font-medium text-white bg-gray-900 rounded-lg hover:bg-gray-700 disabled:opacity-50 transition-colors shadow-sm"
                    >
                        <span v-if="form.processing">Creating…</span>
                        <span v-else>Create Ticket</span>
                    </button>
                </div>

            </form>
        </div>
    </AuthenticatedLayout>
</template>
