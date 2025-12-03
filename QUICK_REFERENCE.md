# Quick Reference - Git Commands Cheat Sheet

## 🚀 Daily Workflow (Copy-Paste Ready)

### Before Starting Work
```bash
git checkout siddharth          # Switch to your branch (or raj/prashant)
git fetch origin                # Get latest info
git merge origin/main           # Sync with main
```

### Making Changes
```bash
git add .                       # Stage all changes
git commit -m "Your message"    # Commit
git push origin siddharth       # Push to your branch
```

### Complete Daily Workflow
```bash
# 1. Sync with main
git checkout siddharth
git fetch origin
git merge origin/main

# 2. Make your changes (edit files)

# 3. Commit and push
git add .
git commit -m "Add feature X"
git push origin siddharth
```

---

## 🔍 Status & Information

```bash
git status                      # Check what's changed
git branch                      # List branches (* = current)
git log --oneline              # View commit history
git diff                        # See uncommitted changes
```

---

## 🔄 Branch Operations

```bash
# Switch branch
git checkout branch-name

# Create new branch
git checkout -b new-branch

# List all branches
git branch -a
```

---

## ⚠️ Conflict Resolution

```bash
# If merge conflict occurs:
git status                      # See conflicted files
# Edit files manually to resolve
git add resolved-file.php      # Mark as resolved
git commit                     # Complete merge
```

---

## 🆘 Emergency Commands

```bash
# Discard uncommitted changes
git restore .

# Undo last commit (keep changes)
git reset --soft HEAD~1

# Save work temporarily
git stash
git stash pop                  # Restore later
```

---

## 📋 Branch-Specific Commands

### For Siddharth
```bash
git checkout siddharth
git merge origin/main
git push origin siddharth
```

### For Raj
```bash
git checkout raj
git merge origin/main
git push origin raj
```

### For Prashant
```bash
git checkout prashant
git merge origin/main
git push origin prashant
```

---

## ✅ Golden Rules

1. **Always sync with main before starting work**
2. **Commit frequently with clear messages**
3. **Push your work regularly**
4. **Communicate when working on shared files**
5. **Test before committing**

---

**Need more details? Check `GIT_WORKFLOW_GUIDE.md`**

