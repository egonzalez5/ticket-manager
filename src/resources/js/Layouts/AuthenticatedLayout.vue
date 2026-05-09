<script setup>
import { ref } from 'vue';
import AppSidebar from '@/Components/AppSidebar.vue';
import AppTopbar from '@/Components/AppTopbar.vue';

defineProps({
    title: { type: String, default: '' },
});

const sidebarOpen = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex">

        <!-- Mobile backdrop -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="sidebarOpen"
                class="fixed inset-0 bg-black/50 z-30 lg:hidden"
                @click="sidebarOpen = false"
            />
        </Transition>

        <!-- Sidebar: fixed on desktop, slide-over on mobile -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-40 flex-shrink-0',
                'transition-transform duration-200 ease-in-out',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            ]"
        >
            <AppSidebar />
        </aside>

        <!-- Content area: offset by sidebar width on desktop -->
        <div class="flex flex-col flex-1 min-w-0 lg:pl-60">

            <AppTopbar
                :title="title"
                @toggle-sidebar="sidebarOpen = !sidebarOpen"
            />

            <main class="flex-1 overflow-auto">
                <slot />
            </main>

        </div>
    </div>
</template>
