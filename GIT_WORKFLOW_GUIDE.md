# Git Workflow Guide - Collaborative Development

This guide ensures smooth collaboration between team members (Siddharth, Raj, and Prashant) without merge conflicts.

## 📋 Table of Contents
1. [Initial Setup](#initial-setup)
2. [Daily Workflow](#daily-workflow)
3. [Branch Strategy](#branch-strategy)
4. [Syncing with Main Branch](#syncing-with-main-branch)
5. [Handling Conflicts](#handling-conflicts)
6. [Best Practices](#best-practices)
7. [Common Commands Reference](#common-commands-reference)

---

## 🚀 Initial Setup

### For New Team Members (First Time Setup)

1. **Clone the repository:**
   ```bash
   git clone https://github.com/siddharthgorinfinitie/samer-customisation.git
   cd samer-customisation
   ```

2. **Checkout your branch:**
   ```bash
   # For Siddharth
   git checkout -b siddharth origin/siddharth

   # For Raj
   git checkout -b raj origin/raj

   # For Prashant
   git checkout -b prashant origin/prashant
   ```

3. **Verify you're on the correct branch:**
   ```bash
   git branch
   # You should see * next to your branch name
   ```

---

## 📅 Daily Workflow

### Step 1: Always Start with Syncing Main Branch

**Before starting any work, sync with the main branch:**

```bash
# 1. Switch to main branch
git checkout main

# 2. Fetch latest changes from remote
git fetch origin

# 3. Pull latest changes
git pull origin main

# 4. Switch back to your branch
git checkout siddharth  # or raj/prashant

# 5. Merge main into your branch to get latest updates
git merge main
```

**Why?** This ensures your branch always has the latest code from main, reducing conflicts later.

### Step 2: Create a Feature Branch (Recommended)

**For each feature/task, create a separate branch from your main branch:**

```bash
# Create and switch to a new feature branch
git checkout -b feature/your-feature-name

# Example:
git checkout -b feature/add-payment-gateway
git checkout -b feature/fix-login-bug
```

**Why?** Keeps your main branch clean and makes it easier to review and merge changes.

### Step 3: Make Your Changes

- Work on your code
- Test thoroughly
- Commit frequently with clear messages

```bash
# Stage your changes
git add .

# Or stage specific files
git add path/to/file1.php path/to/file2.php

# Commit with descriptive message
git commit -m "Add payment gateway integration"
```

### Step 4: Push Your Changes

```bash
# Push to your branch
git push origin siddharth  # or raj/prashant

# If pushing a feature branch for the first time
git push -u origin feature/your-feature-name
```

---

## 🌳 Branch Strategy

### Branch Hierarchy

```
main (production-ready code)
├── siddharth (Siddharth's development branch)
│   ├── feature/feature-1
│   ├── feature/feature-2
│   └── ...
├── raj (Raj's development branch)
│   ├── feature/feature-1
│   └── ...
└── prashant (Prashant's development branch)
    ├── feature/feature-1
    └── ...
```

### Branch Naming Convention

- **Personal branches:** `siddharth`, `raj`, `prashant`
- **Feature branches:** `feature/description` (e.g., `feature/add-shipping-api`)
- **Bug fixes:** `fix/description` (e.g., `fix/login-error`)
- **Hotfixes:** `hotfix/description` (e.g., `hotfix/security-patch`)

---

## 🔄 Syncing with Main Branch

### Regular Sync (Recommended: Daily or Before Major Work)

```bash
# Method 1: Merge main into your branch (Recommended)
git checkout siddharth  # or your branch
git fetch origin
git merge origin/main

# Method 2: Rebase your branch on main (Advanced - cleaner history)
git checkout siddharth
git fetch origin
git rebase origin/main
```

### When to Sync

- ✅ **Before starting new work** - Get latest changes
- ✅ **Before pushing major changes** - Avoid conflicts
- ✅ **Daily** - Stay up to date
- ✅ **After someone merges to main** - Get their changes

---

## ⚠️ Handling Conflicts

### If You Get Merge Conflicts

1. **Identify conflicted files:**
   ```bash
   git status
   # Look for files marked as "both modified"
   ```

2. **Open conflicted files and look for conflict markers:**
   ```
   <<<<<<< HEAD
   Your changes
   =======
   Changes from main
   >>>>>>> origin/main
   ```

3. **Resolve conflicts manually:**
   - Keep your changes
   - Keep their changes
   - Combine both
   - Write new code

4. **After resolving:**
   ```bash
   # Mark conflicts as resolved
   git add conflicted-file.php

   # Complete the merge
   git commit -m "Merge main into siddharth - resolved conflicts"
   ```

### Conflict Resolution Strategies

**Strategy 1: Accept Theirs (When their code is correct)**
```bash
git checkout --theirs path/to/file.php
git add path/to/file.php
```

**Strategy 2: Accept Yours (When your code is correct)**
```bash
git checkout --ours path/to/file.php
git add path/to/file.php
```

**Strategy 3: Manual Resolution (Recommended)**
- Open the file
- Edit to combine both changes correctly
- Save and stage the file

---

## ✅ Best Practices

### 1. **Communication is Key**
   - Inform team members before working on shared files
   - Use GitHub Issues or project management tools
   - Coordinate on major changes

### 2. **Work on Different Files When Possible**
   - Assign specific modules/features to each developer
   - Avoid simultaneous edits to the same file

### 3. **Commit Frequently**
   - Small, focused commits are better than large ones
   - Commit working code (not broken code)
   - Write clear commit messages

### 4. **Pull Before Push**
   - Always sync with main before pushing
   - Reduces conflicts significantly

### 5. **Test Before Committing**
   - Ensure your code works
   - Don't commit broken code

### 6. **Use Feature Branches**
   - Keep your main branch stable
   - Merge feature branches when complete

### 7. **Regular Backups**
   - Push your work frequently
   - Don't keep code only on local machine

---

## 📝 Common Commands Reference

### Daily Commands

```bash
# Check current branch
git branch

# Check status
git status

# View changes
git diff

# View commit history
git log --oneline

# Fetch latest from remote (without merging)
git fetch origin

# Pull latest changes
git pull origin main

# Push your changes
git push origin your-branch-name
```

### Branch Management

```bash
# List all branches
git branch -a

# Create new branch
git checkout -b new-branch-name

# Switch branch
git checkout branch-name

# Delete local branch
git branch -d branch-name

# Delete remote branch
git push origin --delete branch-name
```

### Stashing (Temporary Save)

```bash
# Save current changes temporarily
git stash

# List stashes
git stash list

# Apply last stash
git stash pop

# Apply specific stash
git stash apply stash@{0}

# Clear all stashes
git stash clear
```

### Undoing Changes

```bash
# Discard changes in working directory
git restore file.php

# Discard all uncommitted changes
git restore .

# Undo last commit (keep changes)
git reset --soft HEAD~1

# Undo last commit (discard changes)
git reset --hard HEAD~1
```

---

## 🔀 Merging Your Branch to Main

### When Your Feature is Complete

1. **Ensure your branch is up to date:**
   ```bash
   git checkout siddharth
   git fetch origin
   git merge origin/main
   ```

2. **Resolve any conflicts**

3. **Push your updated branch:**
   ```bash
   git push origin siddharth
   ```

4. **Create Pull Request on GitHub:**
   - Go to: https://github.com/siddharthgorinfinitie/samer-customisation
   - Click "New Pull Request"
   - Select `siddharth` → `main`
   - Add description and create PR

5. **After PR is approved and merged:**
   ```bash
   git checkout main
   git pull origin main
   ```

---

## 🚨 Emergency Procedures

### If You Accidentally Committed to Main

```bash
# Create a branch from current state
git branch backup-branch

# Reset main to match remote
git fetch origin
git reset --hard origin/main

# Switch to your branch and cherry-pick your commit
git checkout siddharth
git cherry-pick backup-branch
```

### If You Need to Revert a Commit

```bash
# Revert last commit (creates new commit)
git revert HEAD

# Revert specific commit
git revert commit-hash
```

---

## 📞 Quick Reference for Each Team Member

### Siddharth's Workflow
```bash
git checkout siddharth
git fetch origin
git merge origin/main
# Make changes
git add .
git commit -m "Description"
git push origin siddharth
```

### Raj's Workflow
```bash
git checkout raj
git fetch origin
git merge origin/main
# Make changes
git add .
git commit -m "Description"
git push origin raj
```

### Prashant's Workflow
```bash
git checkout prashant
git fetch origin
git merge origin/main
# Make changes
git add .
git commit -m "Description"
git push origin prashant
```

---

## 💡 Pro Tips

1. **Use `.gitignore` properly** - Don't commit unnecessary files
2. **Review your changes** - Use `git diff` before committing
3. **Keep commits atomic** - One feature/fix per commit
4. **Write meaningful commit messages** - Future you will thank you
5. **Use GitHub's web interface** - For reviewing PRs and managing issues
6. **Set up branch protection** - Prevent direct pushes to main (optional)

---

## ❓ Troubleshooting

### "Your branch is behind 'origin/main'"
```bash
git fetch origin
git merge origin/main
```

### "Failed to push some refs"
```bash
git pull origin your-branch-name
# Resolve conflicts if any
git push origin your-branch-name
```

### "Merge conflict"
- Follow the [Handling Conflicts](#handling-conflicts) section above

### "Permission denied"
- Check if you have write access to the repository
- Verify your GitHub credentials

---

## 📚 Additional Resources

- [Git Documentation](https://git-scm.com/doc)
- [GitHub Guides](https://guides.github.com/)
- [Atlassian Git Tutorials](https://www.atlassian.com/git/tutorials)

---

**Remember:** Communication and regular syncing are the keys to avoiding conflicts. When in doubt, sync with main first!

