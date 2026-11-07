<?php

use App\Support\Rbac\PolicyGenerator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;

test('it generates a policy that checks resource permissions', function () {
    $path = storage_path('framework/testing/rbac/ProductPolicy.php');

    File::deleteDirectory(dirname($path));

    $generator = new PolicyGenerator(new Filesystem);
    $generated = $generator->generate('App\\Policies\\ProductPolicy', 'product', $path);

    expect($generated)->toBe($path)
        ->and(File::exists($path))->toBeTrue();

    $contents = File::get($path);

    expect($contents)
        ->toContain('namespace App\\Policies;')
        ->toContain('class ProductPolicy')
        ->toContain('use ChecksResourcePermissions;')
        ->toContain("return 'product';");

    File::deleteDirectory(storage_path('framework/testing/rbac'));
});

test('it does not overwrite an existing policy', function () {
    $path = storage_path('framework/testing/rbac/ExistingPolicy.php');

    File::ensureDirectoryExists(dirname($path));
    File::put($path, 'existing');

    $generator = new PolicyGenerator(new Filesystem);

    expect($generator->generate('App\\Policies\\ExistingPolicy', 'existing', $path))->toBeNull()
        ->and(File::get($path))->toBe('existing');

    File::deleteDirectory(storage_path('framework/testing/rbac'));
});
