import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
    is_superadmin: boolean;
    roles: string[];
    permissions: string[];
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    rbac: { enabled: boolean };
    errors: Record<string, string>;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    is_superadmin: boolean;
    created_at: string;
    updated_at: string;
}

export interface ManagedUser {
    id: number;
    name: string;
    email: string;
    is_superadmin: boolean;
    roles: string[];
    created_at: string | null;
}

export interface ManagedRole {
    id: number;
    name: string;
    users_count: number;
    permissions_count: number;
    created_at: string | null;
}

export interface RoleDetails {
    id: number;
    name: string;
    permissions: string[];
}

export interface PermissionAbility {
    name: string;
    label: string;
}

export interface PermissionGroup {
    resource: string;
    label: string;
    abilities: PermissionAbility[];
}

export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

export type BreadcrumbItemType = BreadcrumbItem;
