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
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types/index.d';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { route } from 'ziggy-js';
import { ProductItem } from '../../types';

interface Props {
    products: {
        data: ProductItem[];
        current_page: number;
        per_page: number;
        total: number;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Products', href: route('products.index') }];

const products = ref(props.products.data);

function handlePageChange(newPage: number) {
    router.visit(route('products.index'), {
        method: 'get',
        data: { page: newPage },
        only: ['products'],
    });
}

function handleDelete(productId: number) {
    router.delete(route('products.destroy', productId));
}

function formatDate(dateString: string) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

watch(
    () => props.products,
    (newVal) => {
        products.value = newVal.data;
    },
);
</script>

<template>
    <Head title="Products" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="mb-4 flex items-center justify-between">
                <h1 class="text-2xl font-bold">Products</h1>
                <Link :href="route('products.create')">
                    <Button>Create</Button>
                </Link>
            </div>
            <TableCaption v-if="products.length == 0" class="w-full text-center">Empty!</TableCaption>
            <Table v-else>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>Category</TableHead>
                        <TableHead>Price</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Featured</TableHead>
                        <TableHead>Created At</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="product in products" :key="product.id">
                        <TableCell class="font-medium">{{ product.name }}</TableCell>
                        <TableCell class="capitalize">{{ product.category }}</TableCell>
                        <TableCell>${{ product.price }}</TableCell>
                        <TableCell>
                            <Badge :variant="product.status === 'active' ? 'default' : 'secondary'">{{ product.status }}</Badge>
                        </TableCell>
                        <TableCell>{{ product.is_featured ? 'Yes' : 'No' }}</TableCell>
                        <TableCell>{{ formatDate(product.created_at) }}</TableCell>
                        <TableCell class="text-right">
                            <div class="flex justify-end gap-2">
                                <Link :href="route('products.edit', product.id)">
                                    <Button variant="outline" size="sm">Edit</Button>
                                </Link>
                                <AlertDialog>
                                    <AlertDialogTrigger as-child>
                                        <Button variant="destructive" size="sm">Delete</Button>
                                    </AlertDialogTrigger>
                                    <AlertDialogContent>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>Delete "{{ product.name }}"?</AlertDialogTitle>
                                            <AlertDialogDescription>
                                                This action cannot be undone. This will permanently remove the product.
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                                            <AlertDialogAction @click="handleDelete(product.id)">Delete</AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
            <Pagination
                v-show="props.products.total > props.products.per_page"
                class="mt-4"
                @page-change="handlePageChange"
                :current-page="props.products.current_page"
                :per-page="props.products.per_page"
                :total="props.products.total"
            />
        </div>
    </AppLayout>
</template>
