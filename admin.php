<?php 
require 'db.php';
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') { header("Location: index.php"); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#007bff">
    <title>User Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <div class="header-top">
                <span>User Management</span>
                <span class="header-icon" onclick="openUserSheet()">➕</span>
            </div>
            <div class="header-search">
                <input type="text" id="searchInput" placeholder="Search name, role, or mobile..." oninput="filterUsers(this.value)">
            </div>
        </header>

        <main class="app-content" id="userList">
            <p class="empty-state">Loading users...</p>
        </main>

        <nav class="app-bottom-nav">
            <a href="admin.php" class="nav-item active"><span class="nav-icon">👥</span><span>Management</span></a>
            <a href="#" class="nav-item" onclick="event.preventDefault(); confirmAction('Logout?', 'Are you sure you want to log out?', () => window.location.href='logout.php')"><span class="nav-icon">🚪</span><span>Logout</span></a>
        </nav>

        <!-- Bottom Sheet for User -->
        <div class="sheet-overlay" id="sheetOverlay" onclick="closeUserSheet()"></div>
        <div class="bottom-sheet" id="bottomSheet">
            <div class="sheet-header">
                <h3 id="sheetTitle">Add New User</h3>
                <span class="close-sheet" onclick="closeUserSheet()">✕</span>
            </div>
            <form id="userForm" class="sheet-form">
                <input type="hidden" id="user_id" name="id" value="">
                <div class="form-group"><label>Full Name</label><input type="text" id="user_name" name="name" required placeholder="e.g. John Doe"></div>
                <div class="form-group"><label>Role</label><input type="text" id="user_role" name="role" required placeholder="e.g. Developer"></div>
                <div class="form-group"><label>Mobile Number</label><input type="text" id="user_mobile" name="mobile" required placeholder="e.g. 2000000001"></div>
                <div class="form-group"><label>Password <small>(Leave blank to keep current)</small></label><input type="password" id="user_password" name="password" placeholder="Enter new password"></div>
                <button type="submit" class="btn-primary">Save User</button>
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
    let allUsers = [];
    document.addEventListener('DOMContentLoaded', loadUsers);

    function loadUsers() {
        fetch('get_users.php').then(r=>r.json()).then(d=>{
            allUsers = d.users;
            renderUsers(allUsers);
        });
    }

    function renderUsers(users) {
        const container = document.getElementById('userList');
        if (users.length === 0) {
            container.innerHTML = '<p class="empty-state">No users found.</p>';
            return;
        }
        container.innerHTML = users.map(u => `
            <div class="user-card clickable" onclick="window.location.href='user_tasks.php?id=${u.id}'">
                <div class="user-header">
                    <h3 class="user-name">${u.name}</h3>
                    <span class="task-count-badge">${u.task_count} Task${u.task_count != 1 ? 's' : ''}</span>
                </div>
                <div class="user-details">
                    <span>🏷️ ${u.role}</span>
                    <span>📱 ${u.mobile}</span>
                </div>
                <div class="user-actions" onclick="event.stopPropagation()">
                    <button class="action-btn btn-edit" onclick="openUserSheet(${u.id}, '${u.name.replace(/'/g, "\\'")}', '${u.role.replace(/'/g, "\\'")}', '${u.mobile}')">Edit</button>
                    <button class="action-btn btn-delete" onclick="requestDeleteUser(${u.id}, '${u.name.replace(/'/g, "\\'")}')">Remove</button>
                </div>
            </div>
        `).join('');
    }

    function filterUsers(query) {
        const q = query.toLowerCase();
        const filtered = allUsers.filter(u => u.name.toLowerCase().includes(q) || u.role.toLowerCase().includes(q) || u.mobile.includes(q));
        renderUsers(filtered);
    }

    function openUserSheet(id=null, name='', role='', mobile='') {
        document.getElementById('user_id').value = id || '';
        document.getElementById('user_name').value = name;
        document.getElementById('user_role').value = role;
        document.getElementById('user_mobile').value = mobile;
        document.getElementById('user_password').value = '';
        document.getElementById('sheetTitle').innerText = id ? 'Edit User' : 'Add New User';
        document.getElementById('sheetOverlay').classList.add('active');
        document.getElementById('bottomSheet').classList.add('active');
    }

    function closeUserSheet() {
        document.getElementById('sheetOverlay').classList.remove('active');
        document.getElementById('bottomSheet').classList.remove('active');
    }

    function requestDeleteUser(id, name) {
        confirmAction(`Remove "${name}"?`, `This will permanently delete this user and ALL tasks related to them.`, () => executeDelete(id));
    }

    function executeDelete(id) {
        const formData = new FormData(); formData.append('id', id);
        fetch('delete_user.php', { method: 'POST', body: formData })
        .then(r=>r.json()).then(d=>{
            if(d.success) { showToast('User and related tasks deleted', 'success'); loadUsers(); } 
            else { showToast('Failed to delete user', 'error'); }
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

    document.getElementById('userForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('save_user.php', { method: 'POST', body: formData })
        .then(r=>r.json()).then(d=>{
            if(d.success) { closeUserSheet(); showToast('User saved successfully', 'success'); loadUsers(); } 
            else { showToast(d.message, 'error'); }
        });
    });
    </script>
</body>
</html>
