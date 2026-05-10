<script setup>
import { computed, ref } from 'vue'
import { router, useForm, Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    ticket:     Object,
    statuses:   Array,
    priorities: Array,
    agents:     Array,
    canUpdate:  Boolean,
    isStaff:    Boolean,
})

// ── Helpers ───────────────────────────────────────────────────────────────────
const initials = (name) =>
    name ? name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase() : '?'

const relDate = (iso) => {
    if (!iso) return '—'
    const secs = Math.floor((Date.now() - new Date(iso)) / 1000)
    if (secs < 60)    return 'just now'
    if (secs < 3600)  return `${Math.floor(secs / 60)}m ago`
    if (secs < 86400) return `${Math.floor(secs / 3600)}h ago`
    const days = Math.floor(secs / 86400)
    if (days < 7) return `${days}d ago`
    return new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const absDate = (iso) => {
    if (!iso) return '—'
    return new Date(iso).toLocaleString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    })
}

const formatMinutes = (mins) => {
    if (!mins) return '—'
    const h = Math.floor(mins / 60)
    const m = mins % 60
    if (!h) return `${m}m`
    return m ? `${h}h ${m}m` : `${h}h`
}

const formatSize = (bytes) => {
    if (!bytes) return ''
    if (bytes < 1024)    return `${bytes} B`
    if (bytes < 1048576) return `${Math.round(bytes / 1024)} KB`
    return `${(bytes / 1048576).toFixed(1)} MB`
}

const fileExt = (name) => (name?.split('.').pop() ?? '').toUpperCase()

// ── Badge / color maps ────────────────────────────────────────────────────────
const STATUS_MAP = {
    open:        { label: 'Open',        badge: 'badge-blue',    dot: 'bg-blue-500'    },
    in_progress: { label: 'In Progress', badge: 'badge-amber',   dot: 'bg-amber-500'   },
    pending:     { label: 'Pending',     badge: 'badge-violet',  dot: 'bg-violet-500'  },
    resolved:    { label: 'Resolved',    badge: 'badge-emerald', dot: 'bg-emerald-500' },
    closed:      { label: 'Closed',      badge: 'badge-gray',    dot: 'bg-gray-400'    },
}
const PRIORITY_MAP = {
    3: { label: 'High',   badge: 'badge-red',     icon: '↑' },
    2: { label: 'Medium', badge: 'badge-orange',  icon: '→' },
    1: { label: 'Low',    badge: 'badge-emerald', icon: '↓' },
}
const AVATAR_COLORS = [
    'bg-indigo-500', 'bg-violet-500', 'bg-blue-500',   'bg-emerald-500',
    'bg-amber-500',  'bg-rose-500',   'bg-teal-500',   'bg-cyan-500',
    'bg-pink-500',   'bg-sky-500',    'bg-orange-500', 'bg-lime-600',
]

const statusFor   = (s) => STATUS_MAP[s]   ?? { label: s ?? '—', badge: 'badge-gray', dot: 'bg-gray-400' }
const priorityFor = (l) => PRIORITY_MAP[l] ?? { label: '—',       badge: 'badge-gray', icon: '•' }
const avatarColor = (id) => AVATAR_COLORS[(id ?? 0) % AVATAR_COLORS.length]
const isAgentUser = (item) => ['admin', 'agent'].includes(item.user?.role?.slug)

// ── Computed ──────────────────────────────────────────────────────────────────
const attachments = computed(() => props.ticket.attachments ?? [])
const canEdit     = computed(() => props.canUpdate && props.isStaff)

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

const slaWarning = computed(() => {
    if (!props.ticket.due_date) return null
    const msLeft = new Date(props.ticket.due_date) - Date.now()
    if (msLeft < 0) return {
        type: 'overdue',
        label: 'SLA Breached',
        sub: `Overdue by ${formatMinutes(Math.abs(Math.ceil(msLeft / 60000)))}`,
    }
    if (msLeft < 4 * 3600 * 1000) return {
        type: 'warning',
        label: 'SLA Warning',
        sub: `${formatMinutes(Math.ceil(msLeft / 60000))} remaining until breach`,
    }
    return null
})

// ── Sidebar updates ───────────────────────────────────────────────────────────
const updateField = (data) => {
    router.patch(route('tickets.update', props.ticket.id), data, { preserveScroll: true })
}
const onStatusChange   = (e) => updateField({ status_id:   parseInt(e.target.value) })
const onPriorityChange = (e) => updateField({ priority_id: parseInt(e.target.value) })
const onAssigneeChange = (e) => updateField({ assigned_to: e.target.value ? parseInt(e.target.value) : null })

// ── Reply form ────────────────────────────────────────────────────────────────
const fileInputRef  = ref(null)
const selectedFiles = ref([])

const replyForm = useForm({
    message:     '',
    is_internal: false,
    attachments: [],
})

const onFileChange = (e) => {
    selectedFiles.value   = Array.from(e.target.files)
    replyForm.attachments = Array.from(e.target.files)
}

const removeFile = (idx) => {
    selectedFiles.value.splice(idx, 1)
    replyForm.attachments = [...selectedFiles.value]
    if (selectedFiles.value.length === 0 && fileInputRef.value) fileInputRef.value.value = ''
}

const submitReply = () => {
    if (!replyForm.message.trim()) return
    replyForm.post(`/tickets/${props.ticket.id}/messages`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            replyForm.reset()
            selectedFiles.value = []
            if (fileInputRef.value) fileInputRef.value.value = ''
        },
    })
}
</script>

<template>
    <Head :title="`#${ticket.id} – ${ticket.title}`" />

    <AuthenticatedLayout title="Tickets">

        <!-- ── Page wrapper ──────────────────────────────────────────────── -->
        <div class="bg-gray-50 min-h-full">
            <div class="px-6 py-7">

                <!-- ── Page header ───────────────────────────────────────── -->
                <header class="mb-7">
                    <div class="flex items-center gap-3 mb-3">
                        <Link
                            href="/tickets"
                            class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400
                                   hover:text-gray-700 hover:bg-white/80 transition-colors border border-transparent
                                   hover:border-gray-200"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </Link>
                        <span class="text-xs font-mono text-gray-400 tracking-wide">TICK-{{ ticket.id }}</span>
                        <span class="text-gray-300">·</span>
                        <span v-if="ticket.status" :class="['badge', statusFor(ticket.status.slug).badge]">
                            <span :class="['w-1.5 h-1.5 rounded-full', statusFor(ticket.status.slug).dot]" />
                            {{ statusFor(ticket.status.slug).label }}
                        </span>
                        <span v-if="ticket.priority" :class="['badge hidden sm:inline-flex', priorityFor(ticket.priority.level).badge]">
                            <span class="font-bold text-[10px] leading-none">{{ priorityFor(ticket.priority.level).icon }}</span>
                            {{ priorityFor(ticket.priority.level).label }}
                        </span>
                        <span v-if="ticket.is_overdue" class="badge badge-red">Overdue</span>
                    </div>
                    <h1 class="text-[22px] font-semibold text-gray-900 leading-snug">{{ ticket.title }}</h1>
                </header>

                <!-- ── Two-column: flex (robusto vs grid) ────────────────── -->
                <div class="flex gap-6 items-start">

                    <!-- ── MAIN: izquierda, toma el espacio disponible ───── -->
                    <div class="flex-1 min-w-0 space-y-5">

                        <!-- Description card -->
                        <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                            <h2 class="text-[13px] font-semibold text-gray-900 mb-3">Description</h2>
                            <p class="text-sm text-gray-500 leading-relaxed whitespace-pre-wrap">{{ ticket.description }}</p>

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
                                    class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50
                                           border border-gray-100 transition-colors group"
                                >
                                    <span class="w-7 h-7 rounded-md bg-gray-100 flex items-center justify-center
                                                 text-[9px] font-bold text-gray-500 flex-shrink-0">
                                        {{ fileExt(att.file_name) }}
                                    </span>
                                    <span class="text-[12.5px] text-gray-600 flex-1 truncate">{{ att.file_name }}</span>
                                    <span class="text-[11px] text-gray-400">{{ formatSize(att.file_size) }}</span>
                                    <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-gray-500 flex-shrink-0 transition-colors"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <!-- Activity + Reply card -->
                        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">

                            <!-- Card header -->
                            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                                <h2 class="text-[13px] font-semibold text-gray-900">Activity</h2>
                                <span class="text-xs text-gray-400">{{ timeline.length }} event{{ timeline.length === 1 ? '' : 's' }}</span>
                            </div>

                            <!-- Timeline feed -->
                            <div class="px-6 py-6">

                                <!-- Empty state -->
                                <div v-if="!timeline.length" class="py-10 text-center">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-5 h-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
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

                                            <!-- Message avatar -->
                                            <div
                                                v-if="item._type === 'message'"
                                                :class="[
                                                    'w-8 h-8 rounded-full flex items-center justify-center',
                                                    'text-white text-[10px] font-bold flex-shrink-0 shadow-sm',
                                                    avatarColor(item.user?.id),
                                                ]"
                                            >{{ initials(item.user?.name) }}</div>

                                            <!-- History event: icon in gray circle -->
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
                                            <div v-if="idx < timeline.length - 1" class="w-px flex-1 mt-2 mb-1 bg-gray-100" />
                                        </div>

                                        <!-- Right: content -->
                                        <div :class="['flex-1 min-w-0', idx < timeline.length - 1 ? 'pb-5' : 'pb-0']">

                                            <!-- ── History event: compact inline style ── -->
                                            <div v-if="item._type === 'history'"
                                                 class="flex items-center gap-2 pt-1.5 flex-wrap">
                                                <span class="text-[12.5px] font-medium text-gray-500">
                                                    {{ item.user?.name ?? 'System' }}
                                                </span>
                                                <span class="text-[12.5px] text-gray-400">{{ historyLabel(item) }}</span>
                                                <span class="text-[11px] text-gray-300">·</span>
                                                <span class="text-[11px] text-gray-400">{{ relDate(item.created_at) }}</span>
                                            </div>

                                            <!-- ── Message: card bubble style ── -->
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

                                                <!-- Bubble card: color by type -->
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
                                                    <div v-if="item.attachments?.length" class="mt-3 pt-3 border-t space-y-1.5"
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
                                                            <span class="text-[12px] text-gray-600 truncate max-w-[180px]">{{ att.file_name }}</span>
                                                            <span class="text-[11px] text-gray-400 flex-shrink-0">{{ formatSize(att.file_size) }}</span>
                                                            <svg class="w-3 h-3 text-gray-300 group-hover:text-gray-600 flex-shrink-0 transition-colors"
                                                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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

                            <!-- ── Reply area ─────────────────────────────── -->
                            <div class="border-t border-gray-100">

                                <!-- Tabs: Reply / Internal (staff only) -->
                                <div v-if="isStaff" class="flex border-b border-gray-100 px-6 pt-1">
                                    <button
                                        @click="replyForm.is_internal = false"
                                        :class="[
                                            'px-3 py-2.5 text-xs font-medium border-b-2 -mb-px transition-colors',
                                            !replyForm.is_internal
                                                ? 'border-indigo-500 text-indigo-600'
                                                : 'border-transparent text-gray-400 hover:text-gray-600',
                                        ]"
                                    >Reply</button>
                                    <button
                                        @click="replyForm.is_internal = true"
                                        :class="[
                                            'px-3 py-2.5 text-xs font-medium border-b-2 -mb-px transition-colors flex items-center gap-1.5',
                                            replyForm.is_internal
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

                                <!-- Textarea -->
                                <div :class="['px-6 pt-5 pb-3 transition-colors', replyForm.is_internal ? 'bg-amber-50/40' : '']">
                                    <textarea
                                        v-model="replyForm.message"
                                        rows="3"
                                        :placeholder="replyForm.is_internal
                                            ? 'Write an internal note (only visible to agents)…'
                                            : 'Add a comment…'"
                                        class="w-full text-[13.5px] text-gray-700 placeholder-gray-300
                                               resize-none focus:outline-none bg-transparent leading-relaxed"
                                    />
                                    <p v-if="replyForm.errors.message" class="mt-1 text-xs text-red-500">
                                        {{ replyForm.errors.message }}
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

                                <!-- Toolbar -->
                                <div :class="[
                                    'flex items-center justify-between px-5 py-3 border-t border-gray-100',
                                    replyForm.is_internal ? 'bg-amber-50/40' : 'bg-gray-50/60',
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
                                        @click="submitReply"
                                        :disabled="!replyForm.message.trim() || replyForm.processing"
                                        type="button"
                                        :class="[
                                            'inline-flex items-center gap-1.5 px-4 py-2 rounded-lg',
                                            'text-[13px] font-medium transition-all',
                                            'disabled:opacity-40 disabled:cursor-not-allowed',
                                            replyForm.is_internal
                                                ? 'bg-amber-500 hover:bg-amber-600 text-white'
                                                : 'bg-indigo-600 hover:bg-indigo-700 text-white',
                                        ]"
                                    >
                                        <svg v-if="replyForm.processing"
                                             class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4" />
                                            <path class="opacity-75" fill="currentColor"
                                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                        </svg>
                                        <template v-else>
                                            {{ replyForm.is_internal ? 'Save note' : 'Add comment' }}
                                        </template>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── SIDEBAR: ancho fijo, sticky ────────────────────── -->
                    <div class="w-72 xl:w-80 flex-none sticky top-6 space-y-4">

                        <!-- Details card -->
                        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">
                            <h2 class="text-[13px] font-semibold text-gray-900 mb-4">Details</h2>
                            <div class="space-y-4">

                                <!-- Status -->
                                <div>
                                    <label class="block text-[11px] text-gray-400 font-medium mb-1.5">Status</label>
                                    <div class="relative">
                                        <select
                                            v-if="canEdit"
                                            :value="ticket.status?.id"
                                            @change="onStatusChange"
                                            class="w-full appearance-none border border-gray-200 rounded-lg
                                                   px-3 py-2 text-[13px] text-gray-800 bg-white cursor-pointer pr-8
                                                   focus:outline-none focus:ring-2 focus:ring-indigo-500/20
                                                   focus:border-indigo-300 transition-colors"
                                        >
                                            <option v-for="s in statuses" :key="s.id" :value="s.id">{{ s.name }}</option>
                                        </select>
                                        <svg v-if="canEdit"
                                             class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5
                                                    text-gray-400 pointer-events-none"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                        <span v-if="!canEdit && ticket.status"
                                              :class="['badge', statusFor(ticket.status.slug).badge]">
                                            <span :class="['w-1.5 h-1.5 rounded-full', statusFor(ticket.status.slug).dot]" />
                                            {{ statusFor(ticket.status.slug).label }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Priority -->
                                <div>
                                    <label class="block text-[11px] text-gray-400 font-medium mb-1.5">Priority</label>
                                    <div class="relative">
                                        <select
                                            v-if="canEdit"
                                            :value="ticket.priority?.id"
                                            @change="onPriorityChange"
                                            class="w-full appearance-none border border-gray-200 rounded-lg
                                                   px-3 py-2 text-[13px] text-gray-800 bg-white cursor-pointer pr-8
                                                   focus:outline-none focus:ring-2 focus:ring-indigo-500/20
                                                   focus:border-indigo-300 transition-colors"
                                        >
                                            <option v-for="p in priorities" :key="p.id" :value="p.id">{{ p.name }}</option>
                                        </select>
                                        <svg v-if="canEdit"
                                             class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5
                                                    text-gray-400 pointer-events-none"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                        <span v-if="!canEdit && ticket.priority"
                                              :class="['badge', priorityFor(ticket.priority.level).badge]">
                                            <span class="font-bold text-[10px] leading-none">{{ priorityFor(ticket.priority.level).icon }}</span>
                                            {{ priorityFor(ticket.priority.level).label }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Assignee -->
                                <div>
                                    <label class="block text-[11px] text-gray-400 font-medium mb-1.5">Assignee</label>
                                    <div class="relative">
                                        <select
                                            v-if="canEdit"
                                            :value="ticket.assigned_to?.id ?? ''"
                                            @change="onAssigneeChange"
                                            class="w-full appearance-none border border-gray-200 rounded-lg
                                                   px-3 py-2 text-[13px] text-gray-800 bg-white cursor-pointer pr-8
                                                   focus:outline-none focus:ring-2 focus:ring-indigo-500/20
                                                   focus:border-indigo-300 transition-colors"
                                        >
                                            <option value="">Unassigned</option>
                                            <option v-for="a in agents" :key="a.id" :value="a.id">{{ a.name }}</option>
                                        </select>
                                        <svg v-if="canEdit"
                                             class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5
                                                    text-gray-400 pointer-events-none"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                        <div v-if="!canEdit" class="flex items-center gap-2">
                                            <div v-if="ticket.assigned_to"
                                                 :class="['w-5 h-5 rounded-full flex items-center justify-center',
                                                          'text-white text-[9px] font-bold', avatarColor(ticket.assigned_to.id)]">
                                                {{ initials(ticket.assigned_to.name) }}
                                            </div>
                                            <div v-else class="w-5 h-5 rounded-full border-2 border-dashed border-gray-200" />
                                            <span class="text-[13px] text-gray-700">
                                                {{ ticket.assigned_to?.name ?? 'Unassigned' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Category -->
                                <div v-if="ticket.category">
                                    <label class="block text-[11px] text-gray-400 font-medium mb-1">Category</label>
                                    <span class="text-[13px] text-gray-700">{{ ticket.category.name }}</span>
                                </div>

                            </div>
                        </div>

                        <!-- SLA Warning card (conditional) -->
                        <div
                            v-if="slaWarning"
                            :class="[
                                'rounded-2xl border p-4',
                                slaWarning.type === 'overdue'
                                    ? 'bg-red-50 border-red-200'
                                    : 'bg-amber-50 border-amber-200',
                            ]"
                        >
                            <div class="flex items-start gap-3">
                                <div :class="[
                                    'w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5',
                                    slaWarning.type === 'overdue' ? 'bg-red-100' : 'bg-amber-100',
                                ]">
                                    <svg class="w-3.5 h-3.5"
                                         :class="slaWarning.type === 'overdue' ? 'text-red-600' : 'text-amber-600'"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p :class="['text-[13px] font-semibold',
                                                slaWarning.type === 'overdue' ? 'text-red-700' : 'text-amber-700']">
                                        {{ slaWarning.label }}
                                    </p>
                                    <p :class="['text-[12px] mt-0.5',
                                                slaWarning.type === 'overdue' ? 'text-red-500' : 'text-amber-600']">
                                        {{ slaWarning.sub }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- SLA info card (sin warning) -->
                        <div v-if="ticket.sla && !slaWarning" class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">
                            <h2 class="text-[13px] font-semibold text-gray-900 mb-3">SLA</h2>
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-[11.5px] text-gray-400">Plan</dt>
                                    <dd class="text-[13px] text-gray-700 font-medium">{{ ticket.sla.name }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-[11.5px] text-gray-400">Response</dt>
                                    <dd class="text-[13px] text-gray-700">{{ formatMinutes(ticket.sla.response_time) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-[11.5px] text-gray-400">Resolution</dt>
                                    <dd class="text-[13px] text-gray-700">{{ formatMinutes(ticket.sla.resolution_time) }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Information card -->
                        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">
                            <h2 class="text-[13px] font-semibold text-gray-900 mb-4">Information</h2>
                            <div class="space-y-4">

                                <div class="flex items-start gap-3">
                                    <svg class="w-4 h-4 text-gray-300 flex-shrink-0 mt-0.5" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <div>
                                        <p class="text-[10.5px] text-gray-400 mb-0.5">Reporter</p>
                                        <p class="text-[13px] text-gray-800 font-medium">{{ ticket.user?.name ?? '—' }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <svg class="w-4 h-4 text-gray-300 flex-shrink-0 mt-0.5" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <div>
                                        <p class="text-[10.5px] text-gray-400 mb-0.5">Created</p>
                                        <p class="text-[13px] text-gray-700">{{ absDate(ticket.created_at) }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <svg class="w-4 h-4 text-gray-300 flex-shrink-0 mt-0.5" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <p class="text-[10.5px] text-gray-400 mb-0.5">Last Updated</p>
                                        <p class="text-[13px] text-gray-700">{{ absDate(ticket.updated_at) }}</p>
                                    </div>
                                </div>

                                <div v-if="ticket.due_date" class="flex items-start gap-3">
                                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5"
                                         :class="ticket.is_overdue ? 'text-red-400' : 'text-gray-300'"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <div>
                                        <p class="text-[10.5px] text-gray-400 mb-0.5">Due Date</p>
                                        <p :class="['text-[13px] font-medium', ticket.is_overdue ? 'text-red-600' : 'text-gray-700']">
                                            {{ absDate(ticket.due_date) }}
                                        </p>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                    <!-- end sidebar -->

                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
