<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    title: { type: String, default: '' },
});

defineEmits(['toggleSidebar']);

const page = usePage();
const user = computed(() => page.props.auth.user);

const dropdownOpen = ref(false);
const dropdownRef  = ref(null);

const handleOutside = (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        dropdownOpen.value = false;
    }
};

onMounted(()  => document.addEventListener('mousedown', handleOutside));
onUnmounted(() => document.removeEventListener('mousedown', handleOutside));
</script>

<template>
    <header class="h-14 bg-white border-b border-gray-200/80 flex items-center gap-3 px-4 flex-shrink-0 z-10">

        <!-- Mobile sidebar toggle -->
        <button
            @click="$emit('toggleSidebar')"
            class="lg:hidden flex-shrink-0 p-1.5 -ml-1 rounded-md text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Title -->
        <h1 v-if="title" class="text-[13px] font-semibold text-gray-800 truncate flex-1">
            {{ title }}
        </h1>
        <div v-else class="flex-1" />

        <!-- Right side -->
        <div class="flex items-center gap-0.5 flex-shrink-0">

            <!-- Notifications placeholder -->
            <button class="p-2 rounded-md text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </button>

            <!-- User dropdown -->
            <div ref="dropdownRef" class="relative ml-1">
                <button
                    @click="dropdownOpen = !dropdownOpen"
                    class="flex items-center gap-2 pl-2 pr-1.5 py-1.5 rounded-md hover:bg-gray-100 transition-colors"
                >
                    <div class="w-6 h-6 rounded-full bg-indigo-500 flex items-center justify-center flex-shrink-0">
                        <span class="text-white text-[10px] font-bold leading-none">
                            {{ user?.name?.[0]?.toUpperCase() ?? '?' }}
                        </span>
                    </div>
                    <span class="hidden sm:block text-[13px] font-medium text-gray-700 max-w-[120px] truncate">
                        {{ user?.name }}
                    </span>
                    <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <Transition
                    enter-active-class="transition duration-100 ease-out"
                    enter-from-class="scale-95 opacity-0"
                    enter-to-class="scale-100 opacity-100"
                    leave-active-class="transition duration-75 ease-in"
                    leave-from-class="scale-100 opacity-100"
                    leave-to-class="scale-95 opacity-0"
                >
                    <div
                        v-if="dropdownOpen"
                        class="absolute right-0 top-full mt-1.5 w-56 bg-white rounded-xl shadow-lg shadow-gray-200/80 border border-gray-200/80 py-1 z-50 origin-top-right"
                    >
                        <div class="px-3 py-2.5 border-b border-gray-100">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ user?.name }}</p>
                            <p class="text-xs text-gray-400 truncate mt-0.5">{{ user?.email }}</p>
                        </div>
                        <div class="py-1">
                            <Link
                                :href="route('profile.edit')"
                                @click="dropdownOpen = false"
                                class="flex items-center gap-2.5 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                            >
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Profile
                            </Link>
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                            >
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Sign out
                            </Link>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>
    </header>
</template>
