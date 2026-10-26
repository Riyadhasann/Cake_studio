# Graph Report - Cake_studio  (2026-10-26)

## Corpus Check
- 225 files · ~76,325 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 22 file(s) not represented in the graph (top: (none) 16, .css 2, .example 1)

## Summary
- 1280 nodes · 2195 edges · 169 communities (41 shown, 128 thin omitted)
- Extraction: 94% EXTRACTED · 6% INFERRED · 0% AMBIGUOUS · INFERRED: 121 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `d8972b24`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Database\Eloquent\Relations\BelongsTo
- Illuminate\View\View
- CakeDesignTest.php
- FortifyServiceProvider.php
- CakeVariantRealismTest.php
- package.json
- UserFactory.php
- UpdateCakeVariantRequest.php
- CartController.php
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
- CakeDesign
- Illuminate\Support\Facades\Schema
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
- SizeAdminTest.php
- cake-visuals/index.blade.php
- PolishTest.php
- PasswordValidationRules
- CakePhotoTest.php
- DefaultCakePreviewTest.php
- SeamlessTexture
- SvgLayerImageTest.php
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
- CakeStudioController.php
- SaveCakeDesignRequest
- StoreCakeCustomizationRequest
- Illuminate\Http\RedirectResponse
- UpdateCakeDesignFitRequest
- CakeVariantSlot
- 2026_10_15_100000_drop_cake_previews_table.php
- StoreVariantBaseRequest
- CakeVariantImageController.php
- CakeVariant
- Logout.php
- CreamFlavor
- 2026_10_14_140000_add_photos_and_shading_to_cake_variants.php
- 2026_10_14_110000_add_texture_and_sort_order_to_flavor_tables.php
- 2026_10_25_110000_create_cake_designs_table.php
- 2026_10_25_110100_add_cake_design_to_cake_customizations_table.php
- 2026_10_25_120000_add_fit_to_cake_designs_table.php
- 2026_10_25_130000_add_toppings_to_cake_designs_table.php
- 2026_10_01_072506_create_cake_previews_table.php
- designs/index.blade.php
- cake-variants/_alerts.blade.php

## God Nodes (most connected - your core abstractions)
1. `CakeVariant` - 44 edges
2. `PublicUploads` - 38 edges
3. `User` - 35 edges
4. `completeVariant()` - 34 edges
5. `CakeDesign` - 32 edges
6. `CreamFlavor` - 29 edges
7. `SpongeFlavor` - 29 edges
8. `SvgSanitizer` - 23 edges
9. `Controller` - 21 edges
10. `CakeSize` - 21 edges

## Surprising Connections (you probably didn't know these)
- `{closure#3}()` --references--> `CakeDesign`  [EXTRACTED]
  tests/Feature/CakeDesignTest.php → app/Models/CakeDesign.php
- `{closure#5}()` --references_constant--> `CakeDesign`  [EXTRACTED]
  tests/Feature/CakeDesignTest.php → app/Models/CakeDesign.php
- `designWithPhotos()` --references--> `CakeDesign`  [EXTRACTED]
  tests/Feature/CakeDesignTest.php → app/Models/CakeDesign.php
- `useSizeInOrder()` --references--> `CakeSize`  [EXTRACTED]
  tests/Feature/SizeAdminTest.php → app/Models/CakeSize.php
- `completeVariant()` --references--> `CakeVariant`  [EXTRACTED]
  tests/Pest.php → app/Models/CakeVariant.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **CI Pipeline Setup and Check Flow** — _github_workflows_tests_actions_checkout, _github_workflows_tests_setup_php, _github_workflows_tests_setup_node, _github_workflows_tests_composer_setup, _github_workflows_tests_composer_ci_check [EXTRACTED 1.00]

## Communities (169 total, 128 thin omitted)

### Community 0 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.15
Nodes (4): CakeCustomization, {closure#2}(), CakeCustomizationLayer, CakeDesignFrame

### Community 1 - "Illuminate\View\View"
Cohesion: 0.05
Nodes (18): DeleteCakeSize, CakeSizeController, {closure#1}(), OrderController, CakeStudioController, Controller, {closure#1}(), {closure#2}() (+10 more)

### Community 2 - "CakeDesignTest.php"
Cohesion: 0.19
Nodes (11): cartPayload(), {closure#12}(), {closure#13}(), {closure#14}(), {closure#15}(), {closure#3}(), {closure#5}(), {closure#7}() (+3 more)

### Community 3 - "FortifyServiceProvider.php"
Cohesion: 0.06
Nodes (10): EnsureUserIsAdmin, AppServiceProvider, {closure#1}(), {closure#10}(), {closure#8}(), {closure#9}(), FortifyServiceProvider, {closure#1}() (+2 more)

### Community 4 - "CakeVariantRealismTest.php"
Cohesion: 0.07
Nodes (17): {closure#2}(), {closure#3}(), {closure#4}(), gray(), savedImage(), sideLitPng(), {closure#1}(), {closure#11}() (+9 more)

### Community 5 - "package.json"
Cohesion: 0.05
Nodes (35): dependencies, alpinejs, concurrently, @laravel/passkeys, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, three (+27 more)

### Community 7 - "UpdateCakeVariantRequest.php"
Cohesion: 0.31
Nodes (4): {closure#1}(), {closure#1}(), {closure#2}(), {closure#1}()

### Community 8 - "CartController.php"
Cohesion: 0.09
Nodes (6): CartController, CartService, {closure#2}(), {closure#1}(), LayerStrips, {closure#29}()

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
Cohesion: 0.18
Nodes (12): {closure#1}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}() (+4 more)

### Community 21 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 22 - "laravel"
Cohesion: 0.40
Nodes (5): extra, laravel, post-create-project, dont-discover, installer

### Community 26 - "CakeVariantAdminTest.php"
Cohesion: 0.13
Nodes (14): {closure#11}(), {closure#12}(), {closure#13}(), {closure#14}(), {closure#15}(), {closure#16}(), {closure#2}(), {closure#4}() (+6 more)

### Community 28 - "viewer.js"
Cohesion: 0.06
Nodes (50): create(), AUTO_ROTATE, averageColor(), buildCake(), buildDesignCake(), BUMP, cleanTopTexture(), CREAM_BULGE (+42 more)

### Community 36 - "⚡two-factor-setup-modal.blade.php"
Cohesion: 0.50
Nodes (3): confirmTwoFactor, resetVerification, showVerificationIfNecessary

### Community 37 - "CakeDesign"
Cohesion: 0.22
Nodes (5): CakeDesignController, {closure#4}(), SaveCakeDesignToppingRequest, CakeDesign, {closure#4}()

### Community 38 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.15
Nodes (3): {closure#1}(), {closure#1}(), {closure#1}()

### Community 39 - "CutFaceFinder"
Cohesion: 0.24
Nodes (3): {closure#1}(), {closure#2}(), CutFaceFinder

### Community 40 - "Illuminate\Http\UploadedFile"
Cohesion: 0.19
Nodes (6): {closure#1}(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#6}(), {closure#3}()

### Community 43 - "CakeVisualController.php"
Cohesion: 0.16
Nodes (4): ReorderFlavors, CakeVisualController, {closure#1}(), UpdateLayerImageRequest

### Community 103 - "PolishTest.php"
Cohesion: 0.18
Nodes (3): {closure#10}(), {closure#3}(), {closure#5}()

### Community 104 - "PasswordValidationRules"
Cohesion: 0.19
Nodes (4): CreateNewUser, ResetUserPassword, PasswordValidationRules, ProfileValidationRules

### Community 105 - "CakePhotoTest.php"
Cohesion: 0.08
Nodes (17): cakePhotoPng(), {closure#16}(), {closure#17}(), {closure#18}(), {closure#19}(), {closure#2}(), {closure#23}(), {closure#27}() (+9 more)

### Community 106 - "DefaultCakePreviewTest.php"
Cohesion: 0.09
Nodes (6): CakeSizeSeeder, CakeVariantSeeder, {closure#1}(), CreamFlavorSeeder, DatabaseSeeder, SpongeFlavorSeeder

### Community 107 - "SeamlessTexture"
Cohesion: 0.16
Nodes (4): FlavorSurfaceController, UpdateFlavorSurfaceRequest, {closure#1}(), SeamlessTexture

### Community 110 - "SvgLayerImageTest.php"
Cohesion: 0.27
Nodes (9): {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}(), savedSvg() (+1 more)

### Community 115 - "cake-variants/edit.blade.php"
Cohesion: 0.33
Nodes (5): admin.cake-variants._base, admin.cake-variants._cut-face, admin.cake-variants._realism, admin.cake-variants._alerts, admin.cake-variants._publish

### Community 116 - "PublicUploads"
Cohesion: 0.19
Nodes (5): {closure#1}(), PublicUploads, CakeDesignSeeder, CakePhotoSeeder, {closure#1}()

### Community 129 - "CakeStudioVariantPreviewTest.php"
Cohesion: 0.20
Nodes (3): {closure#4}(), {closure#7}(), {closure#8}()

### Community 139 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.19
Nodes (3): ApplyCakePhoto, CakePhotoController, CakeVariantImageController

### Community 141 - "CakeVariantSlot"
Cohesion: 0.24
Nodes (6): CakeVariantSlot, {closure#3}(), {closure#4}(), {closure#12}(), {closure#13}(), {closure#26}()

### Community 151 - "CakeVariant"
Cohesion: 0.08
Nodes (12): {closure#1}(), SaveCakeVariant, CakeVariantController, UpdateCakeVariantRequest, CakeVariant, FlavorVariantImage, CakeDrawings, {closure#1}() (+4 more)

### Community 157 - "2026_10_14_140000_add_photos_and_shading_to_cake_variants.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

## Knowledge Gaps
- **49 isolated node(s):** `concurrently`, `tailwindcss`, `vite`, `@laravel/multiplex`, `@rollup/rollup-linux-x64-gnu` (+44 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 564 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **128 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `SizeAdminTest.php`, `CakeDesignTest.php`, `CakeVariantRealismTest.php`, `UserFactory.php`, `PolishTest.php`, `PasswordValidationRules`, `CakePhotoTest.php`, `DefaultCakePreviewTest.php`, `FlavorAdminTest.php`, `EmailVerificationTest.php`, `SvgLayerImageTest.php`, `SecurityTest.php`, `Laravel\Fortify\Features`, `StoreVariantPhotoRequest.php`, `PasswordResetTest.php`, `CakeVariantAdminTest.php`, `DashboardTest.php`?**
  _High betweenness centrality (0.103) - this node is a cross-community bridge._
- **What connects `concurrently`, `tailwindcss`, `vite` to the rest of the system?**
  _49 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\View\View` be split into smaller, more focused modules?**
  _Cohesion score 0.05109126984126984 - nodes in this community are weakly interconnected._
- **Why does `CreamFlavor` connect `CreamFlavor` to `SizeAdminTest.php`, `Illuminate\View\View`, `CakeDesignTest.php`, `CakeStudioVariantPreviewTest.php`, `CakeVariantRealismTest.php`, `CakeStudioController.php`, `PolishTest.php`, `Illuminate\Http\UploadedFile`, `CartController.php`, `DefaultCakePreviewTest.php`, `SeamlessTexture`, `CakeVisualController.php`, `Illuminate\Http\RedirectResponse`, `CakePhotoTest.php`, `FlavorAdminTest.php`, `SvgLayerImageTest.php`, `CakeVariant`, `ReorderFlavorsRequest`?**
  _High betweenness centrality (0.061) - this node is a cross-community bridge._
- **Should `FortifyServiceProvider.php` be split into smaller, more focused modules?**
  _Cohesion score 0.05990338164251208 - nodes in this community are weakly interconnected._
- **Why does `SpongeFlavor` connect `CreamFlavor` to `SizeAdminTest.php`, `Illuminate\View\View`, `CakeDesignTest.php`, `CakeStudioVariantPreviewTest.php`, `CakeVariantRealismTest.php`, `CakeStudioController.php`, `PolishTest.php`, `Illuminate\Http\UploadedFile`, `CartController.php`, `DefaultCakePreviewTest.php`, `SeamlessTexture`, `CakeVisualController.php`, `Illuminate\Http\RedirectResponse`, `CakePhotoTest.php`, `FlavorAdminTest.php`, `SvgLayerImageTest.php`, `CakeVariant`, `ReorderFlavorsRequest`?**
  _High betweenness centrality (0.060) - this node is a cross-community bridge._
- **Should `CakeVariantRealismTest.php` be split into smaller, more focused modules?**
  _Cohesion score 0.06951219512195123 - nodes in this community are weakly interconnected._