# 🤝 Contributing to Coliving App

Thank you for your interest in contributing to **Coliving App**!  
This guide will help you get started and make meaningful contributions.

---

## 🐛 Bug Fixes

If you’ve found a bug:

1. Check if the issue is already reported.
2. If not, [create a new issue](https://github.com/colivingapp/coliving-app/issues).
3. If you know how to fix it, fork the repo, make your changes, and submit a pull request.

Please include a clear description of the bug and how your fix resolves it.

---

## 🌱 Suggesting Features

To propose a new feature:

1. Start a topic in [GitHub Discussions](https://github.com/colivingapp/coliving-app/discussions) to share your idea.
2. Include:
   - **Why** the feature is necessary
   - **How** it benefits the coliving community
   - Any design or technical suggestions

Once the idea is approved and aligns with our development roadmap, you're welcome to implement it and submit a pull request.

Please understand that while all ideas are appreciated, not all will be accepted. We aim to keep the app simple and sustainable.

> Pro tip: Use the [Feature Proposal Template](./docs/Feature-Proposal.md) to clearly explain the proposed feature.

---

## 🔍 Contribution Focus

To ensure sustainable development, we're using a tiered approach to contributions:

### Tier 1: Always Welcome
- Bug fixes
- Performance optimizations
- Documentation improvements
- Test coverage

### Tier 2: Welcome with Discussion
- Enhancements to existing features
- UI/UX improvements
- New integrations with existing components

### Tier 3: Requires Proposal
- New major features
- Architecture changes
- New dependencies or technologies

For Tier 3 contributions, please start a topic in [GitHub Discussions](https://github.com/colivingapp/coliving-app/discussions) before investing significant development time.

---

## 🔬 How We Evaluate New Features

To keep the Coliving App focused and sustainable, new feature proposals are reviewed against the following criteria:

### Must-Haves
- **Necessity**: Does it solve a real, recurring problem for coliving users?
- **Clarity**: Is the feature easy to understand and use?
- **Alignment**: Does it match our development roadmap and long-term vision?
- **Non-bloating**: Can it be added without making the app heavier or more complex?
- **Low-maintenance**: Does it avoid introducing tech debt or high upkeep?

### Red Flags
- Solves a niche or rare use case
- Requires major rework of existing code or UX
- Adds a new dependency or third-party service

---

## 📥 Pull Request Guidelines

- Keep changes **scoped and focused**
- Use **descriptive commit messages**
- Match the **existing code style**
- Make sure your PR clearly describes what it does
- Link related issues in the PR description when applicable

For small enhancements or adjustments, you may submit a pull request without creating an issue first.

---

## 💡 Notes for Contributors

- Please base your PRs on the `main` branch by default
- If your PR has conflicts, we may ask you to rebase it onto `dev`
- All contributions are reviewed and merged directly through GitHub
- If you're not sure where to start, head over to the [GitHub Discussions](https://github.com/colivingapp/coliving-app/discussions) — we’d love to help you get involved

---

## 📦 Branches

| Branch      | Use Case                         | Where     | Clean? |
|-------------|----------------------------------|-----------|--------|
| `live`      | Main development & deployment    | GitLab    | ❌     |
| `main`      | Public source of truth           | GitHub    | ✅     |
| `dev`       | Public staging + PR testing      | GitHub    | ✅     |
| `feature/*` | Individual features or hotfixes  | Both      | ✅     |