# Graph Report - Cake_studio  (2026-10-14)

## Corpus Check
- 164 files · ~30,682 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 21 file(s) not represented in the graph (top: (none) 16, .example 1, .neon 1)

## Summary
- 762 nodes · 1093 edges · 127 communities (22 shown, 105 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 19 edges (avg confidence: 0.87)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `ec1f0b92`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Database\Eloquent\Model
- Illuminate\Http\RedirectResponse
- FortifyServiceProvider
- CakeSize
- package.json
- UserFactory.php
- FortifyServiceProvider.php
- App\Http\Controllers\CartController
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
- CakeVariantTest.php
- SizeAdminTest.php
- App\Http\Controllers\Admin\OrderController
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
- Illuminate\View\View
- bootstrap/app.php
- Pest.php
- web.php
- 2026_10_01_064803_create_cream_flavors_table.php
- App\Http\Controllers\Admin\CakePreviewController
- 0001_01_01_000002_create_jobs_table.php
- App\Http\Middleware\EnsureUserIsAdmin
- 2026_10_14_120000_add_weight_kg_to_cake_sizes_table.php
- 2026_10_14_130000_create_cake_variants_table.php
- 2026_10_06_042059_create_orders_table.php
- edit.blade.php
- sizes/index.blade.php

## God Nodes (most connected - your core abstractions)
1. `User` - 26 edges
2. `CakeSize` - 17 edges
3. `CreamFlavor` - 16 edges
4. `SpongeFlavor` - 16 edges
5. `CartService` - 13 edges
6. `scripts` - 13 edges
7. `CakeVisualController` - 12 edges
8. `require-dev` - 12 edges
9. `completeVariant()` - 11 edges
10. `Order` - 10 edges

## Surprising Connections (you probably didn't know these)
- `useSizeInOrder()` --references--> `CakeSize`  [EXTRACTED]
  tests/Feature/SizeAdminTest.php → app/Models/CakeSize.php
- `completeVariant()` --references--> `CakeVariant`  [EXTRACTED]
  tests/Feature/CakeVariantTest.php → app/Models/CakeVariant.php
- `{closure#3}()` --calls--> `CakeVariantSlot`  [EXTRACTED]
  tests/Feature/CakeVariantTest.php → app/Models/CakeVariantSlot.php
- `GitHub Actions Weekly Updates (grouped, 5-day cooldown)` --references--> `actions/checkout (pinned SHA v7.0.1)`  [INFERRED]
  .github/dependabot.yml → .github/workflows/tests.yml
- `GitHub Actions Weekly Updates (grouped, 5-day cooldown)` --references--> `actions/setup-node (Node 22)`  [INFERRED]
  .github/dependabot.yml → .github/workflows/tests.yml

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **CI Pipeline Setup and Check Flow** — _github_workflows_tests_actions_checkout, _github_workflows_tests_setup_php, _github_workflows_tests_setup_node, _github_workflows_tests_composer_setup, _github_workflows_tests_composer_ci_check [EXTRACTED 1.00]

## Communities (127 total, 105 thin omitted)

### Community 0 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.07
Nodes (5): CakeCustomization, CakeCustomizationLayer, CakePart, CakePreview, CakeVariant

### Community 1 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.09
Nodes (8): ReorderFlavors, CakeVisualController, {closure#1}(), {closure#1}(), ReorderFlavorsRequest, Logout, CreamFlavor, SpongeFlavor

### Community 3 - "FortifyServiceProvider"
Cohesion: 0.08
Nodes (7): CreateNewUser, ResetUserPassword, PasswordValidationRules, ProfileValidationRules, AppServiceProvider, {closure#1}(), FortifyServiceProvider

### Community 4 - "CakeSize"
Cohesion: 0.08
Nodes (8): DeleteCakeSize, CakeSizeController, SaveCakeSizeRequest, StoreFlavorTextureRequest, PlaceOrderRequest, {closure#1}(), StoreCakeCustomizationRequest, CakeSize

### Community 5 - "package.json"
Cohesion: 0.06
Nodes (30): dependencies, concurrently, @laravel/passkeys, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite, vite-plus (+22 more)

### Community 7 - "FortifyServiceProvider.php"
Cohesion: 0.16
Nodes (3): {closure#10}(), {closure#8}(), {closure#9}()

### Community 8 - "App\Http\Controllers\CartController"
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

### Community 39 - "CakeVariantTest.php"
Cohesion: 0.07
Nodes (20): CakeVariantSlot, CakePreviewSeeder, CakeSizeSeeder, CakeVariantSeeder, {closure#1}(), CreamFlavorSeeder, DatabaseSeeder, SpongeFlavorSeeder (+12 more)

### Community 43 - "App\Http\Controllers\Admin\OrderController"
Cohesion: 0.29
Nodes (3): {closure#1}(), OrderController, Order

### Community 107 - "bootstrap/app.php"
Cohesion: 0.32
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 114 - "0001_01_01_000002_create_jobs_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

## Knowledge Gaps
- **39 isolated node(s):** `admin.sizes._alerts`, `admin.sizes._fields`, `admin.sizes._alerts`, `admin.sizes._fields`, `closeDeleteModal` (+34 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 387 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **105 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `Illuminate\Database\Eloquent\Model`, `Illuminate\Http\RedirectResponse`, `Laravel\Fortify\Features`, `FortifyServiceProvider`, `UserFactory.php`, `EmailVerificationTest.php`, `PasswordResetTest.php`, `SecurityTest.php`, `ProfileUpdateTest.php`, `TwoFactorChallengeTest.php`, `DashboardTest.php`?**
  _High betweenness centrality (0.137) - this node is a cross-community bridge._
- **What connects `admin.sizes._alerts`, `admin.sizes._fields`, `admin.sizes._alerts` to the rest of the system?**
  _39 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Database\Eloquent\Model` be split into smaller, more focused modules?**
  _Cohesion score 0.07092198581560284 - nodes in this community are weakly interconnected._
- **Why does `CreamFlavor` connect `Illuminate\Http\RedirectResponse` to `App\Http\Controllers\CartController`, `Illuminate\Database\Eloquent\Model`, `FlavorAdminTest.php`, `CakeVariantTest.php`?**
  _High betweenness centrality (0.073) - this node is a cross-community bridge._
- **Should `Illuminate\Http\RedirectResponse` be split into smaller, more focused modules?**
  _Cohesion score 0.08668076109936575 - nodes in this community are weakly interconnected._
- **Why does `SpongeFlavor` connect `Illuminate\Http\RedirectResponse` to `App\Http\Controllers\CartController`, `Illuminate\Database\Eloquent\Model`, `FlavorAdminTest.php`, `CakeVariantTest.php`?**
  _High betweenness centrality (0.073) - this node is a cross-community bridge._
- **Should `FortifyServiceProvider` be split into smaller, more focused modules?**
  _Cohesion score 0.08095238095238096 - nodes in this community are weakly interconnected._