Aight I've read the whole thing. Here's the full picture of how to use GSD with OpenCode on your existing club app.

---

## Using GSD with OpenCode on an Existing Project

### Step 1: Install GSD for OpenCode

```bash
npx get-shit-done-cc@latest --opencode --global
```

That puts the commands in `~/.config/opencode/`. Restart OpenCode after that.

---

### Step 2: Map Your Existing Codebase First

Since you already have code (even if it's just blade views with dummy content), you don't want GSD to start from scratch and assume nothing exists. Run this **first**:

```
/gsd-map-codebase
```

This spawns parallel agents that scan your whole repo — stack, architecture, what files exist, what's implemented vs dummy. It creates a codebase map that the next command reads.

---

### Step 3: Initialize the Project

```
/gsd-new-project
```

It will see the codebase map and ask you focused questions — goals, features you want, what's already there vs what needs building. It'll produce `PROJECT.md`, `REQUIREMENTS.md`, and a `ROADMAP.md` with phases.

This is basically the "plan.md" you used before, but structured for GSD's whole workflow. Once this is done, OpenCode knows what the app is supposed to be.

---

### Step 4: Build Phase by Phase

For each phase (e.g. CTF management, user auth, events, etc.):

```
/gsd-discuss-phase 1   # tell it your preferences for this feature
/gsd-plan-phase 1      # it researches + creates task plans
/gsd-execute-phase 1   # it actually writes the code
/gsd-verify-work 1     # you confirm it works
```

---

### For Adding a Single New Feature (Your Main Question)

When you just want to implement one specific thing without going through the whole phase flow — say you want to add CTF submission tracking right now — use:

```
/gsd-quick
```

It asks: *"What do you want to do?"* You say something like: `"Implement CTF challenges page with a Livewire component that lists challenges, lets members mark them as solved, and tracks points per member"`. It plans and executes without the full ceremony.

If it's something tiny (typo fix, add a column): `/gsd-fast "add is_solved column to ctf_challenges table with migration"`

If you're not sure how to approach it technically: `/gsd-quick --research` — spawns a researcher first before planning.

---

### Quick Reference for Your Situation

| Situation | Command |
|---|---|
| First time setup on existing repo | `/gsd-map-codebase` → `/gsd-new-project` |
| Building a full feature (planned) | `/gsd-discuss-phase N` → `/gsd-plan-phase N` → `/gsd-execute-phase N` |
| Adding one feature ad-hoc | `/gsd-quick` |
| Tiny thing, no planning needed | `/gsd-fast "description"` |
| Lost, don't know what's next | `/gsd-next` or `/gsd-progress` |
| Something broke | `/gsd-debug "describe the problem"` |

---

The key difference from your old plan.md workflow: GSD maintains `STATE.md` across sessions, so even after you close OpenCode and come back, it remembers where you were. You don't have to re-explain the project every time.
