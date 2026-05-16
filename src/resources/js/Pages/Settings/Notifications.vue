<script setup>
import { useForm, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    settings: { type: Object, default: () => ({}) },
})

const form = useForm({
    ticket_created:    props.settings.ticket_created    ?? true,
    ticket_assigned:   props.settings.ticket_assigned   ?? true,
    ticket_replied:    props.settings.ticket_replied    ?? true,
    ticket_closed:     props.settings.ticket_closed     ?? false,
    sla_breach:        props.settings.sla_breach        ?? true,
    dashboard:         props.settings.dashboard         ?? true,
    mail_from_name:    props.settings.mail_from_name    ?? '',
    mail_from_address: props.settings.mail_from_address ?? '',
})

const save = () => form.put(route('settings.notifications.update'))
</script>

<template>
    <Head title="Notifications · Settings" />

    <AuthenticatedLayout title="Settings">
        <div class="bg-gray-50 min-h-full px-6 py-7">

            <!-- ── Page header ────────────────────────────────────────── -->
            <div class="mb-6">
                <h1 class="text-[17px] font-semibold text-gray-900">Notification Settings</h1>
                <p class="text-[13px] text-gray-400 mt-0.5">Control when and how your team receives email notifications.</p>
            </div>

            <div class="max-w-2xl space-y-5">

                <!-- ── Ticket Events ──────────────────────────────────── -->
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h2 class="text-[13px] font-semibold text-gray-900">Ticket Events</h2>
                        <p class="text-[12px] text-gray-400 mt-0.5">Email notifications triggered by ticket lifecycle events.</p>
                    </div>
                    <div class="divide-y divide-gray-50">

                        <!-- Toggle row component pattern -->
                        <div v-for="item in [
                            { key: 'ticket_created',  label: 'Ticket created',  desc: 'Notify the reporter when their ticket is successfully submitted.' },
                            { key: 'ticket_assigned', label: 'Ticket assigned',  desc: 'Alert the assigned agent when a ticket is assigned to them.' },
                            { key: 'ticket_replied',  label: 'New reply',        desc: 'Notify the relevant party when a reply or comment is added.' },
                            { key: 'ticket_closed',   label: 'Ticket closed',    desc: 'Send a confirmation email when a ticket is marked resolved or closed.' },
                        ]" :key="item.key" class="flex items-center justify-between px-5 py-4 hover:bg-gray-50/60 transition-colors">
                            <div class="flex-1 min-w-0 pr-6">
                                <p class="text-[13.5px] font-medium text-gray-900">{{ item.label }}</p>
                                <p class="text-[12px] text-gray-400 mt-0.5">{{ item.desc }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                                <input type="checkbox" v-model="form[item.key]" class="sr-only">
                                <div :class="['w-10 h-[22px] rounded-full transition-colors duration-200', form[item.key] ? 'bg-indigo-500' : 'bg-gray-200']" />
                                <div :class="['absolute top-[2px] left-[2px] w-[18px] h-[18px] bg-white rounded-full shadow transition-transform duration-200', form[item.key] ? 'translate-x-[18px]' : 'translate-x-0']" />
                            </label>
                        </div>
                    </div>
                </div>

                <!-- ── SLA & Alerts ───────────────────────────────────── -->
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h2 class="text-[13px] font-semibold text-gray-900">SLA &amp; Alerts</h2>
                        <p class="text-[12px] text-gray-400 mt-0.5">Critical alerts for SLA compliance and internal visibility.</p>
                    </div>
                    <div class="divide-y divide-gray-50">
                        <div v-for="item in [
                            { key: 'sla_breach',  label: 'SLA breach alerts',          desc: 'Warn assigned agents when a ticket is approaching or has exceeded its SLA deadline.' },
                            { key: 'dashboard',   label: 'Dashboard notifications',     desc: 'Show in-app notification badges and alerts in the dashboard for agents.' },
                        ]" :key="item.key" class="flex items-center justify-between px-5 py-4 hover:bg-gray-50/60 transition-colors">
                            <div class="flex-1 min-w-0 pr-6">
                                <p class="text-[13.5px] font-medium text-gray-900">{{ item.label }}</p>
                                <p class="text-[12px] text-gray-400 mt-0.5">{{ item.desc }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                                <input type="checkbox" v-model="form[item.key]" class="sr-only">
                                <div :class="['w-10 h-[22px] rounded-full transition-colors duration-200', form[item.key] ? 'bg-indigo-500' : 'bg-gray-200']" />
                                <div :class="['absolute top-[2px] left-[2px] w-[18px] h-[18px] bg-white rounded-full shadow transition-transform duration-200', form[item.key] ? 'translate-x-[18px]' : 'translate-x-0']" />
                            </label>
                        </div>
                    </div>
                </div>

                <!-- ── Email Configuration ────────────────────────────── -->
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h2 class="text-[13px] font-semibold text-gray-900">Email Configuration</h2>
                        <p class="text-[12px] text-gray-400 mt-0.5">Customize the sender identity for outgoing notification emails.</p>
                    </div>
                    <div class="px-5 py-5 space-y-4">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Sender name -->
                            <div>
                                <label class="block text-[12px] font-medium text-gray-600 mb-1.5">Sender name</label>
                                <input
                                    v-model="form.mail_from_name"
                                    type="text"
                                    placeholder="Support Team"
                                    :class="['w-full px-3 py-2 text-[13.5px] border rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/25 focus:border-indigo-400',
                                             form.errors.mail_from_name ? 'border-red-300' : 'border-gray-200']"
                                />
                                <p v-if="form.errors.mail_from_name" class="mt-1 text-[11.5px] text-red-500">{{ form.errors.mail_from_name }}</p>
                            </div>

                            <!-- Sender email -->
                            <div>
                                <label class="block text-[12px] font-medium text-gray-600 mb-1.5">Sender email</label>
                                <input
                                    v-model="form.mail_from_address"
                                    type="email"
                                    placeholder="support@yourcompany.com"
                                    :class="['w-full px-3 py-2 text-[13.5px] border rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/25 focus:border-indigo-400',
                                             form.errors.mail_from_address ? 'border-red-300' : 'border-gray-200']"
                                />
                                <p v-if="form.errors.mail_from_address" class="mt-1 text-[11.5px] text-red-500">{{ form.errors.mail_from_address }}</p>
                            </div>
                        </div>

                        <p class="text-[12px] text-gray-400">
                            These settings apply to all outgoing notification emails. Changes take effect immediately.
                        </p>
                    </div>
                </div>

                <!-- ── Save ───────────────────────────────────────────── -->
                <div class="flex items-center justify-end">
                    <button
                        @click="save"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-900 hover:bg-gray-700 disabled:opacity-50 text-white text-[13px] font-medium rounded-lg transition-colors shadow-sm"
                    >
                        <svg v-if="form.processing" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ form.processing ? 'Saving…' : 'Save configuration' }}</span>
                    </button>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
