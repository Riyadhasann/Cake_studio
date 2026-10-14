# Graph Report - phase-4-variants-admin  (2026-10-14)

## Corpus Check
- 173 files · ~34,223 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 22 file(s) not represented in the graph (top: (none) 17, .example 1, .neon 1)

## Summary
- 817 nodes · 1213 edges · 128 communities (22 shown, 106 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 26 edges (avg confidence: 0.87)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `17053e17`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Database\Eloquent\Model
- Illuminate\Http\RedirectResponse
- EmailVerificationTest.php
- FortifyServiceProvider.php
- SvgSanitizer
- package.json
- UserFactory.php
- CreamFlavor
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
- VariantAdminTest.php
- SizeAdminTest.php
- Illuminate\Database\Seeder
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
- PasswordValidationRules
- CakeSize
- ProfileValidationRules
- bootstrap/app.php
- 2026_10_01_064803_create_cream_flavors_table.php
- Logout.php
- 0001_01_01_000002_create_jobs_table.php
- cake-variants/index.blade.php
- 2026_10_14_120000_add_weight_kg_to_cake_sizes_table.php
- 2026_10_14_130000_create_cake_variants_table.php
- 2026_10_06_042059_create_orders_table.php
- cake-variants/edit.blade.php
- sizes/edit.blade.php
- sizes/index.blade.php

## God Nodes (most connected - your core abstractions)
1. `User` - 29 edges
2. `CakeVariant` - 21 edges
3. `CreamFlavor` - 17 edges
4. `SpongeFlavor` - 17 edges
5. `CakeSize` - 16 edges
6. `Controller` - 14 edges
7. `CakeVisualController` - 13 edges
8. `CartService` - 13 edges
9. `SvgSanitizer` - 13 edges
10. `scripts` - 13 edges

## Surprising Connections (you probably didn't know these)
- `useSizeInOrder()` --references--> `CakeSize`  [EXTRACTED]
  tests/Feature/SizeAdminTest.php → app/Models/CakeSize.php
- `completeVariant()` --references--> `CakeVariant`  [EXTRACTED]
  tests/Feature/CakeVariantTest.php → app/Models/CakeVariant.php
- `variantForm()` --references--> `CakeVariant`  [EXTRACTED]
  tests/Feature/VariantAdminTest.php → app/Models/CakeVariant.php
- `{closure#3}()` --calls--> `CakeVariantSlot`  [EXTRACTED]
  tests/Feature/CakeVariantTest.php → app/Models/CakeVariantSlot.php
- `GitHub Actions Weekly Updates (grouped, 5-day cooldown)` --references--> `actions/checkout (pinned SHA v7.0.1)`  [INFERRED]
  .github/dependabot.yml → .github/workflows/tests.yml

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **CI Pipeline Setup and Check Flow** — _github_workflows_tests_actions_checkout, _github_workflows_tests_setup_php, _github_workflows_tests_setup_node, _github_workflows_tests_composer_setup, _github_workflows_tests_composer_ci_check [EXTRACTED 1.00]

## Communities (128 total, 106 thin omitted)

### Community 0 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.14
Nodes (5): CakeCustomization, {closure#2}(), CakeCustomizationLayer, CakePart, CakePreview

### Community 1 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.06
Nodes (17): ReorderFlavors, {closure#1}(), SaveCakeVariant, CakePreviewController, CakeSizeController, CakeVariantController, CakeVisualController, {closure#1}() (+9 more)

### Community 3 - "FortifyServiceProvider.php"
Cohesion: 0.08
Nodes (6): AppServiceProvider, {closure#1}(), {closure#10}(), {closure#8}(), {closure#9}(), FortifyServiceProvider

### Community 4 - "SvgSanitizer"
Cohesion: 0.06
Nodes (9): {closure#1}(), ReorderFlavorsRequest, {closure#1}(), SaveCakeVariantRequest, StoreFlavorTextureRequest, PlaceOrderRequest, {closure#1}(), StoreCakeCustomizationRequest (+1 more)

### Community 5 - "package.json"
Cohesion: 0.06
Nodes (30): dependencies, concurrently, @laravel/passkeys, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite, vite-plus (+22 more)

### Community 8 - "CartController.php"
Cohesion: 0.17
Nodes (3): CartController, CartService, {closure#2}()

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

### Community 39 - "VariantAdminTest.php"
Cohesion: 0.07
Nodes (24): CakeVariantSlot, CakeVariantSeeder, {closure#1}(), {closure#1}(), {closure#10}(), {closure#11}(), {closure#2}(), {closure#3}() (+16 more)

### Community 43 - "Illuminate\Database\Seeder"
Cohesion: 0.17
Nodes (5): CakePreviewSeeder, CakeSizeSeeder, CreamFlavorSeeder, DatabaseSeeder, SpongeFlavorSeeder

### Community 104 - "PasswordValidationRules"
Cohesion: 0.24
Nodes (3): CreateNewUser, ResetUserPassword, PasswordValidationRules

### Community 107 - "bootstrap/app.php"
Cohesion: 0.32
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 114 - "0001_01_01_000002_create_jobs_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

## Knowledge Gaps
- **42 isolated node(s):** `concurrently`, `tailwindcss`, `vite`, `@laravel/multiplex`, `@rollup/rollup-linux-x64-gnu` (+37 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 405 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **106 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `EmailVerificationTest.php`, `UserFactory.php`, `VariantAdminTest.php`, `PasswordValidationRules`, `FlavorAdminTest.php`, `ProfileValidationRules`, `SizeAdminTest.php`, `DefaultCakePreviewTest.php`, `SecurityTest.php`, `ProfileUpdateTest.php`, `DashboardTest.php`?**
  _High betweenness centrality (0.162) - this node is a cross-community bridge._
- **What connects `concurrently`, `tailwindcss`, `vite` to the rest of the system?**
  _42 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Database\Eloquent\Model` be split into smaller, more focused modules?**
  _Cohesion score 0.13538461538461538 - nodes in this community are weakly interconnected._
- **Why does `CreamFlavor` connect `CreamFlavor` to `Illuminate\Database\Eloquent\Model`, `Illuminate\Http\RedirectResponse`, `SvgSanitizer`, `CartController.php`, `CakeSize`, `FlavorAdminTest.php`, `Illuminate\Database\Seeder`, `DefaultCakePreviewTest.php`, `SizeAdminTest.php`?**
  _High betweenness centrality (0.061) - this node is a cross-community bridge._
- **Should `Illuminate\Http\RedirectResponse` be split into smaller, more focused modules?**
  _Cohesion score 0.05939629990262902 - nodes in this community are weakly interconnected._
- **Why does `SpongeFlavor` connect `CreamFlavor` to `Illuminate\Database\Eloquent\Model`, `Illuminate\Http\RedirectResponse`, `SvgSanitizer`, `CartController.php`, `CakeSize`, `FlavorAdminTest.php`, `Illuminate\Database\Seeder`, `DefaultCakePreviewTest.php`, `SizeAdminTest.php`?**
  _High betweenness centrality (0.061) - this node is a cross-community bridge._
- **Should `EmailVerificationTest.php` be split into smaller, more focused modules?**
  _Cohesion score 0.046511627906976744 - nodes in this community are weakly interconnected._