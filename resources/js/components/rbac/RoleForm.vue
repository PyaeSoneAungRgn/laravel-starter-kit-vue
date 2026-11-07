<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type PermissionGroup } from '@/types';
import { Link, useForm } from '@inertiajs/vue3';

interface Props {
    role?: {
        id: number;
        name: string;
        permissions: string[];
    };
    permissionGroups: PermissionGroup[];
}

const props = defineProps<Props>();

const form = useForm({
    name: props.role?.name ?? '',
    permissions: props.role?.permissions ?? [],
});

function togglePermission(permission: string): void {
    form.permissions = form.permissions.includes(permission)
        ? form.permissions.filter((assigned) => assigned !== permission)
        : [...form.permissions, permission];
}

function groupSelected(group: PermissionGroup): boolean {
    return group.abilities.every((ability) => form.permissions.includes(ability.name));
}

function toggleGroup(group: PermissionGroup): void {
    const names = group.abilities.map((ability) => ability.name);

    form.permissions = groupSelected(group)
        ? form.permissions.filter((permission) => !names.includes(permission))
        : [...new Set([...form.permissions, ...names])];
}

function submit(): void {
    if (props.role) {
        form.put(route('roles.update', props.role.id), { preserveScroll: true });

        return;
    }

    form.post(route('roles.store'));
}
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <div class="grid gap-2">
            <Label for="name">Name</Label>
            <Input id="name" v-model="form.name" class="mt-1 block w-full" required placeholder="Role name" />
            <InputError class="mt-2" :message="form.errors.name" />
        </div>

        <div class="space-y-4">
            <Label>Permissions</Label>
            <p v-if="permissionGroups.length === 0" class="text-muted-foreground text-sm">
                No permissions have been generated yet. Run <code>php artisan rbac:sync</code> first.
            </p>
            <div v-for="group in permissionGroups" :key="group.resource" class="border-sidebar-border/70 rounded-lg border p-4">
                <div class="mb-3 flex items-center justify-between">
                    <span class="font-medium">{{ group.label }}</span>
                    <Button type="button" variant="secondary" size="sm" @click="toggleGroup(group)">
                        {{ groupSelected(group) ? 'Unselect All' : 'Select All' }}
                    </Button>
                </div>
                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="ability in group.abilities" :key="ability.name" class="flex items-center gap-2">
                        <Checkbox
                            :id="ability.name"
                            :model-value="form.permissions.includes(ability.name)"
                            @update:model-value="togglePermission(ability.name)"
                        />
                        <Label :for="ability.name" class="font-normal">{{ ability.label }}</Label>
                    </div>
                </div>
            </div>
            <InputError :message="form.errors.permissions" />
        </div>

        <div class="flex items-center gap-4">
            <Button :disabled="form.processing">Save</Button>
            <Link :href="route('roles.index')">
                <Button type="button" variant="ghost">Cancel</Button>
            </Link>
        </div>
    </form>
</template>
