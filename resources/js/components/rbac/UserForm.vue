<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Link, useForm } from '@inertiajs/vue3';

interface Props {
    user?: {
        id: number;
        name: string;
        email: string;
        is_superadmin: boolean;
        roles: string[];
    };
    roles: string[];
}

const props = defineProps<Props>();

const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    password: '',
    password_confirmation: '',
    roles: props.user?.roles ?? [],
});

function toggleRole(role: string): void {
    form.roles = form.roles.includes(role) ? form.roles.filter((assigned) => assigned !== role) : [...form.roles, role];
}

function submit(): void {
    if (props.user) {
        form.put(route('users.update', props.user.id), {
            preserveScroll: true,
            onSuccess: () => form.reset('password', 'password_confirmation'),
        });

        return;
    }

    form.post(route('users.store'));
}
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <div class="grid gap-2">
            <Label for="name">Name</Label>
            <Input id="name" v-model="form.name" class="mt-1 block w-full" required autocomplete="name" placeholder="Full name" />
            <InputError class="mt-2" :message="form.errors.name" />
        </div>

        <div class="grid gap-2">
            <Label for="email">Email address</Label>
            <Input id="email" v-model="form.email" type="email" class="mt-1 block w-full" required autocomplete="email" placeholder="Email address" />
            <InputError class="mt-2" :message="form.errors.email" />
        </div>

        <div class="grid gap-2">
            <Label for="password">Password</Label>
            <Input
                id="password"
                v-model="form.password"
                type="password"
                class="mt-1 block w-full"
                :required="!user"
                autocomplete="new-password"
                :placeholder="user ? 'Leave blank to keep the current password' : 'Password'"
            />
            <InputError class="mt-2" :message="form.errors.password" />
        </div>

        <div class="grid gap-2">
            <Label for="password_confirmation">Confirm password</Label>
            <Input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                class="mt-1 block w-full"
                :required="!user"
                autocomplete="new-password"
                placeholder="Confirm password"
            />
        </div>

        <div class="grid gap-2">
            <Label>Roles</Label>
            <p v-if="roles.length === 0" class="text-muted-foreground text-sm">
                No roles exist yet. Create one on the <Link class="underline" :href="route('roles.index')">roles page</Link> first.
            </p>
            <div v-else class="space-y-2">
                <div v-for="role in roles" :key="role" class="flex items-center gap-2">
                    <Checkbox :id="`role-${role}`" :model-value="form.roles.includes(role)" @update:model-value="toggleRole(role)" />
                    <Label :for="`role-${role}`" class="font-normal capitalize">{{ role }}</Label>
                </div>
            </div>
            <InputError class="mt-2" :message="form.errors.roles" />
        </div>

        <p v-if="user?.is_superadmin" class="text-muted-foreground border-sidebar-border/70 rounded-md border p-3 text-sm">
            This user is a superadmin. The superadmin flag can only be granted with the
            <code>php artisan rbac:superadmin</code> command.
        </p>

        <div class="flex items-center gap-4">
            <Button :disabled="form.processing">Save</Button>
            <Link :href="route('users.index')">
                <Button type="button" variant="ghost">Cancel</Button>
            </Link>
        </div>
    </form>
</template>
