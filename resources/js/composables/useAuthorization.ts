import type { SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useAuthorization() {
    const page = usePage<SharedData>();
    const enabled = computed(() => page.props.rbac.enabled);

    function can(permission: string): boolean {
        if (!page.props.rbac.enabled) {
            return true;
        }

        if (page.props.auth.is_superadmin) {
            return true;
        }

        return page.props.auth.permissions.includes(permission);
    }

    function canAny(permissions: string[]): boolean {
        return permissions.some((permission) => can(permission));
    }

    return { can, canAny, enabled };
}
