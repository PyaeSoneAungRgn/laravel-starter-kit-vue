<?php

namespace App\Support\Rbac;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use InterNACHI\Modular\Support\ModuleConfig;
use InterNACHI\Modular\Support\ModuleRegistry;
use ReflectionClass;

final readonly class ResourceDiscovery
{
    public function __construct(
        private ModuleRegistry $moduleRegistry,
    ) {}

    /**
     * Discover every resource that should have permissions generated for it.
     *
     * @return Collection<int, ResourceDefinition>
     */
    public function all(): Collection
    {
        return $this->modelClasses()
            ->map(fn (string $modelClass): ResourceDefinition => $this->definitionFor($modelClass))
            ->sortBy('resource')
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    public function modelClasses(): Collection
    {
        $excluded = config('rbac.models.exclude', []);

        return collect([
            ...$this->appModelClasses(),
            ...$this->moduleModelClasses(),
            ...config('rbac.models.include', []),
        ])
            ->unique()
            ->reject(fn (string $modelClass): bool => in_array($modelClass, $excluded, true))
            ->values();
    }

    /**
     * The policy class conventionally paired with the model, whether or not it
     * exists yet.
     */
    public function policyClassFor(string $modelClass): string
    {
        $module = $this->moduleRegistry->moduleForClass($modelClass);

        if ($module instanceof ModuleConfig) {
            return rtrim((string) $module->namespace(), '\\') . '\\Policies\\' . class_basename($modelClass) . 'Policy';
        }

        return 'App\\Policies\\' . class_basename($modelClass) . 'Policy';
    }

    public function policyPathFor(string $modelClass): string
    {
        $module = $this->moduleRegistry->moduleForClass($modelClass);

        if ($module instanceof ModuleConfig) {
            return $module->path('src/Policies/' . class_basename($modelClass) . 'Policy.php');
        }

        return app_path('Policies/' . class_basename($modelClass) . 'Policy.php');
    }

    /**
     * @return list<string>
     */
    public function abilitiesFor(string $modelClass): array
    {
        /** @var list<string> $abilities */
        $abilities = config('rbac.abilities', []);

        $usesSoftDeletes = in_array(SoftDeletes::class, class_uses_recursive($modelClass), true);

        if (! $usesSoftDeletes) {
            $abilities = array_values(array_diff($abilities, ['restore', 'forceDelete']));
        }

        return $abilities;
    }

    private function definitionFor(string $modelClass): ResourceDefinition
    {
        return new ResourceDefinition(
            modelClass: $modelClass,
            resource: Str::snake(class_basename($modelClass)),
            policyClass: $this->policyClassFor($modelClass),
            abilities: $this->abilitiesFor($modelClass),
        );
    }

    /**
     * @return list<string>
     */
    private function appModelClasses(): array
    {
        return $this->modelClassesIn(app_path('Models'), 'App\\Models\\');
    }

    /**
     * @return list<string>
     */
    private function moduleModelClasses(): array
    {
        return $this->moduleRegistry->modules()
            ->flatMap(fn (ModuleConfig $module): array => $this->modelClassesIn(
                $module->path('src/Models'),
                rtrim((string) $module->namespace(), '\\') . '\\Models\\',
            ))
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function modelClassesIn(string $directory, string $namespace): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        return collect(File::allFiles($directory))
            ->map(function ($file) use ($directory, $namespace): ?string {
                $modelClass = $namespace . str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    Str::after($file->getPathname(), rtrim($directory, '/') . '/'),
                );

                if (! class_exists($modelClass) || ! is_subclass_of($modelClass, Model::class)) {
                    return null;
                }

                if ((new ReflectionClass($modelClass))->isAbstract()) {
                    return null;
                }

                return $modelClass;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
