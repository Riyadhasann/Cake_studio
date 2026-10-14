# Graph Report - Cake_studio  (2026-10-14)

## Corpus Check
- 173 files · ~34,424 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 21 file(s) not represented in the graph (top: (none) 16, .example 1, .neon 1)

## Summary
- 823 nodes · 1230 edges · 128 communities (22 shown, 106 thin omitted)
- Extraction: 96% EXTRACTED · 4% INFERRED · 0% AMBIGUOUS · INFERRED: 47 edges (avg confidence: 0.86)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `17053e17`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Database\Eloquent\Relations\BelongsTo
- Illuminate\Http\RedirectResponse
- Laravel\Fortify\Features
- FortifyServiceProvider.php
- SizeAdminTest.php
- package.json
- UserFactory.php
- Illuminate\Foundation\Http\FormRequest
- CartController.php
- FlavorAdminTest.php
- scripts
- Illuminate\Database\Schema\Blueprint
- require-dev
- ci Job
- require
- composer.json
- Illuminate\Support\Facades\Schema
- Illuminate\Database\Migrations\Migration
- ⚡security.blade.php
- config
- OrderAlerts.php
- psr-4
- laravel
- 0001_01_01_000001_create_cache_table.php
- 2025_08_14_170933_add_two_factor_columns_to_users_table.php
- 2026_10_06_042159_add_order_foreign_key_to_cake_customizations_table.php
- User
- 2026_10_07_060906_add_delivery_details_to_orders_table.php
- 2026_10_08_050619_add_quantity_to_cake_customizations_table.php
- 2026_10_08_050624_drop_delivery_time_from_orders_table.php
- 2026_10_08_142055_add_is_admin_to_users_table.php
- 2026_10_08_142108_add_status_details_to_orders_table.php
- 2026_10_11_100000_add_visuals_to_flavor_tables.php
- 2026_10_11_100100_create_cake_customization_layers_table.php
- 2026_10_14_100000_add_is_default_to_flavor_tables.php
- ⚡two-factor-setup-modal.blade.php
- 2026_10_01_065018_create_cake_customizations_table.php
- 2026_10_01_072506_create_cake_previews_table.php
- CakeVariantAdminTest.php
- CakeVariant
- ⚡profile.blade.php
- Apple Touch Icon (Laravel Logo)
- Site Favicon (favicon.svg)
- regenerateRecoveryCodes
- header.blade.php
- sidebar.blade.php
- card.blade.php
- simple.blade.php
- split.blade.php
- ⚡appearance.blade.php
- ⚡delete-user-form.blade.php
- robots.txt Allow-All Crawl Policy
- Illuminate\Database\Eloquent\Model
- Logout.php
- Illuminate\Database\Seeder
- 2026_10_01_064803_create_cream_flavors_table.php
- CakeSize
- 0001_01_01_000002_create_jobs_table.php
- cake-variants/edit.blade.php
- 2026_10_14_120000_add_weight_kg_to_cake_sizes_table.php
- 2026_10_14_130000_create_cake_variants_table.php
- 2026_10_06_042059_create_orders_table.php
- cake-variants/index.blade.php
- edit.blade.php
- sizes/index.blade.php
- cake-variants/_alerts.blade.php

## God Nodes (most connected - your core abstractions)
1. `User` - 29 edges
2. `completeVariant()` - 21 edges
3. `CakeVariant` - 20 edges
4. `CreamFlavor` - 17 edges
5. `SpongeFlavor` - 17 edges
6. `CakeSize` - 16 edges
7. `Controller` - 14 edges
8. `SvgSanitizer` - 14 edges
9. `CakeVisualController` - 13 edges
10. `CartService` - 13 edges

## Surprising Connections (you probably didn't know these)
- `useSizeInOrder()` --references--> `CakeSize`  [EXTRACTED]
  tests/Feature/SizeAdminTest.php → app/Models/CakeSize.php
- `completeVariant()` --references--> `CakeVariant`  [EXTRACTED]
  tests/Pest.php → app/Models/CakeVariant.php
- `{closure#3}()` --calls--> `CakeVariantSlot`  [EXTRACTED]
  tests/Feature/CakeVariantTest.php → app/Models/CakeVariantSlot.php
- `{closure#2}()` --calls--> `completeVariant()`  [INFERRED]
  tests/Feature/CakeVariantAdminTest.php → tests/Pest.php
- `{closure#4}()` --calls--> `completeVariant()`  [INFERRED]
  tests/Feature/CakeVariantAdminTest.php → tests/Pest.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **CI Pipeline Setup and Check Flow** — _github_workflows_tests_actions_checkout, _github_workflows_tests_setup_php, _github_workflows_tests_setup_node, _github_workflows_tests_composer_setup, _github_workflows_tests_composer_ci_check [EXTRACTED 1.00]

## Communities (128 total, 106 thin omitted)

### Community 0 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.13
Nodes (4): CakeCustomization, {closure#2}(), CakeCustomizationLayer, CakePreview

### Community 1 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.07
Nodes (15): ReorderFlavors, CakePreviewController, CakeSizeController, CakeVisualController, {closure#1}(), {closure#1}(), OrderController, CakeStudioController (+7 more)

### Community 3 - "FortifyServiceProvider.php"
Cohesion: 0.05
Nodes (10): CreateNewUser, ResetUserPassword, PasswordValidationRules, ProfileValidationRules, AppServiceProvider, {closure#1}(), {closure#10}(), {closure#8}() (+2 more)

### Community 5 - "package.json"
Cohesion: 0.06
Nodes (30): dependencies, concurrently, @laravel/passkeys, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite, vite-plus (+22 more)

### Community 7 - "Illuminate\Foundation\Http\FormRequest"
Cohesion: 0.08
Nodes (8): EnsureUserIsAdmin, {closure#1}(), ReorderFlavorsRequest, StoreFlavorTextureRequest, {closure#1}(), {closure#2}(), UpdateCakeVariantRequest, PlaceOrderRequest

### Community 8 - "CartController.php"
Cohesion: 0.11
Nodes (5): CartController, {closure#1}(), StoreCakeCustomizationRequest, CartService, {closure#2}()

### Community 10 - "scripts"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 11 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.17
Nodes (7): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}()

### Community 12 - "require-dev"
Cohesion: 0.17
Nodes (12): require-dev, fakerphp/faker, larastan/larastan, laravel/boost, laravel/pail, laravel/pao, laravel/pint, laravel/sail (+4 more)

### Community 13 - "ci Job"
Cohesion: 0.27
Nodes (8): Dependabot Config, actions/checkout (pinned SHA v7.0.1), ci Job, composer ci:check script, composer setup script, actions/setup-node (Node 22), shivammathur/setup-php (PHP 8.4, composer v2), tests Workflow

### Community 14 - "require"
Cohesion: 0.20
Nodes (10): require, doctrine/dbal, laravel/chisel, laravel/fortify, laravel/framework, laravel/tinker, livewire/blaze, livewire/flux (+2 more)

### Community 15 - "composer.json"
Cohesion: 0.17
Nodes (11): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+3 more)

### Community 18 - "⚡security.blade.php"
Cohesion: 0.25
Nodes (7): closeDeleteModal, confirmDelete({{ $passkey[, deletePasskey, disable, $dispatch(, pages, partials.settings-heading

### Community 19 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 21 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 22 - "laravel"
Cohesion: 0.40
Nodes (5): extra, laravel, post-create-project, dont-discover, installer

### Community 36 - "⚡two-factor-setup-modal.blade.php"
Cohesion: 0.50
Nodes (3): confirmTwoFactor, resetVerification, showVerificationIfNecessary

### Community 39 - "CakeVariantAdminTest.php"
Cohesion: 0.07
Nodes (27): CakeVariantSlot, CakeVariantSeeder, {closure#1}(), {closure#11}(), {closure#12}(), {closure#13}(), {closure#14}(), {closure#15}() (+19 more)

### Community 40 - "CakeVariant"
Cohesion: 0.12
Nodes (5): {closure#1}(), SaveCakeVariant, CakeVariantController, CakeVariant, SvgSanitizer

### Community 104 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.16
Nodes (3): CakePart, CreamFlavor, SpongeFlavor

### Community 106 - "Illuminate\Database\Seeder"
Cohesion: 0.17
Nodes (5): CakePreviewSeeder, CakeSizeSeeder, CreamFlavorSeeder, DatabaseSeeder, SpongeFlavorSeeder

### Community 114 - "0001_01_01_000002_create_jobs_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

## Knowledge Gaps
- **44 isolated node(s):** `concurrently`, `tailwindcss`, `vite`, `@laravel/multiplex`, `@rollup/rollup-linux-x64-gnu` (+39 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 403 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **106 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `Laravel\Fortify\Features`, `FortifyServiceProvider.php`, `SizeAdminTest.php`, `UserFactory.php`, `CakeVariantAdminTest.php`, `FlavorAdminTest.php`, `EmailVerificationTest.php`, `PasswordResetTest.php`, `DefaultCakePreviewTest.php`, `SecurityTest.php`, `AuthenticationTest.php`, `DashboardTest.php`?**
  _High betweenness centrality (0.150) - this node is a cross-community bridge._
- **What connects `concurrently`, `tailwindcss`, `vite` to the rest of the system?**
  _44 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Database\Eloquent\Relations\BelongsTo` be split into smaller, more focused modules?**
  _Cohesion score 0.13438735177865613 - nodes in this community are weakly interconnected._
- **Why does `CreamFlavor` connect `Illuminate\Database\Eloquent\Model` to `Illuminate\Http\RedirectResponse`, `SizeAdminTest.php`, `Illuminate\Foundation\Http\FormRequest`, `CartController.php`, `FlavorAdminTest.php`, `Illuminate\Database\Seeder`, `DefaultCakePreviewTest.php`?**
  _High betweenness centrality (0.060) - this node is a cross-community bridge._
- **Should `Illuminate\Http\RedirectResponse` be split into smaller, more focused modules?**
  _Cohesion score 0.0673076923076923 - nodes in this community are weakly interconnected._
- **Why does `SpongeFlavor` connect `Illuminate\Database\Eloquent\Model` to `Illuminate\Http\RedirectResponse`, `SizeAdminTest.php`, `Illuminate\Foundation\Http\FormRequest`, `CartController.php`, `FlavorAdminTest.php`, `Illuminate\Database\Seeder`, `DefaultCakePreviewTest.php`?**
  _High betweenness centrality (0.060) - this node is a cross-community bridge._
- **Should `FortifyServiceProvider.php` be split into smaller, more focused modules?**
  _Cohesion score 0.05279034690799397 - nodes in this community are weakly interconnected._