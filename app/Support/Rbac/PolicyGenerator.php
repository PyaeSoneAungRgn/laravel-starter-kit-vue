<?php

namespace App\Support\Rbac;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

final readonly class PolicyGenerator
{
    private const string STUB = <<<'PHP'
<?php

namespace {{ namespace }};

use App\Policies\Concerns\ChecksResourcePermissions;

class {{ class }}
{
    use ChecksResourcePermissions;

    protected function resource(): string
    {
        return '{{ resource }}';
    }
}

PHP;

    public function __construct(
        private Filesystem $files,
    ) {}

    /**
     * Create the policy file when it does not exist yet.
     *
     * @return string|null The generated path, or null when the file already exists.
     */
    public function generate(string $policyClass, string $resource, string $path): ?string
    {
        if ($this->files->exists($path)) {
            return null;
        }

        $this->files->ensureDirectoryExists(dirname($path));

        $this->files->put($path, str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ resource }}'],
            [Str::beforeLast($policyClass, '\\'), class_basename($policyClass), $resource],
            self::STUB,
        ));

        return $path;
    }
}
