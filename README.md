# Notification Service

Laravel 13 microservice for mass SMS and Email notifications.

The service accepts notification batches through an API, persists the requested work in PostgreSQL, dispatches asynchronous jobs through RabbitMQ, and uses Redis for cache, locks, rate limiting, and idempotency TTL storage.

## Stack

- PHP 8.5
- Laravel 13
- PostgreSQL
- RabbitMQ
- Redis
- PHPUnit

## Local Infrastructure

The Docker stack contains:

- `app`: Laravel HTTP node
- `queue-critical`: worker for transactional notifications
- `queue-default`: worker for regular traffic
- `queue-marketing`: worker for marketing traffic
- `postgres`: main database
- `rabbitmq`: message broker with management UI
- `redis`: cache, locks, rate limiter, and idempotency TTL storage

## Setup

Copy the environment file:

```bash
cp .env.example .env
```

Start the stack:

```bash
docker compose -f docker/compose.yaml up -d
```

Run database migrations:

```bash
docker compose -f docker/compose.yaml exec app php artisan migrate
```

Check container status:

```bash
docker compose -f docker/compose.yaml ps
```

## RabbitMQ UI

RabbitMQ Management UI is available at:

```text
http://localhost:15672
```

Default local credentials:

```text
Username: notification_service
Password: secret
```

## Useful Commands

Run Artisan commands inside the application container:

```bash
docker compose -f docker/compose.yaml exec app php artisan about
```

Run tests:

```bash
docker compose -f docker/compose.yaml exec app php artisan test --compact
```

Check Composer platform requirements:

```bash
docker compose -f docker/compose.yaml exec app composer check-platform-reqs --no-interaction
```

Stop the stack:

```bash
docker compose -f docker/compose.yaml down
```

## API

OpenAPI contract:

```text
openapi.yaml
```

Create a notification batch:

```bash
curl -X POST http://localhost:8000/api/v1/notification-batches \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H 'Idempotency-Key: example-batch-1' \
  -d '{
    "channel": "sms",
    "priority": "transactional",
    "message": "Your code is 1234",
    "recipient_ids": ["subscriber-1", "subscriber-2"],
    "initiator": "auth-service"
  }'
```

Get subscriber notification history:

```bash
curl -H 'Accept: application/json' \
  'http://localhost:8000/api/v1/subscribers/subscriber-1/notifications?status=sent'
```

Register a fake provider delivery event:

```bash
curl -X POST http://localhost:8000/api/v1/provider-events/fake-sms \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{
    "provider_message_id": "fake-sms-message-id",
    "event_type": "delivered"
  }'
```

## Queues

RabbitMQ queues are created from `docker/rabbitmq/definitions.json`:

- `notifications-critical`
- `notifications-default`
- `notifications-marketing`

Each queue is durable and configured with a dead-letter exchange. PostgreSQL remains the source of truth for notification status and history.
