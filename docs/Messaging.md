
## 💬 Messaging System – Developer Guide

The Coliving App includes a robust messaging system designed to support communication between users (mates) and coliving space admins. This guide explains the structure, flow, and design philosophy behind the system.

---

### 🧱 Core Tables

#### `inbox_mate_mate`
- Stores **private (1:1) conversations** between mates.
- `user1`, `user2`: participants (by UID).
- `user1_checked`, `user2_checked`: track when each user last checked the conversation.

#### `inbox_mate_space`
- Stores **conversations between a mate and a coliving space** (e.g., joining requests, general questions).
- `user1`: the mate UID.
- `user2`: the space UID.
- `user1_checked`, `user2_checked`: similar logic as above.

#### `messages`
- Stores the actual messages.
- `conversation_uid`: links to the conversation.
- `sender_uid`: UID of the sender.
- `content`: the message content.

---

### 📥 Inbox Structure

- The frontend fetches `/api/conversations`, which returns:
  - `conversations_mate_pov`: all **personal conversations** from the user’s point of view, including:
    - `inbox_mate_mate` (mate-to-mate)
    - `inbox_mate_space` where the user is `user1` (initiator)
  - `conversations`: space-related conversations (from `inbox_mate_space` where the user is the space admin)
- Each conversation includes:
  - Last message timestamp (`updated_at`)
  - `user1_checked` and `user2_checked`
  - `new`: boolean flag computed in frontend if there are unread messages

---

### 🔴 Unread Detection

Unread status is determined by comparing the conversation’s `updated_at` with the `userX_checked` timestamp:

- If `updated_at > user1_checked`, the message is **unread** by `user1`
- Same applies for `user2`

This logic runs both:
- In the **frontend**, for live updating and dot indicators in inbox views
- In the **MateService** backend, to include top-level `.hasUnreadPersonal` and `.hasUnreadSpaces` flags used globally (e.g., in the header menu)

---

### ✅ Smart Syncing

When a user visits the Inbox view:
- `/api/conversations` is called
- Unread states are calculated in JavaScript and `new` flags are added per conversation
- Vue's `computed` values derive `hasNewPersonal` and `hasNewSpaces`
- Red dots (`●`) are shown automatically in Inbox tabs

The header `Inbox` menu dot checks `window.mate.hasUnreadPersonal` and `.hasUnreadSpaces`, which are set by backend and updated live by the frontend via `inboxStore.js`. No watchers are required in the header.

---

### 🛡️ Design Philosophy

- Only **conversations** are stored in the `inbox_mate_mate` and `inbox_mate_space` tables – messages themselves live in the `messages` table.
- Separation of user-facing logic and backend tracking ensures speed and flexibility.
- The system is easily extendable (e.g., groups, threads, reactions) with minimal changes.

---

> For questions, improvements, or to contribute, feel free to open an issue or submit a pull request.
