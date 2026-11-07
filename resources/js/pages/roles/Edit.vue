<script setup lang="ts">
import RoleForm from '@/components/rbac/RoleForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type PermissionGroup, type RoleDetails } from '@/types';
import { Head } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

interface Props {
    role: RoleDetails;
    permissionGroups: PermissionGroup[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Roles', href: route('roles.index') },
    { title: 'Edit role', href: route('roles.edit', props.role.id) },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Edit role" />

        <div class="p-4">
            <h1 class="mb-4 text-2xl font-bold">Edit role</h1>
            <div class="max-w-3xl">
                <RoleForm :role="role" :permission-groups="permissionGroups" />
            </div>
        </div>
    </AppLayout>
</template>
