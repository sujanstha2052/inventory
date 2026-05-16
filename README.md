# Inventory CRM System

A comprehensive Customer Relationship Management (CRM) and inventory management system built with **Laravel 11** and **Filament 3**. Designed for small-to-medium businesses to manage customers, products, orders, invoices, stock, warehouses, and payments with a modern, user-friendly admin panel.

## Key Features

### Customer Management
- Customer profiles with contact information
- Customer addresses and groups
- Order history and purchase tracking

### Inventory & Warehouse Management
- Product catalog with variants (colors, sizes, etc.)
- Multi-warehouse stock tracking with batch numbers
- Real-time stock movements and audit trails
- Expiry date tracking for perishable items
- Unit management (kg, liter, piece, etc.)
- Product categorization and branding

### Order & Fulfillment
- Draft and confirmed order states
- Order status tracking (draft → confirmed → dispatched → delivered)
- Order items with line-level tracking
- Soft deletes for data integrity
- Order history and status timeline

### Payments & Invoicing
- Invoice generation and management
- Payment processing and tracking
- Payment allocations to invoices
- PDF invoice generation via DomPDF

### Admin Panel
- **Filament 3** admin dashboard with comprehensive CRUD interfaces
- Intuitive forms for managing customers, products, orders, and stock
- Real-time relationship management
- Advanced filtering and search capabilities
- Role-based access control via Spatie Laravel Permission

### Developer-Friendly
- Clean Laravel architecture with well-organized models
- Database migrations with proper relationships
- Database seeders for testing
- Queue support for background jobs
- Tailwind CSS + Vite for modern frontend

## Technology Stack

- **Framework**: Laravel 11.31
- **Admin Panel**: Filament 3.2
- **Database**: MySQL/PostgreSQL
- **Frontend**: Tailwind CSS 3, Vite
- **Authentication**: Spatie Laravel Permission
- **PDF Generation**: DomPDF
- **Testing**: PHPUnit 11
- **Code Quality**: Laravel Pint
- **PHP Version**: 8.2+

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js 16+ and npm
- MySQL 8.0+ or PostgreSQL 12+
- Git

## Installation & Setup

### 1. Clone the repository

```bash
git clone <repository-url> && cd inventory
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Setup environment file

```bash
cp .env.example .env
```

Update the following in `.env`:
- `APP_NAME=Inventory CRM`
- `DB_CONNECTION=mysql` (or postgresql)
- `DB_HOST=127.0.0.1`
- `DB_DATABASE=inventory`
- `DB_USERNAME=root`
- `DB_PASSWORD=`
- `MAIL_*` settings for email notifications

### 4. Generate app key and run migrations

```bash
php artisan key:generate
php artisan migrate --seed
```

### 5. Install and build frontend assets

```bash
npm install
npm run dev
```

### 6. Create a Filament admin user

```bash
php artisan make:filament-user
```

### 7. Start the development server

```bash
php artisan serve
```

Access the application at `http://127.0.0.1:8000` and the admin panel at `http://127.0.0.1:8000/admin`.

## Development Commands

### Run with all services (Laravel, Queue, Logs, Vite)

```bash
composer run dev
```

### Run database migrations

```bash
php artisan migrate
```

### Seed the database with sample data

```bash
php artisan db:seed
```

### Run tests

```bash
./vendor/bin/phpunit
```

### Code formatting and linting

```bash
./vendor/bin/pint
```

## Project Structure

```
app/
├── Models/              # Database models (Order, Product, Customer, etc.)
├── Filament/
│   └── Resources/       # Filament admin resources and forms
├── Http/
│   └── Controllers/     # API and web controllers
└── Providers/           # Service providers

database/
├── migrations/          # Database schema definitions
└── seeders/             # Sample data seeders

resources/
├── css/                 # Tailwind CSS
└── js/                  # Frontend scripts

routes/
├── web.php              # Web routes
└── console.php          # Console commands
```

## Main Models

- **Customer**: Customer information and profiles
- **Order**: Sales orders with status tracking
- **OrderItem**: Line items within orders
- **Invoice**: Customer invoices
- **Payment**: Payment records
- **Product**: Product catalog
- **ProductVariant**: Product variants (colors, sizes, etc.)
- **Stock**: Inventory tracking across warehouses
- **StockMovement**: Audit trail of stock changes
- **Warehouse**: Warehouse/location management
- **Dispatch**: Order fulfillment tracking
- **Category**: Product categorization
- **Brand**: Product brand management
- **Unit**: Measurement units (kg, liter, etc.)

## API Endpoints

The application provides RESTful API endpoints for integration. Check `routes/web.php` for available endpoints.

## Contributing

We welcome contributions! To contribute:

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature`
3. Make your changes with clear commit messages
4. Add tests for new functionality
5. Submit a pull request with a detailed description

## Troubleshooting

### Migrations fail
- Ensure database credentials in `.env` are correct
- Run `php artisan migrate:fresh --seed` to reset and reseed

### Filament admin not accessible
- Run `php artisan filament:upgrade` to ensure Filament is properly installed
- Verify a user exists: `php artisan make:filament-user`

### Assets not loading
- Run `npm run dev` for development or `npm run build` for production
- Clear Vite cache if needed

## License

This project is open-sourced software licensed under the [MIT License](LICENSE).

## Support

For issues and questions, please open an issue on the repository.
