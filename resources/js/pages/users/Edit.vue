<script setup lang="ts">
import UserForm from '@/components/rbac/UserForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

interface Props {
    user: {
        id: number;
        name: string;
        email: string;
        is_superadmin: boolean;
        roles: string[];
    };
    roles: string[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Users', href: route('users.index') },
    { title: 'Edit user', href: route('users.edit', props.user.id) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Edit user" />

        <div class="p-4">
            <h1 class="mb-4 text-2xl font-bold">Edit user</h1>
            <div class="max-w-2xl">
                <UserForm :user="user" :roles="roles" />
            </div>
        </div>
    </AppLayout>
</template>
