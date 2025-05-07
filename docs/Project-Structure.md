# 🧭 Project Structure Overview

This project is a **full-stack Laravel + Vue 3 application** built to support shared living experiences through space listings, user profiles, and community interactions.

---

## 🧱 Tech Stack

- **Backend**: Laravel 11 (PHP), Sanctum Auth
- **Frontend**: Vue 3, Vue Router, Vite
- **Database**: MySQL (Eloquent ORM)
- **Deployment**: Composer, NPM

---

## 🗂️ Backend (Laravel)

| Path | Description |
|------|-------------|
| `routes/` | Defines API and web routes |
| └─ `api.php` | Primary JSON API endpoints (Sanctum protected) |
| └─ `web.php` | Basic public or fallback web routes |
| `app/Http/Controllers/` | Core controllers (e.g. `SpaceController`, `InboxController`) |
| `app/Models/` | Eloquent models like `Mate`, `Space`, `InboxMessage`, etc. |
| `app/Listeners/` | Event handlers (e.g. after email verification) |
| `database/migrations/` | DB schema definitions |
| `app/Actions/Fortify/` | Custom auth logic (password reset, registration, etc.) |
| `.env.example` | Environment configuration sample |

---

## 🎨 Frontend (Vue 3 + Vite)

| Path | Description |
|------|-------------|
| `resources/js/pages/` | Main routed views (e.g. `Home.vue`, `Space.vue`, `Mate.vue`) |
| `resources/js/inbox/` | Inbox & messaging UI |
| `resources/js/hub/` | Hub UI |
| `resources/js/hub_chat/` | Hub messaging UI |
| `resources/js/form/` | Forms for creating/editing mates and spaces |
| `resources/js/layout/` | Layout components: headers, footers, containers |
| `resources/js/account/` | Account settings and profile views |
| `resources/js/map/` | Map display and location-related features |
| `resources/js/widget/` | Embedded widgets and lightweight UI blocks |
| `resources/js/app.js` | Vue app initialization |

---

## 🧠 State Management

This app does **not** use Vuex or Pinia. Instead, it:
- Leverages `window.*` globals injected via Laravel Blade
- Uses local component state or simple JS stores

---

## 💡 Notes

- The project uses Laravel's **Sanctum** for API authentication.
- Hot Module Reloading is enabled via `vite`.
- `.env.example` includes all necessary keys to get started.

---

## 📦 Getting Started

See the [`README.md`](./README.md) for full setup instructions.

---

## 🤝 Contributing

For development conventions and how to get involved, see [`CONTRIBUTING.md`](./CONTRIBUTING.md).
