# Coliving App

[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue](https://img.shields.io/badge/Vue.js-3-4FC08D?style=flat-square&logo=vue.js&logoColor=white)](https://vuejs.org)
[![Vite](https://img.shields.io/badge/Vite-Latest-646CFF?style=flat-square&logo=vite&logoColor=white)](https://vitejs.dev)
[![Composer](https://img.shields.io/badge/Composer-2-885630?style=flat-square&logo=composer&logoColor=white)](https://getcomposer.org)
[![NPM](https://img.shields.io/badge/NPM-Latest-CB3837?style=flat-square&logo=npm&logoColor=white)](https://www.npmjs.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net)

[![Active](https://img.shields.io/badge/status-active-brightgreen)](https://github.com/colivingapp/coliving-app)
[![Contributors](https://img.shields.io/github/contributors/colivingapp/coliving-app?style=flat-square)](https://github.com/colivingapp/coliving-app/graphs/contributors)
[![GitHub Issues](https://img.shields.io/github/issues/colivingapp/coliving-app?style=flat-square)](https://github.com/colivingapp/coliving-app/issues)
[![Last Commit](https://img.shields.io/github/last-commit/colivingapp/coliving-app?style=flat-square)](https://github.com/colivingapp/coliving-app/commits/main)
[![License](https://img.shields.io/github/license/colivingapp/coliving-app?style=flat-square&v=001)](https://github.com/colivingapp/coliving-app/blob/main/LICENSE)

---

## 🌐 About

**Coliving App** is an open-source, community-driven platform dedicated to transforming shared living experiences. It empowers users to create, manage, and explore coliving spaces with built-in social and communication features.

**LIVE:** [https://coliving.app](https://coliving.app)

---

## 🚀 Project Status

Coliving App is in **Community Development** phase. The core functionality is stable, while new features are being explored through community contribution. The maintainer reviews PRs on a bi-weekly basis.

Feedback, ideas, and feature proposals are welcome in 👉 [GitHub Discussions](https://github.com/colivingapp/coliving-app/discussions).

---

## 🛠️ Local Setup

1. Clone the repository and install dependencies:
   ```bash
   git clone https://github.com/colivingapp/coliving-app.git
   cd coliving-app
   composer install
   npm install
   ```  
2. Create a virtual host (e.g. http://ca.local) pointing to the Laravel project's /public directory.

3. Copy `.env.example` to `.env`, and set at least the following:
   ```
   APP_URL=http://ca.local
   DB_DATABASE=your_database_name
   ```
4. In `vite.config.js`, update the server.origin to match your local URL:
   ```
   server: {
      origin: 'http://ca.local'
   }
   ```
5. Run setup commands:  
   ```bash
   php artisan migrate
   php artisan key:generate
   php artisan storage:link
   npm run watch
   ```
6. Visit `http://ca.local` in your browser and sign up with your desired email. The first user is auto-verified and becomes the owner of the example coliving space. Configure your email server credentials in `.env` to enable full functionality.

---

## 🤝 Contributing

We welcome contributions that help improve the Coliving App — whether it's fixing bugs, refining features, or developing new tools for the coliving community.

For more info, check our [Contributing Guidelines](./CONTRIBUTING.md).

---

## 📋 Development Roadmap

Our mission is to make it easy for anyone to create, join, and thrive in coliving communities around the world.

### Tools for Coliving Mates & Spaces

Helps people set up, promote and coordinate coliving spaces:

- **Instant Spaces** – launch a coliving space, even before renting an apartment.
- **Templates** – configure rent, dates, house rules, deposits easily.
- **Promotion Tools** – tools to help you launch, promote, and fill your space.
- **Invite System** – add people to your space via links or QR codes.
- **Roles** – assign roles like Organizer, Finance Manager, Chore Lead.
- **Expense Estimator** – per-person calculation of rent, utilities, and deposits.
- **Admin Tools** – space management, and host-specific tools.

Daily life organization tools (Hub):

- **Voting** – propose and decide on house rules, events, expenses, or new member applications.
- **Chat** – real-time group messaging, pinned items, updates, and shared files.
- **Agenda** – shared calendar for events, tasks, cleaning schedules, or dinners.
- **Chore Management** – task assigning, reminders, and completion tracking.
- **Pulse Check** – lightweight weekly mood tracker for emotional well-being.
- **Expense Sharing** – track ongoing shared expenses (e.g. rent, groceries).

### Global Coliving Community

Gamified social engagement and community-building:

- **Achievements** – unlock badges for contributions, hosting, and community participation.
- **Leaderboards** – highlight top mates and spaces based on engagement, reviews, and profile completeness.
- **CoMatch Score** – compatibility score for finding like-minded housemates and communities.
- **Social Sparks** – lightweight prompts and activities designed to spark new conversations and deeper friendships.
- **Social Discovery** – connect with people through shared bookmarks, interests, locations, and travel plans.
- **Global Coliving Ratings** – rate cities, neighborhoods, and countries for coliving quality.
- **Coliving Profile** – build a trusted profile showcasing stays across spaces and positive community reputation.
- **Discussion Forum** - open discussions around coliving tips, travel advice, lifestyle topics, and community ideas.

Global exploration and discovery tools:

- **Coliving Explorer** – browse community members and spaces worldwide through an interactive map.
- **Coliving Travel Planner** – organize group trips, local meetups, and coliving journeys.
- **Coliving Circles** – form small trusted groups with mates and travelers for easier reconnection across cities.
- **Local Events** – discover open dinners, workshops, and social gatherings hosted by nearby coliving spaces.
- **Feedback & Reviews** – transparent reviews for spaces, hosts, and housemates.
- **Safety Tools** – emergency alerts, safety ratings, and community-driven reporting systems.
- **Space Discovery Feed** – dynamic feed of newly launched spaces, trending communities, and open spots for travelers.

---

## 📚 Documentation

- [Contributing Guidelines](./CONTRIBUTING.md)
- [Feature Proposal Template](./docs/Feature-Proposal.md)
- [Project Structure](./docs/Project-Structure.md)
- [Map Components Overview](./docs/Maps.md)
- [Messaging System](./docs/Messaging.md)

---

## 🏘️ Join the Movement

Coliving App is more than just software — it’s a growing experiment in how we live, share, build, and connect. Whether you’re a developer, a digital nomad, or just someone passionate about coliving, your energy and ideas are welcome.

- [Create a new GitHub Issue](https://github.com/colivingapp/coliving-app/issues/new)
- [Provide feedback or suggest new features](https://github.com/colivingapp/coliving-app/discussions)
- [Add a new coliving space](https://coliving.app/space/new)