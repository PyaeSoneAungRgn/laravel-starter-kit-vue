<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types/index.d';
import { Head, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ProductItem } from '../../types';

const props = defineProps<{
    product: ProductItem;
    categories: string[];
    statuses: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Products',
        href: route('products.index'),
    },
    {
        title: 'Edit',
        href: route('products.edit', props.product.id),
    },
];

const form = useForm({
    name: props.product.name,
    description: props.product.description,
    category: props.product.category,
    price: props.product.price,
    status: props.product.status,
    is_featured: props.product.is_featured,
});

const submit = () => {
    form.put(route('products.update', props.product.id));
};
</script>

<template>
    <Head title="Edit Product" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="space-y-6">
                <Heading title="Edit Product" description="Update the product details." />
                <form @submit.prevent="submit" class="space-y-8">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="name">Name</Label>
                            <Input id="name" class="mt-1 block w-full" v-model="form.name" required placeholder="Product name" />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="category">Category</Label>
                            <Select v-model="form.category" required>
                                <SelectTrigger id="category" class="mt-1 w-full">
                                    <SelectValue placeholder="Select category" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="category in categories" :key="category" :value="category">
                                        {{ category }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError class="mt-2" :message="form.errors.category" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="price">Price</Label>
                            <Input
                                id="price"
                                type="number"
                                step="0.01"
                                min="0"
                                class="mt-1 block w-full"
                                v-model="form.price"
                                required
                                placeholder="0.00"
                            />
                            <InputError class="mt-2" :message="form.errors.price" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="status">Status</Label>
                            <Select v-model="form.status" required>
                                <SelectTrigger id="status" class="mt-1 w-full">
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="status in statuses" :key="status" :value="status">
                                        {{ status }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError class="mt-2" :message="form.errors.status" />
                        </div>

                        <div class="flex items-center gap-2">
                            <Checkbox id="is_featured" :checked="form.is_featured" @update:checked="(value) => (form.is_featured = value)" />
                            <Label for="is_featured">Featured product</Label>
                            <InputError class="mt-2" :message="form.errors.is_featured" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <Textarea id="description" class="mt-1 block w-full" v-model="form.description" required placeholder="Product description" />
                        <InputError class="mt-2" :message="form.errors.description" />
                    </div>
                    <div class="flex justify-end">
                        <Button type="submit" :disabled="form.processing">Save</Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
