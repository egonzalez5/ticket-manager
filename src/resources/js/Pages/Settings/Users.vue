<script setup>
import { ref, watch } from 'vue'
import { router, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import UserFormModal from '@/Components/Settings/UserFormModal.vue'
import { initials, avatarColor, relDate, absDate } from '@/composables/useTicketHelpers'

const props = defineProps({
    users:   { type: Object, default: () => ({}) },
    roles:   { type: Array,  default: () => [] },
    filters: { type: Object, default: () => ({}) },
})

// ── Filters ───────────────────────────────────────────────────────────────────
const search = ref(props.filters.search ?? '')
const role   = ref(props.filters.role   ?? '')
const status = ref(props.filters.status ?? '')

const applyFilters = () => {
    const params = {}
    if (search.value) params.search = search.value
    if (role.value)   params.role   = role.value
    if (status.value) params.status = status.value

    router.get(route('settings.users.index'), params, {
        preserveState: true,
        replace: true,
        only: ['users', 'filters'],
    })
}

let searchTimer = null
watch(search, () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(applyFilters, 350)
})
watch([role, status], applyFilters)

const clearFilters = () => {
    search.value = ''
    role.value   = ''
    status.value = ''
    applyFilters()
}

const hasFilters = () => search.value || role.value || status.value

// ── Modal ─────────────────────────────────────────────────────────────────────
const showModal  = ref(false)
const editingUser = ref(null)

const openCreate = () => { editingUser.value = null; showModal.value = true }
const openEdit   = (u) => { editingUser.value = u;    showModal.value = true }
const closeModal = () => { showModal.value = false }

// ── Toggle active ─────────────────────────────────────────────────────────────
const toggle = (u) => {
    router.patch(route('settings.users.toggle', u.id), {}, {
        preserveScroll: true,
        only: ['users'],
    })
}

// ── Helpers ───────────────────────────────────────────────────────────────────
const ROLE_STYLE = {
    admin: 'bg-violet-50 text-violet-700 ring-violet-200/80',
    agent: 'bg-indigo-50 text-indigo-700 ring-indigo-200/80',
    user:  'bg-gray-100  text-gray-500   ring-gray-200/80',
}
const roleStyle = (slug) => ROLE_STYLE[slug] ?? ROLE_STYLE.user

const rows = (props.users?.data ?? [])
const meta = (props.users?.meta ?? {})
</script>

<template>
    <Head title="Users · Settings" />

    <AuthenticatedLayout title="Settings">
        <div class="bg-gray-50 min-h-full px-6 py-7">

            <!-- ── Page header ────────────────────────────────────────── -->
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h1 class="text-[17px] font-semibold text-gray-900">Users</h1>
                    <p class="text-[13px] text-gray-400 mt-0.5">Manage team members, roles and account access.</p>
                </div>
                <button
                    @click="openCreate"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gray-900 hover:bg-gray-700 text-white text-[13px] font-medium rounded-lg transition-colors shadow-sm"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    New User
                </button>
            </div>

            <!-- ── Filters ────────────────────────────────────────────── -->
            <div class="flex items-center gap-2 flex-wrap mb-4">

                <!-- Search -->
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1110.5 18a7.5 7.5 0 016.15-3.35z" />
                    </svg>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search users…"
                        class="pl-9 pr-3 py-[7px] text-[13px] bg-white border border-gray-200 rounded-lg placeholder-gray-300 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/25 focus:border-indigo-400 w-52 transition-all"
                    />
                </div>

                <!-- Role -->
                <div class="relative">
                    <select v-model="role"
                        class="py-[7px] pl-3 pr-7 text-[13px] bg-white border border-gray-200 rounded-lg text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/25 focus:border-indigo-400 cursor-pointer appearance-none">
                        <option value="">All roles</option>
                        <option v-for="r in roles" :key="r.id" :value="r.slug">{{ r.name }}</option>
                    </select>
                    <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>

                <!-- Status -->
                <div class="relative">
                    <select v-model="status"
                        class="py-[7px] pl-3 pr-7 text-[13px] bg-white border border-gray-200 rounded-lg text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/25 focus:border-indigo-400 cursor-pointer appearance-none">
                        <option value="">All statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>

                <button v-if="hasFilters()" @click="clearFilters"
                    class="px-2 py-[7px] text-[13px] text-gray-400 hover:text-gray-600 transition-colors">
                    Clear
                </button>

                <!-- count -->
                <span v-if="meta.total" class="ml-auto text-[12.5px] text-gray-400">
                    {{ meta.from }}–{{ meta.to }} of {{ meta.total }}
                </span>
            </div>

            <!-- ── Table ──────────────────────────────────────────────── -->
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/60">
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-widest">User</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-widest">Role</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-widest hidden md:table-cell">Last active</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-widest hidden lg:table-cell">Joined</th>
                            <th class="px-5 py-3 w-24" />
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Empty state -->
                        <tr v-if="!users?.data?.length">
                            <td colspan="6" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-[13.5px] font-semibold text-gray-700">No users found</p>
                                    <p class="text-xs text-gray-400">Try adjusting your filters</p>
                                </div>
                            </td>
                        </tr>

                        <!-- Rows -->
                        <tr
                            v-for="u in users?.data"
                            :key="u.id"
                            class="border-b border-gray-50 hover:bg-gray-50/60 transition-colors"
                        >
                            <!-- User -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div :class="['w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-white text-[11px] font-bold', avatarColor(u.id)]">
                                        {{ initials(u.name) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[13.5px] font-medium text-gray-900 truncate">{{ u.name }}</p>
                                        <p class="text-[12px] text-gray-400 truncate">{{ u.email }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Role -->
                            <td class="px-5 py-3.5">
                                <span v-if="u.role"
                                    :class="['inline-flex items-center px-2 py-0.5 rounded-full text-[11.5px] font-medium ring-1', roleStyle(u.role.slug)]">
                                    {{ u.role.name }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-3.5">
                                <span :class="[
                                    'inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11.5px] font-medium ring-1',
                                    u.active
                                        ? 'bg-emerald-50 text-emerald-700 ring-emerald-200/80'
                                        : 'bg-gray-100 text-gray-400 ring-gray-200/80'
                                ]">
                                    <span :class="['w-1.5 h-1.5 rounded-full', u.active ? 'bg-emerald-500' : 'bg-gray-300']" />
                                    {{ u.active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <!-- Last active -->
                            <td class="px-5 py-3.5 hidden md:table-cell">
                                <span class="text-[12.5px] text-gray-400">
                                    {{ u.last_login_at ? relDate(u.last_login_at) : 'Never' }}
                                </span>
                            </td>

                            <!-- Joined -->
                            <td class="px-5 py-3.5 hidden lg:table-cell">
                                <span class="text-[12.5px] text-gray-400">
                                    {{ new Date(u.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- Edit -->
                                    <button
                                        @click="openEdit(u)"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors"
                                        title="Edit user"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <!-- Toggle -->
                                    <button
                                        @click="toggle(u)"
                                        :class="['p-1.5 rounded-lg transition-colors',
                                                 u.active
                                                    ? 'text-gray-400 hover:text-red-500 hover:bg-red-50'
                                                    : 'text-gray-400 hover:text-emerald-600 hover:bg-emerald-50']"
                                        :title="u.active ? 'Deactivate' : 'Activate'"
                                    >
                                        <svg v-if="u.active" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                        <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div
                    v-if="meta.last_page > 1"
                    class="flex items-center justify-between px-5 py-3 border-t border-gray-100 bg-white"
                >
                    <span class="text-[12.5px] text-gray-400">
                        Page {{ meta.current_page }} of {{ meta.last_page }}
                    </span>
                    <div class="flex items-center gap-1">
                        <button
                            :disabled="meta.current_page <= 1"
                            @click="router.get(route('settings.users.index'), { ...filters, page: meta.current_page - 1 }, { preserveState: true })"
                            class="px-3 py-1.5 text-[13px] rounded-md border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        >← Prev</button>
                        <button
                            :disabled="meta.current_page >= meta.last_page"
                            @click="router.get(route('settings.users.index'), { ...filters, page: meta.current_page + 1 }, { preserveState: true })"
                            class="px-3 py-1.5 text-[13px] rounded-md border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        >Next →</button>
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal -->
        <UserFormModal
            :show="showModal"
            :user="editingUser"
            :roles="roles"
            @close="closeModal"
        />
    </AuthenticatedLayout>
</template>
