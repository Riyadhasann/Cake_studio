# Graph Report - Cake_studio  (2026-10-15)

## Corpus Check
- 196 files · ~52,838 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 21 file(s) not represented in the graph (top: (none) 16, .example 1, .neon 1)

## Summary
- 1058 nodes · 1712 edges · 150 communities (35 shown, 115 thin omitted)
- Extraction: 94% EXTRACTED · 6% INFERRED · 0% AMBIGUOUS · INFERRED: 95 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `0145e355`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Database\Eloquent\Model
- SizeAdminTest.php
- FortifyServiceProvider.php
- CakeVariantRealismTest.php
- package.json
- UserFactory.php
- Closure
- CartService
- FlavorAdminTest.php
- scripts
- Illuminate\Database\Schema\Blueprint
- require-dev
- ci Job
- require
- composer.json
- Illuminate\Database\Migrations\Migration
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
- FortifyServiceProvider
- Illuminate\Support\Facades\Schema
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
- LayerStrips
- cake-visuals/index.blade.php
- PasswordValidationRules
- CakePhotoTest.php
- DefaultCakePreviewTest.php
- CutFaceFinder
- PolishTest.php
- Illuminate\Foundation\Http\FormRequest
- 0001_01_01_000002_create_jobs_table.php
- cake-variants/edit.blade.php
- StoreCakeCustomizationRequest
- 2026_10_14_120000_add_weight_kg_to_cake_sizes_table.php
- 2026_10_14_130000_create_cake_variants_table.php
- 2026_10_06_042059_create_orders_table.php
- cake-variants/index.blade.php
- edit.blade.php
- ReorderFlavorsRequest
- admin.sizes._alerts
- UpdateLayerImageRequest
- 2026_10_06_042159_add_order_foreign_key_to_cake_customizations_table.php
- 2026_10_06_100909_rename_total_price_to_total_on_orders_table.php
- 2026_10_15_120000_add_base_image_to_cake_variants.php
- 2026_10_15_140000_drop_texture_image_from_flavor_tables.php
- StoreVariantBaseRequest
- StoreVariantPhotoRequest
- .roundedRectPath
- 2026_10_15_100000_drop_cake_previews_table.php
- Tests\TestCase

## God Nodes (most connected - your core abstractions)
1. `CakeVariant` - 40 edges
2. `completeVariant()` - 34 edges
3. `PublicUploads` - 29 edges
4. `User` - 26 edges
5. `CreamFlavor` - 20 edges
6. `SpongeFlavor` - 20 edges
7. `CakeSize` - 15 edges
8. `SvgSanitizer` - 15 edges
9. `CartService` - 13 edges
10. `scripts` - 13 edges

## Surprising Connections (you probably didn't know these)
- `{closure#1}()` --calls--> `completeVariant()`  [INFERRED]
  tests/Feature/CakePhotoTest.php → tests/Pest.php
- `{closure#3}()` --calls--> `completeVariant()`  [INFERRED]
  tests/Feature/CakePhotoTest.php → tests/Pest.php
- `{closure#4}()` --calls--> `completeVariant()`  [INFERRED]
  tests/Feature/CakeStudioVariantPreviewTest.php → tests/Pest.php
- `{closure#7}()` --calls--> `completeVariant()`  [INFERRED]
  tests/Feature/CakeStudioVariantPreviewTest.php → tests/Pest.php
- `{closure#8}()` --calls--> `completeVariant()`  [INFERRED]
  tests/Feature/CakeStudioVariantPreviewTest.php → tests/Pest.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **CI Pipeline Setup and Check Flow** — _github_workflows_tests_actions_checkout, _github_workflows_tests_setup_php, _github_workflows_tests_setup_node, _github_workflows_tests_composer_setup, _github_workflows_tests_composer_ci_check [EXTRACTED 1.00]

## Communities (150 total, 115 thin omitted)

### Community 0 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.06
Nodes (12): CakeStudioController, CakeCustomization, {closure#2}(), CakeCustomizationLayer, CakePart, CakeVariantSlot, FlavorVariantImage, CakeDrawings (+4 more)

### Community 1 - "SizeAdminTest.php"
Cohesion: 0.09
Nodes (6): DeleteCakeSize, CakeSizeController, SaveCakeSizeRequest, CakeSize, {closure#10}(), useSizeInOrder()

### Community 3 - "FortifyServiceProvider.php"
Cohesion: 0.06
Nodes (13): {closure#1}(), OrderController, Controller, OrderTrackingController, EnsureUserIsAdmin, Order, {closure#10}(), {closure#8}() (+5 more)

### Community 4 - "CakeVariantRealismTest.php"
Cohesion: 0.12
Nodes (12): {closure#1}(), {closure#11}(), {closure#12}(), {closure#13}(), {closure#16}(), {closure#2}(), {closure#20}(), {closure#3}() (+4 more)

### Community 5 - "package.json"
Cohesion: 0.06
Nodes (30): dependencies, concurrently, @laravel/passkeys, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite, vite-plus (+22 more)

### Community 7 - "Closure"
Cohesion: 0.33
Nodes (4): {closure#1}(), {closure#1}(), {closure#2}(), {closure#1}()

### Community 8 - "CartService"
Cohesion: 0.18
Nodes (4): CartController, CartService, {closure#2}(), {closure#29}()

### Community 9 - "FlavorAdminTest.php"
Cohesion: 0.06
Nodes (9): {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}(), savedSvg() (+1 more)

### Community 10 - "scripts"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 11 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.16
Nodes (8): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}()

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

### Community 16 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.15
Nodes (3): {closure#1}(), {closure#1}(), {closure#1}()

### Community 18 - "⚡security.blade.php"
Cohesion: 0.25
Nodes (7): closeDeleteModal, confirmDelete({{ $passkey[, deletePasskey, disable, $dispatch(, pages, partials.settings-heading

### Community 19 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 20 - "CakeStudioVariantPreviewTest.php"
Cohesion: 0.20
Nodes (3): {closure#4}(), {closure#7}(), {closure#8}()

### Community 21 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 22 - "laravel"
Cohesion: 0.40
Nodes (5): extra, laravel, post-create-project, dont-discover, installer

### Community 26 - "CakeVariantAdminTest.php"
Cohesion: 0.16
Nodes (11): {closure#11}(), {closure#12}(), {closure#13}(), {closure#14}(), {closure#15}(), {closure#16}(), {closure#2}(), {closure#4}() (+3 more)

### Community 36 - "⚡two-factor-setup-modal.blade.php"
Cohesion: 0.50
Nodes (3): confirmTwoFactor, resetVerification, showVerificationIfNecessary

### Community 37 - "FortifyServiceProvider"
Cohesion: 0.17
Nodes (3): AppServiceProvider, {closure#1}(), FortifyServiceProvider

### Community 38 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.15
Nodes (3): {closure#1}(), {closure#1}(), {closure#1}()

### Community 39 - "completeVariant"
Cohesion: 0.21
Nodes (10): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}() (+2 more)

### Community 40 - "CakeVariant"
Cohesion: 0.09
Nodes (7): {closure#1}(), SaveCakeVariant, CakeVariantController, UpdateCakeVariantRequest, CakeVariant, {closure#1}(), SvgSanitizer

### Community 43 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.07
Nodes (14): ReorderFlavors, ApplyCakePhoto, {closure#1}(), CakePhotoController, CakeVariantImageController, CakeVisualController, {closure#1}(), {closure#1}() (+6 more)

### Community 104 - "PasswordValidationRules"
Cohesion: 0.16
Nodes (4): CreateNewUser, ResetUserPassword, PasswordValidationRules, ProfileValidationRules

### Community 105 - "CakePhotoTest.php"
Cohesion: 0.07
Nodes (24): {closure#4}(), cakePhotoPng(), {closure#1}(), {closure#12}(), {closure#13}(), {closure#16}(), {closure#17}(), {closure#18}() (+16 more)

### Community 106 - "DefaultCakePreviewTest.php"
Cohesion: 0.07
Nodes (6): CakeSizeSeeder, CakeVariantSeeder, {closure#1}(), CreamFlavorSeeder, DatabaseSeeder, SpongeFlavorSeeder

### Community 107 - "CutFaceFinder"
Cohesion: 0.26
Nodes (3): {closure#1}(), {closure#2}(), CutFaceFinder

### Community 110 - "PolishTest.php"
Cohesion: 0.18
Nodes (3): {closure#10}(), {closure#3}(), {closure#5}()

### Community 114 - "0001_01_01_000002_create_jobs_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 115 - "cake-variants/edit.blade.php"
Cohesion: 0.33
Nodes (5): admin.cake-variants._base, admin.cake-variants._cut-face, admin.cake-variants._realism, admin.cake-variants._alerts, admin.cake-variants._publish

### Community 141 - ".roundedRectPath"
Cohesion: 0.50
Nodes (3): {closure#5}(), {closure#10}(), {closure#11}()

## Knowledge Gaps
- **46 isolated node(s):** `admin.cake-variants._publish`, `admin.cake-variants._alerts`, `admin.cake-variants._base`, `admin.cake-variants._cut-face`, `admin.cake-variants._realism` (+41 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 491 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **115 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `SizeAdminTest.php`, `Laravel\Fortify\Features`, `UserFactory.php`, `PasswordValidationRules`, `EmailVerificationTest.php`, `SecurityTest.php`, `PasswordResetTest.php`, `DashboardTest.php`?**
  _High betweenness centrality (0.103) - this node is a cross-community bridge._
- **Are the 32 inferred relationships involving `completeVariant()` (e.g. with `{closure#1}()` and `{closure#17}()`) actually correct?**
  _`completeVariant()` has 32 INFERRED edges - model-reasoned connections that need verification._
- **What connects `admin.cake-variants._publish`, `admin.cake-variants._alerts`, `admin.cake-variants._base` to the rest of the system?**
  _46 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Database\Eloquent\Model` be split into smaller, more focused modules?**
  _Cohesion score 0.056107539450613676 - nodes in this community are weakly interconnected._
- **Why does `CakeVariant` connect `CakeVariant` to `CakeVariant.php`, `Illuminate\Database\Eloquent\Model`, `CakePhotoTest.php`, `DefaultCakePreviewTest.php`, `Illuminate\Http\RedirectResponse`, `PolishTest.php`, `CakeStudioVariantPreviewTest.php`?**
  _High betweenness centrality (0.061) - this node is a cross-community bridge._
- **Should `SizeAdminTest.php` be split into smaller, more focused modules?**
  _Cohesion score 0.08522727272727272 - nodes in this community are weakly interconnected._
- **Why does `SpongeFlavor` connect `Illuminate\Http\RedirectResponse` to `Illuminate\Database\Eloquent\Model`, `SizeAdminTest.php`, `CakeVariantRealismTest.php`, `CakePhotoTest.php`, `DefaultCakePreviewTest.php`, `FlavorAdminTest.php`, `PolishTest.php`, `CakeStudioVariantPreviewTest.php`?**
  _High betweenness centrality (0.054) - this node is a cross-community bridge._