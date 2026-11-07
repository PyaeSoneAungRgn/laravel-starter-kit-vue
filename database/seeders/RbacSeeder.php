<?php

namespace Database\Seeders;

use App\Support\Rbac\PermissionSynchronizer;
use App\Support\Rbac\ResourceDefinition;
use App\Support\Rbac\ResourceDiscovery;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    public function __construct(
        private readonly ResourceDiscovery $resourceDiscovery,
        private readonly PermissionSynchronizer $permissionSynchronizer,
    ) {}

    /**
     * Seed the generated permissions for every discovered resource.
     */
    public function run(): void
    {
        $resources = $this->resourceDiscovery->all()
            ->filter(fn (ResourceDefinition $resource): bool => $resource->hasPolicy())
            ->values();

        if ($resources->isEmpty()) {
            return;
        }

        $this->permissionSynchronizer->sync($resources);
    }
}
