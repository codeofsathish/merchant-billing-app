<<<<<<< HEAD
# Subscription Billing & Usage Metering - Laravel

Target: Laravel 10 + PHP 8.1+, MySQL 8/MariaDB.

## 1. Copy the files

Copy the folders/files in this package into your existing Laravel project. If the files already exist, replace them.

Then run:

```bash
composer dump-autoload
php artisan optimize:clear
php artisan migrate
```

If you are creating a new project:

```bash
composer create-project laravel/laravel subscription-billing "10.*"
cd subscription-billing
```

Configure `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=subscription_billing
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=file
QUEUE_CONNECTION=database
```

Create the queue table if your Laravel project does not already have it:

```bash
php artisan queue:table
php artisan migrate
```

Run a worker:

```bash
php artisan queue:work --queue=usage,billing
```

Run the app on WAMP using Apache's public folder, or for development:

```bash
php artisan serve
```

## 2. API examples

Create subscription:

POST `/api/subscriptions`

```json
{
  "merchant_id": 1,
  "customer_id": 1,
  "plan_id": 1,
  "starts_at": "2026-09-01 00:00:00"
}
```

Record usage:

POST `/api/usage`

```json
{
  "merchant_id": 1,
  "customer_id": 1,
  "event_key": "provider-event-000001",
  "occurred_at": "2026-09-29 12:10:00",
  "units": 5,
  "type": "api",
  "metadata": {
    "endpoint": "/v1/orders"
  }
}
```

Retrying the exact same `event_key` for the same merchant/customer returns `duplicate=true` and does not insert another usage row.

Change plan:

POST `/api/subscriptions/1/change-plan`

```json
{
  "plan_id": 2,
  "effective_at": "2026-09-16 00:00:00"
}
```

Dashboard:

GET `/api/merchants/1/dashboard`

## 3. Billing

Queue due invoices:

```bash
php artisan billing:generate-due-invoices
```

For production, schedule it every few minutes in Laravel's scheduler.

The billing calculator creates one invoice line per subscription-plan segment. Each segment gets:
- base price prorated by days in the cycle
- included units prorated by days in the cycle
- actual usage attributed by event timestamp
- overage charged using that segment's frozen overage rate

This is what makes a mid-cycle upgrade/downgrade safe: old usage is never repriced using the new plan.

## 4. High-volume design

`usage_events` is append-heavy and has:
- unique `(merchant_id, customer_id, event_key)` for idempotency
- `(merchant_id, customer_id, occurred_at)` for customer/cycle queries
- `(merchant_id, usage_date, id)` for date-oriented processing

`daily_usages` is a compact aggregate with one row per merchant/customer/day.

At 50L+ rows, do not scan the raw table for dashboards. Use `daily_usages` for dashboards and operational reporting. Keep invoice calculation on raw events only for the current invoice's segment boundaries.

For a much larger deployment:
1. Partition `usage_events` by `usage_date` (monthly partitions are a practical starting point).
2. Retain raw events according to the business retention policy and keep daily/monthly aggregates longer.
3. Run aggregation in batches/chunks and use bulk upserts.
4. Move queue workers to Redis/SQS.
5. Use read replicas for dashboards if required.
6. Consider a separate analytics store for very large reporting workloads.

## 5. Cache

Plan lookups use `merchant:{merchantId}:plan:{planId}` with a short TTL. When a plan changes, invalidate that key (for example in a Plan model observer after `created/updated/deleted`, or immediately after an admin update). In this exercise the subscription period stores a pricing snapshot (`base_price`, `included_units`, `overage_rate_per_unit`) so invoices remain historically correct even if the plan itself is edited later.

## 6. Rate limiting

The usage endpoint uses Laravel's named `usage` limiter:
120 requests/minute per merchant + IP. For production, a merchant API key or authenticated tenant identity should be the limiter key instead of IP.

## 7. Tests

Run:

```bash
php artisan test
```

The tests cover:
- mid-cycle proration
- included allowance and overage
- usage-event idempotency

## Important production refinement

The provided implementation is deliberately small and interview-friendly. For truly high write volume, change the usage endpoint to a bulk ingestion endpoint and replace `firstOrCreate` with an atomic insert-ignore/upsert pattern specific to your database. The unique key remains the source of truth for idempotency.
=======
# merchant-billing-app
>>>>>>> 1103fdd3a8b5cbce3d4db5f8bbbebac9da5980a7
