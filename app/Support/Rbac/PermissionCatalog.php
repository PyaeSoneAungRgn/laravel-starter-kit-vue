<?php

namespace App\Support\Rbac;

use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

final class PermissionCatalog
{
    /**
     * Group every permission for the default guard by resource.
     *
     * @return list<array{resource: string, label: string, abilities: list<array{name: string, label: string}>}>
     */
    public function grouped(): array
    {
        $groups = [];
        $parsingOrder = $this->parsingOrder();
        $displayOrder = array_flip($this->configAbilitySlugs());

        $permissions = Permission::query()
            ->where('guard_name', $this->guard())
            ->orderBy('name')
            ->pluck('name');

        foreach ($permissions as $name) {
            $parsed = $this->parse($name, $parsingOrder);

            if ($parsed === null) {
                continue;
            }

            [$ability, $resource] = $parsed;

            $groups[$resource] ??= [
                'resource' => $resource,
                'label' => Str::headline($resource),
                'abilities' => [],
            ];

            $groups[$resource]['abilities'][] = [
                'name' => $name,
                'label' => $this->abilityLabel($ability),
                'position' => $displayOrder[$ability] ?? PHP_INT_MAX,
            ];
        }

        foreach ($groups as $resource => $group) {
            usort($group['abilities'], fn (array $left, array $right): int => $left['position'] <=> $right['position']);

            $groups[$resource]['abilities'] = array_map(
                fn (array $ability): array => ['name' => $ability['name'], 'label' => $ability['label']],
                $group['abilities'],
            );
        }

        ksort($groups);

        return array_values($groups);
    }

    private function guard(): string
    {
        return (string) config('auth.defaults.guard', 'web');
    }

    /**
     * Ability slugs in configuration order, used to display the matrix.
     *
     * @return list<string>
     */
    private function configAbilitySlugs(): array
    {
        /** @var list<string> $abilities */
        $abilities = config('rbac.abilities', []);

        return array_map(fn (string $ability): string => Str::snake($ability), $abilities);
    }

    /**
     * Ability slugs ordered from most to least specific so "view_any_product"
     * is parsed as "view_any" + "product" and not "view" + "any_product".
     *
     * @return list<string>
     */
    private function parsingOrder(): array
    {
        return collect($this->configAbilitySlugs())
            ->sortByDesc(fn (string $ability): int => strlen($ability))
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $abilityOrder
     * @return array{0: string, 1: string}|null
     */
    private function parse(string $name, array $abilityOrder): ?array
    {
        foreach ($abilityOrder as $ability) {
            if (str_starts_with($name, $ability . '_')) {
                return [$ability, Str::after($name, $ability . '_')];
            }
        }

        return null;
    }

    private function abilityLabel(string $ability): string
    {
        return match ($ability) {
            'update' => 'Edit',
            default => Str::headline($ability),
        };
    }
}
