<?php

namespace App\Console\Commands;

use App\Support\Rbac\PermissionSynchronizer;
use App\Support\Rbac\PolicyGenerator;
use App\Support\Rbac\ResourceDefinition;
use App\Support\Rbac\ResourceDiscovery;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Attribute\AsCommand;

use function Laravel\Prompts\confirm;

#[AsCommand(name: 'rbac:sync', description: 'Create the permissions for every discovered model')]
#[Signature('rbac:sync
        {--model= : Limit the sync to one model class or resource name}
        {--policy : Generate missing policy classes for discovered models}
        {--prune : Delete permissions that are no longer generated}
        {--force : Skip the confirmation prompt for --prune}')]
class SyncPermissionsCommand extends Command
{
    public function __construct(
        private readonly ResourceDiscovery $resourceDiscovery,
        private readonly PermissionSynchronizer $permissionSynchronizer,
        private readonly PolicyGenerator $policyGenerator,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! config('rbac.enabled')) {
            $this->components->warn('RBAC is disabled (RBAC_ENABLED=false). Permissions will be created but never checked.');
        }

        $resources = $this->resources();

        if ($resources->isEmpty()) {
            $this->components->error('No models were discovered. Check the model directories or the rbac.models config.');

            return self::FAILURE;
        }

        if ($this->option('policy')) {
            $this->generatePolicies($resources);
            $resources = $this->resources();
        }

        $withPolicies = $resources->filter(fn (ResourceDefinition $resource): bool => $resource->hasPolicy())->values();
        $missingPolicies = $resources->reject(fn (ResourceDefinition $resource): bool => $resource->hasPolicy());

        foreach ($missingPolicies as $resource) {
            $this->components->warn(sprintf(
                'Skipping %s: no policy at %s (run with --policy to generate it).',
                $resource->modelClass,
                $resource->policyClass,
            ));
        }

        if ($withPolicies->isEmpty()) {
            $this->components->error('No resource has a policy, so no permissions were generated.');

            return self::FAILURE;
        }

        $prune = $this->shouldPrune();
        $result = $this->permissionSynchronizer->sync($withPolicies, $prune);

        $this->table(
            ['Resource', 'Permissions'],
            $withPolicies
                ->map(fn (ResourceDefinition $resource): array => [
                    $resource->resource,
                    implode(', ', array_map($resource->permissionName(...), $resource->abilities)),
                ])
                ->all(),
        );

        $this->components->info(sprintf(
            '%d permission(s) created, %d already existed%s.',
            count($result['created']),
            count($result['existing']),
            $prune ? sprintf(', %d pruned', count($result['pruned'])) : '',
        ));

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, ResourceDefinition>
     */
    private function resources(): Collection
    {
        $resources = $this->resourceDiscovery->all();
        $model = $this->option('model');

        if (blank($model)) {
            return $resources;
        }

        return $resources
            ->filter(fn (ResourceDefinition $resource): bool => $resource->modelClass === $model || $resource->resource === $model)
            ->values();
    }

    /**
     * @param  Collection<int, ResourceDefinition>  $resources
     */
    private function generatePolicies(Collection $resources): void
    {
        $generated = [];

        foreach ($resources as $resource) {
            $path = $this->policyGenerator->generate(
                $resource->policyClass,
                $resource->resource,
                $this->resourceDiscovery->policyPathFor($resource->modelClass),
            );

            if ($path !== null) {
                $generated[] = $path;
            }
        }

        foreach ($generated as $path) {
            $this->components->info(sprintf('Generated policy %s', $path));
        }

        if ($generated === []) {
            $this->components->info('Every discovered model already has a policy.');
        }
    }

    private function shouldPrune(): bool
    {
        if (! $this->option('prune')) {
            return false;
        }

        if ($this->option('force')) {
            return true;
        }

        if (! $this->input->isInteractive()) {
            $this->components->warn('Skipping prune: pass --force to prune in non-interactive mode.');

            return false;
        }

        return confirm('Delete permissions that are no longer generated?', default: false);
    }
}
