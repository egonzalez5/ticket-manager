<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import SidebarNavItem from '@/Components/SidebarNavItem.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const isActive = (path, exact = false) => {
    if (exact) return page.url === path;
    return page.url === path || page.url.startsWith(path + '?') || page.url.startsWith(path + '/');
};
</script>

<template>
    <div class="flex flex-col h-full w-60 bg-zinc-950 border-r border-zinc-800/80 select-none">

        <!-- Logo -->
        <div class="flex items-center gap-2.5 h-14 px-4 flex-shrink-0 border-b border-zinc-800/60">
            <div class="w-7 h-7 rounded-lg bg-indigo-500 flex items-center justify-center flex-shrink-0 shadow-sm">
                <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
            </div>
            <span class="text-[13px] font-semibold text-white tracking-tight">TicketManager</span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-2 pt-3 pb-2 space-y-0.5 overflow-y-auto scrollbar-none">

            <!-- Main -->
            <p class="px-3 mb-1.5 text-[10px] font-semibold uppercase tracking-widest text-zinc-600">
                Main
            </p>

            <SidebarNavItem href="/dashboard" :active="isActive('/dashboard', true)">
                <template #icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </template>
                Dashboard
            </SidebarNavItem>

            <!-- Tickets -->
            <p class="px-3 mt-4 mb-1.5 text-[10px] font-semibold uppercase tracking-widest text-zinc-600">
                Tickets
            </p>

            <SidebarNavItem
                href="/tickets"
                :active="isActive('/tickets') && !page.url.includes('mine=1')"
            >
                <template #icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </template>
                All Tickets
            </SidebarNavItem>

            <SidebarNavItem href="/tickets?mine=1" :active="page.url.includes('mine=1')">
                <template #icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                </template>
                My Tickets
            </SidebarNavItem>

        </nav>

        <!-- User footer -->
        <div class="border-t border-zinc-800/60 p-2.5 flex-shrink-0">
            <Link
                :href="route('profile.edit')"
                class="flex items-center gap-2.5 px-2.5 py-2 rounded-md hover:bg-zinc-800/60 transition-colors group"
            >
                <div class="w-7 h-7 rounded-full bg-indigo-500 flex items-center justify-center flex-shrink-0 shadow-sm">
                    <span class="text-white text-[11px] font-bold leading-none">
                        {{ user?.name?.[0]?.toUpperCase() ?? '?' }}
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[12.5px] font-medium text-zinc-300 truncate leading-tight">{{ user?.name }}</p>
                    <p class="text-[11px] text-zinc-500 truncate leading-tight mt-0.5">{{ user?.email }}</p>
                </div>
                <svg class="w-3 h-3 text-zinc-700 group-hover:text-zinc-500 flex-shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </Link>
        </div>

    </div>
</template>
