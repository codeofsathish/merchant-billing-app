# Database and Indexing

The schema is normalized around merchants, plans, customers, subscriptions and pricing periods. Usage is separated into raw events and derived daily aggregates.

## Critical indexes

`usage_events`:
- UNIQUE `(merchant_id, customer_id, event_key)` for database-level idempotency.
- `(merchant_id, customer_id, occurred_at)` for exact billing-period scans.
- `(merchant_id, usage_date, id)` for date/chunk processing.
- `(customer_id, usage_date)` for customer history.

`daily_usages`:
- UNIQUE `(merchant_id, customer_id, usage_date)` so aggregation is deterministic.
- `(merchant_id, usage_date, units)` for dashboard/monthly queries.

`invoices`:
- UNIQUE `(subscription_id, period_start)` prevents duplicate invoices.

## 50L+ rows

At 50 million+ raw events, dashboard requests should use `daily_usages`, not raw events. For substantially larger workloads I would add monthly RANGE partitioning on `usage_date`, bulk ingestion, Redis/SQS workers, read replicas and a dedicated analytics store. Partitioning should be validated against retention, backup and query patterns before production rollout.
