# SupportDesk

A modern, Dockerized support ticket management platform built with Laravel, Python FastAPI, Node.js, MySQL, Valkey, Nginx, and background queue workers.

SupportDesk centralizes customer support requests, agent workflows, realtime conversations, and lightweight ticket intelligence in one clean application.

## Why SupportDesk?

Support requests often become scattered across email, Messenger, WhatsApp, spreadsheets, and manual notes.

SupportDesk provides one structured workflow for:

- Ticket creation
- Agent assignment
- Customer and agent replies
- Internal support notes
- Status and priority management
- Private attachments
- Ticket history
- Search and filtering
- Background ticket analysis
- Realtime updates
- Support dashboards

## Technology Responsibilities

Each technology has a clear purpose.

### Laravel

Laravel owns the business application:

- Authentication
- Authorization
- Ticket management
- Messages
- Attachments
- Agent assignment
- Status and priority
- Notifications
- Database state
- Queue dispatch
- Business rules

### Python FastAPI

Python provides lightweight ticket intelligence:

- Category suggestion
- Priority suggestion
- Urgency detection
- Keyword extraction
- Short ticket summary
- Basic similar-ticket analysis

Python only returns suggestions. Laravel remains the source of truth, and agents can override analysis results.

### Node.js + Socket.IO

Node.js handles realtime updates:

- New ticket events
- New replies
- Agent assignment
- Status changes
- Priority changes
- Ticket resolution

### MySQL

MySQL stores persistent business data.

### Valkey

Valkey provides Redis-compatible queue infrastructure.

### Laravel Worker

The worker handles asynchronous processing such as ticket analysis and notifications.

### Nginx

Nginx is the HTTP entry point and forwards PHP requests to PHP-FPM.

### Docker Compose

Docker Compose orchestrates the complete multi-service stack.

## Architecture

```text
                            Browser
                               │
                               ▼
                             Nginx
                               │
                               ▼
                            Laravel
                               │
          ┌────────────────────┼────────────────────┐
          │                    │                    │
          ▼                    ▼                    ▼
        MySQL                Valkey               Node.js
                               │                 Socket.IO
                               ▼                    │
                             Worker                 ▼
                               │                 Browser
                               ▼
                        Python FastAPI
                               │
                               ▼
                      Ticket Intelligence
```

Important architecture rules:

```text
MySQL = source of truth
Python = analysis service
Node.js = realtime enhancement
Valkey = queue backend
Worker = background orchestration
```

## Core Features

- Customer registration and login
- Customer, Agent, and Admin roles
- Unique support ticket numbers
- Ticket categories
- Ticket priorities
- Ticket status lifecycle
- Agent assignment
- Customer replies
- Agent replies
- Internal agent notes
- Private attachments
- Ticket activity history
- Ticket intelligence suggestions
- Realtime ticket updates
- Search and filtering
- Pagination
- Dashboard statistics
- Database notifications
- Background queue processing
- Scheduled ticket maintenance
- Dockerized development environment

## Ticket Workflow

```text
Open
  ↓
Assigned
  ↓
In Progress
  ↓
Waiting Customer
  ↓
In Progress
  ↓
Resolved
  ↓
Closed
```

Ticket priorities:

```text
Low
Normal
High
Urgent
```

## Ticket Intelligence Example

A customer may submit:

```text
Subject:
Production checkout is down

Message:
Customers cannot checkout and we are losing sales.
```

Python may return:

```json
{
  "suggested_category": "Technical Issue",
  "suggested_priority": "urgent",
  "urgency_score": 0.94,
  "keywords": [
    "production",
    "checkout",
    "customers",
    "sales"
  ],
  "summary": "Production checkout outage is preventing customers from completing purchases."
}
```

These are suggestions only.

Agents remain in control of final ticket categorization and priority.

## User Roles

### Customer

Customers can:

- Create support tickets
- View their own tickets
- Reply to tickets
- Upload attachments
- Track ticket status
- Receive realtime updates

### Agent

Agents can:

- View assigned tickets
- Handle allowed unassigned tickets
- Reply to customers
- Add internal notes
- Change ticket status
- Change priority
- Review Python suggestions
- Override suggestions
- Resolve tickets

### Admin

Admins can:

- View all tickets
- Assign tickets
- Manage categories
- Manage agents/users
- Change priority and status
- View dashboard statistics
- Review ticket history
- Review analysis data

## Core Data Model

The main domain is expected to include:

```text
users
tickets
ticket_categories
ticket_messages
ticket_attachments
ticket_activities
notifications
```

Typical ticket fields include:

```text
ticket_number
customer_id
assigned_to
category_id
subject
priority
status
ai_suggested_category
ai_suggested_priority
urgency_score
summary
keywords
analysis_status
last_reply_at
resolved_at
closed_at
```

## Docker Services

The completed development stack will include:

```text
nginx
laravel
mysql
valkey
worker
scheduler
python
node
```

Laravel Web, Worker, and Scheduler reuse the same Laravel application image.

```text
Laravel Web
→ php-fpm

Worker
→ php artisan queue:work

Scheduler
→ php artisan schedule:work
```

## Persistent Storage

Docker named volumes are planned for:

```text
mysql_data
support_attachments
```

Persistent data should survive normal container recreation.

## Queue Flow

```text
Laravel
   ↓
Valkey
   ↓
Worker
   ↓
Python FastAPI
   ↓
Analysis Result
   ↓
Laravel / MySQL
```

Typical queued jobs:

```text
AnalyzeTicket
SendTicketNotification
```

## Realtime Flow

```text
Laravel / Worker
       ↓
POST /event
       ↓
Node.js
       ↓
Socket.IO
       ↓
Browser
```

Realtime events may include:

```text
ticket.created
ticket.analysis.completed
ticket.assigned
ticket.reply.created
ticket.status.changed
ticket.priority.changed
ticket.resolved
ticket.closed
```

## Security Principles

- Customers can access only their own tickets
- Internal notes are hidden from customers
- Attachments are private
- Downloads require Laravel authorization
- Uploads are validated
- Laravel CSRF protection is used
- Policies/middleware protect sensitive actions
- Production `.env` files are not committed
- Python and Node do not become sources of business truth

## Development Philosophy

SupportDesk is designed to be completed, not endlessly expanded.

Development principles:

```text
One functionality at a time
Implement
Run
Test
Verify
Commit
Continue
```

The project avoids unnecessary architecture until a real need exists.

## MVP Definition

The MVP is complete when this entire flow works:

```text
Customer registers
      ↓
Customer creates ticket
      ↓
Unique ticket number generated
      ↓
AnalyzeTicket queued
      ↓
Worker calls Python
      ↓
Python suggestions saved
      ↓
Agent sees ticket realtime
      ↓
Ticket assigned
      ↓
Agent replies
      ↓
Customer sees reply realtime
      ↓
Customer replies
      ↓
Agent changes status
      ↓
Agent resolves ticket
      ↓
Ticket closes
      ↓
History remains searchable
```

The completed MVP should also include:

- Authorization
- Persistent attachments
- Queue worker
- Python analysis
- Node realtime updates
- Ticket search and filters
- Dashboards
- Important tests
- Production Docker configuration
- Final documentation

## Local Development

The exact setup may evolve while the project is implemented.

The intended workflow will be similar to:

```bash
git clone <repository-url>
cd supportdesk

cp laravel/.env.example laravel/.env

docker compose build
docker compose up -d

docker compose exec laravel php artisan key:generate
docker compose exec laravel php artisan migrate
```

Check services:

```bash
docker compose ps
```

View logs:

```bash
docker compose logs -f
```

Run Laravel commands:

```bash
docker compose exec laravel php artisan <command>
```

Worker logs:

```bash
docker compose logs -f worker
```

Python health:

```text
http://localhost:<python-port>/health
```

Node health:

```text
http://localhost:<node-port>/health
```

> Setup instructions will be finalized as implementation progresses.

## Development Roadmap

Major implementation areas:

1. Laravel foundation
2. Python FastAPI service
3. Node realtime service
4. Dockerfiles
5. Nginx
6. Docker Compose
7. MySQL persistence
8. Valkey
9. Worker
10. Scheduler
11. Authentication
12. Roles
13. Base SaaS layout
14. Ticket enums
15. Ticket categories
16. Ticket model
17. Messages
18. Attachments
19. Activity history
20. Ticket creation
21. Ticket analysis queue
22. Python analysis
23. Save suggestions
24. Customer ticket management
25. Agent workflow
26. Internal notes
27. Status/priority management
28. Authorization
29. Node Socket.IO integration
30. Realtime events
31. Dashboards
32. Search/filtering
33. Notifications
34. Scheduler logic
35. Tests
36. UI polish
37. Health checks
38. Production Docker
39. Final documentation

## Scope

The MVP intentionally excludes:

- Billing and subscriptions
- AI chatbot
- Paid LLM dependency
- Multi-tenancy
- Complex SLA engine
- CRM
- Voice/video support
- WhatsApp integration
- Messenger integration
- Mobile application
- Large analytics platform
- Knowledge base AI

The priority is to finish a strong support ticket product first.

## Project Status

🚧 **Under active development**

Development is incremental, and each functionality is tested before moving to the next.

## License

Choose an appropriate license before public release.