# Inventory CRM

A lightweight Customer Relationship Management (CRM) and inventory system built with Laravel. This project manages customers, products, orders, invoices, stock movements, warehouses, and payments for small-to-medium businesses.

## Key Features

- Customer management (profiles, addresses, groups)
- Product and variant management (brands, categories, units)
- Order and invoice processing with payment allocations
- Stock tracking and movements across warehouses
- Dispatch and fulfillment workflow
- Basic reporting and history for orders and stock

## Requirements

- PHP 8.1+ with required extensions
- Composer
- Node.js and npm (for frontend assets)
- MySQL or PostgreSQL

## Quick Setup

1. Clone the repo:

	git clone <repo-url> && cd inventory

2. Install PHP dependencies:

	composer install

3. Copy environment file and set credentials:

	cp .env.example .env
	# update database and mail settings in .env

4. Generate app key and run migrations:

	php artisan key:generate
	php artisan migrate --seed

5. Install frontend dependencies and build assets:

	npm install
	npm run dev

6. Run the development server:

	php artisan serve

## Running Tests

Run the test suite with:

```
./vendor/bin/phpunit
```

## Contributing

Contributions are welcome. Open an issue to discuss major changes or submit a pull request with clear description and tests where applicable.

## License

This project is open-sourced software licensed under the MIT license.
