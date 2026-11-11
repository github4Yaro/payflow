# 0003. identity  aggregation

## Status - Accepted 

## Context
order and invoice is the same aggregation or different. also need to choose the relation type 
should it be entity or entity.id

 
## Decision
As order can exist as a draft without of invoice I decided to use 2 separate aggregations
chosen Relation by Id to get aggregation boundaries highlighter




## Consequences
Created two separated aggregations. we will create invoice as soon as order will get the new status  
