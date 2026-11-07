<?php

namespace App\Support\Rbac;

final readonly class ResourceDefinition
{
    /**
     * @param  list<string>  $abilities
     */
    public function __construct(
        public string $modelClass,
        public string $resource,
        public string $policyClass,
        public array $abilities,
    ) {}

    public function hasPolicy(): bool
    {
        return class_exists($this->policyClass);
    }

    public function permissionName(string $ability): string
    {
        return PermissionName::make($this->resource, $ability);
    }
}
