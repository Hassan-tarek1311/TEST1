# TimeBee — Employee Time Tracking System

> A full-stack employee time tracking and productivity monitoring system, inspired by [timebee.app](https://timebee.app)  
> Built as a technical assessment for EAIS.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 11 (PHP 8.4 — matches `Dockerfile` / `composer.json`) |
| Frontend | Laravel Blade + Vanilla JS |
| Database | MySQL 8.0 |
| Authentication | JWT (tymon/jwt-auth) |
| AI Integration | OpenAI GPT-3.5 / Mock Engine |
| DevOps | Docker + Docker Compose |

---

## Architecture Overview

```
┌────────────────────────────────────────────────────────┐
│                  Docker Environment                    │
│                                                        │
│  ┌──────────┐    ┌──────────────────┐   ┌──────────┐  │
│  │ Browser  │───▶│  Laravel         │──▶│  MySQL   │  │
│  │ (Web UI) │    │  Web + API       │   │    DB    │  │
│  └──────────┘    └────┬─────────────┘   └──────────┘  │
│                       │                                │
│  ┌──────────┐    ┌────▼─────┐                          │
│  │ Postman  │───▶│ REST API │                          │
│  │  Client  │    │  /api/*  │                          │
│  └──────────┘    └──────────┘                          │
└────────────────────────────────────────────────────────┘
```

### Scalability (discussion-ready)

- **Stateless API auth (JWT)** — API servers can scale horizontally; no server-side session store required for `/api/*`.
- **MySQL + indexed foreign keys** — org → users → projects → time_logs; pagination caps (`per_page` max 100) avoid huge responses.
- **AI off the critical path** — productivity endpoints aggregate DB stats then call OpenAI or mock; can be moved to a queue later without changing the REST contract.
- **Docker** — same image runs locally or on a VM/cluster; add Redis/queue workers when background jobs grow.

---

## Web UI Pages

| URL | Page | Access |
|-----|------|--------|
| `/login` | Sign In | Guest |
| `/register` | Create Account + Organization | Guest |
| `/dashboard` | Stats, active timer, recent logs | Auth |
| `/projects` | Project CRUD | Auth |
| `/time-logs` | Time log list, start/stop timer | Auth |
| `/users` | User management | Admin only |
| `/ai-report` | AI productivity reports | Auth |

---

## Quick Start

```bash
git clone https://github.com/dhahi-lab/timebee
cd timebee
docker-compose up --build
```

The app automatically installs, migrates, and starts.

- **Web UI:** http://localhost:8000  
- **phpMyAdmin:** http://localhost:8080

Visit http://localhost:8000/register to create your first account.

---

## API Reference

All protected endpoints require: `Authorization: Bearer {token}`

### Auth
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | Register org + admin |
| POST | `/api/auth/login` | Login |
| POST | `/api/auth/logout` | Logout |
| GET  | `/api/auth/me` | Current user |

### Projects
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET    | `/api/projects` | List projects |
| POST   | `/api/projects` | Create |
| PUT    | `/api/projects/{id}` | Update |
| DELETE | `/api/projects/{id}` | Delete |

### Time Logs
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET    | `/api/time-logs` | My logs (optional query: `date`, `from`, `to`, `project_id`, `active_only=1`, `per_page` ≤ 100, `page`) |
| POST   | `/api/time-logs/start` | Start timer |
| PATCH  | `/api/time-logs/{id}/stop` | Stop timer |
| DELETE | `/api/time-logs/{id}` | Delete |

### Users (admin)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET    | `/api/users` | List users |
| POST   | `/api/users` | Create user |
| PUT    | `/api/users/{id}` | Update |
| PATCH  | `/api/users/{id}/toggle-status` | Toggle active |

### AI
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/ai/productivity/{userId}` | Productivity report |
| GET | `/api/ai/team-summary` | Team summary (admin) |

---

## Technical Decisions

**Laravel** — unified full-stack: Blade views + REST API from the same codebase.  
**Blade (not React/Vue)** — no build step, same API consumed by both browser and Postman.  
**JWT** — stateless auth, easy horizontal scaling. Stored in PHP session for web views.  
**Mock AI** — runs without API key. Set `AI_PROVIDER=openai` + `OPENAI_API_KEY` for real AI.  
**MySQL** — relational structure maps naturally to org → users → projects → logs; transactional, widely supported on hosts and in Docker.

---

## Project Structure

```
timebee/
├── Dockerfile
├── docker-compose.yml
├── docker/entrypoint.sh
└── app/
    ├── app/Http/Controllers/
    │   ├── Api/          ← JSON API
    │   └── Web/          ← Blade pages
    ├── resources/views/
    │   ├── layouts/app.blade.php
    │   ├── auth/{login,register}.blade.php
    │   ├── dashboard/index.blade.php
    │   ├── projects/index.blade.php
    │   ├── timelogs/index.blade.php
    │   ├── users/index.blade.php
    │   └── ai/report.blade.php
    └── routes/{api,web}.php
```

---

Built with ❤️ for EAIS Technical Assessment
