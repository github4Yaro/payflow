## SQL

```sql
SELECT setseed(0.42);

INSERT INTO invoices (
    id, order_id, status, number_series, number_sequence,
    total_minor_units, total_currency, issued_at
)
SELECT
    gen_random_uuid(),
    gen_random_uuid(),
    CASE
        WHEN g % 1000 <  20 THEN 'issued'     -- 2%  new, wait for a payment
        WHEN g % 1000 <  35 THEN 'overdue'    -- 1.5%
        WHEN g % 1000 <  50 THEN 'refunded'   -- 1.5%
        WHEN g % 1000 <  60 THEN 'void'       -- 1%
        ELSE 'paid'                            -- 94%
    END,
    2026,
    g,
    (random() * 500000)::int + 100,
    'usd',
    now() - ((1000000 - g) * interval '30 seconds')
FROM generate_series(1, 1000000) AS g;
```

Add more `issued` invoices for the last 10 days

```sql
UPDATE invoices SET status = 'issued'
 WHERE issued_at > now() - interval '10 days'
   AND status = 'paid'
   AND number_sequence % 3 = 0; 
```


