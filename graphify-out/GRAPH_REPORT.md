# Graph Report - Cake_studio  (2026-10-27)

## Corpus Check
- 235 files · ~88,926 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 22 file(s) not represented in the graph (top: (none) 16, .css 2, .example 1)

## Summary
- 1393 nodes · 2499 edges · 185 communities (48 shown, 137 thin omitted)
- Extraction: 93% EXTRACTED · 7% INFERRED · 0% AMBIGUOUS · INFERRED: 167 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `64b74bc4`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Database\Eloquent\Relations\BelongsTo
- CakeSize
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
- SiteSettings
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
- CakeDesignTest.php
- 2026_10_01_064446_create_cake_sizes_table.php
- CutFaceFinder
- Illuminate\Http\UploadedFile
- CakeVisualController.php
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
- Illuminate\Foundation\Http\FormRequest
- PasswordValidationRules
- CakePhotoTest.php
- Cake3dTest.php
- CakeVariantSlot
- UpdateFlavorSurfaceRequest
- SvgLayerImageTest.php
- cake-variants/edit.blade.php
- cutFace
- 2026_10_14_120000_add_weight_kg_to_cake_sizes_table.php
- 2026_10_14_130000_create_cake_variants_table.php
- FortifyServiceProvider.php
- cake-variants/index.blade.php
- sizes/edit.blade.php
- App\Services\LayerStrips
- sizes/index.blade.php
- SizeAdminTest.php
- CakeDesign
- web.php
- 2026_10_06_100909_rename_total_price_to_total_on_orders_table.php
- 2026_10_15_120000_add_base_image_to_cake_variants.php
- Illuminate\View\View
- Illuminate\Database\Seeder
- ExtraLayerCountTest.php
- App\Actions\Variants\ApplyCakePhoto
- PublicUploads
- CakeDesignController.php
- Illuminate\Support\Facades\Schema
- StoreCakeDesignFramesRequest
- CreamFlavor
- Illuminate\Http\RedirectResponse
- ProfileValidationRules
- SvgSanitizer
- FlavorSurfaceController
- Logout.php
- CakeVariant
- 2026_10_14_140000_add_photos_and_shading_to_cake_variants.php
- App\Http\Controllers\Admin\OrderController
- Illuminate\Database\Eloquent\Model
- bootstrap/app.php
- 2026_10_14_110000_add_texture_and_sort_order_to_flavor_tables.php
- 2026_10_25_110000_create_cake_designs_table.php
- 2026_10_25_110100_add_cake_design_to_cake_customizations_table.php
- 2026_10_25_120000_add_fit_to_cake_designs_table.php
- 2026_10_25_130000_add_toppings_to_cake_designs_table.php
- StorefrontTest.php
- App\Http\Requests\Admin\UpdateCakeDesignFitRequest
- CakeStudioController.php
- 2026_10_07_060906_add_delivery_details_to_orders_table.php
- 2026_10_15_140000_drop_texture_image_from_flavor_tables.php
- OrderAlerts.php
- 2026_10_27_100000_add_top_image_to_cake_designs_table.php
- 2026_10_01_064659_create_sponge_flavors_table.php
- 2026_10_01_065018_create_cake_customizations_table.php
- 2026_10_28_100000_create_site_settings_table.php
- CreamFlavorSeeder

## God Nodes (most connected - your core abstractions)
1. `CakeVariant` - 52 edges
2. `PublicUploads` - 51 edges
3. `completeVariant()` - 36 edges
4. `User` - 31 edges
5. `CakeDesign` - 29 edges
6. `DesignPhotoReader` - 22 edges
7. `SvgSanitizer` - 21 edges
8. `CreamFlavor` - 19 edges
9. `SpongeFlavor` - 19 edges
10. `SiteSettings` - 18 edges

## Surprising Connections (you probably didn't know these)
- `{closure#3}()` --references--> `CakeDesign`  [EXTRACTED]
  tests/Feature/CakeDesignTest.php → app/Models/CakeDesign.php
- `{closure#3}()` --calls--> `CakeVariantSlot`  [INFERRED]
  tests/Feature/CakeVariantTest.php → app/Models/CakeVariantSlot.php
- `{closure#5}()` --references_constant--> `CakeDesign`  [EXTRACTED]
  tests/Feature/CakeDesignTest.php → app/Models/CakeDesign.php
- `designWithPhotos()` --references--> `CakeDesign`  [EXTRACTED]
  tests/Feature/CakeDesignTest.php → app/Models/CakeDesign.php
- `completeVariant()` --references--> `CakeVariant`  [EXTRACTED]
  tests/Pest.php → app/Models/CakeVariant.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **CI Pipeline Setup and Check Flow** — _github_workflows_tests_actions_checkout, _github_workflows_tests_setup_php, _github_workflows_tests_setup_node, _github_workflows_tests_composer_setup, _github_workflows_tests_composer_ci_check [EXTRACTED 1.00]

## Communities (185 total, 137 thin omitted)

### Community 0 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.14
Nodes (3): CakeCustomization, {closure#2}(), CakeCustomizationLayer

### Community 1 - "CakeSize"
Cohesion: 0.21
Nodes (5): DeleteCakeSize, CakeSizeController, Controller, SaveCakeSizeRequest, CakeSize

### Community 3 - "AppServiceProvider.php"
Cohesion: 0.16
Nodes (3): AppServiceProvider, {closure#1}(), FortifyServiceProvider

### Community 4 - "CakeVariantRealismTest.php"
Cohesion: 0.13
Nodes (11): {closure#1}(), {closure#11}(), {closure#12}(), {closure#13}(), {closure#16}(), {closure#2}(), {closure#20}(), {closure#7}() (+3 more)

### Community 5 - "package.json"
Cohesion: 0.05
Nodes (35): dependencies, alpinejs, concurrently, @laravel/passkeys, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, three (+27 more)

### Community 7 - "DesignPhotoReader.php"
Cohesion: 0.09
Nodes (9): {closure#12}(), {closure#14}(), {closure#15}(), {closure#2}(), {closure#4}(), {closure#6}(), DesignPhotoReader, {closure#1}() (+1 more)

### Community 8 - "App\Services\CartService"
Cohesion: 0.19
Nodes (4): CakeStudioController, CartController, CartService, {closure#2}()

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
Cohesion: 0.18
Nodes (3): DashboardController, FlavorController, SaveFlavorRequest

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
Nodes (30): {closure#4}(), {closure#7}(), {closure#8}(), {closure#11}(), {closure#12}(), {closure#13}(), {closure#14}(), {closure#15}() (+22 more)

### Community 28 - "viewer.js"
Cohesion: 0.06
Nodes (57): create(), addStand(), AUTO_ROTATE, averageColor(), buildCake(), buildDesignCake(), BUMP, cleanTopTexture() (+49 more)

### Community 36 - "⚡two-factor-setup-modal.blade.php"
Cohesion: 0.50
Nodes (3): confirmTwoFactor, resetVerification, showVerificationIfNecessary

### Community 37 - "CakeDesignTest.php"
Cohesion: 0.18
Nodes (11): bundledPhotoUpload(), cartPayload(), {closure#13}(), {closure#14}(), {closure#15}(), {closure#3}(), {closure#5}(), {closure#7}() (+3 more)

### Community 39 - "CutFaceFinder"
Cohesion: 0.24
Nodes (3): {closure#1}(), {closure#2}(), CutFaceFinder

### Community 40 - "Illuminate\Http\UploadedFile"
Cohesion: 0.29
Nodes (3): {closure#3}(), {closure#6}(), {closure#3}()

### Community 43 - "CakeVisualController.php"
Cohesion: 0.19
Nodes (3): ReorderFlavors, CakeVisualController, {closure#1}()

### Community 104 - "PasswordValidationRules"
Cohesion: 0.24
Nodes (3): CreateNewUser, ResetUserPassword, PasswordValidationRules

### Community 105 - "CakePhotoTest.php"
Cohesion: 0.08
Nodes (13): cakePhotoPng(), {closure#1}(), {closure#2}(), {closure#23}(), {closure#27}(), {closure#29}(), {closure#3}(), {closure#30}() (+5 more)

### Community 107 - "Cake3dTest.php"
Cohesion: 0.19
Nodes (6): {closure#2}(), {closure#3}(), {closure#4}(), gray(), savedImage(), sideLitPng()

### Community 110 - "CakeVariantSlot"
Cohesion: 0.11
Nodes (10): CakeVariantSlot, FlavorVariantImage, CakeDrawings, {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}() (+2 more)

### Community 114 - "SvgLayerImageTest.php"
Cohesion: 0.27
Nodes (9): {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}(), savedSvg() (+1 more)

### Community 115 - "cake-variants/edit.blade.php"
Cohesion: 0.40
Nodes (4): admin.cake-variants._base, admin.cake-variants._cut-face, admin.cake-variants._realism, admin.cake-variants._publish

### Community 116 - "cutFace"
Cohesion: 0.24
Nodes (9): {closure#12}(), {closure#13}(), {closure#16}(), {closure#17}(), {closure#18}(), {closure#19}(), {closure#4}(), {closure#5}() (+1 more)

### Community 119 - "FortifyServiceProvider.php"
Cohesion: 0.16
Nodes (3): {closure#10}(), {closure#8}(), {closure#9}()

### Community 128 - "CakeDesign"
Cohesion: 0.33
Nodes (5): CakeDesignController, {closure#4}(), CakeDesign, {closure#17}(), {closure#4}()

### Community 134 - "Illuminate\Database\Seeder"
Cohesion: 0.17
Nodes (5): CakeSizeSeeder, CakeVariantSeeder, {closure#1}(), DatabaseSeeder, SpongeFlavorSeeder

### Community 135 - "ExtraLayerCountTest.php"
Cohesion: 0.18
Nodes (7): {closure#1}(), StoreCakeCustomizationRequest, {closure#2}(), {closure#3}(), {closure#4}(), {closure#6}(), orderLayers()

### Community 136 - "App\Actions\Variants\ApplyCakePhoto"
Cohesion: 0.17
Nodes (4): ApplyCakePhoto, {closure#1}(), CakePhotoController, StoreVariantBaseRequest

### Community 139 - "PublicUploads"
Cohesion: 0.16
Nodes (6): {closure#1}(), {closure#1}(), PublicUploads, CakeDesignSeeder, CakePhotoSeeder, {closure#1}()

### Community 140 - "CakeDesignController.php"
Cohesion: 0.24
Nodes (3): {closure#1}(), {closure#2}(), {closure#12}()

### Community 142 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.15
Nodes (3): {closure#1}(), {closure#1}(), {closure#1}()

### Community 151 - "SvgSanitizer"
Cohesion: 0.06
Nodes (11): {closure#1}(), ReorderFlavorsRequest, {closure#1}(), {closure#1}(), {closure#2}(), UpdateCakeVariantRequest, {closure#1}(), UpdateLayerImageRequest (+3 more)

### Community 156 - "CakeVariant"
Cohesion: 0.15
Nodes (6): {closure#1}(), SaveCakeVariant, CakeVariantController, {closure#1}(), CakeVariant, {closure#1}()

### Community 157 - "2026_10_14_140000_add_photos_and_shading_to_cake_variants.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 158 - "App\Http\Controllers\Admin\OrderController"
Cohesion: 0.39
Nodes (3): {closure#1}(), OrderController, Order

### Community 159 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.32
Nodes (3): CakeDesignFrame, CakePart, SiteSetting

### Community 161 - "bootstrap/app.php"
Cohesion: 0.32
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 167 - "StorefrontTest.php"
Cohesion: 0.38
Nodes (3): addStorefrontCake(), {closure#3}(), {closure#4}()

## Knowledge Gaps
- **42 isolated node(s):** `admin.cake-variants._publish`, `admin.cake-variants._base`, `admin.cake-variants._cut-face`, `admin.cake-variants._realism`, `admin.cake-variants._publish` (+37 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 598 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **137 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `CakeVariant` connect `CakeVariant` to `Illuminate\Foundation\Http\FormRequest`, `Illuminate\Database\Seeder`, `ExtraLayerCountTest.php`, `App\Actions\Variants\ApplyCakePhoto`, `CakeStudioController.php`, `App\Http\Controllers\CartController`, `CakeVisualController.php`, `PublicUploads`, `CakePhotoTest.php`, `CakeVariantSlot`, `DefaultCakePreviewTest.php`, `App\Models\CreamFlavor`, `Illuminate\Http\RedirectResponse`, `SvgSanitizer`, `completeVariant`, `Illuminate\Database\Eloquent\Model`?**
  _High betweenness centrality (0.099) - this node is a cross-community bridge._
- **What connects `admin.cake-variants._publish`, `admin.cake-variants._base`, `admin.cake-variants._cut-face` to the rest of the system?**
  _42 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Database\Eloquent\Relations\BelongsTo` be split into smaller, more focused modules?**
  _Cohesion score 0.14285714285714285 - nodes in this community are weakly interconnected._
- **Why does `User` connect `User` to `CakeVariantRealismTest.php`, `UserFactory.php`, `PasswordValidationRules`, `CakePhotoTest.php`, `DefaultCakePreviewTest.php`, `Cake3dTest.php`, `EmailVerificationTest.php`, `PasswordResetTest.php`, `SecurityTest.php`, `Laravel\Fortify\Features`, `SvgLayerImageTest.php`, `ProfileValidationRules`, `completeVariant`, `SizeAdminTest.php`?**
  _High betweenness centrality (0.075) - this node is a cross-community bridge._
- **Should `FlavorAdminTest.php` be split into smaller, more focused modules?**
  _Cohesion score 0.14285714285714285 - nodes in this community are weakly interconnected._
- **Why does `PublicUploads` connect `PublicUploads` to `CakeDesign`, `CakeVariantRealismTest.php`, `App\Services\LayerStrips`, `DesignPhotoReader.php`, `App\Actions\Variants\ApplyCakePhoto`, `CakeStudioController.php`, `Illuminate\Http\UploadedFile`, `CakeVisualController.php`, `CakeDesignController.php`, `Illuminate\Database\Seeder`, `CakeVariantSlot`, `App\Models\CreamFlavor`, `CreamFlavor`, `Illuminate\Http\RedirectResponse`, `SiteSettings`, `SvgLayerImageTest.php`, `FlavorSurfaceController`, `CakeVariant`?**
  _High betweenness centrality (0.059) - this node is a cross-community bridge._
- **Should `CakeVariantRealismTest.php` be split into smaller, more focused modules?**
  _Cohesion score 0.13043478260869565 - nodes in this community are weakly interconnected._