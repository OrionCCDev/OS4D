# Quick Reference: Task Reassignment

## 🚀 Quick Start

### Employee Resigning? Here's What to Do:

1. **Go to Users page** → Click **"Reassign"** button next to employee
2. **Select new assignee** from dropdown
3. **Enter reason** (e.g., "Employee resigned")
4. **Check** "Mark user as Inactive" 
5. **Click** "Reassign Selected Tasks"
6. ✅ Done! All tasks transferred with full history

---

## 📋 Common Tasks

### Reassign All Tasks (Employee Leaving)
```
Users → Click "Reassign" → Select new user → Check all tasks → Submit
```

### Reassign One Task
```
Open Task → Click "Reassign" button → Select new user → Submit
```

### Mark User Inactive (Without Reassigning)
```
Users → Status Management → Set to "Inactive" → Add reason → Submit
```

### Reactivate User
```
Users → Status Management → Set to "Active" → Submit
```

---

## 🎯 User Status Options

| Status | Meaning | Can Login? | Use When |
|--------|---------|------------|----------|
| **Active** | Working normally | ✅ Yes | Default state |
| **Inactive** | Temporarily away | ❌ No | Medical leave, vacation |
| **Resigned** | Left company | ❌ No | Permanent departure |

---

## 🔒 Who Can Do What?

| Action | Regular User | Manager | Admin |
|--------|--------------|---------|-------|
| View own tasks | ✅ | ✅ | ✅ |
| Reassign single task | ❌ | ✅ | ✅ |
| Bulk reassign tasks | ❌ | ✅ | ✅ |
| Change user status | ❌ | ✅ | ✅ |

---

## 📍 Where to Find Features

### Users Page
`/admin/users`
- View all users
- See user status
- Click "Reassign" button

### Bulk Reassignment Page
`/users/{user_id}/bulk-reassignment`
- Reassign all tasks from one user
- Deactivate user account

### Task Details Page
`/tasks/{task_id}`
- Reassign individual task
- View task history

---

## ⚠️ Important Rules

1. **Never Delete Users** - Use status instead
2. **Always Add Reason** - Document why tasks are reassigned
3. **Review Task Priority** - Urgent tasks first
4. **Check New Assignee Workload** - Don't overload one person
5. **Communicate** - Let team know about changes

---

## 🆘 Troubleshooting

### "No tasks to reassign"
→ User has no active tasks (all completed/cancelled)

### "Cannot select new assignee"
→ No other active users in system

### "Reassignment failed"
→ Check user permissions and database connection

---

## 💡 Pro Tips

- Use bulk reassignment for employee departures
- Use individual reassignment for specific task changes
- Mark as "inactive" for temporary absences
- Mark as "resigned" for permanent departures
- Always include clear reasons for audit trail
- Check task history to see all reassignments

---

## 📊 Migration Command

After deploying code, run:
```bash
php artisan migrate
```

This adds the user status fields to the database.

---

## 🎬 Example: Employee Resignation

**Before:**
- John has 10 active tasks
- John is resigning

**Steps:**
1. Users → Find John → Click "Reassign"
2. New Assignee: Sarah
3. Reason: "John resigned - effective 10/20/2025"
4. ✅ Check "Mark John as Inactive"
5. Click "Reassign Selected Tasks"

**After:**
- Sarah has 10 new tasks
- Sarah gets 10 notifications
- John marked as "resigned"
- Full history preserved
- Task continuity maintained

---

## 📞 Need Help?

Check the full documentation: `TASK_REASSIGNMENT_EMPLOYEE_MANAGEMENT.md`

