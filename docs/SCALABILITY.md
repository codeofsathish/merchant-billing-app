# Scalability

## Ingestion

Persist raw usage first, then enqueue aggregation. A queue outage therefore does not lose usage.

## Aggregation

Aggregation recomputes a merchant/customer/day total and performs an upsert. Replaying the job does not add units twice.

## High volume

For 50L+ events:
- batch usage ingestion;
- use queue workers with bounded concurrency;
- keep composite indexes aligned with billing queries;
- use daily/monthly aggregates for reads;
- consider monthly raw-event partitions;
- monitor queue lag and aggregation lag;
- use read replicas for dashboards;
- move long-range analytics to an analytics store when required.
