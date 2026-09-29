# Architecture

## Request flow

Controller -> FormRequest -> DTO -> Action -> Model/DB -> Queue Job -> Aggregate.

Controllers translate HTTP concerns only. Actions represent use cases. Services hold reusable business/domain logic. Jobs perform asynchronous work. DTOs make application boundaries explicit.

## Billing flow

GenerateDueInvoices command -> chunk subscriptions -> GenerateInvoiceJob -> GenerateInvoiceAction -> BillingCalculator -> invoices/invoice_lines.

## Source of truth

`usage_events` is the raw auditable source of truth. `daily_usages` is a derived read model for dashboard/reporting. Failed aggregation can be replayed from raw events.

## Historical pricing

`subscription_periods` snapshots base price, included units and overage rate. A plan edit therefore cannot change historical billing.
