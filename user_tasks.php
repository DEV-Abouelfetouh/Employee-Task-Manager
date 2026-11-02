<?php 
require 'db.php';
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') { header("Location: index.php"); exit; }

$user_id = intval($_GET['id'] ?? 0);
if ($user_id <= 0) { header("Location: admin.php"); exit; }

$stmt = $conn->prepare("SELECT name, role, mobile FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) { header("Location: admin.php"); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#007bff">
    <title>Tasks - <?= htmlspecialchars($user['name']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <div class="header-top">
                <span class="header-title"><?= htmlspecialchars($user['name']) ?></span>
                <span class="header-icon" onclick="openTaskSheet()">➕</span>
            </div>
            <div class="header-search">
                <input type="text" id="searchInput" placeholder="Search task name, details, or status..." oninput="filterTasks(this.value)">
            </div>
        </header>

        <main class="app-content" id="taskList">
            <p class="empty-state">Loading tasks...</p>
        </main>

        <nav class="app-bottom-nav">
            <a href="admin.php" class="nav-item"><span class="nav-icon">👥</span><span>Users</span></a>
            <a href="#" class="nav-item active"><span class="nav-icon">📋</span><span>Tasks</span></a>
            <a href="#" class="nav-item" onclick="event.preventDefault(); confirmAction('Logout?', 'Are you sure you want to log out?', () => window.location.href='logout.php')"><span class="nav-icon">🚪</span><span>Logout</span></a>
        </nav>

        <!-- Bottom Sheet for Task -->
        <div class="sheet-overlay" id="sheetOverlay" onclick="closeTaskSheet()"></div>
        <div class="bottom-sheet" id="bottomSheet">
            <div class="sheet-header">
                <h3 id="sheetTitle">Add New Task</h3>
                <span class="close-sheet" onclick="closeTaskSheet()">✕</span>
            </div>
            <form id="taskForm" class="sheet-form">
                <input type="hidden" id="task_id" name="id" value="">
                <input type="hidden" id="task_user_id" name="user_id" value="<?= $user_id ?>">
                <div class="form-group"><label>Task Name</label><input type="text" id="task_name" name="name" required placeholder="e.g. Fix Login Bug"></div>
                <div class="form-group"><label>Details</label><textarea id="task_details" name="details" rows="3" placeholder="Task description..."></textarea></div>
                <div class="form-row">
                    <div class="form-group"><label>Start Time</label><input type="time" id="task_start" name="start_time" required></div>
                    <div class="form-group"><label>End Time</label><input type="time" id="task_end" name="end_time" required></div>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="task_status" name="status" class="form-select">
                        <option value="pending">Pending</option>
                        <option value="started">Started</option>
                        <option value="done">Done</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary">Save Task</button>
            </form>
        </div>
    </div>

    <div id="toast" class="toast"></div>
    <div class="confirm-overlay" id="confirmOverlay">
        <div class="confirm-box">
            <h4 id="confirmTitle">Are you sure?</h4>
            <p id="confirmMessage">This action cannot be undone.</p>
            <div class="confirm-actions">
                <button class="confirm-btn confirm-cancel" onclick="closeConfirm()">Cancel</button>
                <button class="confirm-btn confirm-delete" id="confirmYesBtn">Yes, Proceed</button>
            </div>
        </div>
    </div>

    <script>
    let allTasks = [];
    document.addEventListener('DOMContentLoaded', loadTasks);

    function loadTasks() {
        fetch('get_user_tasks.php?user_id=<?= $user_id ?>').then(r=>r.json()).then(d=>{
            allTasks = d.tasks || [];
            renderTasks(allTasks);
        });
    }

    function formatTime(timeStr) {
        if (!timeStr) return '';
        const parts = timeStr.split(':');
        return parts[0] + ':' + parts[1];
    }

    function renderTasks(tasks) {
        const container = document.getElementById('taskList');
        if (tasks.length === 0) {
            container.innerHTML = '<div class="empty-state"><p>📭</p><p>No tasks found. Tap ➕ to add one.</p></div>';
            return;
        }
        container.innerHTML = tasks.map(t => `
            <div class="task-card">
                <div class="task-header">
                    <h3 class="task-title">${escapeHtml(t.name)}</h3>
                    <span class="status-badge status-${t.status}">${t.status.charAt(0).toUpperCase() + t.status.slice(1)}</span>
                </div>
                <p class="task-details">${escapeHtml(t.details) || '<em style="color:#999">No details</em>'}</p>
                <div class="task-time">🕒 ${formatTime(t.start_time)} - ${formatTime(t.end_time)}</div>
                <div class="user-actions" style="margin-top:15px">
                    <button class="action-btn btn-edit" onclick="openTaskSheet(${t.id}, '${escapeAttr(t.name)}', '${escapeAttr(t.details)}', '${formatTime(t.start_time)}', '${formatTime(t.end_time)}', '${t.status}')">Edit</button>
                    <button class="action-btn btn-delete" onclick="requestDeleteTask(${t.id}, '${escapeAttr(t.name)}')">Remove</button>
                </div>
            </div>
        `).join('');
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function escapeAttr(str) {
        if (!str) return '';
        return str.replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/"/g,'\\"').replace(/\n/g,'\\n').replace(/\r/g,'');
    }

    function filterTasks(query) {
        const q = query.toLowerCase();
        const filtered = allTasks.filter(t => 
            t.name.toLowerCase().includes(q) || 
            (t.details && t.details.toLowerCase().includes(q)) || 
            t.status.toLowerCase().includes(q)
        );
        renderTasks(filtered);
    }

    function openTaskSheet(id=null, name='', details='', start='', end='', status='pending') {
        document.getElementById('task_id').value = id || '';
        document.getElementById('task_name').value = name;
        document.getElementById('task_details').value = details;
        document.getElementById('task_start').value = start;
        document.getElementById('task_end').value = end;
        document.getElementById('task_status').value = status;
        document.getElementById('sheetTitle').innerText = id ? 'Edit Task' : 'Add New Task';
        document.getElementById('sheetOverlay').classList.add('active');
        document.getElementById('bottomSheet').classList.add('active');
    }

    function closeTaskSheet() {
        document.getElementById('sheetOverlay').classList.remove('active');
        document.getElementById('bottomSheet').classList.remove('active');
    }

    function requestDeleteTask(id, name) {
        confirmAction(`Remove task "${name}"?`, `This will permanently delete this task.`, () => executeDeleteTask(id));
    }

    function executeDeleteTask(id) {
        const formData = new FormData(); formData.append('id', id);
        fetch('delete_task.php', { method: 'POST', body: formData })
        .then(r=>r.json()).then(d=>{
            if(d.success) { showToast('Task deleted successfully', 'success'); loadTasks(); } 
            else { showToast('Failed to delete task', 'error'); }
        });
    }

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        toast.innerText = (type === 'success' ? '✅ ' : '❌ ') + message;
        toast.className = `toast ${type} show`;
        setTimeout(() => { toast.classList.remove('show'); }, 3000);
    }

    let confirmCallback = null;
    function confirmAction(title, message, callback) {
        document.getElementById('confirmTitle').innerText = title;
        document.getElementById('confirmMessage').innerText = message;
        confirmCallback = callback;
        document.getElementById('confirmOverlay').classList.add('active');
    }
    function closeConfirm() { document.getElementById('confirmOverlay').classList.remove('active'); confirmCallback = null; }
    document.getElementById('confirmYesBtn').addEventListener('click', () => { if (confirmCallback) confirmCallback(); closeConfirm(); });

    document.getElementById('taskForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('save_task.php', { method: 'POST', body: formData })
        .then(r=>r.json()).then(d=>{
            if(d.success) { closeTaskSheet(); showToast('Task saved successfully', 'success'); loadTasks(); } 
            else { showToast(d.message, 'error'); }
        });
    });
    </script>
</body>
</html>
