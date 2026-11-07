<?php

namespace App\Support\Rbac;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

final readonly class PermissionSynchronizer
{
    public function __construct(
        private PermissionRegistrar $permissionRegistrar,
    ) {}

    /**
     * @param  Collection<int, ResourceDefinition>  $resources
     * @return array{created: list<string>, existing: list<string>, pruned: list<string>}
     */
    public function sync(Collection $resources, bool $prune = false): array
    {
        $guard = $this->guard();

        $expected = $resources
            ->flatMap(fn (ResourceDefinition $resource): array => array_map(
                $resource->permissionName(...),
                $resource->abilities,
            ))
            ->unique()
            ->values();

        $created = [];
        $existing = [];

        foreach ($expected as $name) {
            $permission = Permission::findOrCreate($name, $guard);

            if ($permission->wasRecentlyCreated) {
                $created[] = $name;
            } else {
                $existing[] = $name;
            }
        }

        $pruned = $prune ? $this->prune($guard, $expected->all()) : [];

        $this->permissionRegistrar->forgetCachedPermissions();

        return [
            'created' => $created,
            'existing' => $existing,
            'pruned' => $pruned,
        ];
    }

    public function guard(): string
    {
        return (string) config('auth.defaults.guard', 'web');
    }

    /**
     * @param  list<string>  $expected
     * @return list<string>
     */
    private function prune(string $guard, array $expected): array
    {
        $stale = Permission::query()
            ->where('guard_name', $guard)
            ->whereNotIn('name', $expected)
            ->pluck('name')
            ->all();

        if ($stale !== []) {
            Permission::query()
                ->where('guard_name', $guard)
                ->whereIn('name', $stale)
                ->delete();
        }

        return array_values($stale);
    }
}
