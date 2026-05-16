<script setup>
import { watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    show:  { type: Boolean, required: true },
    user:  { type: Object,  default: null  },
    roles: { type: Array,   default: () => [] },
})
const emit = defineEmits(['close'])

const form = useForm({
    name:                  '',
    email:                 '',
    password:              '',
    password_confirmation: '',
    role_id:               '',
    active:                true,
})

watch(() => props.user, (u) => {
    if (u) {
        form.name    = u.name
        form.email   = u.email
        form.role_id = u.role?.id ?? ''
        form.active  = u.active
    } else {
        form.reset()
        form.active = true
    }
    form.clearErrors()
}, { immediate: true })

const submit = () => {
    const opts = { onSuccess: () => emit('close') }
    if (props.user) {
        form.put(route('settings.users.update', props.user.id), opts)
    } else {
        form.post(route('settings.users.store'), opts)
    }
}
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center px-4"
            >
                <!-- Backdrop -->
                <div
                    class="absolute inset-0 bg-gray-950/40 backdrop-blur-[2px]"
                    @click="emit('close')"
                />

                <!-- Panel -->
                <Transition
                    enter-active-class="transition-all duration-200"
                    enter-from-class="opacity-0 scale-95 translate-y-2"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                >
                    <div
                        v-if="show"
                        class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl ring-1 ring-gray-200/80 overflow-hidden"
                    >
                        <!-- Header -->
                        <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-gray-100">
                            <div>
                                <h2 class="text-[15px] font-semibold text-gray-900">
                                    {{ user ? 'Edit User' : 'New User' }}
                                </h2>
                                <p class="text-[12px] text-gray-400 mt-0.5">
                                    {{ user ? 'Update account details and permissions.' : 'Create a new team member account.' }}
                                </p>
                            </div>
                            <button
                                @click="emit('close')"
                                class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submit" class="px-6 py-5 space-y-4">

                            <!-- Name -->
                            <div>
                                <label class="block text-[12px] font-medium text-gray-600 mb-1.5">Full name</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Alice Brown"
                                    :class="['w-full px-3 py-2 text-[13.5px] border rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/25 focus:border-indigo-400',
                                             form.errors.name ? 'border-red-300 bg-red-50/50' : 'border-gray-200']"
                                />
                                <p v-if="form.errors.name" class="mt-1 text-[11.5px] text-red-500">{{ form.errors.name }}</p>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-[12px] font-medium text-gray-600 mb-1.5">Email address</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    placeholder="alice@example.com"
                                    :class="['w-full px-3 py-2 text-[13.5px] border rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/25 focus:border-indigo-400',
                                             form.errors.email ? 'border-red-300 bg-red-50/50' : 'border-gray-200']"
                                />
                                <p v-if="form.errors.email" class="mt-1 text-[11.5px] text-red-500">{{ form.errors.email }}</p>
                            </div>

                            <!-- Role -->
                            <div>
                                <label class="block text-[12px] font-medium text-gray-600 mb-1.5">Role</label>
                                <div class="relative">
                                    <select
                                        v-model="form.role_id"
                                        :class="['w-full pl-3 pr-8 py-2 text-[13.5px] border rounded-lg appearance-none transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/25 focus:border-indigo-400',
                                                 form.errors.role_id ? 'border-red-300' : 'border-gray-200']"
                                    >
                                        <option value="" disabled>Select role…</option>
                                        <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
                                    </select>
                                    <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                                <p v-if="form.errors.role_id" class="mt-1 text-[11.5px] text-red-500">{{ form.errors.role_id }}</p>
                            </div>

                            <!-- Password (grid) -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[12px] font-medium text-gray-600 mb-1.5">
                                        Password <span v-if="user" class="text-gray-300 font-normal">(optional)</span>
                                    </label>
                                    <input
                                        v-model="form.password"
                                        type="password"
                                        placeholder="Min 8 chars"
                                        :class="['w-full px-3 py-2 text-[13.5px] border rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/25 focus:border-indigo-400',
                                                 form.errors.password ? 'border-red-300' : 'border-gray-200']"
                                    />
                                    <p v-if="form.errors.password" class="mt-1 text-[11.5px] text-red-500">{{ form.errors.password }}</p>
                                </div>
                                <div>
                                    <label class="block text-[12px] font-medium text-gray-600 mb-1.5">Confirm</label>
                                    <input
                                        v-model="form.password_confirmation"
                                        type="password"
                                        placeholder="Repeat"
                                        class="w-full px-3 py-2 text-[13.5px] border border-gray-200 rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/25 focus:border-indigo-400"
                                    />
                                </div>
                            </div>

                            <!-- Active toggle -->
                            <div class="flex items-center justify-between py-1">
                                <div>
                                    <p class="text-[13px] font-medium text-gray-800">Active account</p>
                                    <p class="text-[11.5px] text-gray-400">Inactive users cannot sign in.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="form.active" class="sr-only peer">
                                    <div :class="[
                                        'w-10 h-[22px] rounded-full transition-colors duration-200',
                                        form.active ? 'bg-indigo-500' : 'bg-gray-200'
                                    ]" />
                                    <div :class="[
                                        'absolute top-[2px] left-[2px] w-[18px] h-[18px] bg-white rounded-full shadow transition-transform duration-200',
                                        form.active ? 'translate-x-[18px]' : 'translate-x-0'
                                    ]" />
                                </label>
                            </div>
                        </form>

                        <!-- Footer -->
                        <div class="flex items-center justify-end gap-2.5 px-6 py-4 border-t border-gray-100 bg-gray-50/60">
                            <button
                                type="button"
                                @click="emit('close')"
                                class="px-4 py-2 text-[13px] font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                @click="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 text-[13px] font-medium text-white bg-gray-900 rounded-lg hover:bg-gray-700 disabled:opacity-50 transition-colors"
                            >
                                <span v-if="form.processing">Saving…</span>
                                <span v-else>{{ user ? 'Save changes' : 'Create user' }}</span>
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
