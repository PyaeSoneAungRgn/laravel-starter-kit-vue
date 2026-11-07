<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useAuthorization } from '@/composables/useAuthorization';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type ManagedRole, type Paginated, type SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

interface Props {
    roles: Paginated<ManagedRole>;
}

defineProps<Props>();

const { can } = useAuthorization();
const page = usePage<SharedData>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Roles', href: route('roles.index') }];

function handlePageChange(newPage: number): void {
    router.get(route('roles.index'), { page: newPage }, { preserveState: true, only: ['roles'] });
}

function handleDelete(role: ManagedRole): void {
    router.delete(route('roles.destroy', role.id), { preserveScroll: true });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Roles" />

        <div class="p-4">
            <div class="mb-4 flex items-center justify-between">
                <h1 class="text-2xl font-bold">Roles</h1>
                <Link v-if="can('create_role')" :href="route('roles.create')">
                    <Button>New role</Button>
                </Link>
            </div>

            <p v-if="page.props.errors.role" class="mb-4 text-sm text-red-600 dark:text-red-500">{{ page.props.errors.role }}</p>

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>Users</TableHead>
                        <TableHead>Permissions</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="role in roles.data" :key="role.id">
                        <TableCell class="font-medium capitalize">{{ role.name }}</TableCell>
                        <TableCell>{{ role.users_count }}</TableCell>
                        <TableCell>{{ role.permissions_count }}</TableCell>
                        <TableCell class="text-right">
                            <div class="flex justify-end gap-2">
                                <Link v-if="can('update_role')" :href="route('roles.edit', role.id)">
                                    <Button variant="outline" size="sm">Edit</Button>
                                </Link>
                                <AlertDialog v-if="can('delete_role')">
                                    <AlertDialogTrigger as-child>
                                        <Button variant="destructive" size="sm">Delete</Button>
                                    </AlertDialogTrigger>
                                    <AlertDialogContent>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>Delete "{{ role.name }}"?</AlertDialogTitle>
                                            <AlertDialogDescription>
                                                This action cannot be undone. Roles that are still assigned to users cannot be deleted.
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                                            <AlertDialogAction @click="handleDelete(role)">Delete</AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <p v-if="roles.data.length === 0" class="text-muted-foreground py-6 text-center">No roles found.</p>

            <Pagination
                v-show="roles.total > roles.per_page"
                class="mt-4"
                :current-page="roles.current_page"
                :per-page="roles.per_page"
                :total="roles.total"
                @page-change="handlePageChange"
            />
        </div>
    </AppLayout>
</template>
