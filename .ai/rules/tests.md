---
paths:
  - 'app-modules/*/tests/**'
---

# Tests

## assertInertia component() needs shouldExist=false for module pages
The Inertia testing view-finder uses `config('inertia.testing.page_paths')` and cannot resolve namespaced module pages (`demo::products/Index`), because module view namespaces aren't registered and the repo has no config/inertia.php. Assert module components with `$page->component('demo::products/Index', false)` to skip the file-existence check while still asserting the component name.
