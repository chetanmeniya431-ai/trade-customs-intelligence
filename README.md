# Trade Customs Intelligence

A customs compliance tool for importers, exporters, and freight brokers. Get a dynamic document checklist per country pair, find the right HS tariff code by describing your product, and track submission deadlines — all in one place.

Runs entirely on your own server. No third-party AI costs — uses Ollama for local language models.

---

## Requirements

- Docker and Docker Compose
- [Ollama](https://ollama.com) running on the host machine with these models pulled:
  ```bash
  ollama pull llama3.2:3b
  ollama pull nomic-embed-text
  ```

No local PHP, Node, or PostgreSQL install needed.

---

## Quick start

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Open **http://localhost:8007**

---

## Demo logins

All demo accounts use password `password`.

| Email | Role |
|---|---|
| admin@tradecustoms.local | Customs Broker |
| coordinator@tradecustoms.local | Coordinator |
| compliance@tradecustoms.local | Compliance Manager |
| finance@tradecustoms.local | Finance |
| client@tradecustoms.local | Client |

---

## Features

- Dynamic document checklist per country pair — always current
- AI HS code finder: describe your product, get the right tariff code
- Customs timeline and submission deadline tracking
- Automatic alerts when deadlines are close or documents are missing
- Roles for brokers, coordinators, compliance managers, and clients
- Shipment import via CSV for bulk processing

---

## Key configuration

All settings are in `.env`. Copy `.env.example` to `.env` and adjust:

| Variable | Description |
|---|---|
| `APP_URL` | Public URL of the app |
| `APP_PORT` | Host port (default `8007`) |
| `OLLAMA_BASE_URL` | Ollama server URL (default `http://host.docker.internal:11434`) |
| `OLLAMA_GENERATION_MODEL` | LLM for HS code finder and AI features (default `llama3.2:3b`) |
| `OLLAMA_EMBEDDING_MODEL` | Embedding model for search (default `nomic-embed-text`) |
| `MAIL_MAILER` | Set to `smtp` with SMTP credentials for real email delivery |

For production: set `APP_ENV=production`, `APP_DEBUG=false`, and use a strong `DB_PASSWORD`.

---

## Tech stack

Laravel 11 · PHP 8.3 · PostgreSQL + pgvector · Livewire v3 · Tailwind CSS · Ollama · Docker
