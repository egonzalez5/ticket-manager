<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout title="Dashboard">
        <div class="p-6">
            <div class="max-w-4xl">
                <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm p-6">
                    <p class="text-sm text-gray-500">You're logged in!</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
