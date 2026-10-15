# Graph Report - Cake_studio  (2026-10-15)

## Corpus Check
- 196 files · ~52,838 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 21 file(s) not represented in the graph (top: (none) 16, .example 1, .neon 1)

## Summary
- 1029 nodes · 1650 edges · 143 communities (31 shown, 112 thin omitted)
- Extraction: 94% EXTRACTED · 6% INFERRED · 0% AMBIGUOUS · INFERRED: 93 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `5b1d4b23`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Database\Eloquent\Relations\BelongsTo
- Illuminate\View\View
- EmailVerificationTest.php
- FortifyServiceProvider.php
- CakeVariantRealismTest.php
- package.json
- UserFactory.php
- Illuminate\Foundation\Http\FormRequest
- App\Http\Controllers\CartController
- FlavorAdminTest.php
- scripts
- Illuminate\Database\Schema\Blueprint
- require-dev
- ci Job
- require
- composer.json
- Illuminate\Support\Facades\Schema
- 2026_10_12_100000_create_cake_parts_table.php
- ⚡security.blade.php
- config
- CakeStudioVariantPreviewTest.php
- psr-4
- laravel
- 0001_01_01_000001_create_cache_table.php
- 2025_08_14_170933_add_two_factor_columns_to_users_table.php
- CakeVariantAdminTest.php
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
- Illuminate\Database\Migrations\Migration
- completeVariant
- CakeVariant
- Illuminate\Http\RedirectResponse
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
- PasswordValidationRules
- CakePhotoTest.php
- Illuminate\Database\Seeder
- ProfileValidationRules
- SizeAdminTest.php
- PolishTest.php
- 0001_01_01_000002_create_jobs_table.php
- cake-variants/edit.blade.php
- 2026_10_14_120000_add_weight_kg_to_cake_sizes_table.php
- 2026_10_14_130000_create_cake_variants_table.php
- 2026_10_06_042059_create_orders_table.php
- cake-variants/index.blade.php
- edit.blade.php
- admin.sizes._alerts
- 2026_10_06_042159_add_order_foreign_key_to_cake_customizations_table.php
- 2026_10_06_100909_rename_total_price_to_total_on_orders_table.php
- 2026_10_14_110000_add_texture_and_sort_order_to_flavor_tables.php
- 2026_10_01_064803_create_cream_flavors_table.php
- 2026_10_01_072506_create_cake_previews_table.php
- .roundedRectPath

## God Nodes (most connected - your core abstractions)
1. `CakeVariant` - 39 edges
2. `completeVariant()` - 34 edges
3. `User` - 27 edges
4. `PublicUploads` - 24 edges
5. `CreamFlavor` - 17 edges
6. `SpongeFlavor` - 17 edges
7. `CakeSize` - 15 edges
8. `SvgSanitizer` - 15 edges
9. `CakeVariantSlot` - 14 edges
10. `CakeVisualController` - 13 edges

## Surprising Connections (you probably didn't know these)
- `useSizeInOrder()` --references--> `CakeSize`  [EXTRACTED]
  tests/Feature/SizeAdminTest.php → app/Models/CakeSize.php
- `{closure#1}()` --calls--> `completeVariant()`  [INFERRED]
  tests/Feature/CakePhotoTest.php → tests/Pest.php
- `{closure#3}()` --calls--> `completeVariant()`  [INFERRED]
  tests/Feature/CakePhotoTest.php → tests/Pest.php
- `{closure#10}()` --calls--> `completeVariant()`  [INFERRED]
  tests/Feature/PolishTest.php → tests/Pest.php
- `{closure#3}()` --calls--> `completeVariant()`  [INFERRED]
  tests/Feature/PolishTest.php → tests/Pest.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **CI Pipeline Setup and Check Flow** — _github_workflows_tests_actions_checkout, _github_workflows_tests_setup_php, _github_workflows_tests_setup_node, _github_workflows_tests_composer_setup, _github_workflows_tests_composer_ci_check [EXTRACTED 1.00]

## Communities (143 total, 112 thin omitted)

### Community 0 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.18
Nodes (3): CakeCustomization, {closure#2}(), CakeCustomizationLayer

### Community 1 - "Illuminate\View\View"
Cohesion: 0.07
Nodes (10): DeleteCakeSize, CakeSizeController, {closure#1}(), OrderController, Controller, OrderTrackingController, SaveCakeSizeRequest, CakeSize (+2 more)

### Community 3 - "FortifyServiceProvider.php"
Cohesion: 0.06
Nodes (10): EnsureUserIsAdmin, AppServiceProvider, {closure#1}(), {closure#10}(), {closure#8}(), {closure#9}(), FortifyServiceProvider, {closure#1}() (+2 more)

### Community 4 - "CakeVariantRealismTest.php"
Cohesion: 0.12
Nodes (11): {closure#1}(), {closure#11}(), {closure#12}(), {closure#13}(), {closure#16}(), {closure#2}(), {closure#21}(), {closure#7}() (+3 more)

### Community 5 - "package.json"
Cohesion: 0.06
Nodes (30): dependencies, concurrently, @laravel/passkeys, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite, vite-plus (+22 more)

### Community 7 - "Illuminate\Foundation\Http\FormRequest"
Cohesion: 0.07
Nodes (11): {closure#1}(), ReorderFlavorsRequest, StoreVariantBaseRequest, StoreVariantPhotoRequest, {closure#1}(), StoreVariantShadingRequest, {closure#1}(), {closure#2}() (+3 more)

### Community 8 - "App\Http\Controllers\CartController"
Cohesion: 0.11
Nodes (5): CartController, {closure#1}(), StoreCakeCustomizationRequest, CartService, {closure#2}()

### Community 9 - "FlavorAdminTest.php"
Cohesion: 0.06
Nodes (12): {closure#6}(), {closure#3}(), {closure#3}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}() (+4 more)

### Community 10 - "scripts"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 11 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.16
Nodes (8): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}()

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

### Community 20 - "CakeStudioVariantPreviewTest.php"
Cohesion: 0.13
Nodes (3): {closure#12}(), {closure#13}(), {closure#9}()

### Community 21 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 22 - "laravel"
Cohesion: 0.40
Nodes (5): extra, laravel, post-create-project, dont-discover, installer

### Community 26 - "CakeVariantAdminTest.php"
Cohesion: 0.22
Nodes (8): {closure#11}(), {closure#14}(), {closure#15}(), {closure#16}(), {closure#5}(), {closure#8}(), {closure#9}(), variantInput()

### Community 36 - "⚡two-factor-setup-modal.blade.php"
Cohesion: 0.50
Nodes (3): confirmTwoFactor, resetVerification, showVerificationIfNecessary

### Community 39 - "completeVariant"
Cohesion: 0.15
Nodes (14): {closure#12}(), {closure#13}(), {closure#2}(), {closure#4}(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}() (+6 more)

### Community 40 - "CakeVariant"
Cohesion: 0.10
Nodes (6): {closure#1}(), SaveCakeVariant, CakeVariantController, CakeVariant, {closure#1}(), SvgSanitizer

### Community 43 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.06
Nodes (12): ReorderFlavors, CakeVariantImageController, CakeVisualController, {closure#1}(), {closure#1}(), UpdateLayerImageRequest, Logout, {closure#1}() (+4 more)

### Community 64 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.14
Nodes (4): CakePart, CreamFlavor, FlavorVariantImage, SpongeFlavor

### Community 104 - "PasswordValidationRules"
Cohesion: 0.24
Nodes (3): CreateNewUser, ResetUserPassword, PasswordValidationRules

### Community 105 - "CakePhotoTest.php"
Cohesion: 0.06
Nodes (28): CakeStudioController, CakeVariantSlot, CakeDrawings, {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}() (+20 more)

### Community 106 - "Illuminate\Database\Seeder"
Cohesion: 0.15
Nodes (6): CakeSizeSeeder, CakeVariantSeeder, {closure#1}(), CreamFlavorSeeder, DatabaseSeeder, SpongeFlavorSeeder

### Community 110 - "PolishTest.php"
Cohesion: 0.18
Nodes (3): {closure#10}(), {closure#3}(), {closure#5}()

### Community 114 - "0001_01_01_000002_create_jobs_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 115 - "cake-variants/edit.blade.php"
Cohesion: 0.40
Nodes (4): admin.cake-variants._alerts, admin.cake-variants._base, admin.cake-variants._publish, admin.cake-variants._realism

## Knowledge Gaps
- **44 isolated node(s):** `admin.cake-variants._alerts`, `admin.cake-variants._base`, `admin.cake-variants._publish`, `admin.cake-variants._realism`, `admin.cake-variants._alerts` (+39 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 485 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **112 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `EmailVerificationTest.php`, `UserFactory.php`, `PasswordValidationRules`, `FlavorAdminTest.php`, `ProfileValidationRules`, `SizeAdminTest.php`, `SecurityTest.php`, `ProfileUpdateTest.php`, `DashboardTest.php`?**
  _High betweenness centrality (0.112) - this node is a cross-community bridge._
- **Are the 32 inferred relationships involving `completeVariant()` (e.g. with `{closure#1}()` and `{closure#17}()`) actually correct?**
  _`completeVariant()` has 32 INFERRED edges - model-reasoned connections that need verification._
- **What connects `admin.cake-variants._alerts`, `admin.cake-variants._base`, `admin.cake-variants._publish` to the rest of the system?**
  _44 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\View\View` be split into smaller, more focused modules?**
  _Cohesion score 0.0673758865248227 - nodes in this community are weakly interconnected._
- **Why does `CakeVariant` connect `CakeVariant` to `CakeVariant.php`, `Illuminate\Database\Eloquent\Model`, `Illuminate\Foundation\Http\FormRequest`, `CakePhotoTest.php`, `Illuminate\Database\Seeder`, `Illuminate\Http\RedirectResponse`, `PolishTest.php`, `DefaultCakePreviewTest.php`, `CakeStudioVariantPreviewTest.php`?**
  _High betweenness centrality (0.068) - this node is a cross-community bridge._
- **Should `EmailVerificationTest.php` be split into smaller, more focused modules?**
  _Cohesion score 0.05128205128205128 - nodes in this community are weakly interconnected._
- **Why does `CreamFlavor` connect `Illuminate\Database\Eloquent\Model` to `CakeVariantRealismTest.php`, `Illuminate\Foundation\Http\FormRequest`, `App\Http\Controllers\CartController`, `FlavorAdminTest.php`, `Illuminate\Http\RedirectResponse`, `SizeAdminTest.php`, `PolishTest.php`, `CakeStudioVariantPreviewTest.php`?**
  _High betweenness centrality (0.043) - this node is a cross-community bridge._