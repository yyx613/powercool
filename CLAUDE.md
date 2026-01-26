# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 10 application running on XAMPP, serving as a comprehensive business management system for sales, inventory, production, and e-invoicing operations. The system integrates with multiple e-commerce platforms (Lazada, Shopee, TikTok, WooCommerce) and the Malaysian e-Invoice system.

## Development Commands

### Setup & Installation
```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Copy environment file and generate app key
cp .env.example .env
php artisan key:generate

# Run migrations and seed database
php artisan migrate
php artisan db:seed
```

### Running the Application
```bash
# Start Vite dev server for frontend assets
npm run dev

# Build assets for production
npm run build

# Run artisan commands
php artisan serve
```

### Database Operations
```bash
# Create a new migration
php artisan make:migration create_table_name

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Refresh database (drop all tables and re-migrate)
php artisan migrate:fresh --seed

# Create a new seeder
php artisan make:seeder SeederName
```

### Code Generation
```bash
# Create a new controller
php artisan make:controller ControllerName

# Create a new model with migration
php artisan make:model ModelName -m

# Create a new middleware
php artisan make:middleware MiddlewareName

# Create a new export class
php artisan make:export ExportName

# Create a new command
php artisan make:command CommandName
```

### Testing & Code Quality
```bash
# Run PHPUnit tests
php artisan test

# Run specific test
php artisan test --filter TestName

# Format code with Laravel Pint
./vendor/bin/pint

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Scheduled Commands
The application includes several scheduled commands that should be run via cron:
- `php artisan check:einvoice-status` - Checks e-invoice submission status
- `php artisan expire:quotation` - Expires old quotations
- `php artisan prompt:task` - Sends task reminders
- `php artisan refresh:lazada-token` - Refreshes Lazada API token
- `php artisan refresh:shopee-token` - Refreshes Shopee API token
- `php artisan refresh:tiktok-token` - Refreshes TikTok API token
- `php artisan service:reminder` - Sends service reminders
- `php artisan vehicle-service:reminder` - Sends vehicle service reminders

## Architecture & Key Patterns

### Multi-Tenancy with Branch Scope

The application uses a **BranchScope** (app/Models/Scopes/BranchScope.php) for multi-tenancy, automatically filtering queries based on the user's branch assignment. Most models are scoped by branch using the `#[ScopedBy([BranchScope::class])]` attribute.

**Branch location constants (App\Models\Branch):**
- `LOCATION_EVERY = 0` (All branches - super admin only)
- `LOCATION_KL = 1` (Kuala Lumpur/HQ)
- `LOCATION_PENANG = 2` (Penang)

**Important patterns:**
- Super admins can switch branches via `Session::get('as_branch')`
- Use `withoutGlobalScope(BranchScope::class)` when you need to query across all branches
- New models requiring branch isolation must:
  1. Add `#[ScopedBy([BranchScope::class])]` attribute
  2. Define `branch()` morphOne relationship: `return $this->morphOne(Branch::class, 'object');`
  3. Call `Branch::assign(ModelClass::class, $id)` after creating the record
- Use `getCurrentUserBranch()` to get current user's branch location constant
- Use `getCurrentUserWarehouse()` to get warehouse name ('HQ' or 'Penang')

### Role-Based Access Control

Uses Spatie Laravel Permission package for roles and permissions:
- Permission checking via `hasPermission()` helper (app/helpers.php)
- User roles retrieved with `getUserRole()` and `getUserRoleId()` helpers
- Check for super admin with `isSuperAdmin()` helper
- Models: `App\Models\Role`, `App\Models\User`

**Role ID Constants (App\Models\Role):**
- `SUPERADMIN = 1`, `SALE = 2`, `TECHNICIAN = 3`, `DRIVER = 4`
- `PRODUCTION_WORKER = 5`, `PRODUCTION_SUPERVISOR = 6`, `PRODUCTION_ASSISTANT = 7`
- `WAREHOUSE = 8`, `FINANCE = 9`, `SALE_COORDINATOR = 13`, `PURCHASING = 14`
- `STORE_WORKER = 15`, `SERVICE_HOD = 16`, `LOGISTIC = 17`

**Role-checking helpers:**
- `isProductionWorker()`, `isSalesOnly()`, `isSalesCoordinatorOnly()`, `isFinance()`, `isFinanceOnly()`

### Sales Flow & Document Chain

The sales process follows a specific document flow:

1. **Quotation (QUO)** → `Sale::TYPE_QUO = 1`
2. **Sale Order (SO)** → `Sale::TYPE_SO = 2`
3. **Delivery Order (DO)** → `DeliveryOrder` model
4. **Invoice (INV)** → `Invoice` model
5. **Billing** → `Billing` model
6. **E-Invoice** → `EInvoice`, `ConsolidatedEInvoice` models

**Key relationships:**
- Sales can be converted from quotations to sale orders (status tracking via constants)
- Delivery orders link to sales via `sale_id`
- Invoices can be linked to sales or billings
- E-invoices are generated from invoices and submitted to MyInvois system

**Type constants:**
- `Sale::TYPE_QUO = 1` (Quotation), `TYPE_SO = 2` (Sale Order)
- `Sale::TYPE_PENDING = 3` (Pending assign salesperson), `TYPE_CASH_SALE = 4`

**Status constants:**
- `Sale::STATUS_INACTIVE = 0`, `STATUS_ACTIVE = 1`, `STATUS_CONVERTED = 2`, `STATUS_CANCELLED = 3`
- `Sale::STATUS_APPROVAL_PENDING = 4`, `STATUS_APPROVAL_APPROVED = 5`, `STATUS_TRANSFERRED_BACK = 6`
- `Sale::STATUS_APPROVAL_REJECTED = 7`, `STATUS_PARTIALLY_CONVERTED = 8`
- `Sale::PAYMENT_STATUS_UNPAID = 1`, `PAYMENT_STATUS_PARTIALLY_PAID = 2`, `PAYMENT_STATUS_PAID = 3`
- `Sale::TRANSFER_TYPE_NORMAL = 1`, `TRANSFER_TYPE_TRANSFER_TO = 2`, `TRANSFER_TYPE_TRANSFER_FROM = 3`

### Production & Manufacturing

Production workflow:
1. **Production Request** (`ProductionRequest`) - Material requirements
2. **Raw Material Request** (`RawMaterialRequest`) - Requesting materials from factory
3. **Production** (`Production`) - Main production record
4. **Production Milestone** (`ProductionMilestone`) - Tracks production stages
5. **Material Use** (`MaterialUse`) - Records materials consumed

**Key models:**
- `Product` - Master product catalog with variants
- `ProductChild` - Serial numbered instances/stock items
- `ProductionMilestone` - Tracks manufacturing progress
- `Factory`, `FactoryRawMaterial` - Factory inventory management

### E-Invoice Integration

E-invoice submission to Malaysian MyInvois system:
- XML generation via `App\Services\EInvoiceXmlGenerator`
- Configuration in `config/e-invoices.php` (separate credentials for different company groups)
- Models: `EInvoice`, `ConsolidatedEInvoice`, `CreditNote`, `DebitNote`, `DraftEInvoice`
- Classification codes (`ClassificationCode`) and MSIC codes (`MsicCode`) are required

### Platform Integration

Multi-platform e-commerce sync:
- Controllers: `App\Http\Controllers\Platforms\{Lazada,Shopee,Tiktok,WooCommerce}Controller`
- Configuration: `config/platforms.php`
- Models: `Platform`, `PlatformTokens`
- Orders synced to `Sale` model with `platform_id` and `order_id`

### Custom Middleware

- `ApprovalMiddleware` - Controls access to features requiring approval
- `ProductionWorkerCanAccessMiddleware` - Restricts production worker access
- `NotificationMiddleware` - Handles notification logic
- `SelectLang` - Language selection

### Exports

Excel exports use Maatwebsite Excel package (app/Exports/):
- `CustomerExport`, `SupplierExport`, `ProductExport`
- `SalesReportExport`, `ProductionReportExport`, `EarningReportExport`
- `StockReportExport`, `ServiceReportExport`, `TechnicianStockReportExport`

### Helper Functions

Located in `app/helpers.php` (autoloaded via composer.json). Key helpers include:
- `hasPermission(string $permission): bool` - Permission checking
- `getUserRole(User $user): array` - Get user role names
- `getUserRoleId(User $user): array` - Get user role IDs
- `isSuperAdmin(): bool` - Check if current user is super admin
- `isProductionWorker(): bool`, `isSalesOnly(): bool`, `isFinance(): bool` - Role checks
- `getCurrentUserBranch(): ?int` - Get branch location constant
- `getCurrentUserWarehouse(): ?string` - Get warehouse name ('HQ' or 'Penang')
- `isHiTen(int $company_group): bool` - Check if company group is HiTen (company_group == 2)
- `generateSku(string $prefix, array $existing_skus, ?bool $is_hi_ten): string` - Generate SKU with branch prefix
- `getInvolvedProductChild(?int $production_id): array` - Get product children IDs involved in production/DO/sales
- `priceToWord($num, $currency): string` - Convert price to words (MYR/USD)

## Key Models & Relationships

### Core Business Entities
- **Sale** - Quotations, sale orders, cash sales (polymorphic with multiple types)
- **SaleProduct**, **SaleProductChild** - Sale line items and serial tracking
- **Customer** - Customer master with locations, agents, credit terms
- **Product**, **ProductChild** - Products and serialized inventory
- **DeliveryOrder**, **DeliveryOrderProduct** - Delivery documents
- **Invoice**, **Billing** - Invoicing documents
- **Task**, **TaskMilestone** - Service/installation tasks
- **Ticket** - Customer support tickets

### Inventory & Production
- **ProductionRequest**, **Production** - Manufacturing orders
- **RawMaterialRequest** - Material requisitions
- **MaterialUse** - Material consumption tracking
- **Factory**, **FactoryRawMaterial** - Factory inventory
- **GRN** - Goods Received Notes

### Supporting Data
- **Branch** - Multi-location support (locations: Powercool, HiTen)
- **Area**, **DebtorType** - Customer categorization
- **PaymentMethod**, **CreditTerm** - Payment configurations
- **Currency** - Multi-currency support
- **UOM** - Unit of measure
- **Milestone** - Production/service stages
- **Priority**, **ProjectType** - Classification
- **Warranty**, **WarrantyPeriod** - Warranty tracking

### Vehicles & Service
- **Vehicle**, **VehicleService** - Fleet management
- **InventoryServiceReminder** - Service scheduling
- **Service**, **TaskService** - Service operations

## Important Conventions

### SKU Generation
Most entities have auto-generated SKU codes via `generateSku()` helper. Format: `PREFIX-YY/XXXXXX`
- Quotations: `QUO-25/000001`
- Sale Orders: `SO-25/000001` (KL), `PSO-25/000001` (Penang Powercool), `PHSO-25/000001` (Penang HiTen)
- Invoices: `I-25/000001`, `PI-25/000001`, `PHI-25/000001`
- Delivery Orders: `DO-25/000001`, `PDO-25/000001`, `PHDO-25/000001`
- Production: `PRD-25/000001`, `PPRD-25/000001`, `PHPRD-25/000001`

**Branch prefixes:**
- No prefix = KL (HQ)
- `P` = Penang (Powercool)
- `PH` = Penang (HiTen)
- `W` = Legacy/Wholesale prefix (deprecated)

### File Storage
Public files stored in `storage/app/public/`:
- Delivery orders: `delivery_order/`
- Transport acknowledgements: `transport_acknowledgement/`
- Invoices: `invoice/`
- Attachments: `attachments/`
- E-invoices: `e-invoice/`

### Date Handling
- Laravel Carbon is used throughout
- Models use `serializeDate()` to return Carbon instances
- Date filters commonly use `whereBetween()` with Carbon date ranges

### Soft Deletes
Many models use soft deletes. Always check for `use SoftDeletes` trait before assuming hard deletes.

### API Structure
- Web routes: `routes/web.php` (main application)
- API routes: `routes/api.php` (mobile app endpoints under `/api/v1`)
- Sync routes: `/api/sync` (AutoCount ERP synchronization)
- Auth: Sanctum for API authentication

## Database Structure

The database has 200+ migrations showing evolutionary growth. Key tables:
- Core: `users`, `roles`, `permissions`, `branches`
- Sales: `sales`, `sale_products`, `sale_product_children`, `customers`
- Inventory: `products`, `product_children`, `inventory_categories`
- Production: `productions`, `production_milestones`, `factories`, `raw_material_requests`
- Documents: `delivery_orders`, `invoices`, `billings`, `e_invoices`
- Service: `tasks`, `task_milestones`, `tickets`, `vehicles`, `vehicle_services`
- Integration: `platforms`, `platform_tokens`

## Environment Variables

Key environment variables (see .env.example):
- Database: `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- E-invoice: `EINVOICE_POWERCOOL_CLIENT_ID`, `EINVOICE_POWERCOOL_CLIENT_SECRET`, `EINVOICE_HITEN_CLIENT_ID`, `EINVOICE_HITEN_CLIENT_SECRET`
- Lazada: `LAZADA_APP_KEY`, `LAZADA_SECRET_KEY`
- Shopee: `SHOPEE_PARTNER_ID`, `SHOPEE_PARTNER_KEY`, `SHOPEE_SHOP_ID`
- TikTok: `TIKTOK_APP_KEY`, `TIKTOK_APP_SECRET`

## Frontend Stack

- **Vite** - Asset bundling
- **Tailwind CSS** - Styling framework
- **Alpine.js** - JavaScript interactivity
- **Blade templates** - Server-side rendering (270+ blade files)

Blade views located in `resources/views/`.

## Testing Strategy

When testing:
1. Ensure branch scope is properly applied or bypassed as needed
2. Seed required data: roles, permissions, milestones, warranty periods, inventory categories
3. Test with different user roles (super admin, regular users, production workers)
4. Verify SKU generation doesn't create duplicates
5. Test approval workflows for sales requiring approval
6. Validate e-invoice XML generation before submission

## Notes for AI Development

- This is a complex business system with interconnected modules
- Always check for branch scope when querying models
- SKU generation logic should maintain existing patterns
- Status constants are extensively used - reference model class constants
- Many controllers are large (500+ lines) - consider the full context when modifying
- The sales flow is critical - changes to Sale, DeliveryOrder, Invoice, or EInvoice models require careful consideration of downstream effects
- Production tracking is complex with multiple milestone stages
- API endpoints under `/api/v1` are for mobile app consumption
