# payflow

The payment processing service: order → invoice → pay via PSP →
webhook → async processing → fulfillment.

Stack: PHP 8.4, Symfony 7.4 LTS, PostgreSQL, RabbitMQ, Docker.

## run

    make up
    make install

## tests

    make test

**Status:** in progress.


## Query optimization case

Dashboard query: unpaid invoices from the last 30 days, newest first.
Dataset: 1M invoices, ~3.5% unpaid (see `docs/dataset.md`).

### Before

```
Limit  (cost=21855.31..21861.14 rows=50 width=52) (actual time=31.150..32.547 rows=50 loops=1)
   Buffers: shared hit=5178 read=7371
   ->  Gather Merge  (cost=21855.31..22227.27 rows=3188 width=52) (actual time=31.148..32.540 rows=50 loops=1)
         Workers Planned: 2
         Workers Launched: 2
         Buffers: shared hit=5178 read=7371
         ->  Sort  (cost=20855.28..20859.27 rows=1594 width=52) (actual time=28.621..28.622 rows=34 loops=3)
               Sort Key: issued_at DESC
               Sort Method: top-N heapsort  Memory: 38kB
               Buffers: shared hit=5178 read=7371
               Worker 0:  Sort Method: top-N heapsort  Memory: 38kB
               Worker 1:  Sort Method: top-N heapsort  Memory: 38kB
               ->  Parallel Seq Scan on invoices  (cost=0.00..20802.33 rows=1594 width=52) (actual time=25.837..28.093 rows=4017 loops=3)
                     Filter: (((status)::text = ANY ('{issued,overdue}'::text[])) AND (issued_at >= (now() - '30 days'::interval)))
                     Rows Removed by Filter: 329316
                     Buffers: shared hit=5104 read=7371
 Planning Time: 0.220 ms
 Execution Time: 32.578 ms
```

### Change

```sql
CREATE INDEX idx_invoices_unpaid ON invoices (issued_at DESC)
 WHERE status IN ('issued', 'overdue');
```

### After

```
Limit  (cost=0.29..2.42 rows=50 width=52) (actual time=0.064..0.096 rows=50 loops=1)
   Buffers: shared hit=2 read=2
   ->  Index Scan using idx_invoices_unpaid on invoices  (cost=0.29..162.41 rows=3822 width=52) (actual time=0.062..0.084 rows=50 loops=1)
         Index Cond: (issued_at >= (now() - '30 days'::interval))
         Buffers: shared hit=2 read=2
 Planning:
   Buffers: shared hit=18 read=1
 Planning Time: 0.656 ms
 Execution Time: 0.130 ms
```

### What changed and why

The partial index covers 3.5% of the table and stores rows in the order the
query asks for, so both the sequential scan and the sort disappear. Buffer
reads drop from 12475 to 4 — that number, not wall time, is the honest
measure here.

The scan also stops being parallel: the old plan used two workers plus the leader,
the new one a single process. On a dashboard opened by a dozen people at once, 
that is the difference between saturating the worker pool and not touching it.

Index-only scans depend on VACUUM. After a batch of rolled-back inserts the same 
query reported Heap Fetches: 1111 — the visibility map no longer marked those 
pages as all-visible, so the scan had to check the heap for 2.5% of the rows. 
A plain VACUUM brought it to zero. The plan is identical in both cases: 
nothing about the index or the query changed, only how much work the same plan had 
to do. This coupling does not exist in InnoDB, where visibility is reconstructed from 
the undo log rather than a map.

`INCLUDE` was considered for the dashboard query and rejected: with LIMIT 50 only fifty
heap lookups are saved, which does not justify a larger index. 
It pays off on the aggregate below, which scans the whole index.

```sql
CREATE INDEX idx_invoices_unpaid_cover
ON invoices (issued_at DESC)
INCLUDE (total_minor_units)
WHERE status IN ('issued', 'overdue');
```

```sql
SELECT count(*), sum(total_minor_units)
 FROM invoices
WHERE status IN ('issued', 'overdue');
```

```
 Aggregate  (cost=1616.55..1616.56 rows=1 width=16) (actual time=9.988..9.989 rows=1 loops=1)
   Buffers: shared hit=44 read=170
   ->  Index Only Scan using idx_invoices_unpaid_cover on invoices  (cost=0.29..1394.89 rows=44333 width=4) (actual time=0.414..6.862 rows=44040 loops=1)
         Heap Fetches: 1111
         Buffers: shared hit=44 read=170
 Planning:
   Buffers: shared hit=22 read=1
 Planning Time: 2.721 ms
 Execution Time: 10.081 ms
```

```
VACUUM invoices;
```

```
Aggregate  (cost=1489.79..1489.80 rows=1 width=16) (actual time=7.660..7.661 rows=1 loops=1)
   Buffers: shared hit=171
   ->  Index Only Scan using idx_invoices_unpaid_cover on invoices  (cost=0.29..1280.41 rows=41875 width=4) (actual time=0.040..4.339 rows=44040 loops=1)
         Heap Fetches: 0
         Buffers: shared hit=171
 Planning:
   Buffers: shared hit=35
 Planning Time: 0.599 ms
 Execution Time: 7.730 ms

```


### Cost

The covering index adds 1.4 MB to a 205 MB table (97 MB heap, 107 MB indexes)
— the three full-table unique indexes account for almost all of it.

Inserting 10k unpaid invoices dirties 2245 pages against 1115 
without the index — it doubles the write cost for rows it covers. The same insert 
with paid invoices dirties 1077, indistinguishable from having no index at all:
rows outside the predicate are never touched. Since an invoice enters the index 
once and leaves it once, and 94% of the table sits in paid, the cost applies 
to a small and short-lived fraction of writes while the dashboard query runs on 
every page load.
```
         indexrelname          |  size   | idx_scan 
-------------------------------+---------+----------
invoices_pkey                 | 40 MB   |        0
uniq_active_invoice_per_order | 40 MB   |        0
uniq_invoice_number           | 31 MB   |        0
idx_invoices_unpaid_cover     | 1368 kB |        2
```

### Notes

Test data uses UUIDv4 while the application generates UUIDv7, so the primary
key index is more fragmented here than it would be in production.
