* add nginx on week 3
* Not standard flat Symfony structure to see the borders of the aggregates
* Money always >= 0, to deal with refund going to use transaction entity with debit/credit
* Invoice and Order two aggregations (create an Invoice for order that got the `new` status)
* symfony/uid in domain not as a part of the framework, it's library
* StatusTransitions got an additional guard to prevent mix of implementations
* invoice::createForOrder as a solution to keep data consistent
* OrderId and InvoiceId not the same even if code looks similar
* Invoice will must have an InvoiceNumber that is not an InvoideId and should be gapless
* InvoiceNumber should be created during the invoice issue process