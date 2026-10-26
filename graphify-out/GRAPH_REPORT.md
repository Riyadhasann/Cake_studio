# Graph Report - Cake_studio  (2026-10-26)

## Corpus Check
- 222 files · ~74,179 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 22 file(s) not represented in the graph (top: (none) 16, .css 2, .example 1)

## Summary
- 1273 nodes · 2177 edges · 178 communities (47 shown, 131 thin omitted)
- Extraction: 94% EXTRACTED · 6% INFERRED · 0% AMBIGUOUS · INFERRED: 121 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `3d74c9c7`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Database\Eloquent\Relations\BelongsTo
- CakeSize
- CakeDesignTest.php
- FortifyServiceProvider.php
- CakeVariantRealismTest.php
- package.json
- UserFactory.php
- UpdateCakeVariantRequest.php
- App\Services\CartService
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
- completeVariant
- psr-4
- laravel
- 0001_01_01_000001_create_cache_table.php
- 2025_08_14_170933_add_two_factor_columns_to_users_table.php
- CakeVariantAdminTest.php
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
- Illuminate\Http\RedirectResponse
- Illuminate\Support\Facades\Schema
- CutFaceFinder
- Illuminate\Http\UploadedFile
- App\Http\Controllers\Admin\CakeVisualController
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
- SizeAdminTest.php
- cake-visuals/index.blade.php
- PolishTest.php
- PasswordValidationRules
- CakePhotoTest.php
- DefaultCakePreviewTest.php
- Cake3dTest.php
- CreamFlavor
- Illuminate\Foundation\Http\FormRequest
- cake-variants/edit.blade.php
- PublicUploads
- 2026_10_14_120000_add_weight_kg_to_cake_sizes_table.php
- 2026_10_14_130000_create_cake_variants_table.php
- 2026_10_06_042059_create_orders_table.php
- cake-variants/index.blade.php
- edit.blade.php
- ReorderFlavorsRequest
- sizes/index.blade.php
- Pest.php
- CakeStudioVariantPreviewTest.php
- 2026_10_06_042159_add_order_foreign_key_to_cake_customizations_table.php
- 2026_10_06_100909_rename_total_price_to_total_on_orders_table.php
- 2026_10_15_120000_add_base_image_to_cake_variants.php
- Illuminate\View\View
- AppServiceProvider.php
- StoreCakeCustomizationRequest
- Controller
- UpdateCakeDesignFitRequest
- CakeVariantSlot
- 2026_10_15_100000_drop_cake_previews_table.php
- StoreVariantBaseRequest
- ProfileValidationRules
- .delete
- StoreVariantPhotoRequest
- CakeVariant
- Illuminate\Database\Eloquent\Model
- Logout.php
- 2026_10_14_140000_add_photos_and_shading_to_cake_variants.php
- Order
- StorefrontTest.php
- bootstrap/app.php
- 2026_10_14_110000_add_texture_and_sort_order_to_flavor_tables.php
- 2026_10_25_110000_create_cake_designs_table.php
- 2026_10_25_110100_add_cake_design_to_cake_customizations_table.php
- 2026_10_25_120000_add_fit_to_cake_designs_table.php
- 2026_10_25_130000_add_toppings_to_cake_designs_table.php
- 2026_10_01_072506_create_cake_previews_table.php
- UpdateLayerImageRequest
- FortifyServiceProvider
- designs/index.blade.php
- cake-variants/_alerts.blade.php
- StoreCakeDesignFramesRequest
- UpdateCakeVariantRequest
- OrderAlerts.php
- App\Http\Middleware\EnsureUserIsAdmin

## God Nodes (most connected - your core abstractions)
1. `CakeVariant` - 44 edges
2. `PublicUploads` - 38 edges
3. `User` - 35 edges
4. `completeVariant()` - 34 edges
5. `CakeDesign` - 27 edges
6. `CreamFlavor` - 26 edges
7. `SpongeFlavor` - 26 edges
8. `SvgSanitizer` - 23 edges
9. `Controller` - 19 edges
10. `CakeVariantSlot` - 19 edges

## Surprising Connections (you probably didn't know these)
- `{closure#3}()` --references--> `CakeDesign`  [EXTRACTED]
  tests/Feature/CakeDesignTest.php → app/Models/CakeDesign.php
- `useSizeInOrder()` --references--> `CakeSize`  [EXTRACTED]
  tests/Feature/SizeAdminTest.php → app/Models/CakeSize.php
- `{closure#3}()` --calls--> `CakeVariantSlot`  [EXTRACTED]
  tests/Feature/CakeVariantTest.php → app/Models/CakeVariantSlot.php
- `completeVariant()` --references--> `CakeVariant`  [EXTRACTED]
  tests/Pest.php → app/Models/CakeVariant.php
- `{closure#5}()` --references_constant--> `CakeDesign`  [EXTRACTED]
  tests/Feature/CakeDesignTest.php → app/Models/CakeDesign.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **CI Pipeline Setup and Check Flow** — _github_workflows_tests_actions_checkout, _github_workflows_tests_setup_php, _github_workflows_tests_setup_node, _github_workflows_tests_composer_setup, _github_workflows_tests_composer_ci_check [EXTRACTED 1.00]

## Communities (178 total, 131 thin omitted)

### Community 0 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.16
Nodes (3): CakeCustomization, {closure#2}(), CakeCustomizationLayer

### Community 1 - "CakeSize"
Cohesion: 0.18
Nodes (4): DeleteCakeSize, CakeSizeController, SaveCakeSizeRequest, CakeSize

### Community 2 - "CakeDesignTest.php"
Cohesion: 0.08
Nodes (20): cartPayload(), {closure#12}(), {closure#13}(), {closure#14}(), {closure#15}(), {closure#3}(), {closure#5}(), {closure#7}() (+12 more)

### Community 3 - "FortifyServiceProvider.php"
Cohesion: 0.16
Nodes (3): {closure#10}(), {closure#8}(), {closure#9}()

### Community 4 - "CakeVariantRealismTest.php"
Cohesion: 0.13
Nodes (11): {closure#1}(), {closure#11}(), {closure#12}(), {closure#13}(), {closure#16}(), {closure#2}(), {closure#20}(), {closure#7}() (+3 more)

### Community 5 - "package.json"
Cohesion: 0.05
Nodes (35): dependencies, alpinejs, concurrently, @laravel/passkeys, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, three (+27 more)

### Community 7 - "UpdateCakeVariantRequest.php"
Cohesion: 0.22
Nodes (5): {closure#1}(), StoreVariantShadingRequest, {closure#1}(), {closure#2}(), {closure#1}()

### Community 8 - "App\Services\CartService"
Cohesion: 0.10
Nodes (5): CartController, CartService, {closure#2}(), {closure#1}(), LayerStrips

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
Cohesion: 0.14
Nodes (4): {closure#1}(), {closure#1}(), {closure#1}(), {closure#2}()

### Community 18 - "⚡security.blade.php"
Cohesion: 0.25
Nodes (7): closeDeleteModal, confirmDelete({{ $passkey[, deletePasskey, disable, $dispatch(, pages, partials.settings-heading

### Community 19 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 20 - "completeVariant"
Cohesion: 0.17
Nodes (12): {closure#1}(), {closure#10}(), {closure#11}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}() (+4 more)

### Community 21 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 22 - "laravel"
Cohesion: 0.40
Nodes (5): extra, laravel, post-create-project, dont-discover, installer

### Community 26 - "CakeVariantAdminTest.php"
Cohesion: 0.16
Nodes (12): {closure#11}(), {closure#12}(), {closure#13}(), {closure#14}(), {closure#15}(), {closure#16}(), {closure#2}(), {closure#4}() (+4 more)

### Community 28 - "viewer.js"
Cohesion: 0.06
Nodes (52): create(), addStand(), AUTO_ROTATE, averageColor(), buildCake(), buildDesignCake(), BUMP, cleanTopTexture() (+44 more)

### Community 36 - "⚡two-factor-setup-modal.blade.php"
Cohesion: 0.50
Nodes (3): confirmTwoFactor, resetVerification, showVerificationIfNecessary

### Community 37 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.29
Nodes (4): CakeDesignController, {closure#4}(), CakeDesign, {closure#4}()

### Community 38 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.15
Nodes (3): {closure#1}(), {closure#1}(), {closure#1}()

### Community 39 - "CutFaceFinder"
Cohesion: 0.24
Nodes (3): {closure#1}(), {closure#2}(), CutFaceFinder

### Community 40 - "Illuminate\Http\UploadedFile"
Cohesion: 0.24
Nodes (5): {closure#1}(), {closure#2}(), {closure#3}(), {closure#6}(), {closure#3}()

### Community 43 - "App\Http\Controllers\Admin\CakeVisualController"
Cohesion: 0.22
Nodes (3): ReorderFlavors, CakeVisualController, {closure#1}()

### Community 103 - "PolishTest.php"
Cohesion: 0.18
Nodes (3): {closure#10}(), {closure#3}(), {closure#5}()

### Community 104 - "PasswordValidationRules"
Cohesion: 0.24
Nodes (3): CreateNewUser, ResetUserPassword, PasswordValidationRules

### Community 105 - "CakePhotoTest.php"
Cohesion: 0.07
Nodes (22): cakePhotoPng(), {closure#1}(), {closure#12}(), {closure#13}(), {closure#16}(), {closure#17}(), {closure#18}(), {closure#19}() (+14 more)

### Community 106 - "DefaultCakePreviewTest.php"
Cohesion: 0.08
Nodes (8): CakePhotoSeeder, {closure#1}(), CakeSizeSeeder, CakeVariantSeeder, {closure#1}(), CreamFlavorSeeder, DatabaseSeeder, SpongeFlavorSeeder

### Community 107 - "Cake3dTest.php"
Cohesion: 0.14
Nodes (8): {closure#1}(), SeamlessTexture, {closure#2}(), {closure#3}(), {closure#4}(), gray(), savedImage(), sideLitPng()

### Community 110 - "CreamFlavor"
Cohesion: 0.25
Nodes (3): FlavorSurfaceController, CreamFlavor, SpongeFlavor

### Community 113 - "Illuminate\Foundation\Http\FormRequest"
Cohesion: 0.11
Nodes (4): SaveCakeDesignRequest, SaveCakeDesignToppingRequest, UpdateFlavorSurfaceRequest, PlaceOrderRequest

### Community 115 - "cake-variants/edit.blade.php"
Cohesion: 0.33
Nodes (5): admin.cake-variants._base, admin.cake-variants._cut-face, admin.cake-variants._realism, admin.cake-variants._alerts, admin.cake-variants._publish

### Community 116 - "PublicUploads"
Cohesion: 0.29
Nodes (3): {closure#1}(), PublicUploads, CakeDesignSeeder

### Community 129 - "CakeStudioVariantPreviewTest.php"
Cohesion: 0.20
Nodes (3): {closure#4}(), {closure#7}(), {closure#8}()

### Community 133 - "Illuminate\View\View"
Cohesion: 0.13
Nodes (3): CakeStudioController, {closure#3}(), OrderTrackingController

### Community 139 - "Controller"
Cohesion: 0.26
Nodes (3): ApplyCakePhoto, CakePhotoController, Controller

### Community 141 - "CakeVariantSlot"
Cohesion: 0.38
Nodes (4): CakeVariantSlot, {closure#3}(), {closure#4}(), {closure#26}()

### Community 151 - "CakeVariant"
Cohesion: 0.08
Nodes (9): {closure#1}(), SaveCakeVariant, CakeVariantController, CakeVariant, CakeDrawings, {closure#1}(), {closure#2}(), {closure#6}() (+1 more)

### Community 154 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.27
Nodes (4): CakeDesignFrame, CakePart, FlavorVariantImage, {closure#5}()

### Community 157 - "2026_10_14_140000_add_photos_and_shading_to_cake_variants.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 158 - "Order"
Cohesion: 0.28
Nodes (3): {closure#1}(), OrderController, Order

### Community 159 - "StorefrontTest.php"
Cohesion: 0.32
Nodes (3): addStorefrontCake(), {closure#3}(), {closure#4}()

### Community 160 - "bootstrap/app.php"
Cohesion: 0.32
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

## Knowledge Gaps
- **49 isolated node(s):** `concurrently`, `tailwindcss`, `vite`, `@laravel/multiplex`, `@rollup/rollup-linux-x64-gnu` (+44 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 561 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **131 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `CakeDesignTest.php`, `CakeVariantRealismTest.php`, `UserFactory.php`, `FlavorAdminTest.php`, `ProfileValidationRules`, `CakeVariantAdminTest.php`, `ProfileUpdateTest.php`, `TwoFactorChallengeTest.php`, `SizeAdminTest.php`, `PolishTest.php`, `PasswordValidationRules`, `CakePhotoTest.php`, `DefaultCakePreviewTest.php`, `Cake3dTest.php`, `EmailVerificationTest.php`, `SecurityTest.php`, `Laravel\Fortify\Features`, `PasswordResetTest.php`, `DashboardTest.php`?**
  _High betweenness centrality (0.124) - this node is a cross-community bridge._
- **What connects `concurrently`, `tailwindcss`, `vite` to the rest of the system?**
  _49 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `CakeDesignTest.php` be split into smaller, more focused modules?**
  _Cohesion score 0.08108108108108109 - nodes in this community are weakly interconnected._
- **Why does `CakeVariant` connect `CakeVariant` to `Pest.php`, `CakeStudioVariantPreviewTest.php`, `CakeVariantAdminTest.php`, `UpdateCakeVariantRequest.php`, `Illuminate\Http\UploadedFile`, `CakePhotoTest.php`, `DefaultCakePreviewTest.php`, `App\Http\Controllers\Admin\CakeVisualController`, `Controller`, `PolishTest.php`, `CreamFlavor`, `.delete`, `completeVariant`, `StoreVariantPhotoRequest`, `Illuminate\Database\Eloquent\Model`, `Illuminate\Database\Eloquent\Relations\HasMany`?**
  _High betweenness centrality (0.071) - this node is a cross-community bridge._
- **Should `CakeVariantRealismTest.php` be split into smaller, more focused modules?**
  _Cohesion score 0.13043478260869565 - nodes in this community are weakly interconnected._
- **Why does `CreamFlavor` connect `CreamFlavor` to `SizeAdminTest.php`, `CakeStudioVariantPreviewTest.php`, `CakeDesignTest.php`, `CakeVariantRealismTest.php`, `Illuminate\View\View`, `PolishTest.php`, `CakePhotoTest.php`, `DefaultCakePreviewTest.php`, `App\Http\Controllers\Admin\CakeVisualController`, `Cake3dTest.php`, `FlavorAdminTest.php`, `CakeVariant`, `Illuminate\Database\Eloquent\Model`, `Illuminate\Database\Eloquent\Relations\HasMany`, `StorefrontTest.php`?**
  _High betweenness centrality (0.052) - this node is a cross-community bridge._
- **Should `package.json` be split into smaller, more focused modules?**
  _Cohesion score 0.052564102564102565 - nodes in this community are weakly interconnected._