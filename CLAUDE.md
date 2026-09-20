# Import/Export Customs Document Intelligence System — CLAUDE.md

## What this product does

A customs document management system for importers, exporters, and customs
brokers. Every shipment needs a complete set of documents — commercial invoice,
packing list, bill of lading, certificate of origin, and customs declaration.
One wrong HS tariff code or one missing document means the shipment is held at
the port. Demurrage (port storage) costs $1,000 per day. This system generates
a required document checklist for each shipment based on the country pair and
product type. AI (Ollama, no external API key) reads uploaded documents and
checks for errors — wrong HS codes, value mismatches between invoice and
declaration, missing required documents. Embeddings over past shipments surface
similar document sets as templates. Signals fire for missing documents, value
mismatches, and approaching filing deadlines.

Sells to: customs brokers, import/export coordinators, and logistics managers
at trading companies handling 10–500 shipments per month.

---

## Tech stack (fixed — do not change)

- **Backend:** Laravel (PHP 8.3)
- **Database:** PostgreSQL 16 with the **pgvector** extension (`pgvector/pgvector:pg16` image).
  Embeddings are stored as native `vector(768)` columns, not JSON — similarity search uses
  pgvector's `<=>` cosine-distance operator with an `hnsw` (`vector_cosine_ops`) index instead
  of computing cosine similarity in PHP.
- **Frontend:** Laravel Blade + Livewire + Tailwind CSS v4 + Alpine.js + Vite
- **Runtime:** Docker (docker-compose) — no local PHP or Postgres needed
- **Icons:** Heroicons inline SVG only
- **Font:** Inter (Google Fonts)
- **LLM generation:** Ollama — `llama3.2:3b` (HS code suggestion, document error explanation)
- **Embeddings:** Ollama — `nomic-embed-text`, 768 dimensions (similar shipment detection,
  document search) — matches the `vector(768)` column width above
- **PDF export:** `barryvdh/laravel-dompdf`
- **PDF parsing:** `smalot/pdfparser` (tariff schedule and regulation documents)
- **Excel import:** `maatwebsite/excel` (bulk shipment import)

No external API keys. All AI runs locally via Ollama.

> **Change log:** originally specified MySQL 8.0 with `json` embedding columns. Switched to
> PostgreSQL + pgvector so similarity search (HS code finder, similar-shipment detection) runs
> as an indexed SQL query instead of loading every embedding into PHP and computing cosine
> similarity in a loop. All other stack choices are unchanged.

---

## Assigned ports

- **APP_PORT:** 8007
- **DB_EXTERNAL_PORT:** 33067 (Postgres, mapped from container port 5432)
- **OLLAMA_PORT:** 11434 (shared host Ollama instance)

---

## Shared infrastructure rules

**Ollama — use the host, never add a container:**
Do NOT add an `ollama` service to this project's `docker-compose.yml`.
Ollama runs once on the host machine and is shared by all Nirmantra projects.
The app reaches it at `http://host.docker.internal:11434` — Docker maps this
address to the host from inside any container automatically.

Models are pulled once on the host and reused by every project:
```bash
ollama pull llama3.2:3b
ollama pull nomic-embed-text
```

If models are already pulled (check with `ollama list`), skip the pull step.
Loading the same model twice — once per project container — wastes 2 GB of RAM
per duplicate. One host instance serves all projects simultaneously.

**Database — fully isolated, do not share:**
This project's database runs in its own container with its own Docker volume.
No other project connects to it. Never point this project at another project's
database port. Never share volumes between projects.

---

## Docker setup

```bash
cp .env.example .env
docker compose run --rm app php artisan key:generate
docker compose up -d --build
docker compose exec app php artisan migrate --seed
```

The `db` container runs `pgvector/pgvector:pg16`; the `vector` extension is created
automatically by the first migration (`CREATE EXTENSION IF NOT EXISTS vector`) — no manual step
needed.

`key:generate` runs first, in a throwaway container (`run --rm`), before the persistent
`app`/`queue`/`scheduler` containers are created. `.env.example` ships with `APP_KEY=` empty,
and `docker-compose.yml` loads `.env` via `env_file` — Docker bakes each variable's value into
the container's environment once, at container-creation time, and that baked-in value then
shadows anything a later `key:generate` writes to the `.env` file for the rest of that
container's life (Laravel's dotenv loader never overrides a real OS env var, even an empty
one). Generating the key before `docker compose up` means the containers are created with the
real key already in `.env`. If you ever run `key:generate` against an already-running `app`
container, follow it with `docker compose up -d --force-recreate app queue scheduler` so the
new key actually takes effect.

App at `http://localhost:8007`
Default login: `admin@tradecustoms.local` / `password`

**Manual document/policy import is a first-class feature, not just a seeder convenience.**
Broker and Coordinator users can upload tariff schedules, regulations, and trade agreements as
PDFs from **Tariff Documents** in the sidebar at any time after seeding. Upload runs the same
`TariffIngestService` pipeline the seeder uses: extract text (`smalot/pdfparser`) → chunk →
embed each chunk via `nomic-embed-text` → store as `vector(768)` rows in `tariff_chunks`. The
seeded tariff documents exist only to make the demo work on first login; they are not a
prerequisite for the upload feature and the upload feature is not a stub.

---

## Roles

Five roles using Spatie Laravel Permission:

- **Customs Broker** — full access. Manages all shipments, documents, and signals.
  Runs compliance checks and generates reports.
- **Import/Export Coordinator** — creates and manages shipments. Uploads documents.
  Cannot access other clients' shipments if multi-client setup.
- **Compliance Manager** — reviews flagged shipments and AI-detected errors.
  Can override AI findings with manual notes. Cannot create shipments.
- **Finance** — view-only access to declared values and payment documents.
  Verifies that invoice values match payment records. Cannot see other documents.
- **Client** — external login. Views their own shipments and document status only.
  Cannot see other clients' data. Cannot edit anything.

---

## MVP Features (5 only — build these, nothing else)

### 1. Shipment register with dynamic document checklist

**Create a shipment:**
- Shipment reference (auto-generated: `SHP-YYYY-NNNN`, also accepts custom ref)
- Direction: Import / Export
- Origin country
- Destination country
- Product description (free text)
- HS code (tariff code — 6 or 8 digit)
- Declared value (amount + currency)
- Shipment mode: Air / Sea / Road / Rail
- Incoterms: EXW / FOB / CIF / DDP / etc.
- Expected arrival/departure date
- Client (linked from client register — optional for single-company users)
- Customs filing deadline (date + time)

**Dynamic document checklist:**
Based on the origin + destination country pair and shipment mode, the system
generates a required document checklist. The checklist is seeded for the five
country/mode pairs in the synthetic data plan below (China→India sea,
Germany→India air, India→UAE sea, US→India air, Taiwan→India sea) via the
`document_requirements` table — and, since that table is just data, a broker
can add checklist rows for any other country pair/mode directly in it without
a code change.

Example: US → India, air freight:
- Commercial invoice ✓ required
- Packing list ✓ required
- Air waybill ✓ required
- Certificate of origin (USFTA/general) ✓ required
- Customs declaration (CBP Form 7501 reference) ✓ required
- Insurance certificate ○ optional
- Phytosanitary certificate (if food/agri product) ○ conditional

The manager uploads the actual document for each checklist item. The system
tracks which are uploaded, which are missing, and which have been verified.

### 2. Document upload and AI validation

Each shipment document is uploaded as a PDF. The system runs three automated
checks when a document is uploaded:

**Check 1 — Completeness:** Is every required document for this shipment uploaded?
Runs against the dynamic checklist. Missing required documents trigger a signal.

**Check 2 — HS code consistency:**
AI reads the commercial invoice description and the HS tariff schedule (uploaded
as a regulation document). It compares the product description on the invoice to
the HS code entered in the shipment record.

If there is a mismatch (e.g. the invoice says "cotton fabric woven" but the HS
code maps to "synthetic fabric"), a warning is shown:
> "The product description 'cotton fabric woven' does not match the HS code
> 5407 (woven fabrics of synthetic filament yarn). Suggested code: 5208."

**Check 3 — Value consistency:**
The system reads the declared value from the shipment record and compares it to
the total value on the commercial invoice. If they differ by more than 5%, a
warning is raised:
> "Declared value: $12,500. Invoice total: $15,200. Difference: $2,700 (21.6%).
> This may cause a customs hold for under-declaration."

All three checks produce findings stored in `document_findings`. Each finding
is either confirmed by the compliance manager or marked as resolved.

### 3. HS code intelligence assistant

A dedicated "HS Code Finder" tab. The user describes a product and the system
suggests the correct HS code:
- "6-ply polypropylene woven sacks, 50kg capacity, plain weave"
- "Electronic speed controller (ESC) for brushless drones, 30A, no battery"
- "Unroasted Arabica green coffee beans, not decaffeinated"

The system uses `nomic-embed-text` to search the uploaded tariff schedule
document for the most relevant chapters and headings. It passes the top 5
matches to `llama3.2:3b` with the product description and returns:
- Suggested HS code(s) (most likely 1–3 options)
- Confidence level (High / Medium / Low)
- The tariff chapter and heading name
- The basis for the suggestion (which section of the tariff schedule it comes from)
- A note on common mistakes for this product category

This is a suggestion tool. The user must verify before using in a declaration.
The system logs every HS code lookup with the query and result.

### 4. Similar shipment detection using embeddings

When a new shipment is created, the system embeds the product description using
`nomic-embed-text` and compares it to all past completed shipments.

If a past shipment is highly similar (cosine similarity > 0.85):
> "This shipment looks similar to SHP-2024-0087 (cotton fabric, India→US,
> $14,000, completed 3 months ago, no issues). You can use its document set
> as a template."

The user can copy the document checklist and HS code from the similar past
shipment as a starting point.

Additionally, the system runs a weekly analysis to detect:
- Which product categories most often trigger HS code warnings
- Which country pairs most often have missing documents
- Which clients most often have value mismatches

These patterns are shown in the "Compliance Insights" section of the reports
page — not as signals but as trend information.

### 5. Signals engine and deadline tracking

**10 default signals (seeded, active by default):**

| # | Name | Condition | Severity |
|---|------|-----------|----------|
| 1 | Missing Required Document | A required document is not uploaded 48h before filing deadline | Critical |
| 2 | Customs Filing Deadline — 48h | Filing deadline is 48 hours away | High |
| 3 | Customs Filing Deadline — 24h | Filing deadline is 24 hours away | Critical |
| 4 | HS Code Mismatch Detected | AI check found HS code inconsistency | High |
| 5 | Value Mismatch Detected | Invoice value differs from declared value by 5%+ | High |
| 6 | Filing Deadline Passed — Not Filed | Deadline passed, shipment status not "Filed" | Critical |
| 7 | Expired Document Uploaded | Any certificate or licence with a past expiry date uploaded | Medium |
| 8 | Repeat HS Code Warning — Same Client | Same client has HS mismatch on 3+ shipments in 90 days | Medium |
| 9 | High Value Shipment — Manual Review | Shipment value > $50,000 with no compliance review | High |
| 10 | Missing Certificate of Origin | Shipment to a country with preferential duty rates has no CoO | High |

**Deadline dashboard:**
The main dashboard shows a timeline of all shipments with deadlines in the
next 7 days. Each shipment shows: deadline, document completion %, any open
findings, signal status.

---

## Database schema

```
clients
  id, name, contact_name, contact_email, country, created_at, updated_at

shipments
  id, reference (unique), direction (enum: import|export),
  origin_country (string), destination_country (string),
  product_description (text), hs_code (string),
  declared_value (decimal), declared_currency (string),
  mode (enum: air|sea|road|rail), incoterms (string),
  expected_date (date), filing_deadline (datetime),
  client_id (FK nullable), status (enum: draft|documents_pending|ready|filed|cleared|held),
  created_by (FK users), created_at, updated_at

document_requirements
  id, origin_country, destination_country, mode,
  document_type (string), required (boolean), conditional_on (string nullable),
  created_at

shipment_documents
  id, shipment_id (FK), document_type (string),
  file_path (string nullable), uploaded_by (FK users nullable),
  uploaded_at (timestamp nullable), verified (boolean default false),
  verified_by (FK users nullable), verified_at (timestamp nullable),
  required (boolean), notes (text nullable), created_at, updated_at

document_findings
  id, shipment_id (FK), document_id (FK nullable),
  finding_type (enum: hs_mismatch|value_mismatch|missing_doc|expired_doc|other),
  description (text), suggested_value (string nullable),
  severity (enum: low|medium|high|critical),
  status (enum: open|confirmed|resolved),
  resolved_by (FK users nullable), resolution_note (text nullable),
  resolved_at (timestamp nullable), created_at

hs_lookups
  id, query_text (text), suggested_codes (json),
  confidence (enum: high|medium|low), source_section (string),
  result_summary (text), created_by (FK users), created_at

tariff_documents
  id, name, country_code (string), document_type (enum: tariff_schedule|regulation|trade_agreement),
  file_path, chunk_count (int), embedded_at (timestamp nullable),
  uploaded_by (FK users), created_at

tariff_chunks
  id, document_id (FK), chunk_index, chunk_text (text),
  embedding (vector(768)), created_at
  -- hnsw index on embedding using vector_cosine_ops (builds cleanly on small/empty
  -- tables, unlike ivfflat which needs enough rows to train clusters)

shipment_embeddings
  id, shipment_id (FK unique), embedding (vector(768)), created_at, updated_at
  -- hnsw index on embedding using vector_cosine_ops

signals
  id, name, condition_key, severity (enum), active (boolean), created_at

signal_events
  id, signal_id (FK), shipment_id (FK nullable), triggered_at,
  resolved_at (nullable), resolved_by (FK users nullable),
  note (text nullable), created_at
```

---

## Scheduled jobs

```php
$schedule->command('signals:check')->hourly();
$schedule->command('embeddings:process-shipments')->everyThirtyMinutes();
$schedule->command('documents:check-expiry')->daily();
```

`documents:check-expiry` — scans all uploaded documents that have a known
expiry date and flags expired ones as a finding.

---

## Environment variables

```env
APP_NAME="Trade Customs Intelligence"
APP_ENV=local
APP_PORT=8007

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=trade_customs
DB_EXTERNAL_PORT=33067

OLLAMA_BASE_URL=http://host.docker.internal:11434
OLLAMA_GENERATION_MODEL=llama3.2:3b
OLLAMA_EMBEDDING_MODEL=nomic-embed-text
OLLAMA_TIMEOUT=300

HIGH_VALUE_THRESHOLD=50000
VALUE_MISMATCH_PERCENT=5

QUEUE_CONNECTION=database
```

---

## Synthetic data plan (seeder)

Demo company: **Meridian Trade Services** (a small customs brokerage managing
imports for 5 client companies in India).

**Users (5):**
- `admin@tradecustoms.local` / `password` — Customs Broker (full access)
- `coordinator@tradecustoms.local` / `password` — Import/Export Coordinator
- `compliance@tradecustoms.local` / `password` — Compliance Manager
- `finance@tradecustoms.local` / `password` — Finance (value view only)
- `client@tradecustoms.local` / `password` — Client (one client company view)

**Clients (5):**
- Arjun Textiles Pvt Ltd (imports cotton fabric from China)
- Nexgen Electronics (imports components from China, Taiwan)
- Pure Harvest Foods (imports food products from UAE, Sri Lanka)
- Medica Devices India (imports medical devices from Germany, US)
- Sunbeam Chemicals (exports chemicals to UAE, UK)

**Tariff documents (2, with realistic synthetic content):**
- India Customs Tariff Schedule — Chapters 50–63 (Textiles, 10 pages synthetic)
- India Customs Tariff Schedule — Chapters 84–85 (Electronics, 10 pages synthetic)

**Document requirements seeded for country pairs:**
- China → India (sea): 7 document types
- Germany → India (air): 6 document types
- India → UAE (sea): 6 document types
- US → India (air): 7 document types
- Taiwan → India (sea): 6 document types

**Shipments (20 total):**
- 8 cleared shipments (all documents complete, no issues)
- 4 with all documents uploaded and ready to file
- 3 with documents partially uploaded (missing 1–2 required docs)
- 3 with AI findings: 2 HS code mismatches, 1 value mismatch
- 2 with deadlines in the next 48 hours (triggers deadline signals)

**Signal events:** not hardcoded — the seeder shapes shipment data so each of
the 5 most demo-relevant signal types has at least one real shipment that
should trigger it (a shipment with a missing doc inside the 48h window, one
with a deadline ~20h out, one ~40h out, two with an open HS mismatch finding,
one with an open value mismatch finding), then runs the actual
`SignalsEngineService` — the same code `signals:check` runs hourly — against
that data. The resulting signal_events count follows from real state, not a
fixed number.

**HS code lookup history (5 past lookups):**
Pre-populated with realistic product descriptions and suggested codes so the
demo shows the AI assistant working from first login.

All data is fully synthetic. No real company names, no real shipment records,
no real customs declarations.

---

## Design

Inherits the Nirmantra design system.

- **Accent colour:** Teal (`teal-600`) — logistics, trade, global
- **Layout:**
  - Sidebar + main content area
  - Sidebar links: Dashboard, Shipments, HS Code Finder, Documents,
    Signals, Compliance Insights, Reports, Settings (admin only)
- **Dashboard:** deadline timeline (next 7 days), open signals count, shipments
  by status (donut chart via Chart.js CDN), recent findings
- **Shipment list:** table with status badge, document completion % bar,
  open findings count, deadline with urgency colouring
- **Status badge colours:**
  - Draft: `gray`
  - Documents Pending: `yellow`
  - Ready: `blue`
  - Filed: `indigo`
  - Cleared: `green`
  - Held: `red`
- **Document checklist on shipment detail:** each row shows document type,
  required/optional, upload status, and any open findings for that document

---

## Quality checklist

- [ ] Cold start: `docker compose up` + seed works with zero manual steps
- [ ] Shipment creation generates the correct document checklist for each country pair
- [ ] Document upload triggers all three AI checks (completeness, HS code, value)
- [ ] HS code mismatch warning shows the correct suggested code from the tariff document
- [ ] Value mismatch fires at 5%+ difference (test with a shipment at exactly 4.9% — no fire)
- [ ] HS Code Finder returns plausible suggestions from the seeded tariff content
- [ ] Similar shipment detection surfaces the correct past shipment on the demo data
- [ ] All 10 signals evaluate correctly — verify the 5 demo-relevant ones fire on seeded data
- [ ] Deadline timeline shows all shipments due in the next 7 days correctly
- [ ] Client role sees only their own shipments — no other client data visible
- [ ] Finance role can only see value fields — no access to other document types
- [ ] Bulk shipment import from CSV creates correct records
- [ ] No Ollama API key or external API key anywhere in the codebase
- [ ] `.env.example` documents every variable
- [ ] Works on mobile screen (375px minimum width)
