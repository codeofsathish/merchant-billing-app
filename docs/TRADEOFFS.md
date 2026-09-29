# Trade-offs

1. Database queue/cache are used in the exercise so local setup is simple. Production: Redis/SQS and Horizon.
2. Raw usage is retained as the source of truth; daily usage is eventually consistent but cheap to query.
3. Invoice attribution uses exact event timestamps because mid-cycle plan changes require historical pricing correctness.
4. Pricing snapshots duplicate a few values but prevent mutable plan definitions from rewriting history.
5. Eloquent is used directly rather than adding repositories everywhere; repositories can be introduced if multiple persistence implementations emerge.
6. Monthly partitioning is documented rather than forced into the base schema because partitioning is an operational decision as well as a query optimization.
