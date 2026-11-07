<script setup lang="ts">
import RoleForm from '@/components/rbac/RoleForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type PermissionGroup } from '@/types';
import { Head } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

interface Props {
    permissionGroups: PermissionGroup[];
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Roles', href: route('roles.index') },
    { title: 'Create role', href: route('roles.create') },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create role" />

        <div class="p-4">
            <h1 class="mb-4 text-2xl font-bold">Create role</h1>
            <div class="max-w-3xl">
                <RoleForm :permission-groups="permissionGroups" />
            </div>
        </div>
    </AppLayout>
</template>
