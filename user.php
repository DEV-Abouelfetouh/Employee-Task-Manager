<?php 
require 'db.php';
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'user') { header("Location: index.php"); exit; }

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$user_name = $user ? htmlspecialchars($user['name']) : 'User';

$tasks = $conn->query("SELECT id, name, details, start_time, end_time, status FROM task WHERE user_id = $user_id ORDER BY start_time ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#007bff">
    <title>My Tasks</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <div class="header-top">
                <span class="header-title">Hi, <?= $user_name ?></span>
                <span class="header-icon" onclick="confirmAction('Logout?', 'Are you sure you want to log out?', () => window.location.href='logout.php')">🚪</span>
            </div>
        </header>

        <main class="app-content">
            <?php if ($tasks && $tasks->num_rows > 0): while($task = $tasks->fetch_assoc()): ?>
            <div class="task-card" id="card-<?= $task['id'] ?>">
                <div class="task-header">
                    <h3 class="task-title"><?= htmlspecialchars($task['name']) ?></h3>
                    <span class="status-badge status-<?= htmlspecialchars($task['status']) ?>" id="badge-<?= $task['id'] ?>"><?= ucfirst(htmlspecialchars($task['status'])) ?></span>
                </div>
                <p class="task-details"><?= htmlspecialchars($task['details']) ?></p>
                <div class="task-time">🕒 <?= date('H:i', strtotime($task['start_time'])) ?> - <?= date('H:i', strtotime($task['end_time'])) ?></div>
                <div class="status-actions">
                    <button class="status-btn btn-pending <?= $task['status']=='pending'?'active':'' ?>" onclick="updateStatus(<?= $task['id'] ?>, 'pending')">Pending</button>
                    <button class="status-btn btn-started <?= $task['status']=='started'?'active':'' ?>" onclick="updateStatus(<?= $task['id'] ?>, 'started')">Started</button>
                    <button class="status-btn btn-done <?= $task['status']=='done'?'active':'' ?>" onclick="updateStatus(<?= $task['id'] ?>, 'done')">Done</button>
                </div>
            </div>
            <?php endwhile; else: ?>
            <div class="empty-state"><p>🎉</p><p>No pending tasks!</p></div>
            <?php endif; ?>
        </main>

        <nav class="app-bottom-nav">
            <a href="user.php" class="nav-item active"><span class="nav-icon">📋</span><span>Tasks</span></a>
            <a href="#" class="nav-item" onclick="event.preventDefault(); confirmAction('Logout?', 'Are you sure you want to log out?', () => window.location.href='logout.php')"><span class="nav-icon">🚪</span><span>Logout</span></a>
        </nav>
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
    function updateStatus(taskId, status) {
        const formData = new FormData(); 
        formData.append('task_id', taskId); 
        formData.append('status', status);
        fetch('update_status.php', { method: 'POST', body: formData })
        .then(r=>r.json())
        .then(d=>{
            if (d.success) {
                const badge = document.getElementById('badge-' + taskId);
                badge.className = 'status-badge status-' + status; 
                badge.innerText = status.charAt(0).toUpperCase() + status.slice(1);
                const card = document.getElementById('card-' + taskId);
                card.querySelectorAll('.status-btn').forEach(btn => btn.classList.remove('active'));
                card.querySelector('.btn-' + status).classList.add('active');
                showToast('Task status updated', 'success');
            }
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
    function closeConfirm() { 
        document.getElementById('confirmOverlay').classList.remove('active'); 
        confirmCallback = null; 
    }
    document.getElementById('confirmYesBtn').addEventListener('click', () => { 
        if (confirmCallback) confirmCallback(); 
        closeConfirm(); 
    });
    </script>
</body>
</html>
