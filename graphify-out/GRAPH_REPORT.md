# Graph Report - Cake_studio  (2026-10-14)

## Corpus Check
- Corpus is ~26,432 words - fits in a single context window. You may not need a graph.

## Summary
- 626 nodes · 852 edges · 104 communities (20 shown, 84 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 16 edges (avg confidence: 0.88)
- Token cost: 111,234 input · 0 output

## Community Hubs (Navigation)
- Cake Catalog & Models
- Admin Cake Images
- Auth Tests & Fortify Config
- Fortify User Actions
- Cart & Checkout
- Frontend Build & Passkeys
- Core Config & Factories
- Admin Gate & Fortify Setup
- App Service Provider
- Catalog Seeders
- Composer Scripts
- Base Framework Tables
- Dev Dependencies
- CI & Dependabot
- Runtime Dependencies
- Composer Package Metadata
- Passkeys & Sizes Tables
- Flavor Tables
- Security Settings Page
- Composer Plugin Config
- Cancelled Order Alerts
- PSR-4 Autoload
- Laravel Package Extras
- Cache Table Migration
- Two-Factor Columns Migration
- Order FK Migration
- Order Total Rename Migration
- Delivery Details Migration
- Quantity Column Migration
- Drop Delivery Time Migration
- Admin Flag Migration
- Order Status Migration
- Flavor Visuals Migration
- Customization Layers Migration
- Default Flavor Migration
- Two-Factor Setup Modal
- Customizations Table Migration
- Cake Previews Migration
- Orders Table Migration
- Cake Parts Migration
- Test Autoload
- Profile Settings Page
- Apple Touch Icon
- Site Favicon
- Recovery Codes Page
- Header
- Sidebar
- Card
- Simple
- Split
- Appearance
- Delete User Form
- Robots Policy

## God Nodes (most connected - your core abstractions)
1. `User` - 26 edges
2. `CartService` - 13 edges
3. `scripts` - 13 edges
4. `require-dev` - 12 edges
5. `CreamFlavor` - 11 edges
6. `SpongeFlavor` - 11 edges
7. `Controller` - 10 edges
8. `Order` - 10 edges
9. `require` - 10 edges
10. `CakeVisualController` - 9 edges

## Surprising Connections (you probably didn't know these)
- `GitHub Actions Weekly Updates (grouped, 5-day cooldown)` --references--> `actions/checkout (pinned SHA v7.0.1)`  [INFERRED]
  .github/dependabot.yml → .github/workflows/tests.yml
- `GitHub Actions Weekly Updates (grouped, 5-day cooldown)` --references--> `actions/setup-node (Node 22)`  [INFERRED]
  .github/dependabot.yml → .github/workflows/tests.yml
- `GitHub Actions Weekly Updates (grouped, 5-day cooldown)` --references--> `shivammathur/setup-php (PHP 8.4, composer v2)`  [INFERRED]
  .github/dependabot.yml → .github/workflows/tests.yml
- `GitHub Actions Weekly Updates (grouped, 5-day cooldown)` --conceptually_related_to--> `CI Supply-Chain Hardening (SHA pinning, read-only perms, no persisted credentials)`  [INFERRED]
  .github/dependabot.yml → .github/workflows/tests.yml
- `CartController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/CartController.php → app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **CI Pipeline Setup and Check Flow** — _github_workflows_tests_actions_checkout, _github_workflows_tests_setup_php, _github_workflows_tests_setup_node, _github_workflows_tests_composer_setup, _github_workflows_tests_composer_ci_check [EXTRACTED 1.00]

## Communities (104 total, 84 thin omitted)

### Community 0 - "Cake Catalog & Models"
Cohesion: 0.07
Nodes (8): {closure#1}(), CakeCustomization, CakeCustomizationLayer, CakePart, CakePreview, CakeSize, CreamFlavor, SpongeFlavor

### Community 1 - "Admin Cake Images"
Cohesion: 0.08
Nodes (9): CakePreviewController, CakeVisualController, {closure#1}(), OrderController, CakeStudioController, Controller, OrderTrackingController, Logout (+1 more)

### Community 3 - "Fortify User Actions"
Cohesion: 0.08
Nodes (5): CreateNewUser, ResetUserPassword, PasswordValidationRules, ProfileValidationRules, User

### Community 4 - "Cart & Checkout"
Cohesion: 0.10
Nodes (6): CartController, PlaceOrderRequest, {closure#1}(), StoreCakeCustomizationRequest, CartService, {closure#2}()

### Community 5 - "Frontend Build & Passkeys"
Cohesion: 0.06
Nodes (30): dependencies, concurrently, @laravel/passkeys, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite, vite-plus (+22 more)

### Community 7 - "Admin Gate & Fortify Setup"
Cohesion: 0.10
Nodes (7): EnsureUserIsAdmin, {closure#10}(), {closure#8}(), {closure#9}(), {closure#1}(), {closure#2}(), {closure#3}()

### Community 8 - "App Service Provider"
Cohesion: 0.16
Nodes (3): AppServiceProvider, {closure#1}(), FortifyServiceProvider

### Community 9 - "Catalog Seeders"
Cohesion: 0.16
Nodes (5): CakePreviewSeeder, CakeSizeSeeder, CreamFlavorSeeder, DatabaseSeeder, SpongeFlavorSeeder

### Community 10 - "Composer Scripts"
Cohesion: 0.15
Nodes (13): scripts, ci:check, dev, lint, lint:check, post-autoload-dump, post-create-project-cmd, post-root-package-install (+5 more)

### Community 11 - "Base Framework Tables"
Cohesion: 0.23
Nodes (6): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#3}()

### Community 12 - "Dev Dependencies"
Cohesion: 0.17
Nodes (12): require-dev, fakerphp/faker, larastan/larastan, laravel/boost, laravel/pail, laravel/pao, laravel/pint, laravel/sail (+4 more)

### Community 13 - "CI & Dependabot"
Cohesion: 0.27
Nodes (8): Dependabot Config, actions/checkout (pinned SHA v7.0.1), ci Job, composer ci:check script, composer setup script, actions/setup-node (Node 22), shivammathur/setup-php (PHP 8.4, composer v2), tests Workflow

### Community 14 - "Runtime Dependencies"
Cohesion: 0.20
Nodes (10): require, doctrine/dbal, laravel/chisel, laravel/fortify, laravel/framework, laravel/tinker, livewire/blaze, livewire/flux (+2 more)

### Community 15 - "Composer Package Metadata"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 18 - "Security Settings Page"
Cohesion: 0.25
Nodes (7): closeDeleteModal, confirmDelete({{ $passkey[, deletePasskey, disable, $dispatch(, pages, partials.settings-heading

### Community 19 - "Composer Plugin Config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 21 - "PSR-4 Autoload"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 22 - "Laravel Package Extras"
Cohesion: 0.40
Nodes (5): extra, laravel, post-create-project, dont-discover, installer

### Community 36 - "Two-Factor Setup Modal"
Cohesion: 0.50
Nodes (3): confirmTwoFactor, resetVerification, showVerificationIfNecessary

### Community 43 - "Test Autoload"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

## Knowledge Gaps
- **35 isolated node(s):** `concurrently`, `tailwindcss`, `vite`, `@laravel/multiplex`, `@rollup/rollup-linux-x64-gnu` (+30 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 327 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **84 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `Fortify User Actions` to `Cake Catalog & Models`, `Auth Tests & Fortify Config`, `Core Config & Factories`?**
  _High betweenness centrality (0.150) - this node is a cross-community bridge._
- **What connects `concurrently`, `tailwindcss`, `vite` to the rest of the system?**
  _35 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Cake Catalog & Models` be split into smaller, more focused modules?**
  _Cohesion score 0.07088989441930618 - nodes in this community are weakly interconnected._
- **Why does `CreamFlavor` connect `Cake Catalog & Models` to `Catalog Seeders`, `Cart & Checkout`?**
  _High betweenness centrality (0.065) - this node is a cross-community bridge._
- **Should `Admin Cake Images` be split into smaller, more focused modules?**
  _Cohesion score 0.07922705314009662 - nodes in this community are weakly interconnected._
- **Why does `SpongeFlavor` connect `Cake Catalog & Models` to `Catalog Seeders`, `Cart & Checkout`?**
  _High betweenness centrality (0.065) - this node is a cross-community bridge._
- **Should `Auth Tests & Fortify Config` be split into smaller, more focused modules?**
  _Cohesion score 0.046511627906976744 - nodes in this community are weakly interconnected._