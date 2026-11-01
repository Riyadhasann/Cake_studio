# Graph Report - Cake_studio  (2026-10-29)

## Corpus Check
- 234 files · ~90,117 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 22 file(s) not represented in the graph (top: (none) 16, .css 2, .example 1)

## Summary
- 1407 nodes · 2514 edges · 182 communities (42 shown, 140 thin omitted)
- Extraction: 93% EXTRACTED · 7% INFERRED · 0% AMBIGUOUS · INFERRED: 166 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `4db3e7bc`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Database\Eloquent\Model
- CakeSize
- Cake3dTest.php
- AppServiceProvider.php
- CakeVariantRealismTest.php
- package.json
- UserFactory.php
- DesignPhotoReader.php
- App\Services\CartService
- SaveCakeDesignRequest
- scripts
- Illuminate\Database\Schema\Blueprint
- require-dev
- ci Job
- require
- composer.json
- Illuminate\Database\Migrations\Migration
- App\Models\CreamFlavor
- ⚡security.blade.php
- config
- psr-4
- laravel
- 0001_01_01_000001_create_cache_table.php
- 2025_08_14_170933_add_two_factor_columns_to_users_table.php
- completeVariant
- User
- viewer.js
- 2026_10_08_050619_add_quantity_to_cake_customizations_table.php
- 2026_10_08_050624_drop_delivery_time_from_orders_table.php
- 2026_10_08_142055_add_is_admin_to_users_table.php
- 2026_10_08_142108_add_status_details_to_orders_table.php
- 2026_10_11_100000_add_visuals_to_flavor_tables.php
- 2026_10_11_100100_create_cake_customization_layers_table.php
- 2026_10_14_100000_add_is_default_to_flavor_tables.php
- ⚡two-factor-setup-modal.blade.php
- SvgLayerImageTest.php
- 2026_10_01_064446_create_cake_sizes_table.php
- Illuminate\Http\UploadedFile
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
- StorefrontTest.php
- robots.txt Allow-All Crawl Policy
- Illuminate\Foundation\Http\FormRequest
- UpdateLayerImageRequest
- UpdateSiteImageRequest
- FortifyServiceProvider
- UpdateCakeVariantRequest
- App\Http\Middleware\EnsureUserIsAdmin
- App\Http\Controllers\Controller
- PasswordValidationRules
- Pest.php
- UpdateFlavorSurfaceRequest
- cake-variants/edit.blade.php
- CakePhotoTest.php
- 2026_10_14_120000_add_weight_kg_to_cake_sizes_table.php
- 2026_10_14_130000_create_cake_variants_table.php
- FortifyServiceProvider.php
- cake-variants/index.blade.php
- sizes/edit.blade.php
- LayerStrips
- sizes/index.blade.php
- Illuminate\Http\RedirectResponse
- 2026_10_06_100909_rename_total_price_to_total_on_orders_table.php
- 2026_10_15_120000_add_base_image_to_cake_variants.php
- DefaultCakePreviewTest.php
- CakeDesignTest.php
- Illuminate\Support\Facades\Schema
- StoreCakeDesignFramesRequest
- Illuminate\Database\Eloquent\Builder
- PublicUploads
- ProfileValidationRules
- App\Http\Requests\Admin\ReorderFlavorsRequest
- App\Services\PublicUploads
- Logout.php
- App\Models\CakeVariant
- 2026_10_14_140000_add_photos_and_shading_to_cake_variants.php
- Illuminate\Database\Eloquent\Relations\HasMany
- bootstrap/app.php
- 2026_10_14_110000_add_texture_and_sort_order_to_flavor_tables.php
- 2026_10_25_110000_create_cake_designs_table.php
- 2026_10_25_110100_add_cake_design_to_cake_customizations_table.php
- 2026_10_25_120000_add_fit_to_cake_designs_table.php
- 2026_10_25_130000_add_toppings_to_cake_designs_table.php
- UpdateCakeDesignFitRequest
- 2026_10_07_060906_add_delivery_details_to_orders_table.php
- 2026_10_15_140000_drop_texture_image_from_flavor_tables.php
- Alerts.php
- 2026_10_27_100000_add_top_image_to_cake_designs_table.php
- 2026_10_01_064659_create_sponge_flavors_table.php
- 2026_10_01_065018_create_cake_customizations_table.php
- 2026_10_28_100000_create_site_settings_table.php

## God Nodes (most connected - your core abstractions)
1. `completeVariant()` - 36 edges
2. `SvgSanitizer` - 25 edges
3. `PublicUploads` - 24 edges
4. `User` - 24 edges
5. `CakeVariant` - 23 edges
6. `DesignPhotoReader` - 22 edges
7. `SiteSettings` - 17 edges
8. `CakeDesignController` - 16 edges
9. `SeamlessTexture` - 14 edges
10. `scripts` - 13 edges

## Surprising Connections (you probably didn't know these)
- `{closure#1}()` --calls--> `completeVariant()`  [INFERRED]
  tests/Feature/CakeShapes/CakePhotoTest.php → tests/Pest.php
- `{closure#3}()` --calls--> `completeVariant()`  [INFERRED]
  tests/Feature/CakeShapes/CakePhotoTest.php → tests/Pest.php
- `{closure#1}()` --calls--> `completeVariant()`  [INFERRED]
  tests/Feature/CakeShapes/CakeVariantRealismTest.php → tests/Pest.php
- `{closure#4}()` --calls--> `completeVariant()`  [INFERRED]
  tests/Feature/CakeShapes/ExtraLayerCountTest.php → tests/Pest.php
- `{closure#17}()` --calls--> `completeVariant()`  [INFERRED]
  tests/Feature/CakeShapes/CakePhotoTest.php → tests/Pest.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **CI Pipeline Setup and Check Flow** — _github_workflows_tests_actions_checkout, _github_workflows_tests_setup_php, _github_workflows_tests_setup_node, _github_workflows_tests_composer_setup, _github_workflows_tests_composer_ci_check [EXTRACTED 1.00]

## Communities (182 total, 140 thin omitted)

### Community 0 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.05
Nodes (9): CakeCustomization, {closure#2}(), CakeCustomizationLayer, CakeDesignFrame, CakePart, CakeVariantSlot, SiteSetting, {closure#3}() (+1 more)

### Community 1 - "CakeSize"
Cohesion: 0.20
Nodes (5): DeleteCakeSize, CakeSizeController, Controller, SaveCakeSizeRequest, CakeSize

### Community 2 - "Cake3dTest.php"
Cohesion: 0.19
Nodes (6): {closure#2}(), {closure#3}(), {closure#4}(), gray(), savedImage(), sideLitPng()

### Community 4 - "CakeVariantRealismTest.php"
Cohesion: 0.13
Nodes (11): {closure#1}(), {closure#11}(), {closure#12}(), {closure#13}(), {closure#16}(), {closure#2}(), {closure#20}(), {closure#7}() (+3 more)

### Community 5 - "package.json"
Cohesion: 0.05
Nodes (35): dependencies, alpinejs, concurrently, @laravel/passkeys, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, three (+27 more)

### Community 7 - "DesignPhotoReader.php"
Cohesion: 0.09
Nodes (8): {closure#12}(), {closure#14}(), {closure#15}(), {closure#4}(), {closure#6}(), DesignPhotoReader, {closure#1}(), SeamlessTexture

### Community 8 - "App\Services\CartService"
Cohesion: 0.22
Nodes (3): CartController, CartService, {closure#2}()

### Community 10 - "scripts"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 11 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.12
Nodes (12): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}() (+4 more)

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

### Community 17 - "App\Models\CreamFlavor"
Cohesion: 0.17
Nodes (3): CakeVisualController, {closure#1}(), FlavorController

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

### Community 26 - "completeVariant"
Cohesion: 0.05
Nodes (30): {closure#11}(), {closure#12}(), {closure#13}(), {closure#14}(), {closure#15}(), {closure#16}(), {closure#2}(), {closure#4}() (+22 more)

### Community 28 - "viewer.js"
Cohesion: 0.05
Nodes (63): create(), addStand(), AUTO_ROTATE, averageColor(), buildCake(), buildDesignCake(), BUMP, cleanTopTexture() (+55 more)

### Community 36 - "⚡two-factor-setup-modal.blade.php"
Cohesion: 0.50
Nodes (3): confirmTwoFactor, resetVerification, showVerificationIfNecessary

### Community 37 - "SvgLayerImageTest.php"
Cohesion: 0.27
Nodes (9): {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}(), savedSvg() (+1 more)

### Community 40 - "Illuminate\Http\UploadedFile"
Cohesion: 0.17
Nodes (6): {closure#3}(), {closure#1}(), {closure#1}(), {closure#1}(), {closure#6}(), {closure#3}()

### Community 55 - "StorefrontTest.php"
Cohesion: 0.32
Nodes (3): addStorefrontCake(), {closure#3}(), {closure#4}()

### Community 64 - "Illuminate\Foundation\Http\FormRequest"
Cohesion: 0.14
Nodes (3): SaveFlavorRequest, StoreVariantPhotoRequest, PlaceOrderRequest

### Community 103 - "App\Http\Controllers\Controller"
Cohesion: 0.09
Nodes (6): DashboardController, CakeStudioController, {closure#3}(), OrderTrackingController, {closure#10}(), useSizeInOrder()

### Community 104 - "PasswordValidationRules"
Cohesion: 0.24
Nodes (3): CreateNewUser, ResetUserPassword, PasswordValidationRules

### Community 115 - "cake-variants/edit.blade.php"
Cohesion: 0.40
Nodes (4): admin.cake-variants._base, admin.cake-variants._cut-face, admin.cake-variants._realism, admin.cake-variants._publish

### Community 116 - "CakePhotoTest.php"
Cohesion: 0.07
Nodes (23): cakePhotoPng(), {closure#1}(), {closure#12}(), {closure#13}(), {closure#16}(), {closure#17}(), {closure#18}(), {closure#19}() (+15 more)

### Community 119 - "FortifyServiceProvider.php"
Cohesion: 0.16
Nodes (3): {closure#10}(), {closure#8}(), {closure#9}()

### Community 128 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.16
Nodes (5): CakeDesignController, {closure#1}(), {closure#4}(), CakeDesign, {closure#4}()

### Community 134 - "DefaultCakePreviewTest.php"
Cohesion: 0.07
Nodes (10): {closure#1}(), CakeDesignSeeder, CakePhotoSeeder, {closure#1}(), CakeSizeSeeder, CakeVariantSeeder, {closure#1}(), CreamFlavorSeeder (+2 more)

### Community 135 - "CakeDesignTest.php"
Cohesion: 0.08
Nodes (20): {closure#1}(), StoreCakeCustomizationRequest, {closure#2}(), {closure#3}(), {closure#4}(), {closure#6}(), orderLayers(), bundledPhotoUpload() (+12 more)

### Community 142 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.15
Nodes (3): {closure#1}(), {closure#1}(), {closure#1}()

### Community 146 - "Illuminate\Database\Eloquent\Builder"
Cohesion: 0.16
Nodes (3): ReorderFlavors, CreamFlavor, SpongeFlavor

### Community 147 - "PublicUploads"
Cohesion: 0.05
Nodes (18): ApplyCakePhoto, {closure#1}(), {closure#2}(), CakePhotoController, {closure#1}(), CakeVariantImageController, SiteSettingsController, {closure#1}() (+10 more)

### Community 151 - "App\Http\Requests\Admin\ReorderFlavorsRequest"
Cohesion: 0.23
Nodes (4): {closure#1}(), ReorderFlavorsRequest, {closure#1}(), {closure#2}()

### Community 156 - "App\Models\CakeVariant"
Cohesion: 0.08
Nodes (9): {closure#1}(), SaveCakeVariant, CakeVariantController, CakeDrawings, {closure#1}(), {closure#2}(), {closure#5}(), {closure#6}() (+1 more)

### Community 157 - "2026_10_14_140000_add_photos_and_shading_to_cake_variants.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 158 - "Illuminate\Database\Eloquent\Relations\HasMany"
Cohesion: 0.33
Nodes (3): {closure#1}(), OrderController, Order

### Community 161 - "bootstrap/app.php"
Cohesion: 0.32
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

## Knowledge Gaps
- **42 isolated node(s):** `admin.cake-variants._base`, `admin.cake-variants._cut-face`, `admin.cake-variants._realism`, `admin.cake-variants._publish`, `admin.cake-variants._publish` (+37 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 607 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **140 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `ProfileUpdateTest.php`, `UserFactory.php`, `PasswordValidationRules`, `EmailVerificationTest.php`, `PasswordResetTest.php`, `SecurityTest.php`, `Laravel\Fortify\Features`, `ProfileValidationRules`?**
  _High betweenness centrality (0.078) - this node is a cross-community bridge._
- **Are the 34 inferred relationships involving `completeVariant()` (e.g. with `{closure#1}()` and `{closure#17}()`) actually correct?**
  _`completeVariant()` has 34 INFERRED edges - model-reasoned connections that need verification._
- **What connects `admin.cake-variants._base`, `admin.cake-variants._cut-face`, `admin.cake-variants._realism` to the rest of the system?**
  _42 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Database\Eloquent\Model` be split into smaller, more focused modules?**
  _Cohesion score 0.05 - nodes in this community are weakly interconnected._
- **Why does `SvgSanitizer` connect `App\Models\CakeVariant` to `Illuminate\Http\UploadedFile`, `App\Models\CreamFlavor`, `CakePhotoTest.php`, `App\Http\Requests\Admin\ReorderFlavorsRequest`, `completeVariant`?**
  _High betweenness centrality (0.032) - this node is a cross-community bridge._
- **Should `CakeVariantRealismTest.php` be split into smaller, more focused modules?**
  _Cohesion score 0.13043478260869565 - nodes in this community are weakly interconnected._
- **Why does `completeVariant()` connect `completeVariant` to `CakeVariantRealismTest.php`, `SvgLayerImageTest.php`, `CakeDesignTest.php`, `Pest.php`, `CakePhotoTest.php`, `App\Models\CakeVariant`?**
  _High betweenness centrality (0.019) - this node is a cross-community bridge._