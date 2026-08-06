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