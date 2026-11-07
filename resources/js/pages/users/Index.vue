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
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useAuthorization } from '@/composables/useAuthorization';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type ManagedUser, type Paginated, type SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';

interface Props {
    users: Paginated<ManagedUser>;
    filters: { search: string };
}

const props = defineProps<Props>();

const { can } = useAuthorization();
const page = usePage<SharedData>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Users', href: route('users.index') }];

const search = ref(props.filters.search);

function applySearch(): void {
    router.get(route('users.index'), { search: search.value || undefined }, { preserveState: true, replace: true });
}

function handlePageChange(newPage: number): void {
    router.get(route('users.index'), { page: newPage, search: search.value || undefined }, { preserveState: true, only: ['users'] });
}

function handleDelete(user: ManagedUser): void {
    router.delete(route('users.destroy', user.id), { preserveScroll: true });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Users" />

        <div class="p-4">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-2xl font-bold">Users</h1>
                <div class="flex items-center gap-2">
                    <form @submit.prevent="applySearch">
                        <Input v-model="search" type="search" placeholder="Search name or email" class="w-64" />
                    </form>
                    <Link v-if="can('create_user')" :href="route('users.create')">
                        <Button>New user</Button>
                    </Link>
                </div>
            </div>

            <p v-if="page.props.errors.user" class="mb-4 text-sm text-red-600 dark:text-red-500">{{ page.props.errors.user }}</p>

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>Email</TableHead>
                        <TableHead>Roles</TableHead>
                        <TableHead v-if="page.props.auth.is_superadmin">Superadmin</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="user in users.data" :key="user.id">
                        <TableCell class="font-medium">{{ user.name }}</TableCell>
                        <TableCell>{{ user.email }}</TableCell>
                        <TableCell>
                            <div class="flex flex-wrap gap-1">
                                <Badge v-for="role in user.roles" :key="role" variant="secondary">{{ role }}</Badge>
                                <span v-if="user.roles.length === 0" class="text-muted-foreground">-</span>
                            </div>
                        </TableCell>
                        <TableCell v-if="page.props.auth.is_superadmin">
                            <Badge v-if="user.is_superadmin" variant="default">Superadmin</Badge>
                            <span v-else class="text-muted-foreground">-</span>
                        </TableCell>
                        <TableCell class="text-right">
                            <div class="flex justify-end gap-2">
                                <Link v-if="can('update_user')" :href="route('users.edit', user.id)">
                                    <Button variant="outline" size="sm">Edit</Button>
                                </Link>
                                <AlertDialog v-if="can('delete_user')">
                                    <AlertDialogTrigger as-child>
                                        <Button variant="destructive" size="sm">Delete</Button>
                                    </AlertDialogTrigger>
                                    <AlertDialogContent>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>Delete "{{ user.name }}"?</AlertDialogTitle>
                                            <AlertDialogDescription>
                                                This action cannot be undone. This will permanently remove the user.
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                                            <AlertDialogAction @click="handleDelete(user)">Delete</AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <p v-if="users.data.length === 0" class="text-muted-foreground py-6 text-center">No users found.</p>

            <Pagination
                v-show="users.total > users.per_page"
                class="mt-4"
                :current-page="users.current_page"
                :per-page="users.per_page"
                :total="users.total"
                @page-change="handlePageChange"
            />
        </div>
    </AppLayout>
</template>
