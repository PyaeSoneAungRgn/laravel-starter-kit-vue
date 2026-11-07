<script setup lang="ts">
import UserForm from '@/components/rbac/UserForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

interface Props {
    roles: string[];
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Users', href: route('users.index') },
    { title: 'Create user', href: route('users.create') },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create user" />

        <div class="p-4">
            <h1 class="mb-4 text-2xl font-bold">Create user</h1>
            <div class="max-w-2xl">
                <UserForm :roles="roles" />
            </div>
        </div>
    </AppLayout>
</template>
